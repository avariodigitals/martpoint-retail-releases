<style>
.mp-wishlist-page{width:min(1180px,100% - 40px);margin:48px auto 80px;color:var(--mp-text,#171717)}
.mp-wishlist-page h1{margin:0 0 10px;font-size:clamp(30px,4vw,46px);line-height:1.1}
.mp-wishlist-intro{margin:0 0 30px;color:#737373}
.mp-wishlist-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(220px,100%),1fr));gap:20px}
.mp-wishlist-card{overflow:hidden;border:1px solid rgba(0,0,0,.10);border-radius:14px;background:#fff}
.mp-wishlist-card a{display:block;color:inherit;text-decoration:none}
.mp-wishlist-card img{display:block;width:100%;aspect-ratio:4/5;object-fit:cover;background:#f5f5f4}
.mp-wishlist-card h2{margin:14px 14px 6px;font-size:16px;line-height:1.4}.mp-wishlist-card p{margin:0 14px 16px;color:#737373;font-size:14px}
.mp-wishlist-card .mp-wishlist-heart{position:static;margin:0 14px 14px;width:auto;height:auto;padding:8px 12px;border-radius:999px;font-size:13px}
.mp-wishlist-empty{padding:44px 24px;border:1px dashed #cfcfcf;border-radius:14px;text-align:center}.mp-wishlist-empty a{display:inline-flex;margin-top:12px;color:var(--mp-primary,#111);font-weight:700}
@media(max-width:600px){.mp-wishlist-page{width:calc(100% - 28px);margin:30px auto 56px}.mp-wishlist-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.mp-wishlist-card{border-radius:10px}.mp-wishlist-card h2{margin:10px 10px 4px;font-size:14px}.mp-wishlist-card p{margin:0 10px 12px;font-size:13px}.mp-wishlist-card .mp-wishlist-heart{margin:0 10px 10px}}
</style>
<main class="mp-wishlist-page">
  <h1>Your wishlist</h1>
  <p class="mp-wishlist-intro">Saved products are linked to your <?= htmlspecialchars($store->store_name ?? 'store'); ?> account.</p>
  <?php if(empty($wishlist_products)): ?>
    <div class="mp-wishlist-empty"><p>Your wishlist is empty for now.</p><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">Browse products</a></div>
  <?php else: ?>
    <div class="mp-wishlist-grid">
      <?php foreach($wishlist_products as $item): $price = (float)($item->effective_price ?? 0); $currencyCode = (string)(($store_currency['code'] ?? '') ?: 'NGN'); ?>
        <article class="mp-wishlist-card">
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . (int)$item->id); ?>">
            <?php if(!empty($item->item_image)): ?><img src="<?= base_url($item->item_image); ?>" alt="<?= htmlspecialchars($item->item_name); ?>" loading="lazy"><?php endif; ?>
            <h2><?= htmlspecialchars($item->item_name); ?></h2>
            <p><?= htmlspecialchars($currencyCode . ' ' . number_format($price, 2)); ?></p>
          </a>
          <button type="button" class="mp-wishlist-heart" data-mp-wishlist="<?= (int)$item->id; ?>" aria-label="Remove from wishlist" aria-pressed="true">♥ Remove</button>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
