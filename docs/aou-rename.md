# Africa Online University Platform rename

Applied on 2026-10-07.

| Item | Name |
| --- | --- |
| Institution | Africa Online University |
| Abbreviation | AOU |
| Platform / dashboard title | Africa Online University Platform |
| GitHub repository | [`Africa-Online-University-Platform`](https://github.com/elias0247/Africa-Online-University-Platform) |
| Docker Compose project | `aou-dev` |
| Moodle image | `aou/moodle:5.2.3-dev` |
| RosarioSIS image | `aou/rosariosis:13.0-dev` |
| Moodle address | `http://learn.aou.test:8080` |
| RosarioSIS address | `http://sis.aou.test:8081` |

There is no separate custom dashboard application in this repository yet. The requested title is applied to the Moodle site and RosarioSIS program title, and is the name to use for the future platform dashboard. Application names such as Moodle and RosarioSIS still identify the underlying software.

## Laptop hostname setup

The containers resolve the new names through their Docker network aliases. Add the browser mappings once on the laptop:

```bash
printf '\n127.0.0.1 learn.aou.test sis.aou.test\n' | sudo tee -a /etc/hosts
```

This requires the laptop's sudo password. The old hostname entry may remain while old bookmarks are updated; use the AOU addresses going forward. These are local development addresses, not registered public domains.

## Existing data is preserved

The existing local `.env` retains its database passwords and sets:

```dotenv
COMPOSE_PROJECT_NAME=aou-dev
AOU_VOLUME_PREFIX=neuronet-dev
AOU_VOLUMES_EXTERNAL=true
```

This deliberately attaches the renamed containers to all five original volumes: PostgreSQL, Moodle data, RosarioSIS attachments, student photos, and staff photos. The old volume names are storage identifiers, not visible branding. External volumes are managed separately from Compose; do not delete them or change the prefix to `aou-dev` on this installation. Doing so would disconnect the applications from their existing data.

Fresh installations generated with `scripts/init-env.py` use the `aou-dev` volume prefix and `AOU_VOLUMES_EXTERNAL=false`, so Compose creates their volumes. Never replace an existing `.env` with the example or regenerate its passwords.

The renamed health endpoint is `/aou-health.php`; the cron script inside the Moodle image is `/opt/aou/cron.sh`. The cron profile remains optional.

## Application settings

After installing both applications on a fresh instance, apply the names with:

```bash
sh scripts/apply-branding.sh
```

The platform-owned PHP helpers are copied temporarily into the containers; neither source checkout is edited. Moodle uses its site-settings API to set the full site name and short name, then clears caches. RosarioSIS updates its global program title/name and the single institution's title/short name in a transaction. Its existing Moodle connector URL becomes `http://learn.aou.test:8080`; the API token is retained. The helper checks for the expected single school and known local URL before changing it.

These scripts require installed databases and may be rerun. They print previous names and addresses, never tokens. They do not create accounts, change passwords, or claim that enrollment synchronization is complete. They do not replace old URLs embedded in arbitrary course content; review existing authored content separately if it contains old absolute links.

## GitHub and local folder

The owner renamed the repository to `Africa-Online-University-Platform`. In a valid local Git checkout, update the remote with:

```bash
git remote set-url origin git@github.com:elias0247/Africa-Online-University-Platform.git
git remote -v
```

The working directory for this session remains `/home/elias/projects/neuronet-school-platform`. Renaming that folder is separate from branding and should be done after closing this workspace and reopening its new location. The reserved `.git` directory in this workspace is not a usable Git checkout, so publication uses a temporary checkout.

The two application fork URLs and Moodle's existing local branch name remain valid source references. They are not renamed or pushed by this platform change.

## Recovery

The old application images and original volumes are retained. To return to the previous local setup, stop the AOU containers without deleting volumes, restore the prior platform configuration and `.env`, and start that Compose project. Restore the connector URL to `http://learn.neuronet.test:8080` and update the visible names through the applications' administration screens. The original Compose file and `.env` were temporarily copied to `/tmp/aou-rename-original`; this private temporary directory is not a durable backup.

A full database/file backup and restore drill remains a separate outstanding task. The rename preserves the original volumes in place; it does not create a duplicate backup of their data.
