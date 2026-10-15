#!/usr/bin/env python3
"""Create local database credentials once, without printing or replacing secrets."""
from pathlib import Path
import os
import secrets

root = Path(__file__).resolve().parents[1]
path = root / '.env'
if path.exists():
    raise SystemExit('.env already exists; left unchanged.')
lines = ['COMPOSE_PROJECT_NAME=aou-dev', 'AOU_VOLUME_PREFIX=aou-dev', 'AOU_VOLUMES_EXTERNAL=false']
for key in ('POSTGRES_PASSWORD', 'MOODLE_DB_PASSWORD', 'ROSARIO_DB_PASSWORD'):
    lines.append(f'{key}={secrets.token_hex(24)}')
fd = os.open(path, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
with os.fdopen(fd, 'w') as output:
    output.write('\n'.join(lines) + '\n')
print('Created .env with generated database passwords and mode 600. No secrets printed.')
