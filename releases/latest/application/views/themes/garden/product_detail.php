<?php if (!empty($product->requires_quote)): ?>
<section class="mp-section"><h1><?= htmlspecialchars($product->item_name); ?></h1>
<?php if (!empty($product->item_image)): ?><img src="<?= base_url($product->item_image); ?>" alt="<?= htmlspecialchars($product->item_name); ?>" style="width:100%;max-height:440px;object-fit:contain"><?php endif; ?>
<p><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p><strong>Quote Required</strong>
<?php if (mp_garden_has_mode($garden_profile,'landscaping') || mp_garden_has_mode($garden_profile,'maintenance')): ?><div class="garden-store-links" style="color:var(--mp-primary)"><a href="<?= htmlspecialchars($garden_request_url.'?product_id='.(int)$product->id); ?>">Request a Quotation</a></div><?php endif; ?></section>
<?php else: $this->load->view('themes/shared/product_detail'); endif; ?>
<?php if (!empty($garden_plant_attributes)): ?><section class="mp-section garden-attributes"><h2>Plant Details &amp; Care</h2><dl>
<?php foreach (mp_garden_attributes() as $key=>$label): if (empty($garden_plant_attributes[$key])) continue; ?><dt><?= htmlspecialchars($label); ?></dt><dd><?= htmlspecialchars($garden_plant_attributes[$key]); ?></dd><?php endforeach; ?>
</dl></section><?php endif; ?>
