#!/usr/bin/env python3
"""Offline negative/positive checks for the immutable artifact builder."""

import hashlib
import json
from pathlib import Path
import tempfile
import tarfile

from production_artifact_build import build


def main() -> None:
    with tempfile.TemporaryDirectory(prefix="artifact-self-test-") as temporary:
        root = Path(temporary) / "root"
        root.mkdir()
        file = root / "index.php"
        file.write_text("<?php echo 'ok';\n")
        digest = hashlib.sha256(file.read_bytes()).hexdigest()
        manifest = {
            "entries": [{
                "path": "index.php",
                "type": "file",
                "size": file.stat().st_size,
                "mode": format(file.stat().st_mode & 0o7777, "04o"),
                "sha256": digest,
            }],
            "tombstones": [],
        }
        manifest_path = Path(temporary) / "candidate.json"
        manifest_path.write_text(json.dumps(manifest))
        artifact = Path(temporary) / "candidate.tar.gz"
        build(root, manifest_path, artifact)
        with tarfile.open(artifact) as archive:
            names = archive.getnames()
            assert names == ["index.php"], names
            assert archive.extractfile("index.php").read() == file.read_bytes()
        manifest["entries"][0]["path"] = "../escape"
        manifest_path.write_text(json.dumps(manifest))
        try:
            build(root, manifest_path, Path(temporary) / "bad.tar.gz")
        except SystemExit:
            pass
        else:
            raise AssertionError("path traversal was accepted")
    print("Production artifact builder self-test passed.")


if __name__ == "__main__":
    main()
