<?php $beRoot=base_url('store/'.$settings->store_slug); ?>
<div class="be-catalogue-grid">
<?php foreach(($beItems ?? []) as $p):
 $price=$p->effective_price ?? $this->storefront_model->getProductEffectivePrice($p);
 $type=$p->product_type ?? 'physical';$booking=$type==='service'||!empty($p->service_bit);$physical=$type==='physical'&&!$booking;
 $out=$physical&&(float)$p->stock<=0&&empty($settings->allow_backorder);
 $options=!empty($p->variant_count)||($p->item_group ?? '')==='Variants'||!$physical||!empty($p->requires_quote);
 $detail=$beRoot.'/product/'.(int)$p->id;$image=mp_storefront_image_url($p->item_image ?? '');
 $payload=['id'=>(int)$p->id,'type'=>$type,'name'=>$p->item_name,'price'=>(float)$price,'image'=>$p->item_image ?? '','stock'=>(float)$p->stock];
?>
<article class="be-shop-card" data-product-id="<?= (int)$p->id; ?>">
 <a class="be-shop-image" href="<?= $detail; ?>" aria-label="View <?= htmlspecialchars($p->item_name); ?>"><img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async"><?php if($out): ?><span class="be-shop-badge">Out of stock</span><?php elseif(!empty($p->is_new_arrival)): ?><span class="be-shop-badge">New arrival</span><?php elseif(!$physical): ?><span class="be-shop-badge"><?= htmlspecialchars(ucfirst($type)); ?></span><?php endif; ?></a>
 <div class="be-shop-meta"><span><?= htmlspecialchars($p->category_name ?? $beValue('shop_label')); ?></span><?php if(!empty($p->variant_count)): ?><span>Options available</span><?php endif; ?></div>
 <h2><a href="<?= $detail; ?>"><?= htmlspecialchars($p->item_name); ?></a></h2>
 <div class="be-shop-price"><?= sf_currency($price,$store_currency ?? null); ?><?php if(($p->original_price ?? 0)>$price): ?><del><?= sf_currency($p->original_price,$store_currency ?? null); ?></del><?php endif; ?></div>
 <?php if(!empty($cart_enabled)): ?><?php if($options): ?><a class="be-card-action" href="<?= $detail; ?>"><?= $beCopy($booking?'service_book':(!empty($p->requires_quote)?'service_quote':'view_options')); ?> <span aria-hidden="true">↗</span></a><?php else: ?><button type="button" class="be-card-action" data-be-add="<?= htmlspecialchars(json_encode($payload,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT),ENT_QUOTES,'UTF-8'); ?>" <?= $out?'disabled':''; ?>><?= $out?'Currently unavailable':$beCopy('add_to_bag'); ?><span aria-hidden="true">+</span></button><?php endif; ?><?php else: ?><a class="be-card-action" href="<?= $detail; ?>"><?= $beCopy('view_details'); ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
