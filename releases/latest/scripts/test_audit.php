<?php
define('BASEPATH', __DIR__ . '/system/');
require_once __DIR__ . '/application/config/database.php';
echo "DB OK: " . $db['default']['database'] . "\n";
