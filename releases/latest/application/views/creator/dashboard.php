<?php $this->load->view('creator/_styles'); $CI =& get_instance(); ?>

<div class="cr-hero">
  <div>
    <h2>Creator Workspace</h2>
    <p>Sell courses, digital downloads and memberships. Everything you publish here goes live on your storefront.</p>
  </div>
  <div class="cr-hero-actions">
    <a href="<?= base_url('creator/create/digital'); ?>" class="ghost"><i class="fa fa-download"></i> New Digital Product</a>
    <a href="<?= base_url('creator/create/course'); ?>" class="ghost"><i class="fa fa-play-circle"></i> New Course</a>
    <a href="<?= base_url('creator/create/membership'); ?>" class="ghost"><i class="fa fa-star"></i> New Membership</a>
    <?php if(!empty($store_slug)): ?><a href="<?= base_url('store/' . $store_slug); ?>" target="_blank" class="solid"><i class="fa fa-external-link"></i> View Storefront</a><?php endif; ?>
  </div>
</div>

<div class="cr-kpis">
  <div class="cr-kpi purple">
    <div class="cr-kpi-top"><span class="cr-kpi-label">Revenue this month</span><span class="cr-kpi-icon"><i class="fa fa-money"></i></span></div>
    <div class="cr-kpi-value"><?= $CI->currency($kpi['revenue_month']); ?></div>
    <div class="cr-kpi-sub">Today: <?= $CI->currency($kpi['revenue_today']); ?></div>
  </div>
  <div class="cr-kpi blue">
    <div class="cr-kpi-top"><span class="cr-kpi-label">Orders this month</span><span class="cr-kpi-icon"><i class="fa fa-shopping-bag"></i></span></div>
    <div class="cr-kpi-value"><?= number_format($kpi['orders_month']); ?></div>
    <div class="cr-kpi-sub"><?= number_format($kpi['orders_pending']); ?> pending &middot; <a href="<?= base_url('online_store/orders'); ?>">View orders</a></div>
  </div>
  <div class="cr-kpi green">
    <div class="cr-kpi-top"><span class="cr-kpi-label">Active students</span><span class="cr-kpi-icon"><i class="fa fa-graduation-cap"></i></span></div>
    <div class="cr-kpi-value"><?= number_format($students); ?></div>
    <div class="cr-kpi-sub"><a href="<?= base_url('creator/students'); ?>">Enrolments &rarr;</a></div>
  </div>
  <div class="cr-kpi pink">
    <div class="cr-kpi-top"><span class="cr-kpi-label">Active members</span><span class="cr-kpi-icon"><i class="fa fa-id-badge"></i></span></div>
    <div class="cr-kpi-value"><?= number_format($members); ?></div>
    <div class="cr-kpi-sub"><a href="<?= base_url('creator/members'); ?>">Subscriptions &rarr;</a></div>
  </div>
</div>

<div class="cr-grid-3">
  <a href="<?= base_url('creator/products/digital'); ?>" class="cr-type-card">
    <div class="icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary);"><i class="fa fa-download"></i></div>
    <div class="count"><?= number_format($counts['digital']); ?></div>
    <div class="title">Digital Products</div>
    <div class="meta">Ebooks, templates, audio, files &middot; <?= number_format($downloads); ?> downloads served</div>
  </a>
  <a href="<?= base_url('creator/products/course'); ?>" class="cr-type-card">
    <div class="icon" style="background:rgba(124,58,237,.1);color:#7C3AED;"><i class="fa fa-play-circle"></i></div>
    <div class="count"><?= number_format($counts['course']); ?></div>
    <div class="title">Courses</div>
    <div class="meta">Video lessons with modules and progress tracking</div>
  </a>
  <a href="<?= base_url('creator/products/membership'); ?>" class="cr-type-card">
    <div class="icon" style="background:rgba(236,72,153,.1);color:#EC4899;"><i class="fa fa-star"></i></div>
    <div class="count"><?= number_format($counts['membership']); ?></div>
    <div class="title">Memberships</div>
    <div class="meta">Recurring weekly, monthly or yearly plans</div>
  </a>
</div>

<div class="cr-grid-2">
  <div class="mp-card">
    <div class="mp-card-head"><h3>Recent Orders</h3><a href="<?= base_url('online_store/orders'); ?>" class="mp-card-link">View all</a></div>
    <?php if(empty($recent_orders)): ?>
      <div class="cr-empty" style="border:none!important;border-radius:0!important;">
        <div class="icon"><i class="fa fa-shopping-bag"></i></div>
        <h3>No orders yet</h3>
        <p>Share your storefront link to start getting sales.</p>
      </div>
    <?php else: ?>
    <ul class="cr-list">
      <?php foreach($recent_orders as $o): ?>
      <li>
        <div style="min-width:0;">
          <div class="name"><?= htmlspecialchars($o->customer_name ?: 'Guest'); ?> <span class="sub">&middot; <?= htmlspecialchars($o->order_code ?? ('#'.$o->id)); ?></span></div>
          <div class="sub"><?= show_date($o->created_at); ?> <?= date('H:i', strtotime($o->created_at)); ?></div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <span class="cr-pill <?= htmlspecialchars($o->payment_status); ?>"><?= str_replace('_',' ',$o->payment_status); ?></span>
          <span class="amt"><?= $CI->currency($o->grand_total); ?></span>
          <a href="<?= base_url('online_store/order_detail/' . $o->id); ?>" class="mp-card-link">Open</a>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>

  <div class="mp-card">
    <div class="mp-card-head"><h3>Top Sellers</h3><a href="<?= base_url('creator/products'); ?>" class="mp-card-link">All products</a></div>
    <?php if(empty($top_products)): ?>
      <div class="cr-empty" style="border:none!important;border-radius:0!important;">
        <div class="icon"><i class="fa fa-line-chart"></i></div>
        <h3>No sales yet</h3>
        <p>Your best-selling products will appear here.</p>
      </div>
    <?php else: ?>
    <ul class="cr-list">
      <?php foreach($top_products as $p): ?>
      <li>
        <div style="min-width:0;">
          <div class="name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="sub"><span class="cr-pill <?= htmlspecialchars($p->item_type); ?>"><?= $p->item_type; ?></span> &nbsp;<?= number_format($p->qty); ?> sold</div>
        </div>
        <span class="amt"><?= $CI->currency($p->revenue); ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head"><h3>Get set up</h3></div>
  <div class="mp-card-body">
    <div class="cr-grid-3" style="margin-bottom:0!important;">
      <a href="<?= base_url('online_store/appearance'); ?>" class="cr-type-card">
        <div class="icon" style="background:rgba(13,148,136,.1);color:#0D9488;"><i class="fa fa-paint-brush"></i></div>
        <div class="title">Choose a storefront theme</div>
        <div class="meta">Pick from Creator Focus, Bold or Studio</div>
      </a>
      <a href="<?= base_url('online_store/banners'); ?>" class="cr-type-card">
        <div class="icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning);"><i class="fa fa-image"></i></div>
        <div class="title">Add a hero banner</div>
        <div class="meta">Headline, subtitle and call-to-action</div>
      </a>
      <a href="<?= base_url('paystack/settings'); ?>" class="cr-type-card">
        <div class="icon" style="background:rgba(5,150,105,.1);color:var(--mp-success);"><i class="fa fa-credit-card"></i></div>
        <div class="title">Connect payments</div>
        <div class="meta">Accept cards and transfers with Paystack</div>
      </a>
    </div>
  </div>
</div>
