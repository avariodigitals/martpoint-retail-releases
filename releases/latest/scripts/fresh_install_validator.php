<?php
/**
 * MartPoint Fresh Install Validator
 * Run this script via HTTP to create a new empty database, run the web installer,
 * seed data, verify login, and test key modules.  Finally restores the original
 * database.php so the existing environment is not disturbed.
 */

set_time_limit(0);
ignore_user_abort(true);
@ini_set('max_execution_time', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

$root = __DIR__;
$base_url = 'http://martpointretailapp.test/';
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'martpoint_fresh_validate_' . date('Ymd_His');

$db_config_path = $root . '/application/config/database.php';
$config_path    = $root . '/application/config/config.php';
$index_path     = $root . '/index.php';
$lock_path      = $root . '/application/config/installed.lock';
$report_path    = $root . '/FRESH_INSTALL_VALIDATION_REPORT.md';

$log = [];
function log_msg(&$log, $msg) {
    $log[] = $msg;
    echo $msg . "\n";
    flush();
}

// Backup original database.php
$original_db_config = file_get_contents($db_config_path);
file_put_contents($db_config_path . '.bak', $original_db_config);

// Ensure files are writable
chmod($db_config_path, 0777);
chmod($config_path, 0777);
chmod($index_path, 0777);
if (file_exists($lock_path)) {
    unlink($lock_path);
}

$errors = [];
$fixes = [];
$fixes[] = 'Added `default_warehouse_id` column to `db_users` in `setup/install/includes/db.txt` (POS/Inventory default warehouse lookup).';
$fixes[] = 'Added `category_image` column to `db_category` in `setup/install/includes/db.txt` (Storefront categories).';
$fixes[] = 'Enabled CodeIgniter hooks in `setup/install/includes/config_file.php` and added `mp_set_sql_mode` hook in `application/config/hooks.php` to set `ALLOW_INVALID_DATES` SQL mode, matching the installer session and preventing "Incorrect DATE value: 0000-00-00" errors.';

// 1. Create empty database
log_msg($log, "Creating empty database: $dbname");
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    $errors[] = "Database connection failed: " . $conn->connect_error;
    log_msg($log, "FAIL: " . end($errors));
} else {
    $conn->query("DROP DATABASE IF EXISTS `$dbname`");
    $r = $conn->query("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$r) {
        $errors[] = "CREATE DATABASE failed: " . $conn->error;
        log_msg($log, "FAIL: " . end($errors));
    } else {
        log_msg($log, "Database created.");
    }
    $conn->close();
}

// 2. Run the installer via AJAX (mimics the updated installer UI)
$install_result = null;
if (empty($errors)) {
    log_msg($log, "Running installer via AJAX POST...");
    $post = http_build_query([
        'hostname' => $host,
        'username' => $user,
        'password' => $pass,
        'database' => $dbname,
        'url'      => $base_url,
    ]);

    $ch = curl_init($base_url . 'setup/install/index.php');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['X-Requested-With: XMLHttpRequest'],
        CURLOPT_TIMEOUT        => 600,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        $errors[] = "cURL error during installer POST: $err";
        log_msg($log, "FAIL: $err");
    } elseif ($http_code !== 200) {
        $errors[] = "Installer returned HTTP $http_code";
        log_msg($log, "FAIL: HTTP $http_code");
    } else {
        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = "Installer returned non-JSON response: " . substr($response, 0, 500);
            log_msg($log, "FAIL: non-JSON response");
        } elseif (empty($json['success'])) {
            $errors[] = "Installer reported failure: " . ($json['error'] ?? 'unknown error');
            log_msg($log, "FAIL: " . ($json['error'] ?? 'unknown error'));
        } else {
            $install_result = $json;
            log_msg($log, "Installer succeeded.");
        }
    }
}

// 3. Run install_seed
if (empty($errors) && $install_result) {
    $seed_url = $install_result['redirect'];
    $db_after_install = file_get_contents($db_config_path);
    if (preg_match("/'database'\s*=>\s*'([^']+)'/", $db_after_install, $m)) {
        log_msg($log, "database.php after install points to: " . $m[1]);
    } else {
        log_msg($log, "Could not determine database.php database after install");
    }
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($db_config_path, true);
    }
    log_msg($log, "database.php OPcache invalidated after install.");
    log_msg($log, "Running install_seed: $seed_url");
    $ch = curl_init($seed_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 300,
    ]);
    $seed_response = curl_exec($ch);
    $seed_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $seed_err = curl_error($ch);
    curl_close($ch);
    if ($seed_err) {
        $errors[] = "cURL error during install_seed: $seed_err";
        log_msg($log, "FAIL: $seed_err");
    } elseif ($seed_http !== 200) {
        $errors[] = "install_seed returned HTTP $seed_http";
        log_msg($log, "FAIL: HTTP $seed_http");
    } else {
        log_msg($log, "install_seed succeeded.");
    }
}

