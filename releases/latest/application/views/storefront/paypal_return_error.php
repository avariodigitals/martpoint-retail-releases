<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment status</title></head>
<body style="font:16px/1.5 system-ui,sans-serif;max-width:640px;margin:8vh auto;padding:24px;color:#17212b">
<h1>We’re checking your payment</h1><p><?= htmlspecialchars((string)$message,ENT_QUOTES,'UTF-8'); ?></p>
<p>Order <strong><?= htmlspecialchars((string)$order->order_code,ENT_QUOTES,'UTF-8'); ?></strong> for <?= htmlspecialchars((string)($store->store_name??'the store'),ENT_QUOTES,'UTF-8'); ?>.</p>
<p>Do not submit another payment while this payment is being checked. <a href="<?= base_url('store/'.rawurlencode((string)($settings->store_slug??''))); ?>">Return to the store</a>.</p></body></html>
