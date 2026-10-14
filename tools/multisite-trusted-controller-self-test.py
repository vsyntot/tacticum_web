#!/usr/bin/env python3
"""Isolated acceptance tests for the multisite controller and BASE finalizer."""

from __future__ import annotations

import hashlib
import importlib.util
import json
from pathlib import Path
import tarfile
import tempfile
import unittest


ROOT = Path(__file__).resolve().parent


def load(name: str, filename: str):
    spec = importlib.util.spec_from_file_location(name, ROOT / filename)
    module = importlib.util.module_from_spec(spec)
    assert spec.loader is not None
    spec.loader.exec_module(module)
    return module


controller = load("multisite_trusted_controller", "multisite-trusted-controller.py")
finalizer = load("multisite_release_finalizer", "multisite-release-finalizer.py")


def sha(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def manifest(entries, tree_kind):
    payload = {
        "schemaVersion": 2, "scopeVersion": "fixture-scope-v2",
        "scopeSha256": "a" * 64, "treeKind": tree_kind,
        "platform": {"snapshotSha256": "b" * 64, "vendorManifestSha256": "c" * 64,
                      "schemaProfile": "fixture-schema", "phpVersion": "8.5.11"},
        "entries": sorted(entries, key=lambda item: f"{item['root']}/{item['path']}"),
        "tombstones": [{"root": "primary", "path": "removed.txt"}],
    }
    payload["manifestSha256"] = controller.sha_json(payload)
    return payload


def entry(root, path, data, mode="0644"):
    return {"root": root, "path": path, "type": "file", "sha256": sha(data),
            "size": len(data), "mode": mode}


class ControllerAndFinalizerTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory(prefix="multisite-controller-fixture-")
        self.root = Path(self.tmp.name).resolve()
        self.primary = self.root / "primary"
        self.divatech = self.root / "divatech"
        self.state = self.root / "controller-state"
        for path in (self.primary, self.divatech, self.state):
            path.mkdir(mode=0o700)
        self.primary.joinpath("index.php").write_bytes(b"old primary")
        self.primary.joinpath("old.php").write_bytes(b"old rename")
        self.divatech.joinpath("home.html").write_bytes(b"old secondary")
        self.divatech.joinpath("delete.html").write_bytes(b"delete me")
        for path in (self.primary, self.divatech):
            for file in path.rglob("*"):
                if file.is_file():
                    file.chmod(0o644)

        prod_entries = [entry("primary", "index.php", b"old primary"),
                        entry("primary", "old.php", b"old rename"),
                        entry("divatech", "home.html", b"old secondary"),
                        entry("divatech", "delete.html", b"delete me")]
        candidate_entries = [entry("primary", "index.php", b"new primary"),
                             entry("primary", "renamed.php", b"old rename"),
                             entry("divatech", "home.html", b"new secondary"),
                             entry("divatech", "new/path.html", b"created")]
        self.prod = manifest(prod_entries, "prod")
        self.candidate = manifest(candidate_entries, "candidate")
        prod_map = {f"{item['root']}/{item['path']}": item for item in self.prod["entries"]}
        candidate_map = {f"{item['root']}/{item['path']}": item for item in self.candidate["entries"]}
        self.plan = {"schemaVersion": 2, "scopeVersion": "fixture-scope-v2",
                     "planKind": "DRIFT_COMPARISON_ONLY", "productionMutationAllowed": False,
                     "baseManifestSha256": "d" * 64, "prodManifestSha256": self.prod["manifestSha256"],
                     "candidateManifestSha256": self.candidate["manifestSha256"], "driftResolved": True,
                     "changes": [
                         {"root": "primary", "path": "index.php", "status": "LOCAL_CHANGE", "operation": "replace",
                          "before": prod_map["primary/index.php"], "after": candidate_map["primary/index.php"]},
                         {"root": "primary", "path": "renamed.php", "fromPath": "old.php", "status": "LOCAL_CHANGE",
                          "operation": "rename", "before": prod_map["primary/old.php"], "after": candidate_map["primary/renamed.php"]},
                         {"root": "divatech", "path": "home.html", "status": "LOCAL_CHANGE", "operation": "replace",
                          "before": prod_map["divatech/home.html"], "after": candidate_map["divatech/home.html"]},
                         {"root": "divatech", "path": "delete.html", "status": "LOCAL_CHANGE", "operation": "delete",
                          "before": prod_map["divatech/delete.html"], "after": None},
                         {"root": "divatech", "path": "new/path.html", "status": "LOCAL_CHANGE", "operation": "create",
                          "before": None, "after": candidate_map["divatech/new/path.html"]},
                     ]}
        self.plan["planId"] = controller.sha_json(self.plan)
        self.artifact = self.root / "candidate.tar.gz"
        with tarfile.open(self.artifact, "w:gz") as archive:
            for item in self.candidate["entries"]:
                source = self.root / (item["path"] if item["root"] == "primary" else "staged")
                if item["root"] == "primary":
                    # Candidate primary files are staged separately below.
                    data = {"index.php": b"new primary", "renamed.php": b"old rename"}[item["path"]]
                else:
                    data = {"home.html": b"new secondary", "new/path.html": b"created"}[item["path"]]
                info = tarfile.TarInfo(f"{item['root']}/{item['path']}")
                info.size, info.mode, info.mtime = len(data), int(item["mode"], 8), 0
                import io
                archive.addfile(info, io.BytesIO(data))
        self.approval = {"release_class": "FILE_ONLY", "production_mutation_allowed": True,
                         "plan_id": self.plan["planId"], "artifact_sha256": sha(self.artifact.read_bytes()),
                         "base_manifest_sha256": self.plan["baseManifestSha256"],
                         "prod_manifest_sha256": self.prod["manifestSha256"],
                         "candidate_manifest_sha256": self.candidate["manifestSha256"],
                         "scope_version": "fixture-scope-v2", "controller_version": controller.CONTROLLER_VERSION,
                         "approved_by": "fixture-owner", "approval_source": "isolated-test",
                         "expires_at": "2099-01-01T00:00:00Z"}

    def tearDown(self):
        self.tmp.cleanup()

    def package(self):
        return {"plan": self.plan, "prod": self.prod, "candidate": self.candidate, "approval": self.approval}

    def controller(self):
        return controller.TrustedController({"primary": self.primary, "divatech": self.divatech}, self.state)

    def test_controller_applies_all_explicit_operations_and_recovery_restores(self):
        result = self.controller().apply(self.package(), self.artifact)
        self.assertEqual(result["status"], "applied_unverified")
        self.assertFalse(result["productionApproved"])
        self.assertEqual((self.primary / "index.php").read_bytes(), b"new primary")
        self.assertEqual((self.primary / "renamed.php").read_bytes(), b"old rename")
        self.assertFalse((self.primary / "old.php").exists())
        self.assertEqual((self.divatech / "new/path.html").read_bytes(), b"created")
        self.assertFalse((self.divatech / "delete.html").exists())
        self.assertEqual(self.controller().recover()["status"], "recovered")
        self.assertEqual((self.primary / "index.php").read_bytes(), b"old primary")
        self.assertTrue((self.primary / "old.php").exists())
        self.assertFalse((self.primary / "renamed.php").exists())
        self.assertFalse((self.divatech / "new").exists())

    def test_failure_between_roots_is_journaled_and_recovers(self):
        with self.assertRaises(OSError):
            self.controller().apply(self.package(), self.artifact, failure_after=3)
        self.assertEqual(json.loads((self.state / "journal.json").read_text())["status"], "failed_requires_recovery")
        self.assertEqual(self.controller().recover()["status"], "recovered")
        self.assertEqual((self.primary / "index.php").read_bytes(), b"old primary")
        self.assertTrue((self.divatech / "delete.html").exists())

    def test_controller_rejects_stale_plan_symlink_archive_and_busy_lock(self):
        (self.primary / "index.php").write_bytes(b"out of band")
        with self.assertRaisesRegex(RuntimeError, "drift"):
            self.controller().apply(self.package(), self.artifact)
        (self.primary / "index.php").write_bytes(b"old primary")
        bad = self.root / "symlink.tar.gz"
        with tarfile.open(bad, "w:gz") as archive:
            info = tarfile.TarInfo("primary/escape.php"); info.type = tarfile.SYMTYPE; info.linkname = "/tmp"
            archive.addfile(info)
        package = self.package(); package["approval"] = dict(self.approval, artifact_sha256=sha(bad.read_bytes()))
        with self.assertRaisesRegex(RuntimeError, "unsafe archive"):
            self.controller().apply(package, bad)
        handle = self.controller().lock()
        try:
            with self.assertRaisesRegex(RuntimeError, "busy"):
                self.controller().apply(self.package(), self.artifact)
        finally:
            handle.close()

    def test_controller_stops_before_write_on_disk_preflight_and_unsafe_approval(self):
        limited = controller.TrustedController({"primary": self.primary, "divatech": self.divatech}, self.state,
                                                min_free_bytes=10**30)
        with self.assertRaisesRegex(RuntimeError, "disk space"):
            limited.apply(self.package(), self.artifact)
        unsafe = self.package()
        unsafe["approval"] = dict(self.approval, production_mutation_allowed=False)
        with self.assertRaisesRegex(RuntimeError, "approval"):
            self.controller().apply(unsafe, self.artifact)

    def evidence(self, candidate=None, status="applied_unverified"):
        candidate = candidate or self.candidate
        return {"status": status, "controllerVersion": controller.CONTROLLER_VERSION,
                "finalizerVersion": finalizer.FINALIZER_VERSION, "productionApproved": False,
                "releaseId": "fixture-release-1", "planId": self.plan["planId"],
                "artifactSha256": self.approval["artifact_sha256"],
                "candidateManifestSha256": candidate["manifestSha256"],
                "postManifestSha256": candidate["manifestSha256"], "scopeVersion": "fixture-scope-v2",
                "commitSha": "e" * 40, "schemaProfile": "fixture-schema", "phpVersion": "8.5.11",
                "smoke": {"passed": True, "mutations": False},
                "monitoring": {"passed": True, "mutations": False, "durationSeconds": 900},
                "cacheClear": {"passed": True, "mutations": False}}

    def finalizer(self):
        operational, durable, state = (self.root / name for name in ("base-a", "base-b", "finalizer-state"))
        for path in (operational, durable, state):
            path.mkdir(mode=0o700)
        return finalizer.DualBaseFinalizer(operational, durable, state), operational, durable

    def test_finalizer_requires_evidence_and_promotes_dual_base(self):
        fin, operational, durable = self.finalizer()
        with self.assertRaisesRegex(RuntimeError, "smoke"):
            fin.promote(dict(self.evidence(), smoke={"passed": False, "mutations": False}), self.candidate)
        result = fin.promote(self.evidence(), self.candidate)
        self.assertEqual(result["status"], "verified")
        self.assertEqual((operational / "current.json").read_bytes(), (durable / "current.json").read_bytes())
        self.assertEqual(result["sequence"], 1)

    def test_finalizer_cas_and_partial_promotion_recover_without_verified_state(self):
        fin, operational, durable = self.finalizer()
        with self.assertRaisesRegex(OSError, "injected"):
            fin.promote(self.evidence(), self.candidate, failure_after="operational")
        self.assertEqual(json.loads((fin.journal_path).read_text())["status"], "failed_requires_recovery")
        self.assertEqual(fin.recover()["status"], "recovered")
        self.assertFalse((operational / "current.json").exists())
        with self.assertRaisesRegex(RuntimeError, "CAS"):
            fin.promote(dict(self.evidence(), previousBaseSha256="f" * 64), self.candidate)

    def test_finalizer_recovers_partial_second_promotion_with_existing_base(self):
        fin, operational, durable = self.finalizer()
        fin.promote(self.evidence(), self.candidate)
        previous = (operational / "current.json").read_bytes()
        second = dict(self.evidence(), releaseId="fixture-release-2", sequence=2,
                      previousBaseSha256=sha(previous), previousReleaseId="fixture-release-1")
        with self.assertRaisesRegex(OSError, "injected"):
            fin.promote(second, self.candidate, failure_after="operational")
        self.assertEqual(fin.recover()["status"], "recovered")
        self.assertEqual((operational / "current.json").read_bytes(), previous)
        self.assertEqual((durable / "current.json").read_bytes(), previous)

    def test_finalizer_rejects_tampered_recovery_journal(self):
        fin, operational, durable = self.finalizer()
        fin.promote(self.evidence(), self.candidate)
        previous = (operational / "current.json").read_bytes()
        second = dict(self.evidence(), releaseId="fixture-release-2", sequence=2,
                      previousBaseSha256=sha(previous), previousReleaseId="fixture-release-1")
        with self.assertRaisesRegex(OSError, "injected"):
            fin.promote(second, self.candidate, failure_after="operational")
        journal = json.loads(fin.journal_path.read_text())
        journal["previousSha256"] = "0" * 64
        fin.journal_path.write_text(json.dumps(journal, sort_keys=True, separators=(",", ":")))
        with self.assertRaisesRegex(RuntimeError, "journal previous digest"):
            fin.recover()
        self.assertNotEqual((operational / "current.json").read_bytes(), previous)


if __name__ == "__main__":
    unittest.main()
