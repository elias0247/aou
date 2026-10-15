#!/bin/sh
set -eu
trap 'exit 0' TERM INT
while :; do
    php /var/www/html/admin/cli/cron.php || echo 'Moodle cron failed; inspect the task logs.' >&2
    sleep 60 &
    wait "$!"
done
