<?php $this->load->view('creator/_styles'); $CI =& get_instance();
$typeMeta = [
  'digital'    => ['label' => 'Digital Product', 'icon' => 'fa-download'],
  'course'     => ['label' => 'Course', 'icon' => 'fa-play-circle'],
  'membership' => ['label' => 'Membership', 'icon' => 'fa-star'],
];
$addType = $type ?: 'digital';
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= count($items); ?> published &middot; everything here is live on your storefront</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= base_url('creator/create/' . $addType); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New <?= $typeMeta[$addType]['label']; ?></a>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="cr-tabs">
  <a href="<?= base_url('creator/products'); ?>" class="<?= $type === '' ? 'active' : ''; ?>">All</a>
  <?php if(mp_feature_enabled('digital_products')): ?><a href="<?= base_url('creator/products/digital'); ?>" class="<?= $type === 'digital' ? 'active' : ''; ?>"><i class="fa fa-download"></i> Digital Products</a><?php endif; ?>
  <?php if(mp_feature_enabled('courses')): ?><a href="<?= base_url('creator/products/course'); ?>" class="<?= $type === 'course' ? 'active' : ''; ?>"><i class="fa fa-play-circle"></i> Courses</a><?php endif; ?>
  <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('creator/products/membership'); ?>" class="<?= $type === 'membership' ? 'active' : ''; ?>"><i class="fa fa-star"></i> Memberships</a><?php endif; ?>
</div>

<?php if(empty($items)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa <?= $typeMeta[$addType]['icon']; ?>"></i></div>
    <h3>No <?= strtolower($page_title); ?> yet</h3>
    <p>Create your first <?= strtolower($typeMeta[$addType]['label']); ?>. It will appear on your storefront as soon as it is published.</p>
    <a href="<?= base_url('creator/create/' . $addType); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> Create <?= $typeMeta[$addType]['label']; ?></a>
  </div>
<?php else: ?>
<div class="cr-products">
  <?php foreach($items as $it):
    $pt = $it->product_type ?: 'digital';
    $s = $sales[$it->id] ?? null;
    $course = $courses[$it->id] ?? null;
    $membership = $memberships[$it->id] ?? null;
    $img = (!empty($it->item_image) && file_exists($it->item_image)) ? $it->item_image : '';
  ?>
  <div class="cr-product">
    <div class="cr-product-media">
      <?php if($img): ?>
        <img src="<?= function_exists('mp_minified_image_url') ? mp_minified_image_url($img, 600) : base_url($img); ?>" alt="<?= htmlspecialchars($it->item_name); ?>" loading="lazy" decoding="async">
      <?php else: ?>
        <i class="fa <?= $typeMeta[$pt]['icon'] ?? 'fa-cube'; ?>"></i>
      <?php endif; ?>
      <span class="cr-pill <?= $pt; ?>"><?= $typeMeta[$pt]['label'] ?? ucfirst($pt); ?></span>
    </div>
    <div class="cr-product-body">
      <h4 class="cr-product-name"><?= htmlspecialchars($it->item_name); ?></h4>
      <div class="cr-product-meta">
        <?= htmlspecialchars($it->category_name ?? 'Uncategorised'); ?>
        <?php if($pt === 'membership' && $membership): ?> &middot; billed <?= htmlspecialchars($membership->billing_interval); ?><?php if((int)$membership->trial_days > 0): ?> &middot; <?= (int)$membership->trial_days; ?>-day trial<?php endif; ?><?php endif; ?>
        <?php if($pt === 'digital'): ?> &middot; <?= !empty($it->digital_file) ? 'file attached' : '<span style="color:var(--mp-warning);font-weight:600;">no file uploaded</span>'; ?><?php endif; ?>
      </div>
      <div class="cr-product-price"><?= $CI->currency($it->sales_price); ?></div>
      <div class="cr-product-stats">
        <span><strong><?= $s ? number_format($s->qty) : 0; ?></strong> sold</span>
        <span><strong><?= $CI->currency($s ? $s->revenue : 0); ?></strong> earned</span>
      </div>
      <div class="cr-product-actions">
        <a href="<?= base_url('creator/edit/' . $it->id); ?>">Edit</a>
        <?php if($pt === 'course'): ?>
          <?php if($course): ?>
            <a href="<?= base_url('courses/edit/' . $course->id); ?>" class="primary"><i class="fa fa-list-ul"></i> Curriculum</a>
          <?php else: ?>
            <a href="<?= base_url('courses/add?item_id=' . $it->id); ?>" class="warn"><i class="fa fa-exclamation-circle"></i> Build curriculum</a>
          <?php endif; ?>
        <?php elseif($pt === 'membership'): ?>
          <?php if($membership): ?>
            <a href="<?= base_url('memberships/edit/' . $membership->id); ?>" class="primary"><i class="fa fa-refresh"></i> Billing</a>
          <?php else: ?>
            <a href="<?= base_url('memberships/add?item_id=' . $it->id); ?>" class="warn"><i class="fa fa-exclamation-circle"></i> Set billing</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= base_url('creator/edit/' . $it->id); ?>" class="primary"><i class="fa fa-upload"></i> File &amp; delivery</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
