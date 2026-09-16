<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Vehicle Receipt</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 140px; }
    .receipt { background: #fff; border: 1px dashed var(--mp-border); border-radius: 16px; padding: 20px; }
    .receipt-header { text-align: center; border-bottom: 2px dashed var(--mp-border); padding-bottom: 16px; margin-bottom: 16px; }
    .receipt-header h2 { margin: 0 0 4px; font-size: 20px; }
    .receipt-header p { margin: 0; color: var(--mp-muted); font-size: 13px; }
    .receipt-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .receipt-row .label { color: var(--mp-muted); }
    .receipt-row .value { font-weight: 600; }
    .receipt-section h3 { font-size: 13px; color: var(--mp-muted); text-transform: uppercase; margin: 20px 0 10px; }
    .receipt-total { background: var(--mp-bg); border-radius: 12px; padding: 14px; margin-top: 16px; }
    .receipt-total .receipt-row { border-bottom: none; }
    .receipt-total .total .value { font-size: 17px; color: var(--mp-primary); }
    .vehicle-img { width: 100%; max-height: 180px; object-fit: cover; border-radius: 12px; margin-bottom: 14px; }
    .actions { display: flex; gap: 10px; margin: 16px 0; }
    .btn { flex: 1; padding: 14px; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: #fff; color: var(--mp-ink); border: 1px solid var(--mp-border); }
    .thank-you { text-align: center; color: var(--mp-muted); margin-top: 16px; font-size: 13px; }
    @media print {
      .actions, nav, .mp-mobile-footer { display: none !important; }
      #app { max-width: 100%; }
      .screen { padding: 0; }
      .receipt { border: none; }
    }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="actions">
        <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <a href="<?= base_url('mobile/automobile'); ?>" class="btn btn-secondary">Back</a>
      </div>

      <div class="receipt">
        <div class="receipt-header">
          <h2><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></h2>
          <p>Vehicle Receipt</p>
        </div>

        <div class="receipt-row"><span class="label">Receipt #</span><span class="value">VH-<?= str_pad($vehicle->id, 6, '0', STR_PAD_LEFT); ?></span></div>
        <div class="receipt-row"><span class="label">Date</span><span class="value"><?= !empty($vehicle->sold_date) ? date('M j, Y h:i A', strtotime($vehicle->sold_date)) : date('M j, Y h:i A'); ?></span></div>
        <div class="receipt-row"><span class="label">Sold By</span><span class="value"><?= htmlspecialchars($vehicle->sold_by ?? 'Staff'); ?></span></div>

        <?php if (!empty($vehicle->image_path) && file_exists(FCPATH . $vehicle->image_path)): ?>
          <img src="<?= base_url($vehicle->image_path); ?>" class="vehicle-img" alt="Vehicle image">
        <?php endif; ?>

        <div class="receipt-section">
          <h3>Vehicle</h3>
          <div class="receipt-row"><span class="label">Vehicle</span><span class="value"><?= htmlspecialchars(($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model); ?></span></div>
          <div class="receipt-row"><span class="label">Color</span><span class="value"><?= htmlspecialchars($vehicle->color ?: '-'); ?></span></div>
          <div class="receipt-row"><span class="label">Mileage</span><span class="value"><?= $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : '-'; ?></span></div>
          <div class="receipt-row"><span class="label">VIN</span><span class="value"><?= htmlspecialchars($vehicle->vin ?: '-'); ?></span></div>
          <div class="receipt-row"><span class="label">Condition</span><span class="value"><?= ucfirst($vehicle->vehicle_condition); ?></span></div>
        </div>

        <div class="receipt-section">
          <h3>Buyer</h3>
          <div class="receipt-row"><span class="label">Name</span><span class="value"><?= htmlspecialchars($vehicle->customer_name ?: 'Walk-in'); ?></span></div>
          <div class="receipt-row"><span class="label">Payment</span><span class="value"><?= htmlspecialchars($vehicle->payment_method ?: 'Cash'); ?></span></div>
        </div>

        <div class="receipt-total">
          <div class="receipt-row"><span class="label">Price</span><span class="value"><?= number_format($vehicle->price, 2); ?></span></div>
          <div class="receipt-row"><span class="label">Paid</span><span class="value"><?= number_format($vehicle->amount_paid, 2); ?></span></div>
          <div class="receipt-row total"><span class="label">Balance</span><span class="value"><?= number_format($vehicle->price - $vehicle->amount_paid, 2); ?></span></div>
        </div>

        <p class="thank-you">Thank you for your business.</p>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
    <?php $this->load->view('mobile/mp_alert'); ?>
    <?php $this->load->view('mobile/chat'); ?>
  </div>
</body>
</html>
