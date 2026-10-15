#!/bin/sh
set -eu

# Runs only for a new PostgreSQL volume. Application passwords are never echoed.
psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" --set ON_ERROR_STOP=1 \
  --set moodle_password="$MOODLE_DB_PASSWORD" \
  --set rosario_password="$ROSARIO_DB_PASSWORD" <<'SQL'
CREATE ROLE moodle LOGIN PASSWORD :'moodle_password';
CREATE ROLE rosariosis LOGIN PASSWORD :'rosario_password';
CREATE DATABASE moodle OWNER moodle ENCODING 'UTF8' TEMPLATE template0;
CREATE DATABASE rosariosis OWNER rosariosis ENCODING 'UTF8' TEMPLATE template0;
REVOKE ALL ON DATABASE moodle FROM PUBLIC;
REVOKE ALL ON DATABASE rosariosis FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE moodle TO moodle;
GRANT CONNECT, TEMPORARY ON DATABASE rosariosis TO rosariosis;
SQL
