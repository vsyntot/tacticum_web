#!/usr/bin/env python3
"""Fixture-only dual-BASE finalizer for the multisite release lifecycle.

The finalizer never performs application writes.  It accepts only controller
evidence in ``applied_unverified``/``converged_unverified`` state, requires
successful smoke and monitoring evidence, and promotes the candidate manifest
to two independent private fixture domains using a compare-and-swap check.
"""

from __future__ import annotations

import argparse
import base64
import fcntl
import hashlib
import json
import os
from pathlib import Path
import stat
import tempfile
from typing import Any


FINALIZER_VERSION = "multisite-finalizer-v2-fixture.1"
CONTROLLER_VERSION = "multisite-controller-v2-fixture.1"
HEX64 = set("0123456789abcdef")


def require(condition: bool, message: str) -> None:
    if not condition:
        raise RuntimeError(message)


def canonical(value: Any) -> bytes:
    return json.dumps(value, sort_keys=True, separators=(",", ":"), ensure_ascii=False).encode()


def digest(value: Any) -> str:
    return hashlib.sha256(canonical(value)).hexdigest()


def sha_bytes(value: bytes) -> str:
    return hashlib.sha256(value).hexdigest()


def read_json(path: Path) -> dict[str, Any]:
    value = json.loads(path.read_text())
    require(isinstance(value, dict), f"JSON object required: {path}")
    return value


