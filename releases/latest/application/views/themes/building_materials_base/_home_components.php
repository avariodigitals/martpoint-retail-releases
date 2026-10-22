<?php
// All collections are supplied by Storefront/Theme_engine for this store only.
$bmEscape = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$bmSafeLink = function($value, $fallback = ''){
    $value = trim((string)$value);
    if(preg_match('~^https?://~i', $value) && filter_var($value, FILTER_VALIDATE_URL)) return $value;
    if(preg_match('~^/(?!/)[^\\\\]*$~', $value) || preg_match('~^#[a-z0-9_-]+$~i', $value)) return $value;
    return $fallback;
};
$bmDefaults = [
    'faqs'=>['Questions, answered.','Useful information before your next project.'],
    'newsletter'=>['Project insights, in your inbox.', 'Receive technical guidance, company news and system updates.'],
    'brands'=>['Our partners.',''], 'testimonials'=>['From our customers.',''],
    'instagram_gallery'=>['From the field.',''], 'featured_categories'=>['Explore our systems.','Browse by category, then discuss your requirements with our team.'],
    'featured_services'=>['Support for every stage.','Surveys, installation and ongoing care, connected to your project.'],
    'trust_badges'=>['Our commitments.',''], 'store_hours'=>['Visit or speak with us.',''],
    'whatsapp_cta'=>['A question about your project?','Talk to our team on WhatsApp.'],
    'promo_banner'=>['In focus.','']
];
if(!isset($bmDefaults[$bmSectionBase])) return;
$bmTitle = $bmText('title', $bmDefaults[$bmSectionBase][0]);
$bmSub = $bmText('subtitle', $bmDefaults[$bmSectionBase][1]);
$bmItems = [];
switch($bmSectionBase){
    case 'faqs': $bmItems = $faqs ?? []; break;
    case 'brands': $bmItems = $brands ?? []; break;
    case 'testimonials': $bmItems = $testimonials ?? []; break;
    case 'instagram_gallery': $bmItems = $instagram_posts ?? []; break;
    case 'featured_categories': $bmItems = !empty($sells_products) ? ($categories ?? []) : []; break;
    case 'featured_services': $bmItems = !empty($sells_services) ? ($featured_services ?? []) : []; break;
    case 'store_hours': $bmItems = $business_hours ?? []; break;
    case 'trust_badges':
        $bmItems = array_filter((array)json_decode($settings->trust_badges_json ?? '', true), function($item){ return is_array($item) && trim((string)($item['title'] ?? '')) !== ''; });
        break;
    case 'promo_banner':
        $bmItems = $promo_banners ?? [];
        if(!$bmItems && ($bmText('title') !== '' || $bmText('image_url') !== '')) $bmItems = [(object)[]];
        break;
    case 'newsletter': $bmItems = [true]; break;
    case 'whatsapp_cta': $bmItems = !empty($settings->whatsapp_number) && ($settings->show_whatsapp_cta ?? 1) ? [true] : []; break;
}
// Incomplete/inactive content is never replaced with fabricated public examples.
if(!$bmItems) return;
?>
<section class="section bm-component<?= in_array($bmSectionBase, ['newsletter','faqs'], true) ? ' pale' : ''; ?>">
  <div class="section-head">
    <div>
      <?php if($bmText('eyebrow') !== ''): ?><p class="eyebrow"><?= $bmEscape($bmText('eyebrow')); ?></p><?php endif; ?>
      <h2><?= $bmEscape($bmTitle); ?></h2>
    </div>
    <?php if(trim((string)$bmSub) !== ''): ?><p class="support"><?= nl2br($bmEscape($bmSub)); ?></p><?php endif; ?>
  </div>
  <?php if($bmSectionBase === 'faqs'): ?>
  <div class="bm-faq-list">
    <?php foreach($bmItems as $bmFaq): ?>
    <details class="bm-faq"><summary><?= $bmEscape($bmFaq->question); ?><span aria-hidden="true">+</span></summary><div><?= nl2br($bmEscape($bmFaq->answer)); ?></div></details>
    <?php endforeach; ?>
  </div>
  <?php elseif($bmSectionBase === 'newsletter'): ?>
  <form class="bm-newsletter-form" method="post" action="<?= $bmEscape($bm_base.'/subscribe'); ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" data-newsletter-csrf>
    <input type="hidden" name="source" value="building_homepage">
    <div class="bm-signup-row"><label class="sr-only" for="<?= $bmSectionId; ?>-email">Email address</label><input id="<?= $bmSectionId; ?>-email" name="email" type="email" placeholder="Your email address" autocomplete="email" maxlength="255" required><button class="button" type="submit">Subscribe<span aria-hidden="true">↗</span></button></div>
    <label class="bm-newsletter-consent"><input type="checkbox" name="consent" value="1" required> <span>I agree to receive email updates. <a href="<?= $bmEscape($bm_base.'/privacy'); ?>">Privacy policy</a></span></label>
    <p class="bm-newsletter-result" role="status" aria-live="polite"></p>
  </form>
  <?php elseif($bmSectionBase === 'whatsapp_cta'): ?>
  <a class="button" href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" rel="noopener noreferrer">Discuss your requirements<span aria-hidden="true">↗</span></a>
  <?php elseif($bmSectionBase === 'store_hours'): ?>
  <div class="bm-hours"><?php foreach($bmItems as $bmLine): ?><p><?= $bmEscape($bmLine); ?></p><?php endforeach; ?></div>
  <?php else: ?>
  <div class="bm-component-grid<?= $bmSectionBase === 'brands' ? ' bm-partners' : ''; ?>">
  <?php foreach(array_slice(array_values($bmItems), 0, 12) as $bmItem): ?>
    <?php if($bmSectionBase === 'trust_badges'): ?>
    <article class="bm-info-card"><h3><?= $bmEscape($bmItem['title']); ?></h3><p><?= $bmEscape($bmItem['desc'] ?? ''); ?></p></article>
    <?php elseif($bmSectionBase === 'featured_categories'): ?>
    <a class="bm-info-card" href="<?= $bmEscape($bm_products_url.'?category='.(int)$bmItem->id); ?>"><h3><?= $bmEscape($bmItem->category_name); ?></h3><span class="text-link">Explore systems ↗</span></a>
    <?php elseif($bmSectionBase === 'featured_services'): ?>
    <article class="bm-info-card">
      <?php $bmImage = mp_building_image_url($bmItem->service_image ?? ''); if($bmImage !== ''): ?><img class="bm-component-image" src="<?= $bmEscape($bmImage); ?>" alt="" loading="lazy"><?php endif; ?>
      <h3><?= $bmEscape($bmItem->service_name); ?></h3><p><?= $bmEscape(mb_substr(strip_tags($bmItem->description ?? ''), 0, 180)); ?></p>
      <a class="text-link" href="<?= $bmEscape($bm_base.'/service/'.(int)$bmItem->id); ?>">View service ↗</a>
    </article>
    <?php elseif($bmSectionBase === 'brands'): ?>
    <div class="bm-info-card">
      <?php $bmImage = mp_building_image_url($bmItem->brand_logo ?? ''); $bmLink = $bmSafeLink($bmItem->brand_url ?? ''); ?>
      <?php if($bmLink !== ''): ?><a href="<?= $bmEscape($bmLink); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
      <?php if($bmImage !== ''): ?><img src="<?= $bmEscape($bmImage); ?>" alt="<?= $bmEscape($bmItem->brand_name); ?>" loading="lazy"><?php else: ?><h3><?= $bmEscape($bmItem->brand_name); ?></h3><?php endif; ?>
      <?php if($bmLink !== ''): ?></a><?php endif; ?>
    </div>
    <?php elseif($bmSectionBase === 'testimonials'): ?>
    <figure class="bm-info-card bm-review"><blockquote><?= $bmEscape($bmItem->testimonial_text); ?></blockquote><figcaption><?= $bmEscape($bmItem->customer_name); ?><span aria-label="<?= max(0,min(5,(int)$bmItem->rating)); ?> out of 5 stars"><?= str_repeat('★',max(0,min(5,(int)$bmItem->rating))); ?></span></figcaption></figure>
    <?php elseif($bmSectionBase === 'instagram_gallery'): ?>
    <?php $bmImage = mp_building_image_url($bmItem->image_url ?? ''); $bmLink = $bmSafeLink($bmItem->link_url ?? ''); if($bmImage === '') continue; ?>
    <figure class="bm-gallery-card"><?php if($bmLink !== ''): ?><a href="<?= $bmEscape($bmLink); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?><img src="<?= $bmEscape($bmImage); ?>" alt="<?= $bmEscape($bmItem->caption ?? ''); ?>" loading="lazy"><?php if($bmLink !== ''): ?></a><?php endif; ?><?php if(!empty($bmItem->caption)): ?><figcaption><?= $bmEscape($bmItem->caption); ?></figcaption><?php endif; ?></figure>
    <?php elseif($bmSectionBase === 'promo_banner'): ?>
    <?php $bmImage = mp_building_image_url($bmText('image_url', $bmItem->desktop_image ?? ($bmItem->mobile_image ?? ''))); ?>
    <article class="bm-info-card"><?php if($bmImage !== ''): ?><img class="bm-component-image" src="<?= $bmEscape($bmImage); ?>" alt="" loading="lazy"><?php endif; ?><h3><?= $bmEscape($bmItem->banner_title ?? $bmTitle); ?></h3><p><?= $bmEscape($bmItem->banner_subtitle ?? $bmSub); ?></p><a class="text-link" href="<?= $bmEscape($bmSafeLink($bmItem->button_url ?? '', $bm_contact_url)); ?>"><?= $bmEscape($bmItem->button_text ?? 'Discuss a project'); ?> ↗</a></article>
    <?php endif; ?>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
