<?php
// Guard: if the application is already installed, do not expose the setup UI.
$lock_file = __DIR__ . '/../application/config/installed.lock';
if (file_exists($lock_file)) {
    header('HTTP/1.1 403 Forbidden');
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
        <p>The setup wizard has been locked. To reinstall, remove the file:<br><code>application/config/installed.lock</code></p>
        <a href="../">Go to application</a>
    </div>
</body>
</html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MartPoint Setup</title>
	<link rel="shortcut icon" href="assets/martpoint-icon.png?v=2">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary: #6366f1;
			--primary-dark: #4f46e5;
			--success: #10b981;
			--danger: #ef4444;
			--warning: #f59e0b;
			--bg-dark: #0f172a;
			--bg-card: rgba(255, 255, 255, 0.06);
			--text-primary: #f8fafc;
			--text-secondary: #94a3b8;
		}

		* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

		body {
			margin: 0;
			min-height: 100vh;
			background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
			background-size: 400% 400%;
			animation: gradientShift 15s ease infinite;
			overflow-x: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
		}

		@keyframes gradientShift {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}

		/* Floating orbs */
		.orb {
			position: fixed;
			border-radius: 50%;
			filter: blur(80px);
			opacity: 0.35;
			pointer-events: none;
			z-index: 0;
		}
		.orb-1 { width: 400px; height: 400px; background: radial-gradient(circle, #6366f1 0%, transparent 70%); top: -10%; left: -10%; animation: floatOrb1 20s ease-in-out infinite; }
		.orb-2 { width: 300px; height: 300px; background: radial-gradient(circle, #10b981 0%, transparent 70%); bottom: -5%; right: -5%; animation: floatOrb2 18s ease-in-out infinite; }
		.orb-3 { width: 200px; height: 200px; background: radial-gradient(circle, #f59e0b 0%, transparent 70%); top: 40%; left: 60%; animation: floatOrb3 25s ease-in-out infinite; }

		@keyframes floatOrb1 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(60px,40px) scale(1.1);} }
		@keyframes floatOrb2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-50px,-30px) scale(1.15);} }
		@keyframes floatOrb3 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,50px) scale(0.9);} }

		.glass-card {
			background: var(--bg-card);
			backdrop-filter: blur(24px);
			-webkit-backdrop-filter: blur(24px);
			border: 1px solid rgba(255,255,255,0.08);
			border-radius: 24px;
			box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05) inset;
			padding: 48px 40px;
			max-width: 560px;
			width: 100%;
			margin: 24px;
			position: relative;
			z-index: 1;
			opacity: 0;
			transform: translateY(30px) scale(0.96);
			animation: cardReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
		}

		@keyframes cardReveal {
			to { opacity: 1; transform: translateY(0) scale(1); }
		}

		.logo-wrap {
			text-align: center;
			margin-bottom: 32px;
			opacity: 0;
			animation: fadeSlideUp 0.6s 0.2s ease forwards;
		}
		.logo-wrap img { width: 64px; height: 64px; margin-bottom: 16px; }
		.logo-wrap h1 { font-size: 28px; font-weight: 700; color: var(--text-primary); margin: 0; letter-spacing: -0.5px; }
		.logo-wrap p { color: var(--text-secondary); margin: 6px 0 0; font-size: 14px; }

		.status-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 8px 16px;
			border-radius: 50px;
			font-size: 13px;
			font-weight: 500;
			margin-bottom: 12px;
			opacity: 0;
			animation: fadeSlideUp 0.5s ease forwards;
		}
		.status-ok { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
		.status-bad { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
		.status-info { background: rgba(99,102,241,0.12); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.2); }

		.checklist { list-style: none; padding: 0; margin: 0 0 28px; }
		.checklist li {
			position: relative;
			padding: 12px 0 12px 36px;
			color: var(--text-secondary);
			font-size: 14px;
			line-height: 1.6;
			border-bottom: 1px solid rgba(255,255,255,0.04);
			opacity: 0;
			transform: translateX(-10px);
			animation: fadeSlideRight 0.4s ease forwards;
		}
		.checklist li:last-child { border-bottom: none; }
		.checklist li i {
			position: absolute;
			left: 0;
			top: 14px;
			font-size: 16px;
			color: var(--primary);
		}
		.checklist li a { color: #818cf8; text-decoration: underline; }
		.checklist li a:hover { color: #a5b4fc; }

		@keyframes fadeSlideUp { to { opacity: 1; transform: translateY(0); } }
		@keyframes fadeSlideRight { to { opacity: 1; transform: translateX(0); } }

		.btn-mp {
			width: 100%;
			padding: 16px 24px;
			border-radius: 14px;
			font-size: 15px;
			font-weight: 600;
			letter-spacing: 0.3px;
			border: none;
			cursor: pointer;
			transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
			position: relative;
			overflow: hidden;
			text-transform: none;
			margin-bottom: 12px;
			opacity: 0;
			animation: fadeSlideUp 0.5s ease forwards;
		}
		.btn-mp:disabled { opacity: 0.4; cursor: not-allowed; filter: grayscale(0.5); }

		.btn-install {
			background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
			color: #fff;
			box-shadow: 0 4px 14px rgba(99,102,241,0.35);
		}
		.btn-install:hover:not(:disabled) {
			transform: translateY(-2px);
			box-shadow: 0 8px 28px rgba(99,102,241,0.45);
		}
		.btn-install::after {
			content: '';
			position: absolute;
			top: 0; left: -100%;
			width: 100%; height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
			transition: left 0.5s;
		}
		.btn-install:hover::after { left: 100%; }

		.btn-update {
			background: rgba(255,255,255,0.06);
			color: var(--text-secondary);
			border: 1px solid rgba(255,255,255,0.08);
		}
		.btn-update:hover:not(:disabled) {
			background: rgba(255,255,255,0.1);
			color: var(--text-primary);
			transform: translateY(-2px);
		}

		.shimmer {
			position: absolute;
			inset: 0;
			background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.03) 45%, rgba(255,255,255,0.06) 50%, rgba(255,255,255,0.03) 55%, transparent 60%);
			background-size: 200% 100%;
			animation: shimmer 8s linear infinite;
			pointer-events: none;
			border-radius: 24px;
		}
		@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

		.version-chip {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: rgba(255,255,255,0.05);
			border: 1px solid rgba(255,255,255,0.08);
			padding: 6px 14px;
			border-radius: 50px;
			font-size: 12px;
			color: var(--text-secondary);
			margin-bottom: 24px;
		}

		.pulse-dot {
			width: 8px; height: 8px;
			border-radius: 50%;
			background: var(--success);
			position: relative;
		}
		.pulse-dot::after {
			content: '';
			position: absolute;
			inset: -4px;
			border-radius: 50%;
			background: var(--success);
			opacity: 0.4;
			animation: pulseRing 2s ease-out infinite;
		}
		@keyframes pulseRing {
			0% { transform: scale(1); opacity: 0.4; }
			100% { transform: scale(2.5); opacity: 0; }
		}
	</style>
</head>
<body>
	<div class="orb orb-1"></div>
	<div class="orb orb-2"></div>
	<div class="orb orb-3"></div>

	<div class="glass-card">
		<div class="shimmer"></div>

		<div class="logo-wrap">
			<img src="assets/martpoint-logo.png?v=2" alt="MartPoint" style="width:100%;max-width:420px;height:auto;margin:0 auto 12px;display:block;">
			<p style="font-size:15px;font-weight:500;color:var(--text-primary);margin:4px 0 0;letter-spacing:0.3px;">Built in Africa for African businesses.</p>
		</div>

		<?php
		$flag = true;
		$isNewInstall = false;

		// Detect if this is a fresh installation
		$dbConfigPath = __DIR__ . '/../application/config/database.php';
		if (file_exists($dbConfigPath)) {
			$dbConfig = file_get_contents($dbConfigPath);
			if (strpos($dbConfig, "%HOSTNAME%") !== false ||
			    strpos($dbConfig, "%USERNAME%") !== false ||
			    strpos($dbConfig, "%DATABASE%") !== false) {
				$isNewInstall = true;
			}
		}

		$animDelay = 0.3;

		// PHP version check
		$phpversion = phpversion();
		if (version_compare($phpversion, '7.4', '<')) {
			echo '<div class="status-badge status-bad" style="animation-delay:'.$animDelay.'s"><i class="fa fa-times-circle"></i> PHP '.htmlspecialchars($phpversion).' detected — PHP 7.4 or higher required</div>';
			$flag = false;
			$animDelay += 0.1;
		} else {
			echo '<div class="status-badge status-ok" style="animation-delay:'.$animDelay.'s"><i class="fa fa-check-circle"></i> PHP '.htmlspecialchars($phpversion).' OK</div>';
			$animDelay += 0.1;
		}

		// allow_url_fopen check
		if (ini_get('allow_url_fopen')) {
			echo '<div class="status-badge status-ok" style="animation-delay:'.$animDelay.'s"><i class="fa fa-check-circle"></i> allow_url_fopen enabled</div>';
		} else {
			echo '<div class="status-badge status-bad" style="animation-delay:'.$animDelay.'s"><i class="fa fa-times-circle"></i> allow_url_fopen disabled — please enable</div>';
			$flag = false;
		}
		$animDelay += 0.1;
		?>

		<div class="version-chip" style="opacity:0;animation:fadeSlideUp 0.5s <?=$animDelay?>s ease forwards;">
			<div class="pulse-dot"></div>
			<span>Ready to install</span>
		</div>

		<ul class="checklist">
			<li style="animation-delay: <?=($animDelay+0.1)?>s">
				<i class="fa fa-info-circle"></i>
				<strong>SQL Mode:</strong> Ensure MySQL is not running with <code>ONLY_FULL_GROUP_BY</code> enabled.
			</li>
			<li style="animation-delay: <?=($animDelay+0.2)?>s">
				<i class="fa fa-server"></i>
				<strong>Local install?</strong> Start your web server in <strong>Administrator mode</strong> to avoid permission issues.
			</li>
			<li style="animation-delay: <?=($animDelay+0.3)?>s">
				<i class="fa fa-shield"></i>
				<strong>Database:</strong> Create an empty database beforehand, or the installer can create it for you.
			</li>
		</ul>

		<a href="install" style="text-decoration:none;display:block;opacity:0;animation:fadeSlideUp 0.5s <?=($animDelay+0.4)?>s ease forwards;">
			<button class="btn-mp btn-install" <?=(!$flag) ? 'disabled' : '';?> type="button">
				<i class="fa fa-rocket" style="margin-right:8px;"></i> Install MartPoint
			</button>
		</a>

		<?php if (!$isNewInstall): ?>
		<a href="update" style="text-decoration:none;display:block;opacity:0;animation:fadeSlideUp 0.5s <?=($animDelay+0.5)?>s ease forwards;">
			<button class="btn-mp btn-update" <?=(!$flag) ? 'disabled' : '';?> type="button">
				<i class="fa fa-refresh" style="margin-right:8px;"></i> Update Existing Installation
			</button>
		</a>
		<?php endif; ?>
	</div>
</body>
</html>