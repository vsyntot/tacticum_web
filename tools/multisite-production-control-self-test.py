#!/usr/bin/env python3
"""Offline tests for the production controller/finalizer entry points."""

from __future__ import annotations

import importlib.util
import os
from pathlib import Path
import tempfile


ROOT = Path(__file__).resolve().parent


def load(name: str, filename: str):
    spec = importlib.util.spec_from_file_location(name, ROOT / filename)
    if spec is None or spec.loader is None:
        raise AssertionError(f"cannot load {filename}")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


prod_controller = load("multisite_production_controller", "multisite-production-controller.py")
prod_finalizer = load("multisite_production_finalizer", "multisite-production-finalizer.py")
fixture_tests = load("multisite_fixture_self_test", "multisite-trusted-controller-self-test.py")


def main() -> int:
    assert prod_controller.CONTROLLER_VERSION == "multisite-controller-v2-production.1"
    assert prod_finalizer.FINALIZER_VERSION == "multisite-finalizer-v2-production.1"

    case = fixture_tests.ControllerAndFinalizerTests(methodName="runTest")
    case.setUp()
    try:
        package = case.package()
        package["approval"]["controller_version"] = prod_controller.CONTROLLER_VERSION
        controller = prod_controller.TrustedController(
            {"primary": case.primary, "divatech": case.divatech}, case.state,
            owner_uid=os.getuid(), owner_gid=os.getgid(),
        )
        result = controller.apply(package, case.artifact)
        assert result["status"] == "applied_unverified"
        assert result["controllerVersion"] == prod_controller.CONTROLLER_VERSION
    finally:
        case.tearDown()

    with tempfile.TemporaryDirectory(prefix="multisite-production-finalizer-") as directory:
        root = Path(directory)
        operational, durable, state = (root / name for name in ("operational", "durable", "state"))
        for path in (operational, durable, state):
            path.mkdir(mode=0o700)
        case = fixture_tests.ControllerAndFinalizerTests(methodName="runTest")
        case.setUp()
        try:
            evidence = case.evidence()
            evidence["controllerVersion"] = prod_finalizer.CONTROLLER_VERSION
            evidence["finalizerVersion"] = prod_finalizer.FINALIZER_VERSION
            result = prod_finalizer.DualBaseFinalizer(operational, durable, state).promote(
                evidence, case.candidate
            )
            assert result["status"] == "verified"
        finally:
            case.tearDown()

    print("Multisite production control self-test passed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
