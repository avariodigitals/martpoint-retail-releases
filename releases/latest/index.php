<?php

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
/*############################INSTALL CHECK#########################*/

// Detect if application needs installation
// Redirect to setup if the install lock is missing OR if database.php still has placeholder values.
// Skip this check when running from CLI (e.g. cron jobs) or when the request
// is for the install_seed controller, which creates the lock file as its final step.
$needs_install = false;
$lock_file = __DIR__ . '/application/config/installed.lock';
$db_config_path = __DIR__ . '/application/config/database.php';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$is_install_seed = (strpos($request_uri, 'install_seed') !== false);

if (PHP_SAPI !== 'cli' && !$is_install_seed && !file_exists($lock_file)) {
    // Self-heal: package re-uploads can drop installed.lock. If database.php
    // holds real credentials AND the MartPoint schema exists, this is an
    // installed system — recreate the lock instead of sending to setup.
    if (file_exists($db_config_path)) {
        $dbc = file_get_contents($db_config_path);
        if (strpos($dbc, '%HOSTNAME%') === false && strpos($dbc, '%DATABASE%') === false) {
            $db = null;
            include $db_config_path;
            $d = is_array($db ?? null) ? ($db['default'] ?? null) : null;
            if (is_array($d) && !empty($d['database'])) {
                $link = @mysqli_connect(
                    (string) ($d['hostname'] ?? ''),
                    (string) ($d['username'] ?? ''),
                    (string) ($d['password'] ?? ''),
                    (string) $d['database']
                );
                if ($link) {
                    $t = @mysqli_query($link, "SHOW TABLES LIKE 'db_sitesettings'");
                    if ($t && mysqli_num_rows($t) > 0) {
                        @file_put_contents($lock_file, 'restored ' . date('c'));
                    }
                    mysqli_close($link);
                }
            }
        }
    }
    $needs_install = !file_exists($lock_file);
}

if (!$needs_install && file_exists($db_config_path)) {
    $db_config_content = file_get_contents($db_config_path);
    if (strpos($db_config_content, "'hostname' => '%HOSTNAME%'") !== false ||
        strpos($db_config_content, "'username' => '%USERNAME%'") !== false ||
        strpos($db_config_content, "'password' => '%PASSWORD%'") !== false ||
        strpos($db_config_content, "'database' => '%DATABASE%'") !== false) {
        $needs_install = true;
    }
}

if ($needs_install) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = ($path === '/' || $path === '\\') ? '' : rtrim($path, '/\\');
    header("Location: $protocol://$host$path/setup/");
    exit;
}

/*############################INSTALL CHECK END#########################*/

/*############################REAL#########################*/
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
/*############################REAL END#########################*/



if(!file_exists(".htaccess")){
	fopen(".htaccess", "w");
	copy("uploads/htaccess_file/.htaccess", ".htaccess");
}
/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			$_error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED;
			if (defined('E_STRICT'))
			{
				$_error_reporting &= ~E_STRICT;
			}
			error_reporting($_error_reporting);
		}
		else
		{
			$_error_reporting = E_ALL & ~E_NOTICE & ~E_USER_NOTICE;
			if (defined('E_STRICT'))
			{
				$_error_reporting &= ~E_STRICT;
			}
			error_reporting($_error_reporting);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 * https://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
	$application_folder = 'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view directory out of the application
 * directory, set the path to it here. The directory can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application directory.
 * If you do move this, use an absolute (full) server path.
 *
 * NO TRAILING SLASH!
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
	// The directory name, relative to the "controllers" directory.  Leave blank
	// if your controller is not in a sub-directory within the "controllers" one
	// $routing['directory'] = '';

	// The controller class file name.  Example:  mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	define('EXT', '.php'); //for zend framework barcode library. 13-12-2018 by aslarali

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*############################CUSTOM STOREFRONT DOMAIN DISPATCH#########################*/
// If the request host matches a "connected" custom storefront domain, rewrite the
// request URI to /store/{slug}/... so every storefront route works on that domain.
// Fails open: any error leaves the request untouched and normal routing applies.
try {
	$mp_host = strtolower(trim((string)(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')));
	$mp_host = rtrim(preg_replace('/:\d+$/', '', $mp_host), '.');
	if (PHP_SAPI !== 'cli' && $mp_host !== '' && is_file(APPPATH.'config/database.php')) {
		$mp_dbconf = NULL;
		require APPPATH.'config/database.php';
		$mp_dbc = (isset($active_group) && isset($db[$active_group])) ? $db[$active_group] : NULL;
		if ($mp_dbc && (isset($mp_dbc['dbdriver']) ? $mp_dbc['dbdriver'] : 'mysqli') === 'mysqli' && !empty($mp_dbc['database'])) {
			$mp_conn = @new mysqli($mp_dbc['hostname'], $mp_dbc['username'], $mp_dbc['password'], $mp_dbc['database']);
			if ($mp_conn && !$mp_conn->connect_errno) {
				$mp_alt = (strpos($mp_host, 'www.') === 0) ? substr($mp_host, 4) : 'www.'.$mp_host;
				$mp_slug = NULL;
				$mp_stmt = $mp_conn->prepare("SELECT s.store_slug FROM db_storefront_domains d INNER JOIN db_storefront_settings s ON s.store_id = d.store_id WHERE d.domain_value IN (?, ?) AND d.connection_status = 'connected' LIMIT 1");
				if ($mp_stmt) {
					$mp_stmt->bind_param('ss', $mp_host, $mp_alt);
					$mp_stmt->execute();
					$mp_stmt->bind_result($mp_slug);
					$mp_stmt->fetch();
					$mp_stmt->close();
				}
				$mp_conn->close();
				if ($mp_slug) {
					$mp_uri = (string)(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
					$mp_qs = '';
					$mp_qpos = strpos($mp_uri, '?');
					if ($mp_qpos !== FALSE) { $mp_qs = substr($mp_uri, $mp_qpos); $mp_uri = substr($mp_uri, 0, $mp_qpos); }
					$mp_base = str_replace('\\', '/', dirname((string)$_SERVER['SCRIPT_NAME']));
					if ($mp_base === '/' || $mp_base === '.') { $mp_base = ''; }
					if ($mp_base !== '' && strpos($mp_uri, $mp_base.'/') === 0) { $mp_uri = substr($mp_uri, strlen($mp_base)); }
					elseif ($mp_base !== '' && $mp_uri === $mp_base) { $mp_uri = '/'; }
					$mp_path = '/'.ltrim($mp_uri, '/');

					// Paths that must keep their existing routes on a custom domain
					$mp_skip = array('store/', 'storefront/', 'image/', 'qr/', 'sitemap.xml', 'robots.txt', 'favicon', 'uploads/', 'assets/', 'themes/', 'setup/', 'install', 'index.php', 'api/', 'cron');
					$mp_lower = strtolower($mp_path);
					$mp_rewrite = TRUE;
					foreach ($mp_skip as $mp_p) {
						if (strpos($mp_lower, '/'.$mp_p) === 0) { $mp_rewrite = FALSE; break; }
					}
					if ($mp_rewrite) {
						$_SERVER['REQUEST_URI'] = $mp_base.'/store/'.$mp_slug.($mp_path === '/' ? '' : $mp_path).$mp_qs;
					}
				}
			}
		}
	}
} catch (Throwable $mp_e) { /* custom-domain dispatch failed — continue with normal routing */ }

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
