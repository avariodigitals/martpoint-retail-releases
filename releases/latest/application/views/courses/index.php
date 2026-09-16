<?php $this->load->view('creator/_styles'); $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2>Courses</h2>
    <div class="mp-page-sub"><?= count($courses); ?> courses with curriculum &middot; students are enrolled automatically at checkout</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= base_url('creator/students'); ?>" class="mp-btn"><i class="fa fa-graduation-cap"></i> Students</a>
    <a href="<?= base_url('creator/create/course'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Course</a>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<?php if(!empty($unlinked_items)): ?>
<div class="mp-card" style="border-color:rgba(245,158,11,.4)!important;">
  <div class="mp-card-head"><h3><i class="fa fa-exclamation-circle" style="color:var(--mp-warning);"></i> Courses without a curriculum</h3></div>
  <ul class="cr-list">
    <?php foreach($unlinked_items as $u): ?>
    <li>
      <div style="min-width:0;">
        <div class="name"><?= htmlspecialchars($u->item_name); ?></div>
        <div class="sub"><?= $CI->currency($u->sales_price); ?> &middot; listed on storefront but has no modules or lessons yet</div>
      </div>
      <a href="<?= base_url('courses/add?item_id=' . $u->id); ?>" class="mp-btn mp-btn-primary" style="padding:8px 14px!important;font-size:12px!important;">Build curriculum</a>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?php if(empty($courses) && empty($unlinked_items)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-play-circle"></i></div>
    <h3>Create your first course</h3>
    <p>Step 1: create a course product with a price and cover image. Step 2: build the curriculum with modules and video lessons.</p>
    <a href="<?= base_url('creator/create/course'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Course</a>
  </div>
<?php elseif(!empty($courses)): ?>
<div class="cr-products">
  <?php foreach($courses as $c):
    $img = (!empty($c->item->item_image) && file_exists($c->item->item_image)) ? $c->item->item_image : '';
  ?>
  <div class="cr-product">
    <div class="cr-product-media">
      <?php if($img): ?>
        <img src="<?= function_exists('mp_minified_image_url') ? mp_minified_image_url($img, 600) : base_url($img); ?>" alt="<?= htmlspecialchars($c->title); ?>" loading="lazy" decoding="async">
      <?php else: ?>
        <i class="fa fa-play-circle"></i>
      <?php endif; ?>
      <span class="cr-pill <?= $c->status ? 'active' : 'inactive'; ?>"><?= $c->status ? 'Published' : 'Draft'; ?></span>
    </div>
    <div class="cr-product-body">
      <h4 class="cr-product-name"><?= htmlspecialchars($c->title); ?></h4>
      <div class="cr-product-meta"><?= $c->item ? htmlspecialchars($c->item->item_name) : '<span style="color:var(--mp-danger);">Product missing</span>'; ?></div>
      <div class="cr-product-price"><?= $c->item ? $CI->currency($c->item->sales_price) : '—'; ?></div>
      <div class="cr-product-stats">
        <span><strong><?= (int)$c->module_count; ?></strong> modules</span>
        <span><strong><?= (int)$c->lesson_count; ?></strong> lessons</span>
        <span><strong><?= (int)$c->student_count; ?></strong> students</span>
      </div>
      <div class="cr-product-actions">
        <?php if($c->item): ?><a href="<?= base_url('creator/edit/' . $c->item->id); ?>">Product</a><?php endif; ?>
        <a href="<?= base_url('courses/edit/' . $c->id); ?>" class="primary"><i class="fa fa-list-ul"></i> Curriculum</a>
        <a href="<?= base_url('courses/delete/' . $c->id); ?>" onclick="return confirm('Remove this course curriculum? The product stays in your catalogue.')"><i class="fa fa-trash-o"></i></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
