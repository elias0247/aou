# Phase one: establish a working local foundation

Prepared 2026-09-20. The supplied deployment guide was reviewed as reference material. This plan follows the user's request to begin on the laptop and prepare for a later server deployment.

## What each part does

| Part | Responsibility |
| --- | --- |
| RosarioSIS | Official students, staff, classes, school enrollment, and attendance |
| Moodle | Learning materials, assignments, quizzes, and learning activity |
| Africa Online University platform repository | Configuration that installs, connects, tests, and deploys the applications |
| PostgreSQL | Separate application databases and separate database users |

Start with RosarioSIS sending selected administrative changes to Moodle through Moodle's API. Neither application should write directly into the other's database. Moodle learning grades and official SIS report-card grades remain separate until an explicit transfer workflow is designed.

Use the existing connector first and prefer supported plugins, modules, and themes for customization. A shared login is a separate feature from keeping accounts synchronized.

## Decisions proposed for the first prototype

- Confirmed scope: one school named **Africa Online University**, with fake students and teachers during development.
- Separate application logins initially, pending the user's preference.
- Docker Compose on the laptop and server, with environment-specific configuration.
- Moodle, RosarioSIS, PostgreSQL, a small reverse proxy, and Moodle scheduled tasks as the initial runtime. Add a local mail catcher when testing account emails. Add Redis when justified by measured needs.
- Keep the existing Moodle and RosarioSIS forks. The coordinating repository is [elias0247/Africa-Online-University-Platform](https://github.com/elias0247/Africa-Online-University-Platform). Its existing visibility will be preserved.
- Keep new software as separately configurable services connected through APIs. Add an integration service only when the existing connector cannot meet an agreed requirement.

## Laptop checks already performed

| Check | Observed result |
| --- | --- |
| Operating system | Ubuntu 24.04.4 LTS, x86_64 |
| Memory | About 7.1 GiB total; about 2.5 GiB available during inspection |
| Disk | About 329 GiB available on the project filesystem |
| Git | Installed, version 2.43.0 |
| Docker CLI / Compose | Installed, versions 29.7.1 / v5.4.0 |
| Docker engine access | Verified on 2026-09-21 using elevated tool access; ordinary sandbox calls cannot access the socket |
| Platform Git repository | `git status` reports this is not a Git repository; the reserved `.git` directory is read-only in this session |
| GitHub access | `gh` is not installed; Git over SSH was subsequently verified and used to publish the planning files |

This appears suitable for a modest local prototype, subject to actual container memory usage. It is not a production sizing result. Close memory-heavy applications during builds if needed. No host packages or permissions were changed.

## Work sequence and completion checks

1. **Confirm scope and repository ownership.** Done: one school named Africa Online University, platform repository owned by `elias0247`, and both fork URLs supplied. Shared login timing remains to be confirmed; both cloned source trees are currently clean. Explain any proposed fork code edits to the user before making them.
2. **Prepare GitHub and source code.** The platform repository exists and the initial plan is published. Resolve the workspace Git metadata restriction before initializing this folder. Both source clones are complete. Their exact development commits are recorded in `components.json`; see [checkout details](../sources/README.md).
3. **Choose compatible supported versions.** Inspect the forks, their installation requirements, plugin requirements, PHP extensions, database requirements, and web-root layout. Select released versions and pin them. Do not assume the fork default branch is a stable release.
4. **Build the local environment.** Verify Docker engine access; create application images and Compose definitions. Use distinct databases and database users, persistent file storage, local-only published ports, and URLs that work from both the browser and the connector container. Configure Moodle cron and local email testing. Provide `.env.example` and keep real credentials in ignored local configuration.
5. **Prove the smallest integration.** Configure a dedicated Moodle API account with only the functions and capabilities required by the selected connector. Create a test teacher, student, class, and course in RosarioSIS, then verify the corresponding Moodle account, role, and enrollment. Document the exact trigger for each action.
6. **Check failures and recovery.** Repeat actions and check for duplicates; test student updates, class changes, withdrawal, and a temporary Moodle outage. Record what the connector retries and what requires manual repair. Verify data survives a container restart. Back up both databases and application files, restore into a separate test environment, and check the restored records and attachments.

Phase one is complete when a documented local setup can reproduce that test workflow, both applications retain their data after restart, and the backup can be restored. The planning files alone do not complete the running prototype.

## Integration questions that must be tested

- The upstream connector documents a one-school limit and restrictions on annual rollover. Multiple schools should initially use isolated deployments; a shared installation needs separate design. See the [connector README](https://github.com/francoisjacquet/rosariosis/blob/mobile/plugins/Moodle/README.md).
- Applicant approval, suspension versus deletion, retry handling, and duplicate prevention are requirements to verify, not assumed connector guarantees. Start with administrator-created test records, then test the agreed applicant workflow before enabling public registration.
- Use durable identifiers scoped to the school and preserve mappings to each application ID. Inspect the connector's existing mapping before introducing an additional Africa Online University identifier. Names and emails alone are insufficient identifiers.
- Decide how school subjects, class sections, terms, and teacher assignments map to Moodle courses. Test rollover before real use.
- Keep learner work when a student withdraws; test access removal and retention behavior before choosing a production workflow.

## Version research

The user selected Moodle 5.2.3, verified locally at commit `344232c15336c71b80f9aca8359ce0e0a9f3d116`. The RosarioSIS checkout declares 13.0 and is recorded at `5ece843bb8b4589b8568cb0da1929ff03da7e433`. The earlier 5.3beta inspection is historical; it is not the selected local baseline.

The local development images use PHP 8.3 and PostgreSQL 16. Moodle's published 5.2 minimums are PHP 8.3 and PostgreSQL 16; see [Moodle 5.2 requirements](https://moodledev.io/general/releases/5.2). Full application and connector acceptance remains pending. See [local development](local-development.md) for implemented files, setup, and usage.

## Moving to a server later

Promote the tested code through versioned images to a staging environment, then production. Server configuration supplies domains, HTTPS, secrets, real email, backup destinations, and monitoring. Transfer application data using a coordinated database-and-files backup and a tested restore; Git contains the software and configuration, not school data.

Before launch, test permissions, email, cron, synchronization failure recovery, backup restoration, and expected concurrent usage. Record a rollback procedure that accounts for database changes; switching to an old image alone may not undo an upgrade.

Before server sizing, collect expected student/staff counts, peak concurrent users, file/video storage needs, and whether the server will be in the school or hosted elsewhere. Languages, calendar requirements, and any existing student data need confirmation before implementing the school workflows.

Payments, SMS, library software, mobile apps, analytics, and other integrations can follow after the basic student-to-course workflow is dependable.

## Current handoff

Completed: guide review, confirmed one-school scope, both source clones, exact development commit recording, Docker engine access verification, local Compose configuration, a RosarioSIS image build, separate database provisioning and runtime checks during the earlier session, and setup/usage documentation.

The user requires advance explanation of any Moodle or RosarioSIS code edits. All new code lives in the platform repository; both fork working trees remain unchanged.

Current status on 2026-09-28: both images have built and all three services are running. RosarioSIS's official installer initialized its empty database; its login page loads. Moodle's installer responds when the configured hostname is supplied. The laptop's hostname mapping and Moodle installation remain pending. See [verification results](verification.md).

Pending: adding laptop hostname mappings, completing Moodle installation and both administrator setups, shared-login timing, connector configuration and lifecycle tests, mail catcher setup, backup/restore implementation and testing, and staging/production configuration. Runtime checks do not complete phase-one integration acceptance.
