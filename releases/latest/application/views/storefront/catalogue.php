<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($seo_title ?? 'Catalogue'); ?> | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <meta name="description" content="<?= htmlspecialchars($seo_description ?? ''); ?>">
  <link rel="canonical" href="<?= $seo_canonical ?? ''; ?>">
  <?php if(!empty($favicon_url)): ?><link rel="icon" href="<?= $favicon_url; ?>"><?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary:#3B82F6; --dark:#0F172A; --gray:#64748B; --light:#F1F5F9;
      --border:#E2E8F0; --white:#fff; --green:#10B981; --radius:10px;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    /* Header */
    .cat-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .cat-header-inner { max-width:640px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .cat-back { font-size:22px; color:var(--gray); }
    .cat-header-title { font-size:17px; font-weight:700; flex:1; }
    .cat-logo { height:32px; width:32px; border-radius:6px; object-fit:cover; }

    /* Search */
    .cat-search-wrap { padding:10px 16px; background:var(--white); border-bottom:1px solid var(--border); }
    .cat-search { max-width:640px; margin:0 auto; position:relative; }
    .cat-search input { width:100%; padding:11px 14px 11px 40px; border:none; border-radius:var(--radius); font-size:14px; background:var(--light); outline:none; transition:box-shadow .2s; }
    .cat-search input:focus { box-shadow:0 0 0 2px rgba(59,130,246,0.15); }
    .cat-search .fa { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--gray); font-size:14px; }

    /* Category pills */
    .cat-pills { max-width:640px; margin:0 auto; padding:10px 16px; display:flex; gap:8px; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
    .cat-pills::-webkit-scrollbar { display:none; }
    .cat-pill { padding:7px 16px; border-radius:20px; font-size:13px; font-weight:600; white-space:nowrap; background:var(--white); border:1px solid var(--border); color:var(--gray); transition:all .15s; }
    .cat-pill.active { background:var(--primary); color:#fff; border-color:var(--primary); }
    .cat-pill:hover { border-color:var(--primary); }

    /* Section */
    .cat-section { max-width:640px; margin:0 auto; padding:16px; }

    /* Section label */
    .cat-section-label { font-size:14px; font-weight:700; color:var(--dark); margin-bottom:12px; display:flex; align-items:center; gap:8px; }
    .cat-section-label .count { font-size:12px; font-weight:500; color:var(--gray); background:var(--light); padding:2px 8px; border-radius:10px; }

    /* Product grid */
    .cat-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; margin-bottom:24px; }
    .cat-card { background:var(--white); border-radius:var(--radius); overflow:hidden; border:1px solid var(--border); transition:transform .15s, box-shadow .15s; }
    .cat-card:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,0.08); }
    .cat-card-img { width:100%; height:140px; object-fit:cover; background:var(--light); }
    .cat-card-noimg { width:100%; height:140px; display:flex; align-items:center; justify-content:center; background:var(--light); color:#94A3B8; font-size:28px; }
    .cat-card-body { padding:10px 12px; }
    .cat-card-cat { font-size:11px; font-weight:600; color:var(--gray); text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px; }
    .cat-card-name { font-size:13px; font-weight:600; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:6px; }
    .cat-card-price { font-size:15px; font-weight:700; color:var(--primary); }
    .cat-card-old { font-size:12px; color:var(--gray); text-decoration:line-through; margin-left:4px; }
    .cat-card-wa { display:flex; align-items:center; justify-content:center; gap:6px; width:100%; margin-top:8px; padding:7px; border:none; border-radius:7px; background:#25D366; color:#fff; font-size:12px; font-weight:600; cursor:pointer; transition:opacity .15s; }
    .cat-card-wa:hover { opacity:0.88; }

    /* Service card */
    .cat-service-card { background:var(--white); border-radius:var(--radius); padding:14px; border:1px solid var(--border); display:flex; align-items:center; gap:12px; margin-bottom:10px; transition:transform .15s, box-shadow .15s; }
    .cat-service-card:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.06); }
    .cat-service-icon { width:44px; height:44px; border-radius:10px; background:var(--light); display:flex; align-items:center; justify-content:center; font-size:18px; color:var(--primary); flex-shrink:0; }
    .cat-service-info { flex:1; min-width:0; }
    .cat-service-name { font-size:14px; font-weight:600; margin-bottom:2px; }
    .cat-service-price { font-size:14px; font-weight:700; color:var(--primary); }

    /* Pagination */
    .cat-pagination { display:flex; justify-content:center; gap:8px; margin-top:20px; margin-bottom:32px; }
    .cat-page { padding:7px 14px; border-radius:8px; border:1px solid var(--border); background:var(--white); font-size:13px; font-weight:600; color:var(--gray); }
    .cat-page.active { background:var(--primary); color:#fff; border-color:var(--primary); }

    /* Empty state */
    .cat-empty { text-align:center; padding:48px 20px; color:var(--gray); }
    .cat-empty i { font-size:48px; color:#CBD5E1; margin-bottom:12px; display:block; }
    .cat-empty p { font-size:14px; }

    /* WhatsApp floating button */
    .cat-wa-float { position:fixed; bottom:20px; right:20px; width:52px; height:52px; border-radius:50%; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; font-size:24px; box-shadow:0 4px 16px rgba(37,211,102,0.4); z-index:200; transition:transform .15s; }
    .cat-wa-float:hover { transform:scale(1.08); }

    /* Footer */
    .cat-footer { text-align:center; padding:20px 16px; font-size:12px; color:var(--gray); border-top:1px solid var(--border); background:var(--white); }

    @media(min-width:768px){ .cat-grid { grid-template-columns:repeat(3, 1fr); } }
  </style>
</head>
<body>

<div class="cat-header">
  <div class="cat-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="cat-back">&larr;</a>
    <?php if(!empty($logo_url)): ?>
      <img src="<?= $logo_url; ?>" class="cat-logo" alt="">
    <?php endif; ?>
    <div class="cat-header-title">Catalogue</div>
  </div>
</div>

<div class="cat-search-wrap">
  <div class="cat-search">
    <i class="fa fa-search"></i>
    <input type="text" id="search-input" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search catalogue..." onkeydown="if(event.key==='Enter')doSearch()">
  </div>
</div>

<div class="cat-pills">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/catalogue'); ?>" class="cat-pill <?= !$category_id ? 'active' : ''; ?>">All</a>
  <?php foreach($categories as $cat): ?>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/catalogue'); ?>?category=<?= $cat->id; ?>" class="cat-pill <?= $category_id == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
  <?php endforeach; ?>
</div>

<div class="cat-section">
  <?php if($show_products && !empty($products)): ?>
  <div class="cat-section-label">
    Products <span class="count"><?= $total; ?> items</span>
  </div>
  <div class="cat-grid">
    <?php foreach($products as $p):
      $price = $p->effective_price ?? $p->sales_price;
      $oldPrice = $p->original_price ?? $p->sales_price;
      $hasDiscount = $oldPrice > $price;
      $imgPath = $p->item_image && file_exists($p->item_image) ? base_url($p->item_image) : '';
      $waText = "Hello " . ($store->store_name ?? '') . ", I'm interested in: " . $p->item_name . " (" . store_number_format($price) . ")";
      $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) . "?text=" . urlencode($waText);
    ?>
    <div class="cat-card">
      <?php if($imgPath): ?>
        <img src="<?= $imgPath; ?>" class="cat-card-img" alt="" loading="lazy">
      <?php else: ?>
        <div class="cat-card-noimg"><i class="fa fa-image"></i></div>
      <?php endif; ?>
      <div class="cat-card-body">
        <?php if(!empty($p->category_name)): ?><div class="cat-card-cat"><?= htmlspecialchars($p->category_name); ?></div><?php endif; ?>
        <div class="cat-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div>
          <span class="cat-card-price"><?= store_number_format($price); ?></span>
          <?php if($hasDiscount): ?><span class="cat-card-old"><?= store_number_format($oldPrice); ?></span><?php endif; ?>
        </div>
        <?php if($whatsapp_number): ?>
        <a href="<?= $waLink; ?>" target="_blank" class="cat-card-wa"><i class="fa fa-whatsapp"></i> Enquire</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if($total_pages > 1): ?>
  <div class="cat-pagination">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/catalogue'); ?>?page=<?= $i; ?><?= $category_id ? '&category=' . $category_id : ''; ?><?= $search ? '&search=' . urlencode($search) : ''; ?>" class="cat-page <?= $page == $i ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php elseif($show_products && empty($products) && empty($services)): ?>
  <div class="cat-empty">
    <i class="fa fa-box-open"></i>
    <p>No items found in the catalogue.</p>
  </div>
  <?php endif; ?>

  <?php if($show_services && !empty($services)): ?>
  <div class="cat-section-label">
    Services <span class="count"><?= count($services); ?> items</span>
  </div>
  <?php foreach($services as $s):
    $sPrice = $s->effective_price ?? $s->price;
    $waText = "Hello " . ($store->store_name ?? '') . ", I'm interested in: " . $s->item_name . " (" . store_number_format($sPrice) . ")";
    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) . "?text=" . urlencode($waText);
  ?>
  <div class="cat-service-card">
    <div class="cat-service-icon"><i class="fa fa-scissors"></i></div>
    <div class="cat-service-info">
      <div class="cat-service-name"><?= htmlspecialchars($s->item_name); ?></div>
      <div class="cat-service-price"><?= store_number_format($sPrice); ?></div>
    </div>
    <?php if($whatsapp_number): ?>
    <a href="<?= $waLink; ?>" target="_blank" class="cat-card-wa" style="width:auto;padding:7px 14px;"><i class="fa fa-whatsapp"></i> Enquire</a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if($whatsapp_number): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp_number); ?>?text=<?= urlencode('Hello ' . ($store->store_name ?? '') . ', I have a question about your products.'); ?>" target="_blank" class="cat-wa-float" title="Chat with us">
  <i class="fa fa-whatsapp"></i>
</a>
<?php endif; ?>

<div class="cat-footer">
  <?= htmlspecialchars($store->store_name ?? 'Store'); ?>
</div>

<script>
  function doSearch(){
    var q = document.getElementById('search-input').value.trim();
    var url = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/catalogue'); ?>';
    if(q) url += '?search=' + encodeURIComponent(q);
    window.location.href = url;
  }
</script>

</body>
</html>
