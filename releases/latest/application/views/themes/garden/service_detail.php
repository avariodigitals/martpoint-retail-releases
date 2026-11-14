<?php $policy=mp_service_price_model($service); ?>
<section class="mp-section"><h1><?= htmlspecialchars($service->service_name); ?></h1>
<?php if (!empty($service->service_image) && file_exists(FCPATH.$service->service_image)): ?><img src="<?= base_url($service->service_image); ?>" alt="<?= htmlspecialchars($service->service_name); ?>" style="width:100%;max-height:440px;object-fit:cover;border-radius:14px"><?php endif; ?>
<p><strong><?= $policy === 'quote' ? 'Quote Required' : ($policy === 'starting' ? 'From ' : '').sf_currency($service->effective_price,$store_currency ?? null); ?></strong></p>
<p><?= nl2br(htmlspecialchars($service->description ?? '')); ?></p>
<?php if (!empty($service->service_duration)): ?><p>Duration: <?= htmlspecialchars($service->service_duration); ?></p><?php endif; ?>
<?php if (!empty($service->location_type)): ?><p>Location: <?= htmlspecialchars(ucwords(str_replace('-',' ',$service->location_type))); ?></p><?php endif; ?>
<div class="garden-store-links" style="color:var(--mp-primary)">
<?php if ($policy === 'fixed' && !empty($cart_enabled)): ?><button class="mp-btn-primary" id="garden-add-service">Add Service to Cart</button>
<script>document.getElementById('garden-add-service').addEventListener('click',function(){addToCart(<?= (int)$service->id; ?>,'service',<?= json_encode($service->service_name,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,<?= (float)$service->effective_price; ?>,<?= json_encode($service->service_image ?? '',JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,1,999);});</script><?php endif; ?>
<?php if (mp_garden_has_mode($garden_profile,'maintenance') || mp_garden_has_mode($garden_profile,'landscaping')): ?><a href="<?= htmlspecialchars($garden_request_url.'?service_id='.(int)$service->id); ?>">Request This Service</a><p>Requests are pending until the team confirms availability and pricing.</p><?php endif; ?>
</div></section>
