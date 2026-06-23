<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Products | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }
    .sf-search-wrap { padding:10px 16px; background:var(--white); border-bottom:1px solid var(--border); }
    .sf-search { max-width:600px; margin:0 auto; position:relative; }
    .sf-search input { width:100%; padding:10px 14px 10px 40px; border:none; border-radius:var(--radius-sm); font-size:14px; background:var(--light-gray); outline:none; }
    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .sf-card { background:var(--white); border-radius:var(--radius-sm); overflow:hidden; border:1px solid var(--border); }
    .sf-card-img { width:100%; height:140px; object-fit:cover; background:var(--light-gray); }
    .sf-card-body { padding:10px; }
    .sf-card-name { font-size:13px; font-weight:600; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:6px; }
    .sf-card-price { font-size:15px; font-weight:700; color:var(--primary); }
    .sf-card-old { font-size:12px; color:var(--gray); text-decoration:line-through; margin-left:4px; }
    .sf-card-add { width:100%; margin-top:8px; padding:8px; border:none; border-radius:6px; background:var(--primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; }
    .sf-pagination { display:flex; justify-content:center; gap:8px; margin-top:20px; }
    .sf-page { padding:6px 12px; border-radius:6px; border:1px solid var(--border); background:var(--white); font-size:13px; }
    .sf-page.active { background:var(--primary); color:#fff; border-color:var(--primary); }
    .sf-empty { text-align:center; padding:40px; color:var(--gray); }
    .sf-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--dark); color:#fff; padding:12px 20px; border-radius:var(--radius-sm); font-size:14px; z-index:300; opacity:0; transition:all .3s ease; }
    .sf-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }
    @media(min-width:768px){ .sf-grid { grid-template-columns:repeat(3, 1fr); } }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">Products</div>
  </div>
</div>

<div class="sf-search-wrap">
  <div class="sf-search">
    <input type="text" id="search-input" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products..." onkeydown="if(event.key==='Enter')doSearch()">
  </div>
</div>

<div class="sf-section">
  <?php if(!empty($products)): ?>
  <div class="sf-grid">
    <?php foreach($products as $p): 
      $price = $p->effective_price ?? $p->sales_price;
      $oldPrice = $p->original_price ?? $p->sales_price;
      $hasDiscount = $oldPrice > $price;
    ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="sf-card" style="display:block;">
      <?php if($p->item_image && file_exists($p->item_image)): ?>
        <img src="<?= base_url($p->item_image); ?>" class="sf-card-img" alt="">
      <?php else: ?>
        <div class="sf-card-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:12px;">No Image</div>
      <?php endif; ?>
      <div class="sf-card-body">
        <div class="sf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div>
          <span class="sf-card-price"><?= store_number_format($price); ?></span>
          <?php if($hasDiscount): ?><span class="sf-card-old"><?= store_number_format($oldPrice); ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if($total_pages > 1): ?>
  <div class="sf-pagination">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?page=<?= $i; ?><?= $category_id ? '&category=' . $category_id : ''; ?><?= $search ? '&search=' . urlencode($search) : ''; ?>" class="sf-page <?= $page == $i ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
    <div class="sf-empty">No products found.</div>
  <?php endif; ?>
</div>

<div class="sf-toast" id="toast"></div>
<script>
  function doSearch(){
    var q = document.getElementById('search-input').value.trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?search=' + encodeURIComponent(q);
  }
</script>

</body>
</html>
