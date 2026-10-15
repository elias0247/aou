# Local setup verification

Status updated on 2026-10-07 after the AOU rename. Earlier sections preserve September verification history. This records evidence from actual checks, not full application acceptance.

## Passed

- Both clones match `components.json`: Moodle 5.2.3 at `344232c15336c71b80f9aca8359ce0e0a9f3d116` and RosarioSIS 13.0 at `5ece843bb8b4589b8568cb0da1929ff03da7e433`.
- Both fork working trees are clean; the platform setup did not edit or push their source.
- Compose configuration validates, including the optional cron profile.
- Platform PHP configuration and readiness-check files passed PHP syntax checks. Shell scripts passed syntax checks.
- The local `.env` was generated with file mode 600 and is excluded from Git and image build context.
- RosarioSIS's image built with PHP 8.3.33, gettext, and `max_input_vars=5000`.
- During the earlier runtime session, PostgreSQL created the two application databases with distinct owners. Each application's role could connect to its own database and was denied access to the other application's database.
- RosarioSIS's canonical Docker network address reached its readiness endpoint, returning `runtime: ready` and `schema_present: false`. The application installer had not yet run.
- Apache syntax passed. Direct HTTP access to RosarioSIS's configuration, SQL dump, diagnostic script, and upload `.htaccess` returned 403.
- The web-server account could write RosarioSIS upload/photo volumes and could not write its source `index.php`.

## Incomplete or untested

- School configuration, uploads through the UI, and permissions beyond the connection check have not been tested.
- Account/course/enrollment synchronization, email, persistence across a full application lifecycle, and backup restoration remain untested. Moodle cron startup is recorded below.
- No production configuration or deployment has been performed.

## Earlier missing-schema and hostname fix

At the initial troubleshooting check, both databases were confirmed empty before any installation action. RosarioSIS's bundled installer was then run, as its web-server user, inside its container:

```bash
docker compose exec -T --user www-data rosariosis php InstallDatabase.php
```

It reported successful installation. PostgreSQL now contains 100 public tables in the RosarioSIS database, including `config` with version 13.0. Its login page returns HTTP 200 and no longer contains the missing-`config` error. Moodle's installer also returns HTTP 200 at `/admin/index.php` when addressed with its configured hostname.

At that earlier stage, both readiness endpoints were checked from inside the RosarioSIS container: RosarioSIS reports `schema_present: true`, and Moodle reports `schema_present: false`. All three services are running. Both fork working trees remain clean.

The following mapping has since been added to `/etc/hosts`:

```text
127.0.0.1 learn.neuronet.test sis.neuronet.test
```

## REST connection verified later on 2026-09-28

- Moodle now has its installed application tables, administrator account, and dedicated `rosariosis_api` account. The user has accessed the RosarioSIS plugin configuration screen.
- The connection error was reproduced from the RosarioSIS container: Moodle returned `accessexception`.
- The integration account's custom role lacked REST access and profile-read permissions, and the custom service lacked `core_user_get_users`.
- Added `webservice/rest:use` and `moodle/user:viewdetails` to the dedicated account's role at system context, and added `core_user_get_users` to its service using Moodle's own APIs.
- The same REST request used by the plugin's Test action succeeded from the RosarioSIS container: HTTP 200, no API exception, and the expected user ID returned. No user details were printed.
- Replaced the token exposed in the screenshot, tested the replacement before saving it in RosarioSIS, revoked the old token, and repeated the test with the stored replacement successfully. Token values were not printed or committed.
- Both source checkouts still pass `scripts/check-sources.py` and have clean working trees. No fork code changed.

See [connection settings and next tests](moodle-connection.md). This confirms the API connection, not full synchronization.

## Recreate the environment elsewhere

From the platform directory, with Docker access and a stable network:

```bash
python3 scripts/check-sources.py
docker compose config --quiet
docker compose build
docker compose up -d
```

Only start the services after the build succeeds. Keep the existing clones and `.env`; re-cloning does not solve a dependency-download timeout. The build uses a persistent Composer cache, IPv4, reduced concurrent downloads, and extended transfer timeouts. This improves retry behavior but cannot guarantee network availability.

On this laptop the images and services are already built/running, so skip rebuilding unless configuration changes. The local hostname mappings and application installation are now complete. Follow [local development](local-development.md) for daily commands and enabling cron, and [connection settings](moodle-connection.md) for the remaining integration work.

## AOU rename verified on 2026-10-07

- Both images built successfully as `aou/moodle:5.2.3-dev` and `aou/rosariosis:13.0-dev`.
- `aou-dev-db-1`, `aou-dev-moodle-1`, and `aou-dev-rosariosis-1` report healthy.
- Docker inspection confirms every application still mounts its original data volume. Existing local volumes are declared external through `.env`.
- Moodle and RosarioSIS HTTP pages both display **Africa Online University Platform**, tested from the laptop using curl `--resolve` for the new AOU hostnames.
- The existing RosarioSIS API token successfully retrieved the expected Moodle test user through `http://learn.aou.test:8080`. No token or returned user details were printed.
- The laptop's AOU `/etc/hosts` entries are now present. Both site URLs return HTTP 200 with the AOU title using ordinary hostname resolution, without curl overrides.
- The owner has supplied the renamed GitHub repository: `elias0247/Africa-Online-University-Platform`. No separate custom dashboard has been created.

See [rename details](aou-rename.md) and the [step-by-step run guide](run-and-develop.md).

## Full local stack startup on 2026-10-07

- Started all services with `docker compose --profile tasks up -d --wait`.
- PostgreSQL, Moodle, and RosarioSIS report healthy. The new `moodle-cron` container is running as `www-data`; it has no Docker health check.
- Initial cron logs recorded 38 completed scheduled tasks and no wrapper failure, missing script, or fatal-error marker. The full first cron run had not reported completion at the check; this does not certify every individual background task.
- Both source checkouts still match their recorded commits and remain clean.
- The publication checkout's origin now uses `git@github.com:elias0247/Africa-Online-University-Platform.git`; it fetched the renamed repository successfully.
