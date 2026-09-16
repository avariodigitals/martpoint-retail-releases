<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Vehicle Receipt</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); }
    .receipt { max-width: 600px; margin: 24px auto; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 32px; }
    .receipt-header { text-align: center; border-bottom: 2px dashed var(--mp-border); padding-bottom: 20px; margin-bottom: 24px; }
    .receipt-header h2 { margin: 0 0 6px; font-size: 22px; }
    .receipt-header p { margin: 0; color: var(--mp-muted); font-size: 14px; }
    .receipt-meta { display: flex; justify-content: space-between; margin-bottom: 24px; font-size: 14px; }
    .receipt-section { margin-bottom: 20px; }
    .receipt-section h3 { font-size: 15px; margin: 0 0 10px; color: var(--mp-muted); text-transform: uppercase; font-weight: 600; }
    .receipt-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .receipt-row .label { color: var(--mp-muted); }
    .receipt-row .value { font-weight: 600; }
    .receipt-total { background: var(--mp-bg); border-radius: 12px; padding: 16px; margin-top: 20px; }
    .receipt-total .receipt-row { border-bottom: none; }
    .receipt-total .receipt-row.total .value { font-size: 18px; color: var(--mp-primary); }
    .vehicle-img { width: 100%; max-height: 240px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; }
    .actions { max-width: 600px; margin: 16px auto; text-align: right; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border: none; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .btn i { font-size: 16px; }
    @media print {
      body { background: #fff; }
      .actions { display: none; }
      .receipt { border: none; box-shadow: none; margin: 0; max-width: 100%; }
    }
  </style>
</head>
<body>
  <div class="actions">
    <button class="btn" onclick="window.print()"><i class="fa fa-print"></i> Print Receipt</button>
    <a href="<?= base_url('automobile/list'); ?>" class="btn" style="background:#fff; color:var(--mp-text); border:1px solid var(--mp-border);">Back to Vehicles</a>
  </div>

  <div class="receipt">
    <div class="receipt-header">
      <h2><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></h2>
      <p>Vehicle Receipt</p>
    </div>

    <div class="receipt-meta">
      <div>
        <strong>Receipt #:</strong> VH-<?= str_pad($vehicle->id, 6, '0', STR_PAD_LEFT); ?><br>
        <strong>Date:</strong> <?= !empty($vehicle->sold_date) ? date('M j, Y h:i A', strtotime($vehicle->sold_date)) : date('M j, Y h:i A'); ?>
      </div>
      <div style="text-align:right;">
        <strong>Status:</strong> <?= ucfirst($vehicle->status); ?><br>
        <strong>Sold By:</strong> <?= htmlspecialchars($vehicle->sold_by ?? 'Staff'); ?>
      </div>
    </div>

    <?php if (!empty($vehicle->image_path) && file_exists(FCPATH . $vehicle->image_path)): ?>
      <img src="<?= base_url($vehicle->image_path); ?>" class="vehicle-img" alt="Vehicle image">
    <?php endif; ?>

    <div class="receipt-section">
      <h3>Vehicle Details</h3>
      <div class="receipt-row"><span class="label">Vehicle</span><span class="value"><?= htmlspecialchars(($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model); ?></span></div>
      <div class="receipt-row"><span class="label">Color</span><span class="value"><?= htmlspecialchars($vehicle->color ?: '-'); ?></span></div>
      <div class="receipt-row"><span class="label">Mileage</span><span class="value"><?= $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : '-'; ?></span></div>
      <div class="receipt-row"><span class="label">VIN</span><span class="value"><?= htmlspecialchars($vehicle->vin ?: '-'); ?></span></div>
      <div class="receipt-row"><span class="label">License Plate</span><span class="value"><?= htmlspecialchars($vehicle->license_plate ?: '-'); ?></span></div>
      <div class="receipt-row"><span class="label">Condition</span><span class="value"><?= ucfirst($vehicle->vehicle_condition); ?></span></div>
      <div class="receipt-row"><span class="label">Fuel / Transmission</span><span class="value"><?= htmlspecialchars(($vehicle->fuel_type ?: '-') . ' / ' . ($vehicle->transmission ?: '-')); ?></span></div>
    </div>

    <div class="receipt-section">
      <h3>Buyer Details</h3>
      <div class="receipt-row"><span class="label">Name</span><span class="value"><?= htmlspecialchars($vehicle->customer_name ?: 'Walk-in'); ?></span></div>
      <div class="receipt-row"><span class="label">Customer ID</span><span class="value"><?= $vehicle->customer_id ? (int) $vehicle->customer_id : '-'; ?></span></div>
      <div class="receipt-row"><span class="label">Payment Method</span><span class="value"><?= htmlspecialchars($vehicle->payment_method ?: 'Cash'); ?></span></div>
    </div>

    <div class="receipt-total">
      <div class="receipt-row"><span class="label">Vehicle Price</span><span class="value"><?= number_format($vehicle->price, 2); ?></span></div>
      <div class="receipt-row"><span class="label">Amount Paid</span><span class="value"><?= number_format($vehicle->amount_paid, 2); ?></span></div>
      <div class="receipt-row total"><span class="label">Balance Due</span><span class="value"><?= number_format($vehicle->price - $vehicle->amount_paid, 2); ?></span></div>
    </div>

    <p style="text-align:center; color:var(--mp-muted); margin-top:24px; font-size:13px;">Thank you for your business.</p>
  </div>
</body>
</html>
