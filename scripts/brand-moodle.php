<?php
// Run inside the installed Moodle container; this is platform code, not a fork edit.
define('CLI_SCRIPT', true);
require '/var/www/html/config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/course/lib.php';
\core\session\manager::set_user(get_admin());
$site = $DB->get_record('course', ['id' => SITEID], '*', MUST_EXIST);
echo json_encode(['previous' => ['fullname' => $site->fullname, 'shortname' => $site->shortname]]), PHP_EOL;
foreach ([
    'fullname' => 'Africa Online University Platform',
    'shortname' => 'AOU',
] as $key => $value) {
    $setting = new admin_setting_sitesettext($key, $key, '', null);
    $error = $setting->write_setting($value);
    if ($error !== '') {
        throw new RuntimeException($error);
    }
}
purge_all_caches();
echo "Moodle site title: Africa Online University Platform; short name: AOU.\n";
