#!/usr/bin/env python3
"""Fixture-only trusted controller for the multisite FILE_ONLY contract.

This controller is deliberately not wired to SSH or production.  It accepts an
already approved, immutable package, validates both roots and performs the
smallest possible file transaction in isolated directories.  A successful
apply is always ``applied_unverified``; smoke, monitoring and BASE promotion
belong to ``multisite-release-finalizer.py``.
"""

from __future__ import annotations

import argparse
import datetime as dt
import fcntl
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import shutil
import stat
import tarfile
import tempfile
from typing import Any


CONTROLLER_VERSION = "multisite-controller-v2-fixture.1"
HEX64 = set("0123456789abcdef")


def fail(message: str) -> None:
    raise RuntimeError(message)


def require(condition: bool, message: str) -> None:
    if not condition:
        fail(message)


def canonical(value: Any) -> bytes:
    return json.dumps(value, sort_keys=True, separators=(",", ":"), ensure_ascii=False).encode()


def sha_bytes(value: bytes) -> str:
    return hashlib.sha256(value).hexdigest()


def sha_json(value: Any) -> str:
    return sha_bytes(canonical(value))


def read_json(path: Path) -> dict[str, Any]:
    value = json.loads(path.read_text())
    require(isinstance(value, dict), f"JSON object required: {path}")
    return value


def digest_bound(value: dict[str, Any], field: str) -> str:
    digest = value.get(field)
    payload = {key: item for key, item in value.items() if key != field}
    require(isinstance(digest, str) and len(digest) == 64 and set(digest) <= HEX64,
            f"invalid {field}")
    require(sha_json(payload) == digest, f"{field} integrity")
    return digest


def safe_relative(value: str) -> PurePosixPath:
    path = PurePosixPath(value)
    require(isinstance(value, str) and value and str(path) == value and not path.is_absolute()
            and ".." not in path.parts and "\\" not in value
            and all(ord(char) >= 32 and ord(char) != 127 for char in value),
            f"unsafe path: {value!r}")
    return path


def root_path(roots: dict[str, Path], root: str, relative: str) -> Path:
    require(root in roots, f"unknown root: {root}")
    rel = safe_relative(relative)
    current = roots[root]
    require(current.is_dir() and current.resolve() == current, "root changed")
    for part in rel.parts:
        current = current / part
        require(not current.is_symlink(), f"symlink path: {root}/{relative}")
    return current


def entry_key(entry: dict[str, Any]) -> str:
    return f"{entry['root']}/{entry['path']}"


def expected_entry(entry: dict[str, Any] | None) -> dict[str, Any] | None:
    if entry is None:
        return None
    require(entry.get("type") == "file", "only regular files are deployable")
    require(entry.get("root") in {"primary", "divatech"}, "invalid manifest root")
    safe_relative(entry.get("path"))
    parts = PurePosixPath(entry["path"]).parts
    if entry["root"] == "primary":
        require("bitrix" not in parts and "upload" not in parts, "platform/runtime path in primary artifact")
    else:
        require("local" not in parts and "bitrix" not in parts and "upload" not in parts,
                "shared/runtime path in secondary artifact")
    digest = entry.get("sha256")
    require(isinstance(digest, str) and len(digest) == 64 and set(digest) <= HEX64, "invalid entry digest")
    require(isinstance(entry.get("size"), int) and entry["size"] >= 0, "invalid entry size")
    mode = entry.get("mode")
    require(isinstance(mode, str) and len(mode) == 4 and mode[0] == "0"
            and all(char in "01234567" for char in mode) and not (int(mode, 8) & 0o7000),
            "invalid entry mode")
    return {key: entry[key] for key in ("root", "path", "type", "sha256", "size", "mode")}


