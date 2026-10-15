<?php
// Platform-owned runtime settings. The fork's config sample is left unchanged.
$DatabaseType = 'postgresql';
$DatabaseServer = 'db';
$DatabasePort = '5432';
$DatabaseUsername = 'rosariosis';
$DatabasePassword = getenv('ROSARIO_DB_PASSWORD');
$DatabaseName = 'rosariosis';
$RosarioURL = getenv('ROSARIO_URL');
$RosarioPath = __DIR__ . '/';
$Timezone = 'Africa/Addis_Ababa';
$DefaultSyear = '2026'; // Matches the selected source's initial database; change only after rollover.
$RosarioLocales = ['en_US.utf8'];
$RosarioNotifyAddress = '';
$RosarioErrorsAddress = '';
$wkhtmltopdfPath = ''; // HTML reports initially; PDF generation is a later step.
define('ROSARIO_DISABLE_ADDON_UPLOAD', true);
define('ROSARIO_DISABLE_ADDON_DELETE', true);
define('ROSARIO_DISABLE_USAGE_STATISTICS', true);
