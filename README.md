# Africa Online University Platform

A platform for one school named **Africa Online University (AOU)**, combining RosarioSIS for school administration and Moodle for learning.

Both applications were renamed and verified locally on 2026-10-07. Their pages display **Africa Online University Platform**. The AOU hostname mappings are now present on this laptop. New machines need the [same hostname setup](docs/aou-rename.md#laptop-hostname-setup). The RosarioSIS-to-Moodle REST connection test passes using the configured service account. Student, teacher, course, and enrollment synchronization still require configuration and testing. This is a development environment using test data; production deployment is a later milestone.

Start with [the step-by-step run and development guide](docs/run-and-develop.md). See [local setup and configuration details](docs/local-development.md) for fresh installations. Also read [the phase-one plan](docs/phase-1-plan.md), [implementation and usage](docs/implementation-and-use.md), and [source checkout details](sources/README.md).

See [Moodle connection settings](docs/moodle-connection.md) for the API setup and resolved access error. See [verification results and outstanding work](docs/verification.md) for what has actually been tested.

## Working agreement

Before editing Moodle or RosarioSIS repository code, explain the proposed change to the user, including its purpose, affected files, behavior, and validation. Both application checkouts remain unchanged. Docker images contain copies of their source plus platform-owned runtime configuration and readiness checks.

## Repository layout

```text
Africa-Online-University-Platform/
  compose.yaml         # laptop services, network, and persistent volumes
  components.json      # exact development source commits
  .env.example         # template; real .env is ignored
  docker/              # Dockerfiles and runtime configuration
  scripts/             # environment generation and source verification
  docs/                # design, setup, usage, and test results
  sources/
    moodle/            # independent fork checkout, ignored here
    rosariosis/        # independent fork checkout, ignored here
```

Moodle is pinned to 5.2.3, and RosarioSIS identifies itself as 13.0 at the recorded commit. See `components.json`. Pushing this platform repository does not push either application fork.

The platform repository is [elias0247/Africa-Online-University-Platform](https://github.com/elias0247/Africa-Online-University-Platform). See [the AOU rename guide](docs/aou-rename.md) for local addresses, data preservation, and Git remote updates. School records, uploaded files, database passwords, API tokens, and backups stay outside Git.
