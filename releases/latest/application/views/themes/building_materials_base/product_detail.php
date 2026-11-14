<?php
  $bmProduct = $product;
  $bmProductImage = mp_building_image_url($bmProduct->item_image ?? '');
  $bmProductCategory = trim((string)($bmProduct->category_name ?? ''));
  $bmProductType = (string)($bmProduct->product_type ?? 'physical');
  $bmProductStock = (int)($bmProduct->stock ?? 0);
  $bmQuoteUrl = $bm_quote_url ?? ($bm_contact_url . '?product_id=' . (int)$bmProduct->id);
  $bmBrandName = $bm_brand ?? ($store->store_name ?? 'Store');
  $bmDocTypes = ['datasheet' => 'Datasheet', 'drawing' => 'Drawing', 'manual' => 'Manual', 'certificate' => 'Certificate', 'other' => 'Document'];
  $bmHumanSize = function($bytes){
    $bytes = (int)$bytes;
    if($bytes < 1024) return $bytes . ' B';
    if($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
  };
?>
<section class="product-detail">
  <div class="detail-image">
    <?php if($bmProductImage !== ''): ?><img src="<?= htmlspecialchars($bmProductImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($bmProduct->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?><div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmBrandName, ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bmBrandName), ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>
  </div>
  <div class="detail-copy">
    <p class="eyebrow"><?= htmlspecialchars($bmProductCategory !== '' ? $bmProductCategory : 'SYSTEM', ENT_QUOTES, 'UTF-8'); ?> / TECHNICAL</p>
    <h1><?= htmlspecialchars($bmProduct->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
    <?php if(trim((string)($bmProduct->description ?? '')) !== ''): ?><div class="detail-description"><?= nl2br(htmlspecialchars((string)$bmProduct->description, ENT_QUOTES, 'UTF-8')); ?></div><?php endif; ?>
    <?php if(!empty($bm_show_prices) && isset($bmProduct->effective_price)): ?><p class="detail-price"><span><?= sf_currency($bmProduct->effective_price, $store_currency ?? null); ?></span></p><?php endif; ?>
    <div class="spec-list">
      <?php if($bmProductCategory !== ''): ?><div><span>Category</span><strong><?= htmlspecialchars($bmProductCategory, ENT_QUOTES, 'UTF-8'); ?></strong></div><?php endif; ?>
      <?php if($bmProductType === 'physical'): ?><div><span>Availability</span><strong><?= $bmProductStock > 0 ? 'Available to specify' : 'Lead time to confirm'; ?></strong></div><?php endif; ?>
      <?php foreach(($bm_specs ?? []) as $bmSpec): ?><div><span><?= htmlspecialchars($bmSpec['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span><strong><?= nl2br(htmlspecialchars($bmSpec['value'] ?? '', ENT_QUOTES, 'UTF-8')); ?></strong></div><?php endforeach; ?>
      <?php if(!empty($bm_documents)): ?><div><span>Documents</span><strong><?= count($bm_documents); ?> available</strong></div><?php endif; ?>
    </div>
    <?php if(!empty($bm_documents)): ?><div class="doc-list"><h2>Technical documents</h2><ul>
      <?php foreach($bm_documents as $bmDocument): ?><li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/document/' . (int)$bmProduct->id . '/' . (int)$bmDocument->id); ?>"><span class="doc-title"><?= htmlspecialchars($bmDocument->title ?? '', ENT_QUOTES, 'UTF-8'); ?></span><span class="doc-meta"><?= htmlspecialchars($bmDocTypes[$bmDocument->doc_type ?? 'other'] ?? 'Document', ENT_QUOTES, 'UTF-8'); ?><?= (int)($bmDocument->file_size ?? 0) > 0 ? ' · ' . htmlspecialchars($bmHumanSize($bmDocument->file_size), ENT_QUOTES, 'UTF-8') : ''; ?></span><span class="doc-arrow" aria-hidden="true">↓</span></a></li><?php endforeach; ?>
    </ul></div><?php endif; ?>
    <?php if(!empty($product_variants)): ?><div class="variant-block"><p class="eyebrow">CONFIGURATIONS</p><div class="pill-row variant-row">
      <?php foreach($product_variants as $bmVariant): ?><?php if(!empty($bmVariant->is_current)): ?><span class="active" aria-current="true"><?= htmlspecialchars($bmVariant->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></span><?php else: ?><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . (int)$bmVariant->id); ?>"><?= htmlspecialchars($bmVariant->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?><?php endforeach; ?>
    </div></div><?php endif; ?>
    <a class="button" href="<?= htmlspecialchars($bmQuoteUrl, ENT_QUOTES, 'UTF-8'); ?>">Request a technical quotation<span aria-hidden="true">↗</span></a>
    <p class="detail-secondary">Share the application, dimensions and drawings. The team will confirm the right configuration, lead time and commercial route.</p>
  </div>
</section>
<section class="section pale"><div class="section-head"><div><p class="eyebrow">CONSIDERATIONS</p><h2>Specify with confidence.</h2></div><p class="support">The right system depends on the building, traffic and operating conditions.</p></div><div class="three-columns">
  <?php for($bmNote = 1; $bmNote <= 3; $bmNote++): ?><div><span><?= sprintf('%02d', $bmNote); ?></span><h3><?= htmlspecialchars($bm_values['product_note_' . $bmNote . '_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3><p><?= nl2br(htmlspecialchars($bm_values['product_note_' . $bmNote . '_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p></div><?php endfor; ?>
</div></section>
<?php if(!empty($related_products)): ?><section class="section"><div class="section-head"><div><p class="eyebrow">RELATED SYSTEMS</p><h2>Consider the wider solution.</h2></div><p class="support">Explore adjacent systems and request a coordinated quotation.</p></div><div class="card-grid">
  <?php $bmRelatedTotal = count($related_products); foreach($related_products as $bmIndex => $bmProduct): ?><?php $card_product = $bmProduct; $card_index = $bmIndex; $card_total = $bmRelatedTotal; $this->load->view('themes/building_materials_base/_product_card', get_defined_vars()); ?><?php endforeach; ?>
</div></section><?php endif; ?>
