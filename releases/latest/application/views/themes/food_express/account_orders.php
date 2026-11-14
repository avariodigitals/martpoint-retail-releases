<?php $home = base_url('store/' . $settings->store_slug); ?>
<main class="fx-profile">
  <a class="fx-profile-back" href="<?= $home; ?>/account">‹ Your profile</a>
  <div class="fx-profile-heading"><h1>Your orders</h1><p>A little history of good food.</p></div>
  <?php if(!empty($orders)): ?>
    <div class="fx-profile-recent">
    <?php foreach($orders as $o): ?>
      <article class="fx-order-row"><div><strong><?= htmlspecialchars($o->order_code); ?></strong><small><?= date('j M Y, g:ia', strtotime($o->created_at)); ?></small><span class="fx-order-status"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o->order_status))); ?></span></div><div><b><?= sf_currency($o->grand_total, $store_currency ?? null); ?></b><small><?= htmlspecialchars(ucfirst($o->payment_status ?? 'pending')); ?></small></div></article>
    <?php endforeach; ?>
    </div>
    <a class="fx-profile-cta" href="<?= $home; ?>/track">Track an order →</a>
  <?php else: ?>
    <section class="fx-profile-empty"><h2>Your first meal is waiting.</h2><p>Once you place an order, you can find it here.</p><a class="fx-profile-cta" href="<?= $home; ?>/products">Explore the menu →</a></section>
  <?php endif; ?>
</main>
