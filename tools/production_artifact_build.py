#!/usr/bin/env python3
"""Build the immutable FILE_ONLY artifact from a candidate manifest."""

import argparse
import gzip
import hashlib
import io
import json
import os
from pathlib import Path, PurePosixPath
import tarfile
import tempfile


def fail(message: str) -> None:
    raise SystemExit(message)


def safe_path(name: str) -> PurePosixPath:
    path = PurePosixPath(name)
    if not name or name == '.' or '\\' in name or '\x00' in name or path.is_absolute() or ".." in path.parts or str(path) != name:
        fail(f"unsafe manifest path: {name}")
    return path


def build(root: Path, manifest_path: Path, output: Path, multisite: bool = False) -> None:
    manifest = json.loads(manifest_path.read_text())
    if multisite and (manifest.get('schemaVersion') != 2 or manifest.get('treeKind') != 'candidate'):
        fail('multisite candidate manifest required')
    if not multisite and manifest.get('schemaVersion', 1) != 1:
        fail('use multisite builder for a multi-root manifest')
    entries = manifest.get("entries")
    if not isinstance(entries, list) or not entries:
        fail("candidate manifest must contain entries")
    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists() or output.is_symlink():
        fail("refusing to replace an existing immutable artifact")
    seen = set()
    temp_name = None
    try:
        with tempfile.NamedTemporaryFile(dir=output.parent, delete=False) as raw:
            temp_name = raw.name
            with gzip.GzipFile(filename="", mode="wb", fileobj=raw, mtime=0) as compressed:
                with tarfile.open(fileobj=compressed, mode="w", format=tarfile.PAX_FORMAT) as archive:
                    for entry in sorted(entries, key=lambda item: (item.get('root', ''), item['path'])):
                        if entry.get("type") != "file":
                            fail("artifact accepts regular files only")
                        relative = safe_path(entry["path"])
                        if multisite:
                            if entry.get('root') not in ('primary', 'divatech'):
                                fail('unrecognized artifact root')
                            archive_path = f"{entry['root']}/{relative}"
                            source_relative = relative if entry['root'] == 'primary' else PurePosixPath('sites/divatech') / relative
                        else:
                            archive_path, source_relative = str(relative), relative
                        if archive_path in seen:
                            fail("duplicate manifest path")
                        seen.add(archive_path)
                        source = root
                        if root.is_symlink():
                            fail('symlink artifact source root')
                        for part in source_relative.parts:
                            source = source / part
                            if source.is_symlink():
                                fail("symlink in artifact source path")
                        if not source.is_file():
                            fail("manifest source must be a regular file")
                        content = source.read_bytes()
                        if len(content) != entry['size'] or hashlib.sha256(content).hexdigest() != entry['sha256']:
                            fail("source differs from approved manifest")
                        mode = int(entry['mode'], 8)
                        if source.stat().st_mode & 0o7777 != mode or mode & 0o7000:
                            fail("unsafe or changed source mode")
                        info = tarfile.TarInfo(archive_path)
                        info.size, info.mode, info.mtime = len(content), mode, 0
                        archive.addfile(info, io.BytesIO(content))
            raw.flush()
            os.fsync(raw.fileno())
        # Exclusive publication; never replace an artifact created by another process.
        os.link(temp_name, output)
    finally:
        if temp_name is not None:
            Path(temp_name).unlink(missing_ok=True)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--manifest", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument('--multisite', action='store_true')
    arguments = parser.parse_args()
    build(arguments.root.absolute(), arguments.manifest.resolve(), arguments.output.absolute(), arguments.multisite)
