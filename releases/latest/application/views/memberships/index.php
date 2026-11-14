<?php $this->load->view('creator/_styles'); $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2>Memberships</h2>
    <div class="mp-page-sub"><?= count($memberships); ?> recurring plans &middot; subscriptions are created automatically at checkout</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= base_url('creator/members'); ?>" class="mp-btn"><i class="fa fa-id-badge"></i> Members</a>
    <a href="<?= base_url('creator/create/membership'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Membership</a>
  </div>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<?php if(empty($memberships)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-star"></i></div>
    <h3>Create your first membership</h3>
    <p>Step 1: create a membership product with a price. Step 2: set the billing cycle and trial period here.</p>
    <a href="<?= base_url('creator/create/membership'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Membership</a>
  </div>
<?php else: ?>
<div class="cr-products">
  <?php foreach($memberships as $m):
    $img = (!empty($m->item->item_image) && file_exists($m->item->item_image)) ? $m->item->item_image : '';
    $cycle = ['weekly' => 'week', 'monthly' => 'month', 'yearly' => 'year'][$m->billing_interval] ?? $m->billing_interval;
  ?>
  <div class="cr-product">
    <div class="cr-product-media" style="aspect-ratio:16/7!important;background:linear-gradient(120deg,#1E1B4B,#7C3AED)!important;color:#fff!important;">
      <?php if($img): ?>
        <img src="<?= function_exists('mp_minified_image_url') ? mp_minified_image_url($img, 600) : base_url($img); ?>" alt="<?= htmlspecialchars($m->membership_name); ?>" loading="lazy" decoding="async">
      <?php else: ?>
        <i class="fa fa-star"></i>
      <?php endif; ?>
      <span class="cr-pill <?= $m->status ? 'active' : 'inactive'; ?>"><?= $m->status ? 'Live' : 'Paused'; ?></span>
    </div>
    <div class="cr-product-body">
      <h4 class="cr-product-name"><?= htmlspecialchars($m->membership_name); ?></h4>
      <div class="cr-product-meta"><?= $m->item ? htmlspecialchars($m->item->item_name) : '<span style="color:var(--mp-danger);">Product missing</span>'; ?></div>
      <div class="cr-product-price"><?= $m->item ? $CI->currency($m->item->sales_price) : '—'; ?> <span style="font-size:12px;font-weight:600;color:var(--mp-muted);">/ <?= htmlspecialchars($cycle); ?></span></div>
      <div class="cr-product-stats">
        <span><strong><?= (int)$m->active_count; ?></strong> active members</span>
        <?php if((int)$m->trial_days > 0): ?><span><strong><?= (int)$m->trial_days; ?></strong>-day trial</span><?php endif; ?>
      </div>
      <?php if(!empty($m->description)): ?><div class="cr-product-meta" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= htmlspecialchars($m->description); ?></div><?php endif; ?>
      <div class="cr-product-actions">
        <?php if($m->item): ?><a href="<?= base_url('creator/edit/' . $m->item->id); ?>">Product</a><?php endif; ?>
        <a href="<?= base_url('memberships/edit/' . $m->id); ?>" class="primary"><i class="fa fa-refresh"></i> Billing</a>
        <a href="<?= base_url('memberships/delete/' . $m->id); ?>" onclick="return confirm('Remove this membership plan? The product stays in your catalogue.')"><i class="fa fa-trash-o"></i></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
