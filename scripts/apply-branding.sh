#!/bin/sh
# Apply names after both applications have been installed. No tokens are printed.
set -eu
cd "$(dirname "$0")/.."
docker compose cp scripts/brand-moodle.php moodle:/tmp/aou-brand-moodle.php
docker compose exec -T --user www-data moodle php /tmp/aou-brand-moodle.php
docker compose cp scripts/brand-rosariosis.php rosariosis:/tmp/aou-brand-rosariosis.php
docker compose exec -T rosariosis php /tmp/aou-brand-rosariosis.php