def validate_manifest(manifest: dict[str, Any], tree_kind: str) -> str:
    digest = digest_bound(manifest, "manifestSha256")
    require(manifest.get("schemaVersion") == 2 and manifest.get("treeKind") == tree_kind,
            f"manifest must be schema v2 {tree_kind}")
    require(isinstance(manifest.get("entries"), list), "manifest entries required")
    seen: set[str] = set()
    previous = ""
    for raw in manifest["entries"]:
        entry = expected_entry(raw)
        key = entry_key(entry)
        require(key not in seen, "duplicate manifest entry")
        require(key > previous, "manifest entries must be sorted")
        seen.add(key)
        previous = key
    tombstones = manifest.get("tombstones")
    require(isinstance(tombstones, list), "manifest tombstones required")
    for item in tombstones:
        require(item.get("root") == "primary", "tombstone root")
        safe_relative(item.get("path"))
    return digest


def entry_map(manifest: dict[str, Any]) -> dict[str, dict[str, Any]]:
    return {entry_key(entry): entry for entry in manifest["entries"]}


def observe(path: Path) -> dict[str, Any] | None:
    if not path.exists() and not path.is_symlink():
        return None
    require(not path.is_symlink() and path.is_file(), f"target is not a regular file: {path}")
    data = path.read_bytes()
    return {"sha256": sha_bytes(data), "size": len(data),
            "mode": format(stat.S_IMODE(path.stat().st_mode), "04o"),
            "uid": path.stat().st_uid, "gid": path.stat().st_gid}


def entry_observed(path: Path, entry: dict[str, Any] | None) -> bool:
    actual = observe(path)
    if entry is None:
        return actual is None
    return actual is not None and all(actual[key] == entry[key] for key in ("sha256", "size", "mode"))


