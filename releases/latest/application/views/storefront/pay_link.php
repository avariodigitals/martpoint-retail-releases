<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Public invoice-and-pay page for an online order the customer is being asked to
 * pay after the fact — the prescription journey, and any order where the pharmacy
 * approved first and invoiced second. Standalone by design: no login, no cart,
 * no store theme, because the link is opened from an SMS on a phone that has
 * never visited this store.
 */
$currency = is_array($store_currency ?? null) ? $store_currency : ['symbol' => '', 'placement' => 'Left'];
$paidIn = (float)($paid_in ?? 0);
$money = function ($value) use ($currency) {
    $n = store_number_format((float)$value);
    return strtolower((string)($currency['placement'] ?? 'Left')) === 'left'
        ? trim($currency['symbol'] . ' ' . $n) : trim($n . ' ' . $currency['symbol']);
};
$e = function ($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); };
$esc = function ($url) { return htmlspecialchars((string)$url, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pay <?= $e($order->order_code); ?> — <?= $e($store_name); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:#F1F5F9;color:#0F172A;padding:18px 14px 44px;line-height:1.5;}
    .wrap{max-width:520px;margin:0 auto;}
    .brand{font-size:13px;font-weight:700;letter-spacing:.02em;text-transform:uppercase;color:#64748B;margin-bottom:6px;}
    h1{font-size:21px;font-weight:800;margin-bottom:4px;}
    .sub{font-size:13px;color:#64748B;margin-bottom:18px;}
    .card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 4px 22px rgba(15,23,42,.07);margin-bottom:14px;}
    .card h2{font-size:14px;font-weight:700;margin-bottom:12px;}
    ul.lines{list-style:none;}
    ul.lines li{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #EEF2F7;font-size:14px;}
    ul.lines li:last-child{border-bottom:none;}
    ul.lines .qty{color:#64748B;font-size:12px;display:block;}
    .totals div{display:flex;justify-content:space-between;gap:12px;font-size:14px;padding:6px 0;}
    .totals .due{font-size:18px;font-weight:800;border-top:2px solid #0F172A;margin-top:8px;padding-top:12px;}
    .note{font-size:12px;color:#64748B;}
    .strip{border-radius:12px;padding:13px 15px;font-size:14px;font-weight:600;margin-bottom:14px;}
    .ok{background:#ECFDF5;color:#065F46;}
    .warn{background:#FFFBEB;color:#92400E;}
    .bad{background:#FEF2F2;color:#991B1B;}
    form.pay{margin:0;}
    button.pay{width:100%;padding:15px 0;border:none;border-radius:12px;background:#0D9488;color:#fff;font-size:16px;font-weight:700;font-family:inherit;cursor:pointer;}
    button.pay:hover{filter:brightness(.96);}
    .rx{font-size:13px;color:#334155;background:#F8FAFC;border-left:3px solid #0D9488;border-radius:0 10px 10px 0;padding:11px 13px;margin-bottom:14px;}
    .secure{font-size:12px;color:#64748B;text-align:center;margin-top:12px;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="brand"><?= $e($store_name); ?></div>
  <h1>Invoice for order <?= $e($order->order_code); ?></h1>
  <div class="sub">
    <?php if($branch_name !== ''): ?>Collected or delivered from <?= $e($branch_name); ?>.<?php else: ?>Placed with <?= $e($store_name); ?>.<?php endif; ?>
  </div>

<?php if($state === 'payable'): ?>
  <?php if(!empty($rx_names)): ?>
    <div class="rx">A pharmacist has approved the prescription for: <?= $e(implode(', ', $rx_names)); ?>.</div>
  <?php endif; ?>
  <?php if($pay_error !== ''): ?>
    <div class="strip bad"><?= $e($pay_error); ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>What you are buying</h2>
    <ul class="lines">
      <?php foreach($items as $line): ?>
      <li>
        <span><?= $e($line->item_name); ?><span class="qty"><?= (int)$line->qty; ?> &times; <?= $e($money($line->unit_price)); ?></span></span>
        <span><?= $e($money($line->total_price)); ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card">
    <h2>Amount due</h2>
    <div class="totals">
      <div><span>Subtotal</span><span><?= $e($money($order->subtotal)); ?></span></div>
      <?php if((float)$order->delivery_fee > 0): ?>
      <div>
        <span>Delivery<?= $order->shipping_method && stripos((string)$order->shipping_method, 'pickup') === false ? ' — ' . $e($order->shipping_method) : ''; ?></span>
        <span><?= $e($money($order->delivery_fee)); ?></span>
      </div>
      <?php endif; ?>
      <div class="due"><span>Total</span><span><?= $e($money($order->grand_total)); ?></span></div>
    </div>
    <p class="note" style="margin-top:10px;">Delivery is included in this total, so there is nothing to add on at the counter.</p>
  </div>

  <?php if($allow_paystack): ?>
    <form class="pay" method="post" action="<?= $esc($pay_url); ?>">
      <input type="hidden" name="<?= $e($csrf_name); ?>" value="<?= $e($csrf); ?>">
      <button class="pay" type="submit">Pay <?= $e($money($order->grand_total)); ?> with MartPoint Pay</button>
      <div class="secure">Secure checkout provided by Paystack. <?= $e($store_name); ?> never sees your card details.</div>
    </form>
  <?php else: ?>
    <div class="strip warn">This store has not switched on card payment yet. Pay at collection, or ask them for transfer details.</div>
  <?php endif; ?>

  <p class="note" style="text-align:center;margin-top:14px;">
    This link stops working on <?= $e(date('d M Y \a\t H:i', strtotime((string)$order->pay_link_expires_at))); ?>.
    It was made for this order only.
  </p>
<?php else: ?>
  <?php
    $maps = [
      'paid'     => ['ok',   'This order is already paid. Nothing further is due — you do not need to pay again.'],
      'partial'  => ['warn', 'Part of this order has already reached ' . $store_name . ', so paying the full total again here would charge you twice. ' . $store_name . ' will confirm the balance with you — there is nothing to pay on this page.'],
      'refunded' => ['warn', 'This order was refunded, so it is closed. Contact the store if you think that is wrong.'],
      'closed'   => ['bad',  'This order is no longer active, so it cannot be paid through this link. Contact the store if you still need the items.'],
      'expired'  => ['bad',  'This payment link has expired. Ask ' . $store_name . ' to send you a new one — your order is safe with them.'],
    ];
    $m = $maps[$state] ?? $maps['expired'];
  ?>
  <div class="strip <?= $m[0]; ?>"><?= $e($m[1]); ?></div>
  <?php if($pay_error !== ''): ?><div class="strip bad"><?= $e($pay_error); ?></div><?php endif; ?>
  <div class="card">
    <h2>Order <?= $e($order->order_code); ?></h2>
    <div class="totals">
      <?php if($paidIn > 0): ?>
      <div><span>Already received</span><span><?= $e($money($paidIn)); ?></span></div>
      <?php endif; ?>
      <div class="due"><span>Total</span><span><?= $e($money($order->grand_total)); ?></span></div>
    </div>
    <p class="note" style="margin-top:10px;">Placed with <?= $e($store_name); ?> by <?= $e($order->customer_name); ?>.</p>
  </div>
<?php endif; ?>
</div>
</body>
</html>
