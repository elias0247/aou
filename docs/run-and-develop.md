# Run and develop Africa Online University Platform

Repository: [elias0247/Africa-Online-University-Platform](https://github.com/elias0247/Africa-Online-University-Platform).

This guide covers the existing laptop installation. Both applications and their databases are already installed. Do not rerun installers or replace `.env` to start them again. A fresh machine instead needs the [source checkout instructions](../sources/README.md) and [first setup](local-development.md#first-setup-fresh-databases-only).

## 1. Open the current project directory

```bash
cd /home/elias/projects/neuronet-school-platform
docker --version
docker compose version
```

The GitHub rename does not rename this laptop folder. Start Docker if its engine is not running. Keep commands in this directory so Compose reads the correct `.env` and data-volume settings.

## 2. Check the local names

```bash
getent hosts learn.aou.test sis.aou.test
```

Both should resolve to `127.0.0.1` on this laptop. The entries are present as checked on 2026-10-07. If missing on another machine, open `sudoedit /etc/hosts` and add this line once:

```text
127.0.0.1 learn.aou.test sis.aou.test
```

These hostnames are also configured inside the Docker network so RosarioSIS can reach Moodle.

## 3. Validate and build when needed

```bash
docker compose config --quiet
```

The current images are already built. Run the following only when building for the first time, after code/image-file changes, or when an image is missing:

```bash
docker compose build
```

Retain the current `.env`. It contains the existing database credentials and points to the original data volumes through `AOU_VOLUME_PREFIX=neuronet-dev` and `AOU_VOLUMES_EXTERNAL=true`. Changing that prefix does not rename or migrate the data.

## 4. Start the entire installed stack

```bash
docker compose --profile tasks up -d --wait
docker compose --profile tasks ps
```

Expected: `db`, `moodle`, and `rosariosis` are healthy; `moodle-cron` is running. Cron has no separate Docker health check. It executes Moodle scheduled tasks, then waits approximately one minute before running again. It is appropriate to enable it here because Moodle is installed.

## 5. Open and verify the sites

- Moodle: <http://learn.aou.test:8080>
- RosarioSIS: <http://sis.aou.test:8081>

Log in with your existing administrator accounts. Both sites use the title **Africa Online University Platform**. They still have separate login sessions.

In RosarioSIS, open **School → Configuration → Plugins → Moodle** and click **Test**. The Moodle URL must be `http://learn.aou.test:8080`, protocol REST, with the existing saved token. A passing connection test confirms a read request, not complete user/course/enrollment synchronization.

For local troubleshooting:

```bash
docker compose logs --tail=60 moodle-cron
docker compose logs --tail=60 moodle rosariosis db
```

Inspect logs locally. The connector can put its token in request URLs, so redact tokens and personal details before sharing logs. Do not share `.env` or full rendered Compose configuration.

## 6. Stop and resume

```bash
# Stop all four services while keeping their containers and data.
docker compose --profile tasks stop

# Resume later, including after reboot.
docker compose --profile tasks up -d --wait
```

Do not use `down -v`, prune volumes, or delete data directories for a routine restart. This stack has no automatic restart policy; start it explicitly after reboot. A coordinated backup/restore workflow remains to be completed before real school data is used.

## Editing code and running the changes

Yes, you can edit the downloaded source. An image contains a snapshot of that source at build time. There is no live source mount, so saving a file alone does not update a running application.

| What you change | Edit here | How to apply it |
| --- | --- | --- |
| Moodle behavior, theme, or plugin code | `sources/moodle/` (web application code is under `public/`) | Rebuild Moodle and recreate both Moodle and cron |
| RosarioSIS behavior, theme, or plugin code | `sources/rosariosis/` | Rebuild and recreate RosarioSIS |
| Moodle runtime configuration | `docker/moodle/config.php` | Rebuild Moodle and recreate both Moodle and cron |
| RosarioSIS runtime configuration | `docker/rosariosis/config.inc.php` | Rebuild and recreate RosarioSIS |
| Dockerfiles, Apache or PHP settings | `docker/` | Rebuild and recreate the affected services |
| Compose environment, ports, or networking | `compose.yaml` | Run Compose `up -d` to apply changed service configuration |
| Platform docs | `README.md`, `docs/` | No image rebuild needed |
| School name, site settings, users | Application administration screens | Usually saved directly to the database |

The platform Dockerfiles supply the application configuration files. Editing a fork's `config.php` or `config.inc.php` is not the way to change the container configuration. Files edited only inside a running container disappear when it is replaced.

For a Moodle change:

```bash
docker compose build moodle
docker compose --profile tasks up -d --wait moodle moodle-cron
docker compose exec -T --user www-data moodle php /var/www/html/admin/cli/purge_caches.php
```

For a RosarioSIS change:

```bash
docker compose build rosariosis
docker compose up -d --wait rosariosis
```

For changes to both:

```bash
docker compose build
docker compose --profile tasks up -d --wait
```

Only run the start/recreate command after the build succeeds. A plain `docker compose restart` neither builds code nor applies changed Compose environment settings. Refresh the browser and test the specific changed behavior. Moodle frontend source changes may also require compiling its assets; a plugin version or database change may require its upgrade procedure. The Docker rebuild does not perform every application-specific development step automatically.

## Keep source changes reproducible

The platform and the two forks are three independent repositories. A push to the platform repository does not include `sources/moodle/` or `sources/rosariosis/`.

Before a fork edit, inspect its status and create a feature branch. For example, for Moodle:

```bash
git -C sources/moodle status
git -C sources/moodle switch -c aou/my-change
```

After making and testing the change, review the diff, commit selected files, and push the feature branch to that application's own `origin`. Then update the component's commit in `components.json` to the tested commit and publish the platform manifest separately. Keep Moodle and RosarioSIS version metadata accurate when their actual versions change.

`python3 scripts/check-sources.py` deliberately rejects uncommitted source changes and commits that differ from the manifest. During an intentional local edit that rejection is expected; Docker can still build the edited source. Before publishing a reproducible baseline, commit the work, update the manifest, and require the checker to pass. Do not change the manifest merely to silence an unexplained mismatch.

We will continue explaining proposed fork edits before making them. No application fork code was changed while writing this guide.

## Platform Git checkout in this workspace

The reserved `.git` directory in the shared workspace is not a usable checkout. For this session the publication checkout is `/tmp/aou-platform-publish`, whose origin points to the renamed GitHub repository. It is temporary, not a durable working copy or backup.

For normal development, use a valid clone of the platform repository and retain the existing `.env`, source checkouts, and data-volume references when relocating this installation. Do not start two copies of the Compose project at once. For a valid existing Git checkout, the remote is:

```bash
git remote set-url origin git@github.com:elias0247/Africa-Online-University-Platform.git
```

## Current limits

Starting all four containers starts the complete current local stack. It does not create a separate custom dashboard or enable shared login, real email delivery, production HTTPS, or fully tested enrollment synchronization. The services are accessible only from this laptop. Server deployment remains a separate milestone.
