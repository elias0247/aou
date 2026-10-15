#!/usr/bin/env python3
"""Verify the exact source snapshots before building, without changing the forks."""
import json
from pathlib import Path
import subprocess

root = Path(__file__).resolve().parents[1]
manifest = json.loads((root / 'components.json').read_text())
for name, component in manifest['components'].items():
    path = root / component['path']
    def git(*args):
        return subprocess.check_output(['git', '-C', str(path), *args], text=True).strip()
    if git('rev-parse', 'HEAD') != component['commit']:
        raise SystemExit(f'{name}: commit differs from components.json. Review before building.')
    if git('status', '--porcelain', '--untracked-files=normal'):
        raise SystemExit(f'{name}: local changes found. Review before building.')
    print(f'{name}: {component["version"]}, expected commit, clean working tree')
