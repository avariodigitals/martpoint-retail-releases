<?php
//error_reporting(0);

// Guard: if the application is already installed, do not allow reinstallation.
$lock_file = __DIR__ . '/../../application/config/installed.lock';
if (file_exists($lock_file)) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MartPoint — Already Installed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#0f172a; font-family:Inter,system-ui,sans-serif; color:#f8fafc; }
        .card { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:40px; max-width:480px; text-align:center; }
        h1 { font-size:22px; margin:0 0 12px; }
        p { color:#94a3b8; margin:0 0 24px; line-height:1.6; }
        a { display:inline-block; padding:12px 24px; background:#6366f1; color:#fff; text-decoration:none; border-radius:10px; font-weight:500; }
        code { background:rgba(255,255,255,0.08); padding:2px 6px; border-radius:4px; font-size:12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>MartPoint is already installed</h1>
        <p>The installer is locked. To reinstall, remove the file:<br><code>application/config/installed.lock</code></p>
        <a href="../../">Go to application</a>
    </div>
</body>
</html>
    <?php
    exit;
}

include '../../application/helpers/custom_helper.php';
include '../../application/helpers/appinfo_helper.php';
include '../../application/helpers/business_profile_helper.php';

$business_types  = mp_get_business_types();
$business_models = mp_get_business_models();
$default_industry_type   = 'general_retail';
$default_business_model  = 'product_based';

$db_config_path = '../../application/config/database.php';
$codeigniter_index_page = '../../index.php';
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Do not leak HTML/PHP errors into the JSON response. Capture fatal/shutdown
// errors and return them as JSON so the installer UI can show the real cause.
ini_set('display_errors', 0);
error_reporting(E_ALL);
$installer_json_sent = false;
register_shutdown_function(function() use ($is_ajax, &$installer_json_sent) {
    if ($installer_json_sent) return;
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        @header('Content-Type: application/json; charset=utf-8');
        $msg = $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];
        $installer_json_sent = true;
        echo json_encode(['success' => false, 'error' => 'Server error: ' . $msg]);
    }
});

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST) {

	require_once('taskCoreClass.php');
	require_once('includes/databaseLibrary.php');

	$core = new Core();
	$database = new Database();

	if($core->checkEmpty($_POST) == true)
	{

		$industry_type  = (!empty($_POST['industry_type']) && isset($business_types[$_POST['industry_type']])) ? $_POST['industry_type'] : $default_industry_type;
		$business_model = (!empty($_POST['business_model']) && isset($business_models[$_POST['business_model']])) ? $_POST['business_model'] : $default_business_model;

		// Persist the chosen business type while creating tables
		$_POST['industry_type']  = $industry_type;
		$_POST['business_model'] = $business_model;

       // $database->create_database($_POST);
        if($database->check_database_exist_or_not($_POST) == false)
        {
            $err = $database->error ?: "Database `".$_POST['database']."` does not exist. Please create it manually on your server.";
            $message = $core->show_message('error', $err);
        }

		else if($database->create_database($_POST) == false)
		{
			$err = $database->error ?: "The database could not be created. Please check your hostname, username, password, and database name.";
			$message = $core->show_message('error', $err);
		}

		else if ($database->create_tables($_POST) == false)
		{
			$err = $database->error ?: "Failed to create database tables.";
			$message = $core->show_message('error', $err);
		}

		else if ($core->checkFile() == false)
		{
			$message = $core->show_message('error',"File application/config/database.php is Empty");
		}

		else if ($core->write_config($_POST) == false)
		{
			$message = $core->show_message('error',"The database configuration file could not be written, please chmod application/config/database.php file to 777");
		}

		if(!isset($message)) {
            $urlWb = $core->getAllData($_POST['url']);
            $seedUrl = rtrim($urlWb,'/').'/index.php/install_seed'
                .'?industry_type='.urlencode($industry_type)
                .'&business_model='.urlencode($business_model);
            if ($is_ajax) {
                $installer_json_sent = true;
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => $seedUrl]);
                exit;
            }
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Installing MartPoint...</title>
                <link rel="shortcut icon" href="../assets/martpoint-icon.png?v=2">
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <style>
                    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
                    body {
                        margin: 0; min-height: 100vh;
                        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
                        background-size: 400% 400%;
                        animation: gradientShift 15s ease infinite;
                        overflow-x: hidden;
                        display: flex; align-items: center; justify-content: center;
                        position: relative;
                    }
                    @keyframes gradientShift {
                        0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; }
                    }
                    .orb {
                        position: fixed; border-radius: 50%;
                        filter: blur(80px); opacity: 0.35;
                        pointer-events: none; z-index: 0;
                    }
                    .orb-1 { width: 400px; height: 400px; background: radial-gradient(circle, #6366f1 0%, transparent 70%); top: -10%; left: -10%; animation: floatOrb1 20s ease-in-out infinite; }
                    .orb-2 { width: 300px; height: 300px; background: radial-gradient(circle, #10b981 0%, transparent 70%); bottom: -5%; right: -5%; animation: floatOrb2 18s ease-in-out infinite; }
                    .orb-3 { width: 200px; height: 200px; background: radial-gradient(circle, #f59e0b 0%, transparent 70%); top: 40%; left: 60%; animation: floatOrb3 25s ease-in-out infinite; }
                    @keyframes floatOrb1 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(60px,40px) scale(1.1);} }
                    @keyframes floatOrb2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-50px,-30px) scale(1.15);} }
                    @keyframes floatOrb3 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,50px) scale(0.9);} }
                    .loader-wrap {
                        text-align: center; color: #f8fafc;
                        background: rgba(255,255,255,0.06);
                        backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
                        border: 1px solid rgba(255,255,255,0.08);
                        border-radius: 24px;
                        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05) inset;
                        padding: 48px 40px;
                        max-width: 400px; width: 100%;
                        margin: 24px;
                        position: relative; z-index: 1;
                        opacity: 0; transform: translateY(30px) scale(0.96);
                        animation: cardReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    }
                    @keyframes cardReveal { to { opacity: 1; transform: translateY(0) scale(1); } }
                    .spinner-wrap {
                        width: 80px; height: 80px;
                        margin: 0 auto 24px;
                        position: relative;
                    }
                    .spinner {
                        width: 100%; height: 100%;
                        border: 3px solid rgba(99,102,241,0.15);
                        border-top-color: #6366f1;
                        border-radius: 50%;
                        animation: spin 0.9s linear infinite;
                    }
                    .spinner-inner {
                        position: absolute;
                        top: 10px; left: 10px;
                        width: 60px; height: 60px;
                        border: 3px solid rgba(16,185,129,0.15);
                        border-top-color: #10b981;
                        border-radius: 50%;
                        animation: spin 1.2s linear infinite reverse;
                    }
                    @keyframes spin { to { transform: rotate(360deg); } }
                    .loader-wrap h2 { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
                    .loader-wrap p { color: #94a3b8; margin: 0; font-size: 14px; line-height: 1.6; }
                    .loader-wrap .steps {
                        margin-top: 20px;
                        display: flex; flex-direction: column; gap: 8px;
                    }
                    .loader-wrap .step {
                        display: flex; align-items: center; gap: 10px;
                        font-size: 13px; color: #94a3b8;
                        padding: 8px 12px;
                        border-radius: 10px;
                        background: rgba(255,255,255,0.03);
                    }
                    .loader-wrap .step.active { color: #34d399; }
                    .loader-wrap .step-dot {
                        width: 8px; height: 8px; border-radius: 50%;
                        background: rgba(255,255,255,0.15);
                        flex-shrink: 0;
                    }
                    .loader-wrap .step.active .step-dot { background: #10b981; }
                </style>
            </head>
            <body>
                <div class="orb orb-1"></div>
                <div class="orb orb-2"></div>
                <div class="orb orb-3"></div>
                <div class="loader-wrap">
                    <div class="spinner-wrap">
                        <div class="spinner"></div>
                        <div class="spinner-inner"></div>
                    </div>
                    <h2>Installing MartPoint...</h2>
                    <p>Setting up your database and configuring the application.</p>
                    <div class="steps">
                        <div class="step active"><div class="step-dot"></div>Creating database tables</div>
                        <div class="step"><div class="step-dot"></div>Seeding default data</div>
                        <div class="step"><div class="step-dot"></div>Finalising setup</div>
                    </div>
                </div>
                <script>
                    setTimeout(function(){
                        window.location='<?= $seedUrl; ?>';
                    }, 1500);
                </script>
            </body>
            </html>
            <?php
            exit();
		}
	}
	else {
		$message = $core->show_message('error','The host, username, password, database name, and URL are required.');
	}
}
// If AJAX request resulted in an error, return JSON immediately
if ($is_ajax && !empty($message)) {
    $installer_json_sent = true;
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install MartPoint</title>
    <link rel="shortcut icon" href="../assets/martpoint-icon.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; --primary-dark: #4f46e5;
            --success: #10b981; --danger: #ef4444;
            --bg-dark: #0f172a; --bg-card: rgba(255,255,255,0.06);
            --text-primary: #f8fafc; --text-secondary: #94a3b8;
            --border: rgba(255,255,255,0.08);
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            overflow-x: hidden;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; }
        }
        /* Floating orbs */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(80px); opacity: 0.35;
            pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 400px; height: 400px; background: radial-gradient(circle, #6366f1 0%, transparent 70%); top: -10%; left: -10%; animation: floatOrb1 20s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: radial-gradient(circle, #10b981 0%, transparent 70%); bottom: -5%; right: -5%; animation: floatOrb2 18s ease-in-out infinite; }
        .orb-3 { width: 200px; height: 200px; background: radial-gradient(circle, #f59e0b 0%, transparent 70%); top: 40%; left: 60%; animation: floatOrb3 25s ease-in-out infinite; }
        @keyframes floatOrb1 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(60px,40px) scale(1.1);} }
        @keyframes floatOrb2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-50px,-30px) scale(1.15);} }
        @keyframes floatOrb3 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,50px) scale(0.9);} }
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05) inset;
            padding: 48px 40px;
            max-width: 560px; width: 100%;
            margin: 24px;
            position: relative; z-index: 1;
            opacity: 0; transform: translateY(30px) scale(0.96);
            animation: cardReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes cardReveal { to { opacity: 1; transform: translateY(0) scale(1); } }

        .logo-wrap { text-align: center; margin-bottom: 28px; }
        .logo-wrap img { width: 100%; max-width: 420px; height: auto; margin: 0 auto 12px; display: block; }
        .logo-wrap h1 { font-size: 22px; font-weight: 700; color: var(--text-primary); margin: 0; }
        .logo-wrap .version { color: var(--text-secondary); font-size: 13px; margin-top: 4px; }

        .alert {
            padding: 14px 18px; border-radius: 14px; font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 12px;
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            border-left: 4px solid transparent;
        }
        .alert-danger { background: rgba(239,68,68,0.10); color: #f87171; border: 1px solid rgba(239,68,68,0.15); border-left-color: #ef4444; }
        .alert-warning { background: rgba(245,158,11,0.10); color: #fbbf24; border: 1px solid rgba(245,158,11,0.15); border-left-color: #f59e0b; }
        .alert-success { background: rgba(16,185,129,0.10); color: #34d399; border: 1px solid rgba(16,185,129,0.15); border-left-color: #10b981; }
        .alert i { font-size: 18px; line-height: 1.3; flex-shrink: 0; }
        .alert code { background: rgba(255,255,255,0.08); padding: 2px 6px; border-radius: 6px; font-size: 12px; }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 500;
            color: var(--text-secondary); margin-bottom: 6px;
        }
        .form-group label .req { color: var(--danger); margin-left: 2px; }
        .form-control {
            width: 100%; padding: 12px 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border); border-radius: 12px;
            color: var(--text-primary); font-size: 14px;
            outline: none; transition: all 0.2s;
        }
        .form-control::placeholder { color: #64748b; }
        .form-control:focus {
            border-color: rgba(99,102,241,0.5);
            background: rgba(255,255,255,0.07);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .help-text {
            display: none; font-size: 12px; color: var(--danger); margin-top: 6px;
        }
        .help-text.show { display: block; }

        .btn-install {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff; border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
            transition: all 0.3s ease;
        }
        .btn-install:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99,102,241,0.45);
        }
        .btn-install:disabled { opacity: 0.5; cursor: not-allowed; }

        .footer-note {
            text-align: center; margin-top: 20px;
            font-size: 12px; color: var(--text-secondary);
        }
        .footer-note a { color: #818cf8; text-decoration: none; }

        .error-state {
            text-align: center;
            padding: 20px 8px;
        }
        .error-icon {
            width: 64px; height: 64px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: #f87171;
        }
        .error-state h2 {
            font-size: 20px; font-weight: 700;
            color: var(--text-primary); margin: 0 0 10px;
        }
        .error-state p {
            font-size: 14px; color: var(--text-secondary);
            margin: 0 0 20px; line-height: 1.6;
        }
        .code-block {
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 24px;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 13px;
            color: #e2e8f0;
            text-align: left;
            word-break: break-all;
        }
        .code-block code { background: none; padding: 0; font-size: 13px; }
        .btn-secondary {
            display: inline-block;
            padding: 12px 28px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 14px; font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.18);
            transform: translateY(-1px);
        }

        /* Installation Progress Overlay */
        #install-overlay {
            position: fixed; inset: 0;
            display: none; align-items: center; justify-content: center;
            z-index: 100;
        }
        #install-overlay.show { display: flex; }
        .overlay-backdrop {
            position: absolute; inset: 0;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(12px);
        }
        .install-card {
            position: relative; z-index: 1;
            text-align: center; color: #f8fafc;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);
            padding: 48px 40px;
            max-width: 420px; width: 100%;
            margin: 24px;
            opacity: 0; transform: translateY(30px) scale(0.96);
            animation: cardReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .progress-bar-wrap {
            width: 100%; height: 4px;
            background: rgba(255,255,255,0.08);
            border-radius: 2px;
            margin: 28px 0 24px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%; width: 0%;
            background: linear-gradient(90deg, #6366f1, #10b981);
            border-radius: 2px;
            transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .install-step {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px; color: #94a3b8;
            background: rgba(255,255,255,0.03);
            margin-bottom: 6px;
            transition: all 0.4s ease;
        }
        .install-step:last-child { margin-bottom: 0; }
        .install-step.done { color: #34d399; }
        .install-step.done .step-icon { background: #10b981; color: #fff; }
        .install-step.active { color: #f8fafc; background: rgba(255,255,255,0.06); }
        .install-step.active .step-icon { background: #6366f1; color: #fff; animation: pulse 1.5s ease infinite; }
        .step-icon {
            width: 22px; height: 22px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            background: rgba(255,255,255,0.1); color: #94a3b8;
            flex-shrink: 0; transition: all 0.3s ease;
        }
        @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,0.4);} 50%{box-shadow:0 0 0 8px rgba(99,102,241,0);} }

        /* Error Modal */
        #error-modal {
            position: fixed; inset: 0;
            display: none; align-items: center; justify-content: center;
            z-index: 200;
        }
        #error-modal.show { display: flex; }
        .modal-backdrop {
            position: absolute; inset: 0;
            background: rgba(15,23,42,0.8);
            backdrop-filter: blur(8px);
        }
        .modal-card {
            position: relative; z-index: 1;
            text-align: center;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            padding: 40px 32px;
            max-width: 400px; width: 100%;
            margin: 24px;
            animation: cardReveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 50%;
            margin: 0 auto 16px;
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: #f87171;
        }
        .modal-card h3 { font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 10px; }
        .modal-card p { font-size: 14px; color: var(--text-secondary); margin: 0 0 24px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="glass-card">
        <div class="logo-wrap">
            <img src="../assets/martpoint-logo.png?v=2" alt="MartPoint" style="width:100%;max-width:420px;height:auto;margin:0 auto 12px;display:block;">
            <h1>Install MartPoint</h1>
            <div class="version">Application Version <?=app_version();?></div>
        </div>

        <?php
        if(is_writable($db_config_path) && is_writable($codeigniter_index_page))
        {
        ?>
            <?php if(isset($message)) { ?>
            <div class="alert alert-warning" role="alert">
                <i>&#9888;</i> <?=htmlspecialchars($message)?>
            </div>
            <?php } ?>

            <div class="alert alert-success" role="alert">
                <i>&#9432;</i> Facing any installation issues? Please Contact Support.
            </div>

            <form id="install_form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                    <label for="hostname">Database Hostname <span class="req">*</span></label>
                    <input type="text" id="hostname" value="localhost" placeholder="Usually localhost" class="form-control" name="hostname" />
                    <div class="help-text" id="hostname_msg">Required Field</div>
                </div>

                <div class="form-group">
                    <label for="username">Database Username <span class="req">*</span></label>
                    <input type="text" id="username" placeholder="Database user name" class="form-control" name="username" value="" />
                    <div class="help-text" id="username_msg">Required Field</div>
                </div>

                <div class="form-group">
                    <label for="password">Database Password</label>
                    <input type="password" id="password" placeholder="Database user password" class="form-control" name="password" />
                    <div class="help-text" id="password_msg">Required Field</div>
                </div>

                <div class="form-group">
                    <label for="database">Database Name <span class="req">*</span></label>
                    <input type="text" id="database" placeholder="Database name" class="form-control" name="database" value=""/>
                    <div class="help-text" id="database_msg">Required Field</div>
                </div>

                <?php
                    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    // Use SCRIPT_NAME because it always points to the current file,
                    // regardless of rewrite rules or extra path info.
                    $script_path = $_SERVER['SCRIPT_NAME']; // e.g. /setup/install/index.php
                    $base_path = dirname($script_path);      // e.g. /setup/install
                    $base_path = str_replace('/setup/install', '', $base_path);
                    $base_path = ($base_path === '') ? '/' : $base_path;
                    $url = $protocol . $_SERVER['HTTP_HOST'] . $base_path;
                ?>
                <input type="hidden" id="url" name="url" value="<?=htmlspecialchars($url);?>" />

                <div class="form-group">
                    <label for="industry_type">Business Type</label>
                    <select id="industry_type" name="industry_type" class="form-control">
                        <?php foreach ($business_types as $key => $label): ?>
                        <option value="<?=htmlspecialchars($key);?>" <?=($key === $default_industry_type ? 'selected' : '');?>><?=htmlspecialchars($label);?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="business_model">Business Model</label>
                    <select id="business_model" name="business_model" class="form-control">
                        <?php foreach ($business_models as $key => $label): ?>
                        <option value="<?=htmlspecialchars($key);?>" <?=($key === $default_business_model ? 'selected' : '');?>><?=htmlspecialchars($label);?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" class="btn-install" id="send">Install MartPoint</button>
                <div style="text-align:center;margin-top:14px;">
                    <a href="../" style="font-size:13px;color:var(--text-secondary);text-decoration:none;transition:color 0.3s;" onmouseover="this.style.color='#818cf8'" onmouseout="this.style.color='var(--text-secondary)'">&larr; Back to Setup</a>
                </div>
            </form>

        <?php
        }
        else {
        ?>
            <div class="error-state">
                <div class="error-icon">&#9888;</div>
                <h2>Permission Required</h2>
                <p>The installer needs write access to your configuration files before it can continue.</p>
                <div class="code-block">
                    <code>chmod 777 application/config/database.php</code>
                </div>
                <a href="../" class="btn-secondary">Back to Setup</a>
            </div>
        <?php
        }
        ?>
    </div>

    <!-- Installation Progress Overlay -->
    <div id="install-overlay">
        <div class="overlay-backdrop"></div>
        <div class="install-card">
            <div class="spinner-wrap" style="width:64px;height:64px;margin:0 auto 20px;position:relative;">
                <div class="spinner" style="border-width:3px;width:100%;height:100%;"></div>
                <div class="spinner-inner" style="top:8px;left:8px;width:48px;height:48px;border-width:3px;"></div>
            </div>
            <h2 style="font-size:20px;font-weight:700;margin:0 0 6px;">Installing MartPoint...</h2>
            <p style="color:#94a3b8;font-size:13px;margin:0 0 4px;">This may take a moment. Please do not close this window.</p>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" id="progress-fill"></div>
            </div>
            <div id="install-steps">
                <div class="install-step active" data-step="1">
                    <div class="step-icon">1</div>
                    <span>Connecting to database</span>
                </div>
                <div class="install-step" data-step="2">
                    <div class="step-icon">2</div>
                    <span>Creating tables and schema</span>
                </div>
                <div class="install-step" data-step="3">
                    <div class="step-icon">3</div>
                    <span>Seeding default data</span>
                </div>
                <div class="install-step" data-step="4">
                    <div class="step-icon">4</div>
                    <span>Finalising configuration</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal">
        <div class="modal-backdrop"></div>
        <div class="modal-card">
            <div class="modal-icon">&#9888;</div>
            <h3>Installation Failed</h3>
            <p id="error-message">Something went wrong during installation.</p>
            <div class="modal-actions">
                <button class="btn-install" id="retry-btn" style="width:auto;padding:10px 24px;font-size:14px;">Try Again</button>
                <a href="../" class="btn-secondary">Back to Setup</a>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var overlay = document.getElementById('install-overlay');
            var modal = document.getElementById('error-modal');
            var errorMsg = document.getElementById('error-message');
            var progressFill = document.getElementById('progress-fill');
            var steps = document.querySelectorAll('.install-step');
            var retryBtn = document.getElementById('retry-btn');

            function setStep(n) {
                steps.forEach(function(s) {
                    var num = parseInt(s.dataset.step);
                    s.classList.remove('active', 'done');
                    if (num < n) { s.classList.add('done'); s.querySelector('.step-icon').textContent = '\u2713'; }
                    else if (num === n) { s.classList.add('active'); }
                    else { s.querySelector('.step-icon').textContent = num; }
                });
                progressFill.style.width = Math.min((n - 1) * 33, 100) + '%';
            }

            retryBtn.addEventListener('click', function() {
                modal.classList.remove('show');
                document.getElementById('send').disabled = false;
                document.getElementById('send').textContent = 'Install MartPoint';
            });

            document.getElementById('send').addEventListener('click', function(event) {
                event.preventDefault();
                var flag = true;

                function check_field(id) {
                    var el = document.getElementById(id);
                    var msg = document.getElementById(id + '_msg');
                    if (!el || !el.value.trim()) {
                        if (msg) msg.classList.add('show');
                        flag = false;
                    } else {
                        if (msg) msg.classList.remove('show');
                    }
                }

                check_field("hostname");
                check_field("username");
                check_field("database");
                check_field("url");

                if (!flag) return false;

                var btn = document.getElementById("send");
                btn.textContent = "Please wait...";
                btn.disabled = true;

                overlay.classList.add('show');
                setStep(1);

                var form = document.getElementById('install_form');
                var formData = new FormData(form);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        setStep(2);
                        setTimeout(function() { setStep(3); }, 600);
                        setTimeout(function() { setStep(4); }, 1200);
                        setTimeout(function() { window.location.href = data.redirect; }, 2000);
                    } else {
                        overlay.classList.remove('show');
                        errorMsg.textContent = data.error || 'Installation failed. Please check your details and try again.';
                        modal.classList.add('show');
                    }
                })
                .catch(function(err) {
                    overlay.classList.remove('show');
                    errorMsg.textContent = 'Network error. Please check your connection and try again.';
                    modal.classList.add('show');
                });
            });
        })();
    </script>
</body>
</html>
