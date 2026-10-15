# RosarioSIS to Moodle connection

Connection verified on 2026-09-28. The addresses below reflect the AOU rename on 2026-10-07; see [rename details](aou-rename.md).

## Settings and addresses

In RosarioSIS, open **School → Configuration → Plugins → Moodle**:

| Setting | Local value |
| --- | --- |
| Moodle URL | `http://learn.aou.test:8080` |
| API protocol | `REST` |
| Moodle Token | The replacement token already saved locally; never put it in Git or screenshots |

Use the Moodle base URL, without `/admin` or `/webservice/rest/server.php`. The connector appends the endpoint itself. Docker resolves this hostname to the Moodle container; the laptop resolves it through `/etc/hosts`. Inside RosarioSIS, `localhost` refers to the RosarioSIS container.

## Resolved access-control error

The URL was correct. Moodle rejected the request because the dedicated integration account lacked permissions and its custom service lacked the test function.

The existing Moodle account is `rosariosis_api`, its custom role has short name `siteadmin`, and its custom service is `rosariosis_func`. The role name alone grants no permissions. We verified that this role was assigned only to the integration account before updating it.

The following configuration was added using Moodle's own administration APIs:

| Setting | Purpose |
| --- | --- |
| Role capability `webservice/rest:use`, Allow at system context | Permits the account to call REST web services |
| Role capability `moodle/user:viewdetails`, Allow at system context | Permits the user lookup used by the connection test |
| Service function `core_user_get_users` | Makes that lookup available through this token's service |

To inspect these through Moodle, use **Site administration → Users → Permissions → Define roles** for role capabilities, and **Site administration → Server → Web services → External services → rosariosis_func → Functions** for service functions. Web services, REST, and the custom service were already enabled.

RosarioSIS's Test action calls `core_user_get_users` with an ID criterion of `2`. We reproduced that request from the RosarioSIS container. It failed before the changes and returned the expected user successfully afterward.

The token shown in the screenshot was replaced. The replacement was tested, saved to RosarioSIS, and tested again after revoking the exposed token. No credentials are recorded in this document. **Reload the RosarioSIS configuration page before clicking TEST; do not save the old open form.**

Only application settings stored in the databases changed. Neither fork's source code was edited. The changes persist in the database volume; rebuilding the images does not recreate these settings on a fresh database.

## What remains before synchronization

This verifies the connection and a read operation. Creating users, courses, and enrollments needs additional service functions and capabilities, mapped to the installed connector's actual calls. The existing service also contains unrelated functions selected during initial setup; review and narrow that list during synchronization setup rather than enabling all functions.

The Parent role was subsequently created as ID `10`, and that ID was saved in RosarioSIS after diagnosing disabled synchronization hooks. Student email mapping currently uses Username, so students need an email address as their username unless a separate email field is configured. The parent workflow still needs testing.

Next, use one test teacher, one test student, and one course period to configure and verify user creation, teacher assignment, student enrollment, changes, withdrawal, duplicate prevention, and recovery after a failed request. Record each result before using real school data. A successful connection test does not establish shared login or full synchronization.
