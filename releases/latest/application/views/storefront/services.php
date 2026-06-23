<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Services | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
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
    .sf-search { max-width:600px; margin:0 auto; }
    .sf-search input { width:100%; padding:10px 14px; border:none; border-radius:var(--radius-sm); font-size:14px; background:var(--light-gray); outline:none; }
    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-service-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:14px; display:flex; gap:14px; align-items:center; margin-bottom:10px; }
    .sf-service-img { width:70px; height:70px; border-radius:8px; object-fit:cover; background:var(--light-gray); flex-shrink:0; }
    .sf-service-info { flex:1; min-width:0; }
    .sf-service-name { font-size:15px; font-weight:600; margin-bottom:4px; }
    .sf-service-meta { font-size:12px; color:var(--gray); margin-bottom:4px; }
    .sf-service-price { font-size:16px; font-weight:700; color:var(--primary); }
    .sf-empty { text-align:center; padding:40px; color:var(--gray); }
    .sf-pagination { display:flex; justify-content:center; gap:8px; margin-top:20px; }
    .sf-page { padding:6px 12px; border-radius:6px; border:1px solid var(--border); background:var(--white); font-size:13px; }
    .sf-page.active { background:var(--primary); color:#fff; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">Services</div>
  </div>
</div>

<div class="sf-search-wrap">
  <div class="sf-search">
    <input type="text" id="search-input" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search services..." onkeydown="if(event.key==='Enter')doSearch()">
  </div>
</div>

<div class="sf-section">
  <?php if(!empty($services)): ?>
    <?php foreach($services as $s): 
      $price = $s->effective_price ?? $s->price;
    ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="sf-service-card">
      <?php if($s->service_image && file_exists($s->service_image)): ?>
        <img src="<?= base_url($s->service_image); ?>" class="sf-service-img" alt="">
      <?php else: ?>
        <div class="sf-service-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
      <?php endif; ?>
      <div class="sf-service-info">
        <div class="sf-service-name"><?= htmlspecialchars($s->service_name); ?></div>
        <div class="sf-service-meta"><?= htmlspecialchars($s->service_duration ?: ''); ?> &middot; <?= ucfirst(str_replace('-',' ',$s->location_type)); ?></div>
        <div class="sf-service-price"><?= store_number_format($price); ?></div>
      </div>
    </a>
    <?php endforeach; ?>

    <?php if($total_pages > 1): ?>
    <div class="sf-pagination">
      <?php for($i = 1; $i <= $total_pages; $i++): ?>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>?page=<?= $i; ?><?= $search ? '&search=' . urlencode($search) : ''; ?>" class="sf-page <?= $page == $i ? 'active' : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="sf-empty">No services found.</div>
  <?php endif; ?>
</div>

<script>
  function doSearch(){
    var q = document.getElementById('search-input').value.trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>?search=' + encodeURIComponent(q);
  }
</script>

</body>
</html>
