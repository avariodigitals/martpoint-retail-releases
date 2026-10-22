<section class="mp-section"><h2 class="mp-section-title">Services</h2><div class="garden-services-grid">
<?php foreach (($services ?? []) as $s): $policy=mp_service_price_model($s); ?>
<a href="<?= base_url('store/'.$settings->store_slug.'/service/'.(int)$s->id); ?>"><h3><?= htmlspecialchars($s->service_name); ?></h3><p><?= htmlspecialchars($s->service_duration ?? ''); ?></p>
<strong><?= $policy === 'quote' ? 'Quote Required' : ($policy === 'starting' ? 'From ' : '').sf_currency($s->effective_price ?? $s->price,$store_currency ?? null); ?></strong><p><?= $policy === 'fixed' ? 'View Service' : 'Request Details'; ?></p></a>
<?php endforeach; ?></div></section>
