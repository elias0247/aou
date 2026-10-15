<?php
// Run inside the installed RosarioSIS container; only branding settings change.
require '/var/www/html/config.inc.php';
$db = pg_connect("host=$DatabaseServer port=$DatabasePort dbname=$DatabaseName user=$DatabaseUsername password=$DatabasePassword");
if (!$db) {
    throw new RuntimeException('Database connection failed.');
}
function query($sql, $params = []) {
    global $db;
    $result = pg_query_params($db, $sql, $params);
    if (!$result) {
        throw new RuntimeException('Branding query failed.');
    }
    return $result;
}
query('BEGIN');
try {
    $schools = pg_fetch_all(query('SELECT id, title, short_name FROM schools ORDER BY id'));
    if (count($schools) !== 1 || (int) $schools[0]['id'] !== 1) {
        throw new RuntimeException('Expected the single-school installation (ID 1).');
    }
    $config = pg_fetch_all(query("SELECT title, config_value FROM config WHERE school_id=0 AND title IN ('TITLE','NAME')"));
    $urls = pg_fetch_all(query("SELECT school_id, syear, value FROM program_config WHERE program='moodle' AND title='MOODLE_URL'"));
    foreach ($urls as $url) {
        if (!in_array(rtrim($url['value'], '/'), ['http://learn.neuronet.test:8080', 'http://learn.aou.test:8080'], true)) {
            throw new RuntimeException('Unexpected Moodle URL; review it before applying this local migration.');
        }
    }
    echo json_encode(['previous' => ['schools' => $schools, 'config' => $config, 'moodle_urls' => $urls]]), PHP_EOL;
    query('UPDATE schools SET title=$1, short_name=$2 WHERE id=1', ['Africa Online University', 'AOU']);
    foreach (['TITLE' => 'Africa Online University Platform', 'NAME' => 'AOU'] as $key => $value) {
        $result = query('UPDATE config SET config_value=$1 WHERE school_id=0 AND title=$2', [$value, $key]);
        if (pg_affected_rows($result) !== 1) {
            throw new RuntimeException('Expected one global branding setting.');
        }
    }
    query("UPDATE program_config SET value=$1 WHERE program='moodle' AND title='MOODLE_URL'", ['http://learn.aou.test:8080']);
    query('COMMIT');
    echo "RosarioSIS title and school renamed to AOU; Moodle connector URL updated.\n";
} catch (Throwable $error) {
    query('ROLLBACK');
    throw $error;
}
