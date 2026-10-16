#!/usr/bin/env python3
"""Trusted production controller for the two-root FILE_ONLY contour.

The transaction kernel remains shared with the isolated fixture controller, but
this entry point removes the fixture-only transport switch, pins a production
version and requires explicit application owners for both roots.  It is still
fail-closed: the package must carry an approved FILE_ONLY plan, exact
manifests, artifact digest and a valid server-side lock/state directory.
"""

from __future__ import annotations

import argparse
import importlib.util
import json
from pathlib import Path
import grp
import pwd
import stat
from typing import Any


CONTROLLER_VERSION = "multisite-controller-v2-production.1"
FIXTURE_PATH = Path(__file__).with_name("multisite-trusted-controller.py")


def _load_kernel():
    spec = importlib.util.spec_from_file_location("multisite_fixture_kernel", FIXTURE_PATH)
    if spec is None or spec.loader is None:
        raise RuntimeError("multisite fixture kernel is unavailable")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    # Kernel methods resolve these names in their defining module.  Rebinding
    # the version keeps package evidence bound to this production entry point.
    module.CONTROLLER_VERSION = CONTROLLER_VERSION
    return module


kernel = _load_kernel()
TrustedController = kernel.TrustedController
read_json = kernel.read_json
require = kernel.require


def canonical_root(value: Path) -> Path:
    value = Path(value)
    require(value.is_absolute(), "production root must be absolute")
    require(not value.is_symlink(), "production root symlink denied")
    resolved = value.resolve(strict=True)
    require(resolved == value and resolved.is_dir(), "production root must be a canonical directory")
    return resolved


def account_ids(user: str, group: str) -> tuple[int, int]:
    uid = pwd.getpwnam(user).pw_uid
    gid = grp.getgrnam(group).gr_gid
    require(uid != 0 and gid != 0, "application owner cannot be root")
    return uid, gid


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=["apply", "recover"])
    parser.add_argument("--primary-root", type=Path, required=True)
    parser.add_argument("--divatech-root", type=Path, required=True)
    parser.add_argument("--state", type=Path, required=True)
    parser.add_argument("--owner-user", default="bitrix")
    parser.add_argument("--owner-group", default="bitrix")
    parser.add_argument("--plan", type=Path)
    parser.add_argument("--prod", type=Path)
    parser.add_argument("--candidate", type=Path)
    parser.add_argument("--approval", type=Path)
    parser.add_argument("--artifact", type=Path)
    args = parser.parse_args()
    try:
        primary = canonical_root(args.primary_root)
        divatech = canonical_root(args.divatech_root)
        state = Path(args.state)
        require(state.is_absolute() and state.is_dir() and not state.is_symlink(),
                "production state directory required")
        require(stat.S_IMODE(state.stat().st_mode) == 0o700, "production state must be private")
        owner_uid, owner_gid = account_ids(args.owner_user, args.owner_group)
        controller = TrustedController({"primary": primary, "divatech": divatech}, state,
                                        owner_uid=owner_uid, owner_gid=owner_gid)
        if args.command == "recover":
            result = controller.recover()
        else:
            for value, name in [(args.plan, "plan"), (args.prod, "prod"), (args.candidate, "candidate"),
                                (args.approval, "approval"), (args.artifact, "artifact")]:
                require(value is not None, f"--{name} required")
            package: dict[str, Any] = {
                "plan": read_json(args.plan), "prod": read_json(args.prod),
                "candidate": read_json(args.candidate), "approval": read_json(args.approval),
            }
            result = controller.apply(package, args.artifact)
        print(json.dumps(result, sort_keys=True))
        return 0
    except Exception as error:
        print(f"Multisite production controller stopped: {error}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
