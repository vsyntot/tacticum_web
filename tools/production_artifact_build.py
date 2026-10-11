#!/usr/bin/env python3
"""Build the immutable FILE_ONLY artifact from a candidate manifest."""

import argparse
import json
from pathlib import Path, PurePosixPath
import tarfile


def fail(message: str) -> None:
    raise SystemExit(message)


def safe_path(name: str) -> PurePosixPath:
    path = PurePosixPath(name)
    if path.is_absolute() or ".." in path.parts or str(path) != name:
        fail(f"unsafe manifest path: {name}")
    return path


def build(root: Path, manifest_path: Path, output: Path) -> None:
    manifest = json.loads(manifest_path.read_text())
    entries = manifest.get("entries")
    if not isinstance(entries, list) or not entries:
        fail("candidate manifest must contain entries")
    output.parent.mkdir(parents=True, exist_ok=True)
    with tarfile.open(output, "w:gz") as archive:
        for entry in entries:
            if entry.get("type") != "file":
                fail("artifact accepts regular files only")
            relative = safe_path(entry["path"])
            source = root.joinpath(*relative.parts)
            if not source.is_file() or source.is_symlink():
                fail(f"manifest file is not a regular file: {entry['path']}")
            info = tarfile.TarInfo(entry["path"])
            info.size = source.stat().st_size
            info.mode = int(entry["mode"], 8)
            info.mtime = 0
            with source.open("rb") as handle:
                archive.addfile(info, handle)
    output.chmod(0o600)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--manifest", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    arguments = parser.parse_args()
    build(arguments.root.resolve(), arguments.manifest.resolve(), arguments.output.resolve())
