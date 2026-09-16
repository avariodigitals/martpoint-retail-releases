<?php $this->load->view('creator/_styles'); $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2>Buyers</h2>
    <div class="mp-page-sub"><?= count($rows); ?> people have bought from your storefront</div>
  </div>
  <a href="<?= base_url('online_store/orders'); ?>" class="mp-btn"><i class="fa fa-shopping-bag"></i> All Orders</a>
</div>

<?php if(empty($rows)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-users"></i></div>
    <h3>No buyers yet</h3>
    <p>Everyone who checks out on your storefront will appear here with their lifetime spend.</p>
  </div>
<?php else: ?>
<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Customers</h3></div>
  <div class="mp-dt-scroll">
    <table class="mp-dt-table">
      <thead>
        <tr><th>Buyer</th><th>Contact</th><th>Orders</th><th>Lifetime Spend</th><th>Last Order</th><th style="width:100px;">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td class="row-name"><?= htmlspecialchars($r->customer_name ?: 'Guest'); ?></td>
          <td>
            <div class="row-meta"><?= htmlspecialchars($r->customer_email ?: ''); ?></div>
            <div class="row-meta"><?= htmlspecialchars($r->customer_phone ?: ''); ?></div>
          </td>
          <td><?= number_format($r->orders); ?></td>
          <td class="amt"><?= $CI->currency($r->spent); ?></td>
          <td class="row-meta"><?= show_date($r->last_order); ?></td>
          <td>
            <div class="cr-table-actions">
              <?php if(!empty($r->customer_id)): ?><a href="<?= base_url('customers/update/' . $r->customer_id); ?>" class="primary">Profile</a><?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
