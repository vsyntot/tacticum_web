#!/usr/bin/env python3
"""Trusted production dual-BASE finalizer for the multisite contour."""

from __future__ import annotations

import argparse
import importlib.util
import json
from pathlib import Path


FINALIZER_VERSION = "multisite-finalizer-v2-production.1"
CONTROLLER_VERSION = "multisite-controller-v2-production.1"
FIXTURE_PATH = Path(__file__).with_name("multisite-release-finalizer.py")


def _load_kernel():
    spec = importlib.util.spec_from_file_location("multisite_fixture_finalizer", FIXTURE_PATH)
    if spec is None or spec.loader is None:
        raise RuntimeError("multisite fixture finalizer is unavailable")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    module.FINALIZER_VERSION = FINALIZER_VERSION
    module.CONTROLLER_VERSION = CONTROLLER_VERSION
    return module


kernel = _load_kernel()
DualBaseFinalizer = kernel.DualBaseFinalizer
read_json = kernel.read_json
require = kernel.require


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=["promote", "recover"])
    parser.add_argument("--operational", type=Path, required=True)
    parser.add_argument("--durable", type=Path, required=True)
    parser.add_argument("--state", type=Path, required=True)
    parser.add_argument("--evidence", type=Path)
    parser.add_argument("--candidate", type=Path)
    args = parser.parse_args()
    try:
        finalizer = DualBaseFinalizer(args.operational, args.durable, args.state)
        if args.command == "recover":
            result = finalizer.recover()
        else:
            require(args.evidence is not None and args.candidate is not None,
                    "--evidence and --candidate required")
            result = finalizer.promote(read_json(args.evidence), read_json(args.candidate))
        print(json.dumps(result, sort_keys=True))
        return 0
    except Exception as error:
        print(f"Multisite production finalizer stopped: {error}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
