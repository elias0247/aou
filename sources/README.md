# Application source checkouts

Both clones are present and clean as checked on 2026-09-21:

| Application | Local branch | Version | Commit |
| --- | --- | --- | --- |
| Moodle | `neuronet-5.2` | 5.2.3 | `344232c15336c71b80f9aca8359ce0e0a9f3d116` |
| RosarioSIS | `mobile` | 13.0 | `5ece843bb8b4589b8568cb0da1929ff03da7e433` |

Moodle's local `origin` points to `elias0247/neuronet.moodle`, and its `upstream` points to the official Moodle repository. RosarioSIS's `origin` points to `elias0247/neuronet.rosariosis`. No fork source edits or pushes were performed by the platform setup.

## Recreating the checkouts on another machine

Skip these commands on this laptop, where the checkouts already exist. Do not overwrite or delete existing source directories.

```bash
cd /path/to/Africa-Online-University-Platform

git clone --depth 1 --branch v5.2.3 https://github.com/moodle/moodle.git sources/moodle
git -C sources/moodle remote rename origin upstream
git -C sources/moodle remote add origin git@github.com:elias0247/neuronet.moodle.git
git -C sources/moodle switch -c neuronet-5.2

git clone --depth 1 --single-branch --branch mobile git@github.com:elias0247/neuronet.rosariosis.git sources/rosariosis
python3 scripts/check-sources.py
```

RosarioSIS's `mobile` branch can move. If verification reports a different commit, fetch the recorded commit and create a local baseline branch:

```bash
git -C sources/rosariosis fetch --depth 1 origin 5ece843bb8b4589b8568cb0da1929ff03da7e433
git -C sources/rosariosis switch -c neuronet-baseline 5ece843bb8b4589b8568cb0da1929ff03da7e433
python3 scripts/check-sources.py
```

These commands are for a new, clean checkout. Stop and review any existing modifications before switching branches. The repository owner must retain these commits for this baseline to remain fetchable.

Both source directories are ignored by the parent platform repository and retain their own histories. Explain proposed source edits to the user before making them. Platform builds use copies of these directories; they do not modify the original checkouts.

Exact source commits are recorded in [components.json](../components.json). See [local development](../docs/local-development.md) for building and running them. The earlier Moodle 5.3beta branch is not the selected local baseline.
