<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Account Suspended — MartPoint Retail</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?=base_url();?>theme/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=base_url();?>theme/css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .suspended-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      padding: 50px 40px;
      max-width: 480px;
      width: 90%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .suspended-icon {
      font-size: 72px;
      color: #e74c3c;
      margin-bottom: 20px;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
    .suspended-title {
      font-size: 28px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 12px;
    }
    .suspended-text {
      font-size: 16px;
      color: #555;
      margin-bottom: 30px;
      line-height: 1.6;
    }
    .whatsapp-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #25D366;
      color: #fff;
      font-size: 17px;
      font-weight: 600;
      padding: 14px 32px;
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.3s ease;
      border: none;
    }
    .whatsapp-btn:hover {
      background: #128C7E;
      color: #fff;
      text-decoration: none;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
    }
    .whatsapp-btn i {
      font-size: 22px;
    }
    .store-name {
      font-size: 14px;
      color: #888;
      margin-top: 25px;
    }
    .status-badge {
      display: inline-block;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 20px;
    }
    .status-suspended { background: #e74c3c; color: #fff; }
    .status-expired { background: #f39c12; color: #fff; }
  </style>
</head>
<body>
  <div class="suspended-card">
    <div class="suspended-icon">
      <i class="fa fa-ban"></i>
    </div>

    <?php if($sub_status === 'SUSPENDED'): ?>
      <span class="status-badge status-suspended">Account Suspended</span>
    <?php else: ?>
      <span class="status-badge status-expired">Subscription Expired</span>
    <?php endif; ?>

    <div class="suspended-title">Access Restricted</div>
    <div class="suspended-text">
      <?= $suspension_reason ? htmlspecialchars($suspension_reason) : 'Your subscription has expired or the account has been suspended. Please contact the administrator to restore access.'; ?>
    </div>

    <?php if(!empty($whatsapp_number)): ?>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp_number); ?>?text=Hello,%20my%20MartPoint%20Retail%20account%20is%20suspended.%20Please%20assist." 
         target="_blank" class="whatsapp-btn">
        <i class="fa fa-whatsapp"></i>
        Contact Admin on WhatsApp
      </a>
    <?php else: ?>
      <div class="alert alert-warning" style="margin-top:10px;">
        <i class="fa fa-info-circle"></i> No support number configured.
      </div>
    <?php endif; ?>

    <div class="store-name">
      <i class="fa fa-lock"></i> MartPoint Retail &middot; <?= htmlspecialchars($store_name ?? 'Secure System'); ?>
    </div>
  </div>
</body>
</html>
