<?php
// Platform-owned configuration, copied into the image, never into the fork.
unset($CFG);
global $CFG;
$CFG = new stdClass();
$CFG->dbtype = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost = 'db';
$CFG->dbname = 'moodle';
$CFG->dbuser = 'moodle';
$CFG->dbpass = getenv('MOODLE_DB_PASSWORD');
$CFG->prefix = 'mdl_';
$CFG->dboptions = ['dbpersist' => false, 'dbport' => 5432, 'dbsocket' => false];
$CFG->wwwroot = getenv('MOODLE_URL');
$CFG->dataroot = '/var/www/moodledata';
$CFG->directorypermissions = 02770;
$CFG->admin = 'admin';
$CFG->timezone = 'Africa/Addis_Ababa';
// Local development only: email is disabled until a mail catcher is configured.
$CFG->noemailever = true;
$CFG->disableupdateautodeploy = true;
require_once(__DIR__ . '/lib/setup.php');
