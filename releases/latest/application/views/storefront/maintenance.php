<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Unavailable') ?></title>
    <link rel="stylesheet" href="<?= base_url('theme/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --bg: #f4f7f6;
            --card-bg: #fff;
            --text: #333;
            --muted: #888;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text);
        }
        .maintenance-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 60px 50px;
            text-align: center;
            max-width: 480px;
            width: 90%;
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 42px;
        }
        .maintenance .icon-circle {
            background: #e3f2fd;
            color: var(--primary);
        }
        .deactivated .icon-circle {
            background: #fce4ec;
            color: #c62828;
        }
        .maintenance-card h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .maintenance-card p {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .maintenance .status-badge {
            background: #e3f2fd;
            color: var(--primary);
        }
        .deactivated .status-badge {
            background: #fce4ec;
            color: #c62828;
        }
        .footer-note {
            margin-top: 32px;
            font-size: 13px;
            color: #bbb;
        }
        .footer-note a {
            color: var(--muted);
            text-decoration: none;
        }
        .footer-note a:hover { color: var(--primary); }
        .gear-spin { animation: spin 3s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @media (max-width: 480px) {
            .maintenance-card { padding: 40px 24px; }
            .maintenance-card h1 { font-size: 22px; }
        }
    </style>
</head>
<body class="<?= $store_status ?>">
    <div class="maintenance-card">
        <?php if ($store_status === 'maintenance'): ?>
            <div class="icon-circle">
                <i class="fa fa-cog gear-spin" aria-hidden="true"></i>
            </div>
            <span class="status-badge">Under Maintenance</span>
            <h1>We'll Be Right Back</h1>
            <p>We're currently performing scheduled maintenance to improve your experience. Please check back shortly.</p>
        <?php else: ?>
            <div class="icon-circle">
                <i class="fa fa-lock" aria-hidden="true"></i>
            </div>
            <span class="status-badge">Unavailable</span>
            <h1>Store Not Found</h1>
            <p>The store you're looking for is not available at the moment. It may have been removed or deactivated.</p>
        <?php endif; ?>
        <div class="footer-note">
            <i class="fa fa-shopping-bag" style="margin-right:5px;"></i>
            Powered by <a href="#">MartPoint Retail</a>
        </div>
    </div>
</body>
</html>
