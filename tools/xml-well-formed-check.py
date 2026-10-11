#!/usr/bin/env python3
"""Validate one or more XML documents with the Python standard library."""
from __future__ import annotations

import sys
import xml.etree.ElementTree as ElementTree
from pathlib import Path


def main() -> int:
    if len(sys.argv) < 2:
        print("usage: xml-well-formed-check.py FILE...", file=sys.stderr)
        return 2
    for raw_path in sys.argv[1:]:
        path = Path(raw_path)
        try:
            ElementTree.parse(path)
        except (ElementTree.ParseError, OSError) as error:
            print(f"{path}: invalid XML: {error}", file=sys.stderr)
            return 1
        print(f"✅ {path} valid")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
