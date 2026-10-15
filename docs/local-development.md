# Run Africa Online University on your laptop

This is the first local infrastructure milestone. It is for one school, Africa Online University, and test data. Application installation, SIS-to-Moodle synchronization, mail testing, and backup/restore acceptance are separate steps.

**Current status (2026-10-07):** both applications are installed, AOU hostnames resolve on this laptop, and the REST connection has passed. The full stack now includes Moodle cron. Account and enrollment acceptance remain pending. Start with [Run and develop AOU](run-and-develop.md).

## What was implemented

| Part | What it does |
| --- | --- |
| `components.json` | Records the exact Moodle 5.2.3 and RosarioSIS 13.0 source commits used for this development baseline |
| `compose.yaml` | Starts PostgreSQL and two separate PHP/Apache application containers; optionally starts Moodle cron |
| `docker/moodle/Dockerfile` | Copies Moodle into an image and installs its locked Composer dependencies; serves only `public/` |
| `docker/rosariosis/Dockerfile` | Copies RosarioSIS into its own image and enables PHP gettext |
| `docker/moodle/config.php` | Supplies Moodle's database, site address, timezone, and data-directory settings |
| `docker/rosariosis/config.inc.php` | Supplies RosarioSIS's database, address, school year, and locale settings |
| `docker/postgres/01-databases.sh` | Creates two databases with separate owners and denies cross-application database access |
| `docker/php.ini` | Sets upload sizes, PHP limits, logging, and timezone |
| `docker/health.php` | Checks PHP-to-database connectivity without changing data; reports whether the application's configuration table exists |
| `scripts/init-env.py` | Generates database passwords once in the ignored, owner-readable `.env` |
| `scripts/check-sources.py` | Checks that both clones are clean and match the recorded commits |

These are platform files. No tracked file in either application fork is edited. Runtime configuration is copied into the images; passwords are supplied when containers start. Source changes do not appear in the running applications until their images are rebuilt.

The development base is MoodleHQ's PHP 8.3 Apache image, pinned by digest, and the database image is PostgreSQL 16 Alpine, also pinned by digest. Composer 2.8.12 is verified using its published checksum. The PHP image is a development image; production hardening and dependency review remain future work.

## Addresses and routing

| Application | Local address |
| --- | --- |
| Moodle | http://learn.aou.test:8080 |
| RosarioSIS | http://sis.aou.test:8081 |

Both published ports bind to `127.0.0.1`, so they are accessible from this laptop. PostgreSQL has no published port. We use Apache directly for this first milestone; a common HTTPS reverse proxy is deferred until staging.

The `.test` names are Docker network aliases inside the containers. On the laptop, add this line to `/etc/hosts` using `sudoedit /etc/hosts`:

```text
127.0.0.1 learn.aou.test sis.aou.test
```

This step is required even when `http://localhost:8081` opens RosarioSIS: Moodle redirects to its configured `learn.aou.test` address. Add it only once and preserve existing entries. This is a local name mapping, not a public DNS/domain registration. If either name already maps to a different address, resolve that conflict first. Removing this line later undoes the mapping.

Using the same name and port inside and outside Docker avoids Moodle redirecting API calls to an unreachable address. Do not replace these names with `localhost` in the connector: inside RosarioSIS, `localhost` means the RosarioSIS container. `.localhost` domains are also avoided because curl can resolve them directly to loopback instead of Docker DNS.

For an already installed laptop and the edit/rebuild workflow, follow [Run and develop AOU](run-and-develop.md).

## First setup (fresh databases only)

Run commands from `/home/elias/projects/neuronet-school-platform`.

1. Verify source versions without editing them:

   ```bash
   python3 scripts/check-sources.py
   ```

2. Generate local database passwords if `.env` does not already exist:

   ```bash
   python3 scripts/init-env.py
   ```

   The script refuses to replace an existing file. These are database passwords, not school administrator logins. Do not regenerate them for an existing database: PostgreSQL initialization runs only when its data volume is empty.

3. Validate and build:

   ```bash
   docker compose config --quiet
   docker compose build
   ```

   The first build downloads Composer and Moodle dependencies. Repeated builds reuse cached layers and a persistent Composer download cache. Downloads use IPv4, two concurrent HTTP requests, and an extended timeout to accommodate a slow connection. A successful clone alone does not install Moodle's PHP dependencies.

4. Start the local services:

   ```bash
   docker compose up -d
   docker compose ps
   ```

5. Open Moodle at http://learn.aou.test:8080/admin/index.php. Complete its installation screens and choose your administrator username, password, and email. Use `Africa Online University Platform` as the site name and `AOU` as the short name. Database settings are already provided by the platform configuration.