def sha_file(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def parse_hex(value: Any, name: str, lengths: tuple[int, ...] = (64,)) -> str:
    require(isinstance(value, str) and len(value) in lengths and set(value) <= HEX64, f"invalid {name}")
    return value


def validate_manifest(manifest: dict[str, Any]) -> str:
    value = manifest.get("manifestSha256")
    parse_hex(value, "manifestSha256")
    payload = {key: item for key, item in manifest.items() if key != "manifestSha256"}
    require(digest(payload) == value, "candidate manifest integrity")
    require(manifest.get("schemaVersion") == 2 and manifest.get("treeKind") in {"candidate", "prod"},
            "multisite manifest role")
    require(isinstance(manifest.get("entries"), list) and isinstance(manifest.get("tombstones"), list),
            "manifest inventory")
    return value


def load_current(directory: Path) -> tuple[bytes | None, dict[str, Any] | None]:
    path = directory / "current.json"
    if not path.exists():
        return None, None
    require(path.is_file() and not path.is_symlink(), "BASE copy must be a regular file")
    raw = path.read_bytes()
    return raw, json.loads(raw)


class DualBaseFinalizer:
    def __init__(self, operational: Path, durable: Path, state: Path) -> None:
        self.operational = Path(operational).resolve()
        self.durable = Path(durable).resolve()
        self.state = Path(state).resolve()
        for directory in (self.operational, self.durable, self.state):
            require(directory.is_dir() and directory == Path(directory), "BASE domain must be canonical")
            require(stat.S_IMODE(directory.stat().st_mode) == 0o700, "BASE domains must be private")
        all_paths = [self.operational, self.durable, self.state]
        require(all(a != b and a not in b.parents and b not in a.parents
                    for index, a in enumerate(all_paths) for b in all_paths[index + 1:]),
                "BASE domains must be independent")
        self.lock_path = self.state / "finalizer.lock"
        self.journal_path = self.state / "promotion.json"

    def lock(self):
        handle = self.lock_path.open("a")
        try:
            fcntl.flock(handle, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BaseException:
            handle.close()
            raise RuntimeError("BASE promotion lock is busy")
        return handle

    def _validate_evidence(self, evidence: dict[str, Any], candidate: dict[str, Any]) -> None:
        validate_manifest(candidate)
        require(candidate.get("treeKind") == "candidate", "candidate manifest required")
        require(evidence.get("status") in {"applied_unverified", "converged_unverified"},
                "release is not eligible for finalization")
        require(evidence.get("controllerVersion") == CONTROLLER_VERSION, "controller binding mismatch")
        require(evidence.get("finalizerVersion") in {None, FINALIZER_VERSION}, "finalizer version mismatch")
        require(evidence.get("candidateManifestSha256") == candidate["manifestSha256"]
                and evidence.get("postManifestSha256") == candidate["manifestSha256"],
                "post manifest does not match candidate")
        require(evidence.get("productionApproved") is False, "controller evidence must remain unverified")
        for field in ("smoke", "monitoring", "cacheClear"):
            item = evidence.get(field)
            require(isinstance(item, dict) and item.get("passed") is True, f"{field} evidence failed")
            require(item.get("mutations", False) is False, f"{field} reports mutation")
        require(isinstance(evidence.get("releaseId"), str) and evidence["releaseId"], "release id required")
        require(isinstance(evidence.get("planId"), str) and len(evidence["planId"]) == 64, "plan binding required")
        parse_hex(evidence.get("artifactSha256"), "artifactSha256")
        parse_hex(evidence.get("commitSha"), "commitSha", (40, 64)) if evidence.get("commitSha") else None
        require(isinstance(evidence.get("scopeVersion"), str) and evidence["scopeVersion"], "scope binding required")
        require(isinstance(evidence.get("schemaProfile"), str) and evidence["schemaProfile"], "schema binding required")
        require(isinstance(evidence.get("phpVersion"), str) and evidence["phpVersion"].startswith("8."),
                "PHP binding required")

    def _current(self) -> tuple[bytes | None, dict[str, Any] | None, int]:
        first_raw, first = load_current(self.operational)
        second_raw, second = load_current(self.durable)
        require((first_raw is None) == (second_raw is None), "one BASE copy is missing")
        require(first_raw == second_raw, "independent BASE copies differ")
        if first is None:
            return None, None, 0
        require(first.get("kind") == "multisite-base-record", "invalid BASE record")
        parse_hex(first.get("manifestSha256"), "BASE manifestSha256")
        require(digest({key: value for key, value in first.items() if key != "manifestSha256"})
                == first["manifestSha256"], "BASE record integrity")
        sequence = first.get("attestation", {}).get("sequence")
        require(isinstance(sequence, int) and sequence >= 1, "invalid BASE sequence")
        return first_raw, first, sequence

    def _base_record(self, candidate: dict[str, Any], evidence: dict[str, Any], sequence: int) -> dict[str, Any]:
        payload = {key: value for key, value in candidate.items() if key != "manifestSha256"}
        payload["treeKind"] = "prod"
        payload["kind"] = "multisite-base-record"
        payload["attestation"] = {
            "sequence": sequence,
            "releaseId": evidence["releaseId"],
            "planId": evidence["planId"],
            "artifactSha256": evidence["artifactSha256"],
            "candidateManifestSha256": candidate["manifestSha256"],
            "postManifestSha256": evidence["postManifestSha256"],
            "scopeVersion": evidence["scopeVersion"],
            "controllerVersion": evidence["controllerVersion"],
            "finalizerVersion": FINALIZER_VERSION,
            "commitSha": evidence.get("commitSha"),
            "schemaProfile": evidence["schemaProfile"],
            "phpVersion": evidence["phpVersion"],
        }
        return {**payload, "manifestSha256": digest(payload)}

    def _atomic_write(self, path: Path, raw: bytes) -> None:
        fd, temporary = tempfile.mkstemp(prefix=".base-", dir=str(path.parent))
        try:
            with os.fdopen(fd, "wb") as output:
                output.write(raw)
                output.flush()
                os.fsync(output.fileno())
            os.chmod(temporary, 0o600)
            os.replace(temporary, path)
        finally:
            Path(temporary).unlink(missing_ok=True)

    def promote(self, evidence: dict[str, Any], candidate: dict[str, Any],
                expected_previous_sha256: str | None = None,
                failure_after: str | None = None) -> dict[str, Any]:
        self._validate_evidence(evidence, candidate)
        with self.lock():
            old_raw, old_record, old_sequence = self._current()
            old_sha = sha_file(self.operational / "current.json") if old_raw is not None else None
            expected = evidence.get("previousBaseSha256", expected_previous_sha256)
            require(expected == old_sha, "BASE CAS compare-and-swap mismatch")
            old_release = old_record.get("attestation", {}).get("releaseId") if old_record else None
            require(evidence.get("previousReleaseId", old_release) == old_release,
                    "active release compare-and-swap mismatch")
            requested_sequence = evidence.get("sequence", old_sequence + 1)
            require(requested_sequence == old_sequence + 1, "BASE sequence is not monotonic")
            new_record = self._base_record(candidate, evidence, requested_sequence)
            raw = canonical(new_record)
            journal = {
                "schemaVersion": 1, "status": "promoting", "finalizerVersion": FINALIZER_VERSION,
                "releaseId": evidence["releaseId"], "sequence": requested_sequence,
                "previousSha256": old_sha, "newSha256": sha_bytes(raw),
                "operational": str(self.operational), "durable": str(self.durable),
                "oldRaw": base64.b64encode(old_raw).decode() if old_raw is not None else None,
            }
            self.journal_path.write_bytes(canonical(journal))
            try:
                self._atomic_write(self.operational / "current.json", raw)
                if failure_after == "operational":
                    raise OSError("injected BASE promotion failure")
                self._atomic_write(self.durable / "current.json", raw)
                require((self.operational / "current.json").read_bytes() == (self.durable / "current.json").read_bytes(),
                        "dual BASE copies differ after write")
                journal["status"] = "verified"
                journal["verifiedSha256"] = sha_bytes(raw)
                self.journal_path.write_bytes(canonical(journal))
                return {"status": "verified", "sequence": requested_sequence,
                        "baseManifestSha256": new_record["manifestSha256"],
                        "dualCopySha256": sha_bytes(raw), "productionMutationAllowed": False}
            except BaseException:
                journal["status"] = "failed_requires_recovery"
                self.journal_path.write_bytes(canonical(journal))
                raise

    def recover(self) -> dict[str, Any]:
        require(self.journal_path.exists(), "no BASE promotion journal")
        with self.lock():
            journal = read_json(self.journal_path)
            require(journal.get("status") == "failed_requires_recovery", "no failed BASE promotion")
            require(journal.get("schemaVersion") == 1 and journal.get("finalizerVersion") == FINALIZER_VERSION,
                    "BASE journal version mismatch")
            require(journal.get("operational") == str(self.operational)
                    and journal.get("durable") == str(self.durable), "BASE journal target mismatch")
            old_raw = base64.b64decode(journal["oldRaw"], validate=True) if journal.get("oldRaw") is not None else None
            old_sha = sha_bytes(old_raw) if old_raw is not None else None
            require(journal.get("previousSha256") == old_sha, "BASE journal previous digest mismatch")
            new_sha = parse_hex(journal.get("newSha256"), "BASE journal newSha256")
            for directory in (self.operational, self.durable):
                current = directory / "current.json"
                if current.exists():
                    require(sha_file(current) in {value for value in (old_sha, new_sha) if value is not None},
                            "BASE recovery found foreign drift")
            for directory in (self.operational, self.durable):
                target = directory / "current.json"
                if old_raw is None:
                    target.unlink(missing_ok=True)
                else:
                    self._atomic_write(target, old_raw)
            first, second = load_current(self.operational)[0], load_current(self.durable)[0]
            require(first == second == old_raw, "BASE recovery mismatch")
            journal["status"] = "recovered"
            self.journal_path.write_bytes(canonical(journal))
            return journal


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=["promote", "recover"])
    parser.add_argument("--operational", type=Path, required=True)
    parser.add_argument("--durable", type=Path, required=True)
    parser.add_argument("--state", type=Path, required=True)
    parser.add_argument("--evidence", type=Path)
    parser.add_argument("--candidate", type=Path)
    parser.add_argument("--isolated", action="store_true", help="required: no production/base transport is available")
    args = parser.parse_args()
    try:
        require(args.isolated, "fixture-only finalizer requires --isolated")
        finalizer = DualBaseFinalizer(args.operational, args.durable, args.state)
        if args.command == "recover":
            result = finalizer.recover()
        else:
            require(args.evidence is not None and args.candidate is not None, "--evidence and --candidate required")
            result = finalizer.promote(read_json(args.evidence), read_json(args.candidate))
        print(json.dumps(result, sort_keys=True))
        return 0
    except Exception as error:
        print(f"Multisite release finalizer stopped: {error}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
