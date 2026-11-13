<?php
$slug = (string)($settings->store_slug ?? '');
$content = $maison_forme_content ?? [];
$products = !empty($featured_products) ? $featured_products : ($all_products ?? []);
$products = array_slice((array)$products, 0, 8);
$currency = $store_currency ?? null;
$heroImage = (string)($content['hero_image'] ?? '');
$editorialImage = (string)($content['editorial_image'] ?? '');
$storyImage = (string)($content['story_image'] ?? '');
$heroTitle = (string)($content['hero_title'] ?? 'A wardrobe\\nof your own.');
$collectionTitle = (string)($content['featured_heading'] ?? 'Good pieces. Great possibilities.');
$statementText = (string)($content['statement_text'] ?? "Personal style isn't about having more. It's about finding the pieces that feel unmistakably you.");
$storyTitle = (string)($content['story_heading'] ?? 'Find your own rhythm.');
$nl = function($value){ return nl2br(htmlspecialchars(str_replace('\\n', "\n", (string)$value))); };
?>
<main id="main-content">
  <section class="hero" aria-label="The new collection">
    <div class="hero-copy">
      <div><span class="eyebrow"><?= $nl($content['hero_kicker'] ?? 'The everyday, reimagined / 01'); ?></span><h1><?php if($heroTitle === 'A wardrobe\\nof your own.'): ?>A wardrobe<br>of <em>your</em><br>own.<?php else: ?><?= $nl($heroTitle); ?><?php endif; ?></h1></div>
      <div><p><?= $nl($content['hero_text'] ?? 'For the way you move. The places you go. The person you are becoming.'); ?></p><a href="<?= base_url('store/' . rawurlencode($slug) . '/products'); ?>" class="text-link"><?= htmlspecialchars($content['hero_cta'] ?? 'EXPLORE THE COLLECTION'); ?><span aria-hidden="true">↗</span></a><div class="edition">FORM, FEELING &amp; A LITTLE FREEDOM.</div></div>
    </div>
    <div class="hero-main"><img src="<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($content['hero_photo_label'] ?? 'Maison Forme collection'); ?>" fetchpriority="high"><span class="photo-label"><?= htmlspecialchars($content['hero_photo_label'] ?? 'IN THE CITY. IN YOUR ELEMENT.'); ?></span></div>
    <div class="hero-aside"><div class="aside-top"><span class="eyebrow"><?= $nl($content['hero_feature_kicker'] ?? 'The style notes'); ?></span><span class="issue">01</span></div><figure><img src="<?= htmlspecialchars($editorialImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($content['hero_feature_title'] ?? 'Editorial fashion'); ?>"></figure><div class="aside-bottom"><h2><?= $nl($content['hero_feature_title'] ?? 'A little less expected.'); ?></h2><a href="#journal" class="text-link"><?= htmlspecialchars($content['hero_feature_cta'] ?? 'READ THE EDITORIAL'); ?><span aria-hidden="true">↗</span></a></div></div>
  </section>
  <div class="ticker"><span><?= htmlspecialchars($content['ticker_left'] ?? 'Expressive shapes. Everyday ease.'); ?></span><span><?= htmlspecialchars($content['ticker_center'] ?? 'A new perspective on getting dressed.'); ?></span><span><?= htmlspecialchars($content['ticker_right'] ?? 'Explore the first edit /'); ?></span></div>

  <section class="collection" id="collection">
    <div class="section-top"><div><p class="eyebrow"><?= htmlspecialchars($content['collection_kicker'] ?? 'THE CURRENT ROTATION'); ?></p><h2><?php if($collectionTitle === 'Good pieces. Great possibilities.'): ?>Good pieces. <em>Great possibilities.</em><?php else: ?><?= htmlspecialchars($collectionTitle); ?><?php endif; ?></h2></div><a class="text-link" href="<?= base_url('store/' . rawurlencode($slug) . '/products'); ?>"><?= htmlspecialchars($content['collection_link'] ?? 'VIEW ALL'); ?><span aria-hidden="true">↗</span></a></div>
    <div class="filters"><div class="tabs" role="group" aria-label="Product categories"><a class="tab active" href="<?= base_url('store/' . rawurlencode($slug) . '/products'); ?>">All pieces</a><?php foreach(array_slice((array)($categories ?? []),0,2) as $category): ?><a class="tab" href="<?= base_url('store/' . rawurlencode($slug) . '/products?category=' . (int)$category->id); ?>"><?= htmlspecialchars($category->category_name); ?></a><?php endforeach; ?></div><a class="sort" href="<?= base_url('store/' . rawurlencode($slug) . '/products'); ?>">Browse the collection ↗</a></div>
    <div class="product-grid">
      <?php foreach($products as $product):
        if(!is_object($product)) continue;
        $price = $product->effective_price ?? $product->sales_price ?? 0;
        $image = !empty($product->item_image) && (function_exists('sf_product_image_exists') ? sf_product_image_exists($product->item_image) : file_exists($product->item_image)) ? mp_minified_image_url($product->item_image, 700) : '';
        $productUrl = base_url('store/' . rawurlencode($slug) . '/product/' . (int)$product->id);
        $tag = !empty($product->category_name) ? $product->category_name : 'THE COLLECTION';
      ?>
      <article class="product-card"><div class="product-image"><a href="<?= $productUrl; ?>" class="image-open" aria-label="View <?= htmlspecialchars($product->item_name); ?>"><?php if($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($product->item_name); ?>" loading="lazy"><?php else: ?><div class="brand-fallback"><?= htmlspecialchars($product->display_brand ?? ($store->store_name ?? 'Store')); ?></div><?php endif; ?></a><span class="tag"><?= htmlspecialchars($tag); ?></span><a class="quick-add" href="<?= $productUrl; ?>">VIEW THIS PIECE +</a></div><div class="product-info"><div><a class="product-title" href="<?= $productUrl; ?>"><?= htmlspecialchars($product->item_name); ?></a><p class="product-meta"><?= htmlspecialchars($product->category_name ?? ''); ?></p></div><span class="price"><?= sf_currency($price, $currency); ?></span></div></article>
      <?php endforeach; ?>
      <?php if(!$products): ?><div class="empty-results">Your first pieces will appear here when you add products to the Online Store.</div><?php endif; ?>
    </div>
    <div class="collection-bottom"><span><?= sprintf('%02d', count($products)); ?> PIECES / THE FIRST EDIT</span><span><?= htmlspecialchars($content['collection_note'] ?? 'A FEW FAVOURITES, CHOSEN WITH INTENTION.'); ?></span></div>
  </section>

  <section class="statement" id="philosophy"><p class="eyebrow"><?= htmlspecialchars($content['statement_kicker'] ?? 'OUR POINT OF VIEW'); ?></p><h2><?php if($statementText === "Personal style isn't about having more. It's about finding the pieces that feel unmistakably you."): ?>Personal style isn't about having more.<br>It's about finding the pieces that feel<br><em>unmistakably you.</em><?php else: ?><?= nl2br(htmlspecialchars($statementText)); ?><?php endif; ?></h2></section>
  <section class="story" id="journal"><div class="story-image"><img src="<?= htmlspecialchars($storyImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($storyTitle); ?>" loading="lazy"><span class="photo-label"><?= htmlspecialchars($content['story_photo_label'] ?? 'THE EDITORIAL / STYLE NOTE 01'); ?></span></div><div class="story-copy"><div class="eyebrow"><?= htmlspecialchars($content['story_kicker'] ?? 'A STUDY IN CONTRAST'); ?></div><div><h2><?php if($storyTitle === 'Find your own rhythm.'): ?>Find your<br><em>own rhythm.</em><?php else: ?><?= nl2br(htmlspecialchars($storyTitle)); ?><?php endif; ?></h2><p><?= htmlspecialchars($content['story_text'] ?? "A generous layer. A sharper line. A colour you didn't know you needed. Start with one good piece and see where the day takes you."); ?></p><a href="<?= base_url('store/' . rawurlencode($slug) . '/about'); ?>" class="text-link"><?= htmlspecialchars($content['story_cta'] ?? 'INSIDE THE EDIT'); ?><span aria-hidden="true">↗</span></a></div><span class="eyebrow" style="margin-top:38px;color:#6b6d60"><?= htmlspecialchars($content['story_signoff'] ?? 'Wear it your way.'); ?></span></div></section>
  <section class="services" aria-label="Shopping services"><div class="service"><span class="service-num">01</span><div><h3><?= htmlspecialchars($content['service_1_title'] ?? 'A closer look'); ?></h3><p><?= htmlspecialchars($content['service_1_text'] ?? 'Explore every piece, its fit and its details.'); ?></p></div></div><div class="service"><span class="service-num">02</span><div><h3><?= htmlspecialchars($content['service_2_title'] ?? 'Find your fit'); ?></h3><p><?= htmlspecialchars($content['service_2_text'] ?? 'Measurements to help you choose with confidence.'); ?></p></div></div><div class="service"><span class="service-num">03</span><div><h3><?= htmlspecialchars($content['service_3_title'] ?? 'Make it yours'); ?></h3><p><?= htmlspecialchars($content['service_3_text'] ?? 'Save your favourites. Build your own edit.'); ?></p></div></div></section>
</main>
