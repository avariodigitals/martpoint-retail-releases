<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$customer_saves = max(0, floatval($total ?? 0) - floatval($package_price ?? 0));
?>

<style>
.mp-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card .mp-card-body { padding: 20px; }
.mp-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.mp-pill.ok, .mp-pill.paid { background: rgba(5,150,105,.1); color: var(--mp-success); }
.mp-pill.out, .mp-pill.unpaid { background: rgba(220,38,38,.1); color: var(--mp-danger); }
.mp-pill.info { background: rgba(0,87,255,.1); color: var(--mp-primary); }
.mp-pill.warn { background: rgba(245,158,11,.1); color: var(--mp-warning); }
.mp-tbl { width: 100%; border-collapse: collapse; }
.mp-tbl th { text-align: left; font-size: 11px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .04em; padding: 10px 16px; border-bottom: 1px solid var(--mp-border); }
.mp-tbl td { padding: 12px 16px; font-size: 13px; color: var(--mp-ink); border-bottom: 1px solid var(--mp-border); }
.mp-tbl tr:last-child td { border-bottom: none; }
.mp-tbl tr:hover td { background: var(--mp-bg); }
.mp-tbl tfoot td { font-weight: 700; }
.pkg-hero { text-align: center; }
.pkg-hero img, .pkg-hero .pkg-placeholder { width: 100%; max-height: 200px; border-radius: 12px; border: 1px solid var(--mp-border); object-fit: cover; }
.pkg-hero .pkg-placeholder { background: var(--mp-bg); display: flex; align-items: center; justify-content: center; color: var(--mp-muted); }
.pkg-hero h3 { font-size: 18px; font-weight: 700; margin: 14px 0 8px; color: var(--mp-text); }
.pkg-pills { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
.pkg-meta-tbl td { padding: 10px 0; font-size: 13px; border-bottom: 1px solid var(--mp-border); }
.pkg-meta-tbl tr:last-child td { border-bottom: none; }
.pkg-price-big { font-size: 22px; font-weight: 800; color: var(--mp-primary); }
.mp-empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
</style>

<div class="mp-section">
  <?php $this->load->view('comman/code_flashdata'); ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= htmlspecialchars($page_title); ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Service package details</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?php echo base_url('service_packages'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Packages
      </a>
      <?php if($CI->permissions('service_packages_edit')): ?>
      <a href="<?php echo base_url('service_packages/update/'.$q_id); ?>" class="mp-qa-btn blue">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Package
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Package Details -->
<div class="mp-section">
  <div class="mp-card">
    <div class="mp-card-head">
      <h3><?= htmlspecialchars($package_name ?? ''); ?></h3>
    </div>
    <div class="mp-card-body">
      <div class="row">
        <div class="col-sm-4 pkg-hero">
          <?php if(!empty($package_image) && file_exists(FCPATH . $package_image)): ?>
            <img src="<?php echo htmlspecialchars(base_url($package_image)); ?>" alt="<?= htmlspecialchars($package_name ?? 'Package'); ?>">
          <?php else: ?>
            <div class="pkg-placeholder"><i class="fa fa-gift" style="font-size:56px;"></i></div>
          <?php endif; ?>
          <h3><?= htmlspecialchars($package_name ?? ''); ?></h3>
          <div class="pkg-pills">
            <span class="mp-pill info"><?= htmlspecialchars(ucfirst($pricing_model ?? '')); ?></span>
            <span class="mp-pill <?= ($redemption_type ?? '') == 'single' ? 'ok' : 'warn'; ?>"><?= htmlspecialchars(ucfirst($redemption_type ?? '')); ?></span>
            <?php if(($status ?? 0) == 1): ?>
              <span class="mp-pill ok">Active</span>
            <?php else: ?>
              <span class="mp-pill out">Inactive</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-sm-8">
          <table class="pkg-meta-tbl" style="width:100%;">
            <tr><td style="width:40%;"><strong>Package Code</strong></td><td><?= htmlspecialchars($package_code ?? ''); ?></td></tr>
            <tr><td><strong>Description</strong></td><td><?= nl2br(htmlspecialchars($description ?? '')); ?></td></tr>
            <tr><td><strong>Package Price</strong></td><td><span class="pkg-price-big"><?= $CI->currency($package_price ?? 0); ?></span></td></tr>
            <?php if(($pricing_model ?? '') == 'calculated'): ?>
            <tr><td><strong>Discount</strong></td><td><?= !empty($discount_type) ? htmlspecialchars(ucfirst($discount_type)) . ': ' . htmlspecialchars($discount) : 'None'; ?></td></tr>
            <?php endif; ?>
            <tr><td><strong>Expiry</strong></td><td><?= ($expiry_type ?? '') == 'none' ? 'No Expiry' : (($expiry_type ?? '') == 'days' ? (int)$expiry_days . ' Days from purchase' : show_date($expiry_date ?? '')); ?></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Package Contents -->
  <div class="mp-card">
    <div class="mp-card-head">
      <h3>Package Contents</h3>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <?php if(empty($package_items)): ?>
        <div class="mp-empty-state">This package has no items yet.</div>
      <?php else: ?>
      <table class="mp-tbl">
        <thead>
          <tr>
            <th>#</th><th>Type</th><th>Item</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0; $i = 1; foreach($package_items as $it):
            $sub = floatval($it->sales_price ?? 0) * floatval($it->quantity ?? 0);
            $total += $sub;
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><?= htmlspecialchars(ucfirst($it->item_type ?? '')); ?></td>
            <td><?= htmlspecialchars($it->item_name ?? ''); ?></td>
            <td><?= (int)($it->quantity ?? 0); ?></td>
            <td><?= $CI->currency($it->sales_price ?? 0); ?></td>
            <td><?= $CI->currency($sub); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="5" class="text-right">Individual Total:</td><td><?= $CI->currency($total); ?></td></tr>
          <tr style="color:var(--mp-success);"><td colspan="5" class="text-right">Package Price:</td><td><?= $CI->currency($package_price ?? 0); ?></td></tr>
          <tr style="color:var(--mp-danger);"><td colspan="5" class="text-right">Customer Saves:</td><td><?= $CI->currency($customer_saves); ?></td></tr>
        </tfoot>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>$('.service-packages-view-active-li,.service_packages-active-li').addClass('active');</script>