class TrustedController:
    """Apply an approved package to two explicitly isolated roots."""

    def __init__(self, roots: dict[str, Path], state: Path,
                 owner_uid: int | None = None, owner_gid: int | None = None,
                 min_free_bytes: int = 0, min_free_inodes: int = 0) -> None:
        require(set(roots) == {"primary", "divatech"}, "two explicit roots required")
        self.roots = {name: Path(value).resolve() for name, value in roots.items()}
        self.state = Path(state).resolve()
        require(self.state.is_dir() and self.state == Path(state), "state must be canonical")
        require(stat.S_IMODE(self.state.stat().st_mode) == 0o700, "private state required")
        for root in self.roots.values():
            require(root.is_dir() and root == Path(root), "root must be canonical directory")
        all_paths = [self.state, *self.roots.values()]
        require(all(a != b and a not in b.parents and b not in a.parents
                    for index, a in enumerate(all_paths) for b in all_paths[index + 1:]),
                "overlapping controller directories")
        self.owner_uid = os.getuid() if owner_uid is None else owner_uid
        self.owner_gid = os.getgid() if owner_gid is None else owner_gid
        self.min_free_bytes = min_free_bytes
        self.min_free_inodes = min_free_inodes
        self.lock_path = self.state / "controller.lock"
        self.journal_path = self.state / "journal.json"

    def lock(self):
        handle = self.lock_path.open("a")
        try:
            fcntl.flock(handle, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BaseException:
            handle.close()
            raise RuntimeError("controller lock is busy")
        return handle

    def _disk_check(self) -> None:
        stats = os.statvfs(self.state)
        require(stats.f_bavail * stats.f_frsize >= self.min_free_bytes, "insufficient free disk space")
        require(stats.f_favail >= self.min_free_inodes, "insufficient free inodes")

    def _validate_package(self, package: dict[str, Any]) -> tuple[dict[str, Any], dict[str, Any], dict[str, Any], dict[str, Any]]:
        plan = package["plan"]
        approval = package["approval"]
        prod = package["prod"]
        candidate = package["candidate"]
        plan_id = digest_bound(plan, "planId")
        prod_hash = validate_manifest(prod, "prod")
        candidate_hash = validate_manifest(candidate, "candidate")
        require(approval.get("release_class") == "FILE_ONLY", "release class must be FILE_ONLY")
        require(approval.get("production_mutation_allowed") is True, "explicit production approval required")
        require(approval.get("plan_id") == plan_id and approval.get("prod_manifest_sha256") == prod_hash
                and approval.get("candidate_manifest_sha256") == candidate_hash, "approval binding mismatch")
        require(approval.get("base_manifest_sha256") == plan.get("baseManifestSha256"),
                "approval BASE binding mismatch")
        require(approval.get("controller_version") == CONTROLLER_VERSION, "controller version mismatch")
        require(plan.get("planKind") == "DRIFT_COMPARISON_ONLY" and plan.get("productionMutationAllowed") is False,
                "plan must be comparison-only")
        require(plan.get("driftResolved") is True and all(change.get("status") in {"LOCAL_CHANGE", "CONVERGED"}
                                                           for change in plan.get("changes", [])),
                "unresolved production drift")
        require(plan.get("prodManifestSha256") == prod_hash and plan.get("candidateManifestSha256") == candidate_hash,
                "plan manifest binding mismatch")
        require(plan.get("baseManifestSha256") is None or
                (isinstance(plan.get("baseManifestSha256"), str)
                 and len(plan["baseManifestSha256"]) == 64 and set(plan["baseManifestSha256"]) <= HEX64),
                "invalid plan BASE binding")
        require(prod.get("scopeVersion") == candidate.get("scopeVersion") == approval.get("scope_version"),
                "scope version mismatch")
        require(isinstance(prod.get("scopeSha256"), str) and len(prod["scopeSha256"]) == 64
                and prod.get("scopeSha256") == candidate.get("scopeSha256"), "scope identity mismatch")
        require(prod.get("platform") == candidate.get("platform"), "platform identity mismatch")
        expires = approval.get("expires_at")
        require(isinstance(expires, str), "approval expiry required")
        try:
            expiry = dt.datetime.fromisoformat(expires.replace("Z", "+00:00"))
        except ValueError as error:
            raise RuntimeError("invalid approval expiry") from error
        require(dt.datetime.now(dt.timezone.utc) < expiry, "approval expired")
        return plan, approval, prod, candidate

    def _extract(self, artifact: Path, candidate: dict[str, Any], artifact_expected: str) -> Path:
        require(artifact.is_file() and sha_bytes(artifact.read_bytes()) == artifact_expected, "artifact integrity")
        destination = Path(tempfile.mkdtemp(prefix="candidate-", dir=self.state))
        expected = {entry_key(entry): entry for entry in candidate["entries"]}
        seen: set[str] = set()
        try:
            with tarfile.open(artifact, "r:gz") as archive:
                for member in archive:
                    name = member.name
                    safe_relative(name)
                    require(member.isfile() and not member.issym() and not member.islnk(), "unsafe archive member")
                    require(name.count("/") >= 1, "archive root missing")
                    require(name in expected and name not in seen, "archive member outside candidate")
                    entry = expected[name]
                    require(member.size == entry["size"], "archive size mismatch")
                    target = destination / name
                    target.parent.mkdir(parents=True, exist_ok=True)
                    source = archive.extractfile(member)
                    require(source is not None, "archive member unreadable")
                    with target.open("xb") as output:
                        shutil.copyfileobj(source, output)
                    target.chmod(int(entry["mode"], 8))
                    seen.add(name)
            require(seen == set(expected), "archive manifest set mismatch")
            for name, entry in expected.items():
                target = destination / name
                require(entry_observed(target, entry), f"staged candidate mismatch: {name}")
            return destination
        except BaseException:
            shutil.rmtree(destination, ignore_errors=True)
            raise

    def _operations(self, plan: dict[str, Any], prod: dict[str, Any], candidate: dict[str, Any]) -> list[dict[str, Any]]:
        prod_map, candidate_map = entry_map(prod), entry_map(candidate)
        operations: list[dict[str, Any]] = []
        for change in plan.get("changes", []):
            if change.get("status") != "LOCAL_CHANGE":
                continue
            operation = change.get("operation")
            root = change.get("root")
            path = change.get("path")
            require(root in self.roots and isinstance(path, str), "change root/path")
            safe_relative(path)
            if operation == "rename":
                source = change.get("fromPath") or change.get("from")
                require(isinstance(source, str), "rename source required")
                safe_relative(source)
                source_key, target_key = f"{root}/{source}", f"{root}/{path}"
                before, after = prod_map.get(source_key), candidate_map.get(target_key)
                require(before is not None and after is not None and target_key not in prod_map,
                        "invalid rename entries")
                operations.extend([
                    {"root": root, "path": source, "before": before, "after": None, "operation": "delete", "group": "rename"},
                    {"root": root, "path": path, "before": None, "after": after, "operation": "create", "group": "rename"},
                ])
                continue
            before = change.get("before")
            after = change.get("after")
            expected_before = prod_map.get(f"{root}/{path}")
            expected_after = candidate_map.get(f"{root}/{path}")
            require((before is None) == (expected_before is None)
                    and (after is None) == (expected_after is None), "change manifest binding")
            if expected_before is not None:
                require(before["sha256"] == expected_before["sha256"], "change before digest")
            if expected_after is not None:
                require(after["sha256"] == expected_after["sha256"], "change after digest")
            derived = "create" if expected_before is None else "delete" if expected_after is None else "replace"
            require(operation in {None, derived}, f"invalid operation: {operation}")
            operations.append({"root": root, "path": path, "before": expected_before, "after": expected_after,
                               "operation": derived})
        return operations

    def _verify_manifest_entries(self, manifest: dict[str, Any]) -> None:
        for entry in manifest["entries"]:
            path = root_path(self.roots, entry["root"], entry["path"])
            require(entry_observed(path, entry), f"production manifest drift: {entry_key(entry)}")
        for tombstone in manifest["tombstones"]:
            path = root_path(self.roots, tombstone["root"], tombstone["path"])
            require(not path.exists() and not path.is_symlink(), f"tombstone reappeared: {entry_key(tombstone)}")

    def _write_file(self, path: Path, data: bytes, mode: str, uid: int, gid: int) -> list[Path]:
        parent = path.parent
        missing: list[Path] = []
        while not parent.exists():
            require(not parent.is_symlink(), "symlink parent")
            missing.append(parent)
            parent = parent.parent
        for directory in reversed(missing):
            directory.mkdir(mode=0o755)
            os.chown(directory, self.owner_uid, self.owner_gid)
        fd, temporary = tempfile.mkstemp(prefix=".multisite-", dir=str(path.parent))
        try:
            with os.fdopen(fd, "wb") as output:
                output.write(data)
                os.fchmod(output.fileno(), int(mode, 8))
                os.fchown(output.fileno(), self.owner_uid, self.owner_gid)
                output.flush()
                os.fsync(output.fileno())
            os.replace(temporary, path)
            os.utime(path, None)
        finally:
            Path(temporary).unlink(missing_ok=True)
        return missing

    def _backup(self, operations: list[dict[str, Any]]) -> tuple[Path, list[dict[str, Any]]]:
        backup = self.state / "before"
        backup.mkdir(mode=0o700)
        metadata: list[dict[str, Any]] = []
        for index, operation in enumerate(operations):
            if operation["before"] is None:
                metadata.append({"index": index, "before": None})
                continue
            path = root_path(self.roots, operation["root"], operation["path"])
            actual = observe(path)
            require(actual is not None and entry_observed(path, operation["before"]), "drift during backup")
            target = backup / str(index)
            target.write_bytes(path.read_bytes())
            target.chmod(0o600)
            metadata.append({"index": index, "before": actual})
        return backup, metadata

    def _restore_rehearsal(self, backup: Path, operations: list[dict[str, Any]], metadata: list[dict[str, Any]]) -> None:
        rehearsal = Path(tempfile.mkdtemp(prefix="restore-", dir=self.state))
        try:
            for item, operation in zip(metadata, operations):
                if operation["before"] is None:
                    actual = observe(root_path(self.roots, operation["root"], operation["path"]))
                    require(actual is None or operation.get("intent") is True,
                            "recovery drift requires review")
                    continue
                source = backup / str(item["index"])
                target = rehearsal / str(item["index"])
                target.write_bytes(source.read_bytes())
                target.chmod(int(item["before"]["mode"], 8))
                require(sha_bytes(target.read_bytes()) == item["before"]["sha256"], "restore rehearsal digest")
                require(format(stat.S_IMODE(target.stat().st_mode), "04o") == item["before"]["mode"],
                        "restore rehearsal metadata")
        finally:
            shutil.rmtree(rehearsal, ignore_errors=True)

    def _apply_one(self, operation: dict[str, Any], staged: Path) -> list[Path]:
        path = root_path(self.roots, operation["root"], operation["path"])
        require(entry_observed(path, operation["before"]), "production drift before operation")
        if operation["after"] is None:
            require(path.is_file() and not path.is_symlink(), "delete target is not regular")
            path.unlink()
            return
        source = staged / operation["root"] / operation["path"]
        require(source.is_file() and not source.is_symlink(), "staged target missing")
        data = source.read_bytes()
        require(sha_bytes(data) == operation["after"]["sha256"] and len(data) == operation["after"]["size"],
                "candidate changed before write")
        return self._write_file(path, data, operation["after"]["mode"], self.owner_uid, self.owner_gid)

    def apply(self, package: dict[str, Any], artifact: Path, failure_after: int | None = None) -> dict[str, Any]:
        require(not self.journal_path.exists(), "prior controller attempt exists; inspect or recover")
        plan, approval, prod, candidate = self._validate_package(package)
        operations = self._operations(plan, prod, candidate)
        self._disk_check()
        with self.lock():
            self._verify_manifest_entries(prod)
            staged = self._extract(artifact, candidate, approval["artifact_sha256"])
            backup, metadata = self._backup(operations)
            self._restore_rehearsal(backup, operations, metadata)
            journal: dict[str, Any] = {
                "schemaVersion": 1, "controllerVersion": CONTROLLER_VERSION, "status": "prepared",
                "planId": approval["plan_id"], "artifactSha256": approval["artifact_sha256"],
                "prodManifestSha256": approval["prod_manifest_sha256"],
                "candidateManifestSha256": approval["candidate_manifest_sha256"],
                "roots": {name: str(path) for name, path in self.roots.items()},
                "owner": [self.owner_uid, self.owner_gid], "operations": operations,
                "metadata": metadata, "createdDirectories": [], "productionApproved": False,
                "restoreRehearsal": "passed", "cachePolicy": "deferred_to_trusted_runtime",
            }
            self.journal_path.write_bytes(canonical(journal))
            try:
                for index, operation in enumerate(operations):
                    require(entry_observed(root_path(self.roots, operation["root"], operation["path"]), operation["before"]),
                            "production drift before operation")
                    operation["intent"] = True
                    journal["status"] = "applying"
                    self.journal_path.write_bytes(canonical(journal))
                    created = self._apply_one(operation, staged)
                    for directory in created or []:
                        root = self.roots[operation["root"]]
                        journal["createdDirectories"].append({"root": operation["root"],
                                                               "path": str(directory.relative_to(root))})
                    self.journal_path.write_bytes(canonical(journal))
                    require(entry_observed(root_path(self.roots, operation["root"], operation["path"]), operation["after"]),
                            "post-write mismatch")
                    if failure_after is not None and index + 1 == failure_after:
                        raise OSError("injected controller failure")
                self._verify_manifest_entries(candidate)
                journal["status"] = "applied_unverified" if operations else "converged_unverified"
                journal["postManifestSha256"] = approval["candidate_manifest_sha256"]
                self.journal_path.write_bytes(canonical(journal))
                return journal
            except BaseException:
                journal["status"] = "failed_requires_recovery"
                self.journal_path.write_bytes(canonical(journal))
                raise
            finally:
                shutil.rmtree(staged, ignore_errors=True)

    def recover(self) -> dict[str, Any]:
        require(self.journal_path.exists(), "no controller journal")
        with self.lock():
            journal = read_json(self.journal_path)
            require(journal.get("status") in {"failed_requires_recovery", "applying", "applied_unverified"},
                    "no recoverable controller attempt")
            require(journal.get("roots") == {name: str(path) for name, path in self.roots.items()}, "target binding differs")
            require(journal.get("owner") == [self.owner_uid, self.owner_gid], "owner binding differs")
            operations = journal["operations"]
            metadata = journal["metadata"]
            backup = self.state / "before"
            for item, operation in zip(metadata, operations):
                if operation["before"] is None:
                    continue
                source = backup / str(item["index"])
                require(source.is_file() and not source.is_symlink()
                        and sha_bytes(source.read_bytes()) == item["before"]["sha256"], "backup corrupted")
                actual = observe(root_path(self.roots, operation["root"], operation["path"]))
                allowed = [operation["before"]]
                if operation.get("intent"):
                    allowed.append(operation["after"])
                require((actual is None and ((operation["before"] is None) or
                                             (operation.get("intent") and operation["after"] is None)))
                        or any(actual is not None and all(actual[key] == candidate[key]
                                                          for key in ("sha256", "size", "mode"))
                               for candidate in allowed if candidate is not None),
                        "recovery drift requires review")
            for index in reversed(range(len(operations))):
                operation = operations[index]
                path = root_path(self.roots, operation["root"], operation["path"])
                if entry_observed(path, operation["before"]):
                    continue
                if operation["before"] is None:
                    require(path.is_file() and not path.is_symlink(), "unexpected recovery target")
                    path.unlink()
                else:
                    source = backup / str(metadata[index]["index"])
                    self._write_file(path, source.read_bytes(), operation["before"]["mode"], self.owner_uid, self.owner_gid)
                require(entry_observed(path, operation["before"]), "recovery mismatch")
            for item in reversed(journal.get("createdDirectories", [])):
                directory = root_path(self.roots, item["root"], item["path"])
                if directory.exists():
                    require(directory.is_dir() and not directory.is_symlink(), "recovery directory changed")
                    directory.rmdir()
            journal["status"] = "recovered"
            self.journal_path.write_bytes(canonical(journal))
            return journal


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=["apply", "recover"])
    parser.add_argument("--primary-root", type=Path, required=True)
    parser.add_argument("--divatech-root", type=Path, required=True)
    parser.add_argument("--state", type=Path, required=True)
    parser.add_argument("--plan", type=Path)
    parser.add_argument("--prod", type=Path)
    parser.add_argument("--candidate", type=Path)
    parser.add_argument("--approval", type=Path)
    parser.add_argument("--artifact", type=Path)
    parser.add_argument("--isolated", action="store_true", help="required: production transport is intentionally unavailable")
    args = parser.parse_args()
    try:
        require(args.isolated, "fixture-only controller requires --isolated")
        controller = TrustedController({"primary": args.primary_root, "divatech": args.divatech_root}, args.state)
        if args.command == "recover":
            result = controller.recover()
        else:
            for value, name in [(args.plan, "plan"), (args.prod, "prod"), (args.candidate, "candidate"),
                                 (args.approval, "approval"), (args.artifact, "artifact")]:
                require(value is not None, f"--{name} required")
            result = controller.apply({"plan": read_json(args.plan), "prod": read_json(args.prod),
                                       "candidate": read_json(args.candidate), "approval": read_json(args.approval)},
                                      args.artifact)
        print(json.dumps(result, sort_keys=True))
        return 0
    except Exception as error:
        print(f"Multisite trusted controller stopped: {error}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
