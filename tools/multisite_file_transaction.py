#!/usr/bin/env python3
"""Candidate file-transaction kernel, exercised only in isolated rehearsals.

No production CLI/SSH entrypoint. A future trusted controller must validate the
scope, full inventories, platform, approval, shared locks and archive before use.
This kernel journals create/replace/delete and supports explicit recovery; it
does not authorize deployment, clear caches, or promote BASE.
"""
import fcntl
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import stat
import tempfile


def require(condition, reason):
    if not condition:
        raise ValueError(reason)


def sha(data):
    return hashlib.sha256(data).hexdigest()


def atomic_json(path, value):
    raw = json.dumps(value, sort_keys=True, separators=(',', ':')).encode()
    fd, name = tempfile.mkstemp(dir=path.parent, prefix='.journal-')
    try:
        with os.fdopen(fd, 'wb') as file:
            file.write(raw)
            file.flush()
            os.fsync(file.fileno())
        os.replace(name, path)
        sync_directory(path.parent)
    finally:
        Path(name).unlink(missing_ok=True)


def sync_directory(path):
    fd = os.open(path, os.O_RDONLY)
    try:
        os.fsync(fd)
    finally:
        os.close(fd)


class FileTransaction:
    def __init__(self, roots, state, uid, gid):
        require(set(roots) == {'primary', 'divatech'}, 'two explicit roots required')
        self.roots = {name: Path(value) for name, value in roots.items()}
        self.state = Path(state)
        self.uid, self.gid = uid, gid
        for path in [*self.roots.values(), self.state]:
            require(path.is_dir() and path.resolve() == path, 'canonical directory required')
        all_paths = [*self.roots.values(), self.state]
        require(all(a != b and a not in b.parents and b not in a.parents
                    for i, a in enumerate(all_paths) for b in all_paths[i + 1:]), 'overlapping directories')
        require(stat.S_IMODE(self.state.stat().st_mode) == 0o700, 'private state required')
        self.journal_file = self.state / 'transaction.json'

    def target(self, entry):
        root, name = entry['root'], entry['path']
        require(root in self.roots and isinstance(name, str), 'unknown root/path')
        rel = PurePosixPath(name)
        require(name and name != '.' and not rel.is_absolute() and '..' not in rel.parts
                and str(rel) == name and '\\' not in name
                and all(ord(c) >= 32 and ord(c) != 127 for c in name), 'unsafe path')
        require(not any(p in {'bitrix', 'upload', '.git', 'node_modules', 'dbconn.php', 'license_key.php',
                             'tacticum_config.php'} or p == '.env' or p.startswith('.env.')
                        or p.startswith('.settings') for p in rel.parts), 'protected path')
        require(not (root == 'divatech' and 'local' in rel.parts), 'shared secondary path')
        path = self.roots[root]
        # Recheck root/ancestors on each operation, including during recovery.
        require(path.resolve() == path and path.is_dir(), 'root changed')
        for part in rel.parts:
            path = path / part
            require(not path.is_symlink(), 'symlink path')
        return path

    def observed(self, entry):
        path = self.target(entry)
        if not path.exists():
            return None
        info = path.lstat()
        require(stat.S_ISREG(info.st_mode), 'target is not regular')
        return {'sha256': sha(path.read_bytes()), 'size': info.st_size,
                'mode': format(stat.S_IMODE(info.st_mode), '04o'), 'uid': info.st_uid, 'gid': info.st_gid}

    def expected(self, entry):
        if entry is None:
            return None
        require(entry.get('type') == 'file' and len(entry['sha256']) == 64
                and all(c in '0123456789abcdef' for c in entry['sha256']), 'invalid digest/type')
        require(isinstance(entry['size'], int) and entry['size'] >= 0
                and len(entry['mode']) == 4 and entry['mode'][0] == '0'
                and all(c in '01234567' for c in entry['mode']), 'invalid metadata')
        return {key: entry[key] for key in ['sha256', 'size', 'mode']} | {'uid': self.uid, 'gid': self.gid}

    def lock(self):
        file = (self.state / 'transaction.lock').open('a')
        try:
            fcntl.flock(file, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BaseException:
            file.close()
            raise
        return file

    def write(self, path, data, metadata):
        fd, name = tempfile.mkstemp(dir=path.parent, prefix='.multisite-')
        try:
            with os.fdopen(fd, 'wb') as file:
                file.write(data)
                os.fchmod(file.fileno(), int(metadata['mode'], 8))
                os.fchown(file.fileno(), metadata['uid'], metadata['gid'])
                file.flush()
                os.fsync(file.fileno())
            os.replace(name, path)
            sync_directory(path.parent)
        finally:
            Path(name).unlink(missing_ok=True)

    def apply(self, changes, staged):
        staged = Path(staged)
        require(staged.is_dir() and staged.resolve() == staged, 'canonical staging required')
        require(all(staged != p and staged not in p.parents and p not in staged.parents
                    for p in [*self.roots.values(), self.state]), 'staging overlaps live/state')
        with self.lock():
            require(not self.journal_file.exists(), 'prior attempt requires recovery review')
            operations, seen = [], set()
            # Complete preflight before writing any application path.
            for change in changes:
                require(change['status'] in {'LOCAL_CHANGE', 'CONVERGED'}, 'unresolved drift')
                key = change['root'] + '/' + change['path']
                require(key not in seen, 'duplicate operation')
                seen.add(key)
                before, after = change['before'], change['after']
                for entry in [before, after]:
                    if entry is not None:
                        require([entry['root'], entry['path']] == [change['root'], change['path']], 'entry identity mismatch')
                observed = self.observed(change)
                require(observed == self.expected(before), 'production drift')
                if change['status'] == 'CONVERGED':
                    require(before == after, 'invalid converged operation')
                    continue
                require(before != after and (before is not None or after is not None), 'empty change')
                if after is not None:
                    source = staged
                    for part in PurePosixPath(key).parts:
                        source /= part
                        require(not source.is_symlink(), 'staging symlink')
                    require(source.is_file(), 'missing candidate file')
                    data = source.read_bytes()
                    require(sha(data) == after['sha256'] and len(data) == after['size'], 'candidate drift')
                    self.expected(after)
                operations.append({'root': change['root'], 'path': change['path'],
                                   'before': self.expected(before), 'after': self.expected(after), 'intent': False})
            backup = self.state / 'before'
            backup.mkdir(mode=0o700)
            for index, op in enumerate(operations):
                if op['before'] is not None:
                    data = self.target(op).read_bytes()
                    require(sha(data) == op['before']['sha256'], 'drift during backup')
                    file = backup / str(index)
                    with file.open('xb') as output:
                        output.write(data)
                        output.flush()
                        os.fsync(output.fileno())
                    file.chmod(0o600)
                    # Rehearse actual write/metadata behavior, not only hash the backup.
                    restore = self.state / 'rehearsal'
                    self.write(restore, file.read_bytes(), op['before'])
                    info = restore.stat()
                    require(sha(restore.read_bytes()) == op['before']['sha256']
                            and [info.st_uid, info.st_gid, format(stat.S_IMODE(info.st_mode), '04o')]
                            == [self.uid, self.gid, op['before']['mode']], 'restore rehearsal failed')
                    restore.unlink()
            sync_directory(backup)
            journal = {'status': 'prepared', 'operations': operations, 'createdDirectories': [],
                       'productionApproved': False, 'restoreRehearsal': 'passed',
                       'roots': {k: str(v) for k, v in self.roots.items()}, 'owner': [self.uid, self.gid]}
            atomic_json(self.journal_file, journal)
            try:
                for op in operations:
                    require(self.observed(op) == op['before'], 'drift before operation')
                    op['intent'] = True
                    journal['status'] = 'applying'
                    atomic_json(self.journal_file, journal)
                    self.apply_one(op, staged, journal)
                    require(self.observed(op) == op['after'], 'post-write mismatch')
                journal['status'] = 'applied_unverified' if operations else 'converged_unverified'
                atomic_json(self.journal_file, journal)
            except BaseException:
                journal['status'] = 'failed_requires_recovery'
                atomic_json(self.journal_file, journal)
                raise
            return journal

    def apply_one(self, op, staged, journal):
        path = self.target(op)
        if op['after'] is None:
            path.unlink()
            sync_directory(path.parent)
            return
        missing = []
        parent = path.parent
        while not parent.exists():
            missing.append(parent)
            parent = parent.parent
        for directory in reversed(missing):
            root = self.roots[op['root']]
            journal['createdDirectories'].append({'root': op['root'], 'path': str(directory.relative_to(root))})
            atomic_json(self.journal_file, journal)
            directory.mkdir(mode=0o755)
            os.chown(directory, self.uid, self.gid)
            sync_directory(directory.parent)
        data = (staged / op['root'] / op['path']).read_bytes()
        require(sha(data) == op['after']['sha256'], 'candidate changed before write')
        self.write(path, data, op['after'])

    def recover(self):
        """Explicit recovery; never overwrite unrelated changes after a failure."""
        with self.lock():
            journal = json.loads(self.journal_file.read_text())
            require(journal['roots'] == {k: str(v) for k, v in self.roots.items()}
                    and journal['owner'] == [self.uid, self.gid], 'recovery target binding differs')
            require(journal['status'] in {'failed_requires_recovery', 'applying', 'applied_unverified'},
                    'no recoverable attempt')
            operations = journal['operations']
            for index, op in enumerate(operations):
                allowed = [op['before'], op['after']] if op['intent'] else [op['before']]
                require(self.observed(op) in allowed, 'recovery drift requires review')
                if op['before'] is not None:
                    file = self.state / 'before' / str(index)
                    require(not file.is_symlink() and sha(file.read_bytes()) == op['before']['sha256'], 'backup corrupted')
            for index in reversed(range(len(operations))):
                op = operations[index]
                if self.observed(op) == op['before']:
                    continue
                path = self.target(op)
                if op['before'] is None:
                    path.unlink()
                    sync_directory(path.parent)
                else:
                    self.write(path, (self.state / 'before' / str(index)).read_bytes(), op['before'])
                require(self.observed(op) == op['before'], 'restore mismatch')
            for entry in reversed(journal['createdDirectories']):
                directory = self.target(entry)
                if directory.exists():
                    # Never recursively remove a directory containing new files.
                    directory.rmdir()
                    sync_directory(directory.parent)
            journal['status'] = 'recovered'
            atomic_json(self.journal_file, journal)
            return journal