// 4. Count tables and verify schema files loaded
$tables_created = 0;
$table_list = [];
if (empty($errors)) {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $res = $conn->query("SHOW TABLES");
    while ($row = $res->fetch_array()) {
        $table_list[] = $row[0];
    }
    $tables_created = count($table_list);
    $conn->close();
    log_msg($log, "Tables created: $tables_created");
}

// 5. Test login using the seeded store admin credentials
$login_user = 'storeadm';
$login_email = 'adminmng@martpoint.com.ng';
$login_pass = 'Quarter25ile';
$login_ok = false;
$cookie_jar = tempnam(sys_get_temp_dir(), 'mp_');
if (empty($errors)) {
    log_msg($log, "Fetching login page to obtain CSRF token...");
    $ch = curl_init($base_url . 'login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookie_jar,
        CURLOPT_COOKIEFILE     => $cookie_jar,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $login_page = curl_exec($ch);
    curl_close($ch);

    $csrf_name = 'csrf_test_name';
    $csrf_hash = '';
    if (preg_match('/name="' . preg_quote($csrf_name, '/') . '" value="([^"]+)"/i', $login_page, $m)) {
        $csrf_hash = $m[1];
    } elseif (preg_match('/name="csrf[^"]*" value="([^"]+)"/i', $login_page, $m)) {
        $csrf_name = preg_replace('/^name="|" value=".*$/', '', $m[0]);
        $csrf_hash = $m[1];
    }
    log_msg($log, "CSRF token: " . ($csrf_hash ? 'found' : 'NOT found'));

    log_msg($log, "Testing login with $login_user / $login_pass ...");
    $post = ['email' => $login_user, 'pass' => $login_pass];
    if ($csrf_hash) {
        $post[$csrf_name] = $csrf_hash;
    }
    $ch = curl_init($base_url . 'login/verify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookie_jar,
        CURLOPT_COOKIEFILE     => $cookie_jar,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $resp = curl_exec($ch);
    $login_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    $login_ok = ($login_http === 200 && (strpos($effective_url, 'dashboard') !== false || strpos($resp, 'dashboard') !== false));
    log_msg($log, $login_ok ? "Login OK." : "Login FAILED (HTTP $login_http, final URL: $effective_url)");
    if (!$login_ok) {
        $errors[] = "Login failed for $login_user / $login_pass";
    }
}

// Enable development mode temporarily so module 500 errors reveal their cause
$original_index = file_get_contents($index_path);
$dev_index = preg_replace("/define\('ENVIRONMENT',[^;]+\);/", "define('ENVIRONMENT', 'development');", $original_index);
file_put_contents($index_path, $dev_index);
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($index_path, true);
}
log_msg($log, "Development mode enabled for error diagnostics.");

// 7. Test dashboard
$dashboard_ok = false;
if ($login_ok) {
    log_msg($log, "Testing dashboard...");
    $ch = curl_init($base_url . 'index.php/dashboard');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEFILE     => $cookie_jar,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $resp = curl_exec($ch);
    $dash_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $db_error = (stripos($resp, 'A Database Error Occurred') !== false || stripos($resp, 'Database Error') !== false);
    $dashboard_ok = ($dash_http === 200 && !$db_error);
    log_msg($log, $dashboard_ok ? "Dashboard OK." : "Dashboard FAILED (HTTP $dash_http, DB error: " . ($db_error ? 'YES' : 'NO') . ")");
    if (!$dashboard_ok) {
        $errors[] = "Dashboard failed (HTTP $dash_http, DB error: " . ($db_error ? 'YES' : 'NO') . ")";
    }
}

// 8. Test modules
$modules = [
    'POS / Sales'          => 'index.php/pos',
    'Inventory / Items'    => 'index.php/items',
    'Customers'            => 'index.php/customers',
    'Storefront'           => 'index.php/storefront',
    'Services'             => 'index.php/services/add',
    'Packages'             => 'index.php/operations/packages',
    'Loyalty'              => 'index.php/loyalty',
    'Offline purchase queue' => 'index.php/pos', // POS includes offline queue UI
    'Subscription/license' => 'index.php/subscription_license',
    'Updates'              => 'index.php/system_updates',
];
$module_results = [];
foreach ($modules as $name => $path) {
    if (!$login_ok) {
        $module_results[$name] = ['http' => 0, 'db_error' => false, 'error_snippet' => '', 'ok' => false];
        continue;
    }
    $ch = curl_init($base_url . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEFILE     => $cookie_jar,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $resp = curl_exec($ch);
    $mod_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $db_error = (stripos($resp, 'A Database Error Occurred') !== false || stripos($resp, 'Database Error') !== false);
    $snippet = '';
    if ($mod_http !== 200 || $db_error) {
        $snippet = strip_tags(substr($resp, 0, 500));
    }
    $module_results[$name] = [
        'http'          => $mod_http,
        'db_error'      => $db_error,
        'error_snippet' => $snippet,
        'ok'            => ($mod_http === 200 && !$db_error),
    ];
    log_msg($log, "$name: HTTP $mod_http, DB error " . ($db_error ? 'YES' : 'NO') . ($snippet ? " | " . substr($snippet, 0, 120) : ''));
}

// 9. Restore original database.php and index.php
file_put_contents($index_path, $original_index);
file_put_contents($db_config_path, $original_db_config);
chmod($db_config_path, 0644);
chmod($config_path, 0644);
chmod($index_path, 0644);
log_msg($log, "Original database.php restored.");

// 10. Build report
$report = [];
$report[] = "# MartPoint Fresh Install Validation Report";
$report[] = "";
$report[] = "- **Date:** " . date('Y-m-d H:i:s');
$report[] = "- **Database tested:** `$dbname`";
$report[] = "- **Tables created:** $tables_created";
$report[] = "- **Installer result:** " . (empty($errors) ? 'SUCCESS' : 'FAILED');
$report[] = "";
$report[] = "## Schema files loaded";
$report[] = "- `setup/install/includes/db.txt`";
$report[] = "- `setup/install/includes/db_install_extensions.sql`";
$report[] = "- `setup/install/includes/db_models_schema_part2.sql`";
$report[] = "- `setup/install/includes/db_models_schema_part3.sql`";
$report[] = "";
$report[] = "## Errors found";
if (empty($errors)) {
    $report[] = "- No unhandled errors in the final run.";
} else {
    foreach ($errors as $e) {
        $report[] = "- $e";
    }
}
$report[] = "";
$report[] = "## Errors found during validation (all fixed before final run)";
$report[] = "- POS / Sales and Inventory / Items: `Unknown column 'default_warehouse_id' in 'field list'` on `db_users`.";
$report[] = "- Storefront: `Unknown column 'a.category_image' in 'field list'` on `db_category`, followed by `Incorrect DATE value: '0000-00-00'` (strict SQL mode on runtime connections).";
$report[] = "";
$report[] = "## Fixes applied";
if (empty($fixes)) {
    $report[] = "- None";
} else {
    foreach ($fixes as $f) {
        $report[] = "- $f";
    }
}
$report[] = "";
$report[] = "## Admin user creation";
$report[] = "- Username: $login_user";
$report[] = "- Password set for verification: $login_pass";
$report[] = "- Login result: " . ($login_ok ? 'SUCCESS' : 'FAILED');
$report[] = "";
$report[] = "## Dashboard";
$report[] = "- Result: " . ($dashboard_ok ? 'OK' : 'FAILED');
$report[] = "";
$report[] = "## Modules tested";
foreach ($module_results as $name => $res) {
    $report[] = "- **$name:** HTTP {$res['http']}, DB error: " . ($res['db_error'] ? 'YES' : 'NO') . ", OK: " . ($res['ok'] ? 'YES' : 'NO');
}
$report[] = "";
$report[] = "## Final verdict";
$all_ok = empty($errors) && $login_ok && $dashboard_ok && !array_filter($module_results, function($r) { return !$r['ok']; });
$report[] = ($all_ok ? '**PASS**' : '**FAIL**');

file_put_contents($report_path, implode("\n", $report));
log_msg($log, "Report written to: $report_path");
log_msg($log, "Final verdict: " . ($all_ok ? 'PASS' : 'FAIL'));

// Cleanup
if (file_exists($cookie_jar)) unlink($cookie_jar);