6. Open http://sis.aou.test:8081/InstallDatabase.php to initialize RosarioSIS's empty database. This is an installation action: it creates the application's tables and bundled template accounts. If it says the database is already installed, proceed to http://sis.aou.test:8081/.

   The upstream initial administrator login is `admin` / `admin`. Change its password during first setup and review the other template accounts. Set the school name to `Africa Online University` using the school setup screens. Do not change the configured `2026` school year independently of RosarioSIS's rollover workflow.

7. After Moodle installation completes, start scheduled tasks:

   ```bash
   docker compose --profile tasks up -d moodle-cron
   docker compose logs --tail=40 moodle-cron
   ```

   The task process runs as the web-server user and invokes Moodle cron approximately once per minute after the previous run finishes. It is not enabled before installation.

If a command reports permission denied for `/var/run/docker.sock`, the terminal account lacks Docker access. This session verified engine access through its elevated tool permission. Do not make the Docker socket world-writable; use your existing authorized Docker access or resolve the account configuration separately.

## Everyday commands

```bash
# Start installed services, including background tasks.
docker compose --profile tasks up -d

# Inspect service status and recent application logs.
docker compose --profile tasks ps
docker compose logs --tail=50 moodle rosariosis

# Stop all Africa Online University services, retaining data and containers.
docker compose --profile tasks stop

# Restart web services after a temporary problem.
docker compose restart moodle rosariosis
```

After changing platform image/configuration files, run `docker compose build` and then `docker compose --profile tasks up -d`. A plain restart does not rebuild an image or apply changed Compose environment variables.

## Data and database separation

Docker volumes persist independently of application container replacement:

- `neuronet-dev_postgres-data`: both databases.
- `neuronet-dev_moodledata`: Moodle uploads, cache, and other application data.
- `neuronet-dev_rosario-files`: RosarioSIS file attachments.
- `neuronet-dev_rosario-students`: student photos.
- `neuronet-dev_rosario-users`: staff/user photos.

Stopping or recreating containers retains these volumes. `docker compose down -v` deletes Compose-managed volumes and must not be used as a routine restart. This migrated laptop uses external volumes, which Compose does not delete, but do not rely on that exception for other installations. Volumes are not backups. A coordinated backup/restore script and a restore drill remain pending; use only test data at this stage.

The database service receives both application database passwords to create the roles. Each application receives only its own database password. The shared server's PostgreSQL administrator still has access to both databases.

For the current AOU names and hostname setup, see [the rename guide](aou-rename.md). Existing data volumes retain their original identifiers through `AOU_VOLUME_PREFIX=neuronet-dev` in the local `.env`; fresh installations use `aou-dev`. Do not change that prefix on this installation without migrating its data.

## What is intentionally pending

- The SIS-to-Moodle API account and token pass the connection test. Matching logins and enrollment synchronization have not been tested; see [connection settings](moodle-connection.md).
- Separate logins are the working assumption. SSO is not installed.
- Moodle email is disabled and RosarioSIS mail is discarded locally. Account activation and password-reset email cannot be tested until a mail catcher is added.
- RosarioSIS reports use HTML; PDF generation is not installed yet.
- Automatic add-on code installation is disabled. Proposed fork code edits must be explained before changes are made.
- Health checks verify HTTP/PHP-to-database connectivity and report `schema_present`. They do not prove that administrator setup, cron, synchronization, or all application features work. Passwords must use the 48-character hexadecimal format generated by `init-env.py`.
- Test account records exist. Full teacher assignment and student enrollment acceptance, production domains, and production secrets remain pending.

## Next integration milestone

Once both administrator dashboards work, configure the school year, one test teacher, one student, and one class/course period. Enable the existing RosarioSIS Moodle plugin, configure the Moodle REST service with a dedicated restricted account, and test creation, assignment, class changes, withdrawal, duplicates, and recovery after an outage. Document the actual API functions, role permissions, menu paths, and results before calling synchronization complete.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Site name does not resolve | Add the `/etc/hosts` line above; use the exact `.test` name and port |
| Connection refused | Run `docker compose ps` and check startup logs; confirm ports 8080/8081 are free |
| Build download fails | Keep the clones; retry the build so completed dependency layers can be reused |
| GitHub SSL/DNS timeouts during Composer installation | Use a stable connection and retry `docker compose build moodle`; cached completed downloads are reused when still present. Do not disable TLS verification or clone the applications again |
| Database password failure after editing `.env` | Restore the original password; editing `.env` does not change existing PostgreSQL roles |
| Moodle says dependencies are missing | Confirm the image build completed its Composer step |
| RosarioSIS reports `relation "config" does not exist` | Its database exists but its tables are missing. Run its `InstallDatabase.php` step once; the current laptop instance has now completed this step |
| Moodle installation reports a PHP/database requirement | Capture the exact message and compare with the pinned source/runtime versions |
| API requests later redirect or fail | Use `http://learn.aou.test:8080` as the connector's Moodle base URL |

Do not paste `.env`, API tokens, or full `docker compose config` output into issue reports. `docker compose config --quiet` validates without displaying credentials.
