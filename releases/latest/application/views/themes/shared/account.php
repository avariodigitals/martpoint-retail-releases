<?php
$root=base_url('store/'.($settings->store_slug ?? ''));
$money=function($order) use ($store_currency){ return htmlspecialchars((string)($order->transaction_currency ?? $order->base_currency ?? $store_currency['code'] ?? 'NGN')).' '.number_format((float)($order->transaction_grand_total ?? $order->grand_total ?? 0),2); };
?>
<section class="mp-portal">
  <p class="mp-portal-kicker"><?= htmlspecialchars($store->store_name ?? 'Your store'); ?> · Your account</p>
  <h1>Welcome back, <?= htmlspecialchars($customer->customer_name ?? 'Customer'); ?>.</h1>
  <p class="mp-portal-intro">Your orders, saved products and purchases, together in one place.</p>
  <nav class="mp-portal-actions" aria-label="Your account"><a class="mp-portal-button" href="<?= $root.'/products'; ?>">Continue shopping</a><a class="mp-portal-button mp-portal-secondary" href="<?= $root.'/account/orders'; ?>">All orders</a><?php if(!empty($wishlist_enabled)): ?><a class="mp-portal-button mp-portal-secondary" href="<?= $root.'/wishlist'; ?>">Wishlist</a><?php endif; ?><a class="mp-portal-button mp-portal-secondary" href="<?= $root.'/account_logout'; ?>">Sign out</a></nav>
  <div class="mp-portal-grid"><section class="mp-portal-card"><h2>Recent orders</h2>
  <?php foreach((array)($orders ?? []) as $o): ?><div class="mp-portal-row"><div><a href="<?= $root.'/order_received/'.rawurlencode($o->order_code); ?>">#<?= htmlspecialchars($o->order_code); ?></a><small><?= htmlspecialchars(ucfirst($o->order_status)); ?></small></div><strong><?= $money($o); ?></strong></div><?php endforeach; ?>
  <?php if(empty($orders)): ?><p>You haven't placed an order yet.</p><?php endif; ?>
  </section><aside class="mp-portal-card"><h2>Your details</h2><p><?= htmlspecialchars($customer->customer_name ?? ''); ?><br><?= htmlspecialchars($customer->mobile ?? $customer->phone ?? ''); ?><br><?= htmlspecialchars($customer->email ?? ''); ?></p><a href="<?= $root.'/contact'; ?>">Contact the store ↗</a></aside></div>
  <?php foreach(['downloads'=>'Your downloads','courses'=>'Your courses','memberships'=>'Your memberships'] as $key=>$title): if(empty($$key)) continue; ?>
  <section class="mp-portal-card" style="margin-top:24px" id="<?= $key; ?>"><h2><?= $title; ?></h2><?php foreach($$key as $entry): ?><div class="mp-portal-row"><span><?= htmlspecialchars($entry['name'] ?? ''); ?></span><?php if(!empty($entry['url'])): ?><a href="<?= htmlspecialchars($entry['url'],ENT_QUOTES); ?>"><?= $key==='downloads'?'Download':'Continue'; ?> ↗</a><?php else: ?><span><?= htmlspecialchars(ucfirst($entry['status'] ?? '')); ?></span><?php endif; ?></div><?php endforeach; ?></section>
  <?php endforeach; ?>
</section>
