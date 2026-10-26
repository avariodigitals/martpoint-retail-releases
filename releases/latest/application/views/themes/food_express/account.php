<?php
$home = base_url('store/' . $settings->store_slug);
$name = trim((string)($customer->customer_name ?? 'Guest'));
$initial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
?>
<main class="fx-profile">
  <div class="fx-profile-heading"><span>Your account</span><h1>Your next good meal<br>starts here.</h1></div>
  <section class="fx-profile-identity" aria-label="Your details">
    <span class="fx-profile-avatar" aria-hidden="true"><?= htmlspecialchars($initial); ?></span>
    <div><h2><?= htmlspecialchars($name); ?></h2><p><?= htmlspecialchars($customer->email ?? ''); ?></p><p><?= htmlspecialchars($customer->mobile ?? ''); ?></p></div>
  </section>
  <nav class="fx-profile-actions" aria-label="Your restaurant account">
    <a href="<?= $home; ?>/account/orders"><span class="fx-profile-action-icon" aria-hidden="true">↗</span><span><strong>Your orders</strong><small>Recent meals and order history</small></span><b aria-hidden="true">›</b></a>
    <a href="<?= $home; ?>/branches"><span class="fx-profile-action-icon" aria-hidden="true">⌖</span><span><strong>Your kitchen</strong><small data-fx-location-value>Choose a restaurant location</small></span><b aria-hidden="true">›</b></a>
    <a href="<?= $home; ?>/track"><span class="fx-profile-action-icon" aria-hidden="true">◷</span><span><strong>Track an order</strong><small>Check its latest status</small></span><b aria-hidden="true">›</b></a>
    <a href="<?= $home; ?>/contact"><span class="fx-profile-action-icon" aria-hidden="true">?</span><span><strong>Need a hand?</strong><small>Get in touch with the restaurant</small></span><b aria-hidden="true">›</b></a>
  </nav>
  <section class="fx-profile-recent"><div class="fx-profile-section-heading"><h2>Recent orders</h2><a href="<?= $home; ?>/account/orders">See all ›</a></div>
    <?php if(!empty($orders)): foreach(array_slice($orders, 0, 3) as $o): ?>
      <article class="fx-order-row"><div><strong><?= htmlspecialchars($o->order_code); ?></strong><small><?= date('j M Y', strtotime($o->created_at)); ?> · <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o->order_status))); ?></small></div><b><?= sf_currency($o->grand_total, $store_currency ?? null); ?></b></article>
    <?php endforeach; else: ?>
      <div class="fx-profile-empty"><h3>Something delicious awaits.</h3><p>Your orders will appear here after your first meal.</p><a class="fx-profile-cta" href="<?= $home; ?>/products">Explore the menu <span aria-hidden="true">→</span></a></div>
    <?php endif; ?>
  </section>
  <a class="fx-profile-signout" href="<?= $home; ?>/account/logout">Sign out</a>
</main>
