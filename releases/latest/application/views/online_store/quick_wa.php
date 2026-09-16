<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-form-grid .full { grid-column:1 / -1; }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Create an order from a direct WhatsApp chat. Stock is deducted when you mark it paid.</div>
  </div>
</div>

<?php if($this->session->flashdata('error')): ?>
<div class="mp-card-form" style="margin-bottom:20px; border-left:4px solid #DC2626;">
  <div class="mp-card-body" style="padding:16px 20px; color:#DC2626; font-weight:600;">
    <?= htmlspecialchars($this->session->flashdata('error')); ?>
  </div>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('success')): ?>
<div class="mp-card-form" style="margin-bottom:20px; border-left:4px solid #10B981;">
  <div class="mp-card-body" style="padding:16px 20px; color:#10B981; font-weight:600;">
    <?= htmlspecialchars($this->session->flashdata('success')); ?>
  </div>
</div>
<?php endif; ?>

<form action="<?= base_url('online_store/quick_wa_save'); ?>" method="post">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-user"></i> Customer Details</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group">
          <label for="customer_name">Customer Name</label>
          <input type="text" class="mp-form-control" id="customer_name" name="customer_name" placeholder="e.g. Chinedu" value="<?= htmlspecialchars($this->input->post('customer_name') ?? ''); ?>">
        </div>
        <div class="mp-form-group">
          <label for="customer_phone">Customer Phone</label>
          <input type="text" class="mp-form-control" id="customer_phone" name="customer_phone" placeholder="e.g. 2348012345678" value="<?= htmlspecialchars($this->input->post('customer_phone') ?? ''); ?>">
        </div>
        <div class="mp-form-group full">
          <label for="customer_address">Address / Note</label>
          <textarea class="mp-form-control" id="customer_address" name="customer_address" rows="2"><?= htmlspecialchars($this->input->post('customer_address') ?? ''); ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-card-form" style="margin-top:20px!important;">
    <div class="mp-card-head"><h3><i class="fa fa-shopping-cart"></i> Order Item</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group full">
          <label for="product_id">Product</label>
          <select class="mp-form-control" id="product_id" name="product_id">
            <option value="">— Select a product —</option>
            <?php foreach($products as $p): ?>
            <option value="<?= (int)$p->id; ?>" <?= ((int)($this->input->post('product_id') ?? 0) === (int)$p->id) ? 'selected' : ''; ?>>
              <?= htmlspecialchars($p->item_name); ?> (<?= store_number_format($p->sales_price); ?>) — <?= (int)$p->stock; ?> in stock
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="qty">Quantity</label>
          <input type="number" class="mp-form-control" id="qty" name="qty" value="<?= (int)($this->input->post('qty') ?? 1); ?>" min="1">
        </div>
      </div>
    </div>
  </div>

  <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    <button type="submit" class="mp-qa-btn" style="background:#25D366; color:#fff; border:none; font-weight:700;"><i class="fa fa-whatsapp"></i> Create WhatsApp Order</button>
    <a href="<?= base_url('online_store/orders'); ?>" class="mp-qa-btn" style="background:var(--mp-surface); color:var(--mp-ink); border:1px solid var(--mp-border);">Back to Orders</a>
  </div>
</form>
