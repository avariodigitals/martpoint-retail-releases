<?php
if (!function_exists('sf_product_image_exists')) $this->load->helper('storefront_product_image');
$slug = (string)($settings->store_slug ?? '');
$home = base_url('store/' . $slug);
$cur = $store_currency ?? null;
$brand = trim((string)($store->store_name ?? '')) ?: 'Pharmacy';
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroPath = $hero && !empty($hero->desktop_image) ? $hero->desktop_image : ($hero->mobile_image ?? '');
$heroImg = trim((string)$heroPath) !== '' ? (preg_match('~^https?://~i', $heroPath) ? $heroPath : base_url($heroPath)) : '';
$products = !empty($featured_products) ? $featured_products : (!empty($new_arrivals) ? $new_arrivals : ($products ?? []));

function mdq_store_image($path) {
    $path = trim((string)$path);
    if ($path === '') return '';
    return preg_match('~^https?://~i', $path) ? $path : base_url($path);
}

function mdq_store_product_card($product, $currency, $slug) {
    $price = $product->effective_price ?? ($product->sales_price ?? 0);
    $oldPrice = $product->original_price ?? ($product->mrp ?? 0);
    $imagePath = $product->item_image ?? '';
    $image = sf_product_image_exists($imagePath) ? mdq_store_image($imagePath) : '';
    $name = htmlspecialchars((string)($product->item_name ?? 'Medicine'), ENT_QUOTES, 'UTF-8');
    $jsName = htmlspecialchars(json_encode((string)($product->item_name ?? 'Medicine')), ENT_QUOTES, 'UTF-8');
    $jsImage = htmlspecialchars(json_encode((string)$imagePath), ENT_QUOTES, 'UTF-8');
    $discount = ((float)$oldPrice > (float)$price && (float)$oldPrice > 0) ? round((1 - ((float)$price / (float)$oldPrice)) * 100) : 0;
    ?>
    <article class="mdq-product">
      <a href="<?= base_url('store/' . $slug . '/product/' . (int)$product->id); ?>" class="mdq-product-media" aria-label="View <?= $name; ?>">
        <?php if ($discount > 0): ?><span class="mdq-sale-badge"><?= $discount; ?>% Off</span><?php endif; ?>
        <?php if ($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES); ?>" alt="<?= $name; ?>" loading="lazy"><?php else: ?><span class="mdq-product-placeholder" aria-hidden="true"><?= htmlspecialchars((string)($GLOBALS['store']->store_name ?? 'Pharmacy')); ?></span><?php endif; ?>
      </a>
      <div class="mdq-product-body">
        <div class="mdq-product-category"><?= htmlspecialchars((string)($product->category_name ?? 'Health essentials')); ?></div>
        <a class="mdq-product-name" href="<?= base_url('store/' . $slug . '/product/' . (int)$product->id); ?>"><?= $name; ?></a>
        <div class="mdq-product-bottom">
          <span class="mdq-price"><?= sf_currency($price, $currency); ?><?php if ($discount > 0): ?><del><?= sf_currency($oldPrice, $currency); ?></del><?php endif; ?></span>
          <button class="mdq-add" type="button" aria-label="Add <?= $name; ?> to cart" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= (int)$product->id; ?>,'product',<?= $jsName; ?>,<?= json_encode((float)$price); ?>,<?= $jsImage; ?>,1,<?= (int)($product->stock ?? 0); ?>)"><svg class="mdq-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.3 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.5L21 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg><span>Add to Cart</span></button>
        </div>
      </div>
    </article>
    <?php
}
?>
<main class="mdq-page mdq-home-page">
  <section class="mdq-hero">
    <div class="mdq-wrap mdq-hero-grid">
      <div class="mdq-hero-art"<?= $heroImg ? ' style="background-image:linear-gradient(90deg,rgba(167,235,102,.98) 0%,rgba(167,235,102,.68) 48%,rgba(167,235,102,.18) 100%),url(' . htmlspecialchars($heroImg, ENT_QUOTES) . ');background-size:cover;background-position:center"' : ''; ?>>
        <h1 aria-label="Pharmacy">Pharmacy</h1>
        <div class="mdq-hero-copy mdq-hero-strap">Online medicine delivery, pharmacist guidance and everyday care for your household.</div>
        <div class="mdq-hero-actions">
          <a class="mdq-btn mdq-btn-primary" href="<?= $home; ?>/products">Shop medicines <svg class="mdq-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M8 5h11v11"></path></svg></a>
          <a class="mdq-btn mdq-btn-soft" href="<?= $home; ?>/products">Browse health categories</a>
        </div>
        <div class="mdq-card-float">
          <span class="mdq-card-float-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"></path></svg></span>
          <strong>Upload prescription</strong>
          <span>Send it to our pharmacy team and we’ll confirm the right next step.</span>
          <a class="mdq-pill" href="<?= $home; ?>/checkout">Order via prescription <svg class="mdq-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M8 5h11v11"></path></svg></a>
        </div>
      </div>
    </div>
  </section>

  <section class="mdq-trustbar"><div class="mdq-wrap mdq-trustgrid">
    <div class="mdq-trustitem"><span class="mdq-trusticon" aria-hidden="true"><svg class="mdq-inline-icon" viewBox="0 0 24 24"><path d="m12 3 7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"></path><path d="m8.5 12 2.2 2.2 4.8-5"></path></svg></span><span>Genuine medicines</span></div>
    <div class="mdq-trustitem"><span class="mdq-trusticon" aria-hidden="true"><svg class="mdq-inline-icon" viewBox="0 0 24 24"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"></path><circle cx="7" cy="19" r="2"></circle><circle cx="17" cy="19" r="2"></circle></svg></span><span>Delivery to your door</span></div>
    <div class="mdq-trustitem"><span class="mdq-trusticon" aria-hidden="true"><svg class="mdq-inline-icon" viewBox="0 0 24 24"><path d="M12 21a8 8 0 1 0-8-8"></path><path d="M4 13h5V8M12 8v4l3 2"></path></svg></span><span>Pharmacist guidance</span></div>
    <div class="mdq-trustitem"><span class="mdq-trusticon" aria-hidden="true"><svg class="mdq-inline-icon" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Z"></path><circle cx="12" cy="9" r="2.3"></circle></svg></span><span><?= htmlspecialchars($brand); ?> locations</span></div>
  </div></section>

  <?php if (!empty($categories)): ?><section class="mdq-section mdq-category-section"><div class="mdq-wrap">
    <div class="mdq-section-head"><div><div class="mdq-kicker">Browse care by need</div><h2 class="mdq-title">Health categories</h2></div><a class="mdq-link" href="<?= $home; ?>/products">Browse all →</a></div>
    <div class="mdq-categories">
      <?php foreach (array_slice($categories, 0, 6) as $category): ?>
        <?php $categoryImage = !empty($category->category_image) ? mdq_store_image($category->category_image) : (!empty($category->image) ? mdq_store_image($category->image) : ''); ?>
        <a class="mdq-category" href="<?= $home; ?>/products?category=<?= (int)$category->id; ?>"<?= $categoryImage ? ' style="background-image:linear-gradient(0deg,rgba(255,255,255,.68),rgba(255,255,255,0) 70%),url(' . htmlspecialchars($categoryImage, ENT_QUOTES) . ');background-size:cover;background-position:center"' : ''; ?>><span><strong><?= htmlspecialchars((string)$category->category_name); ?></strong><?php if (!empty($category->item_count)): ?><small><?= (int)$category->item_count; ?> products</small><?php endif; ?></span></a>
      <?php endforeach; ?>
    </div>
  </div></section><?php endif; ?>

  <?php if (!empty($products)): ?><section class="mdq-section mdq-deals-section"><div class="mdq-wrap">
    <div class="mdq-section-head"><div><div class="mdq-kicker">Carefully selected for you</div><h2 class="mdq-title">Today’s best deals<br>for you!</h2></div><a class="mdq-link" href="<?= $home; ?>/products">See all products →</a></div>
    <div class="mdq-products"><?php foreach (array_slice($products, 0, 8) as $product): mdq_store_product_card($product, $cur, $slug); endforeach; ?></div>
  </div></section><?php endif; ?>

  <section class="mdq-section mdq-care-section"><div class="mdq-wrap"><div class="mdq-care">
    <div><div class="mdq-kicker">Care beyond the basket</div><h2>Clear answers for healthier days.</h2><p>For prescription questions, dosage guidance or help choosing a product, speak with the pharmacy team before you order.</p></div>
    <a class="mdq-btn" href="<?= $home; ?>/contact">Talk to a pharmacist <svg class="mdq-inline-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M8 5h11v11"></path></svg></a>
  </div></div></section>
</main>
