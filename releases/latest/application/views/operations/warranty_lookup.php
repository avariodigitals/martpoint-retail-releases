<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Validate warranty by serial, IMEI, invoice, customer, or product</div>
  </div>
  <a class="mp-btn-secondary" href="<?= base_url('operations/warranty_claims'); ?>"><i class="fa fa-wrench"></i> Warranty cases</a>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Warranty Lookup</h3></div>
  <div class="mp-card-body">
    <form method="get" class="mp-form-grid bm-warranty-search" style="margin-bottom:20px;grid-template-columns:1fr auto auto;">
      <div class="mp-form-group">
        <label>Search</label>
        <input type="text" name="search" class="mp-form-control" placeholder="Serial, IMEI, invoice, customer, or product name" value="<?= htmlspecialchars($search ?? ''); ?>">
      </div>
      <div class="mp-form-group" style="justify-content:flex-end;">
        <label>&nbsp;</label>
        <button type="submit" class="mp-btn-primary"><i class="fa fa-search"></i> Search</button>
      </div>
      <div class="mp-form-group" style="justify-content:flex-end;">
        <label>&nbsp;</label>
        <a href="<?= base_url('operations/warranty_lookup'); ?>" class="mp-btn-secondary">Clear</a>
      </div>
    </form>

    <?php if(!empty($search)): ?>
    <?php if(!empty($results)): ?>
    <div class="mp-table-wrap">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Serial Number</th>
              <th>IMEI</th>
              <th>IMEI 2</th>
              <th>Sale Date</th>
              <th>Customer</th>
              <th>Warranty (Months)</th>
              <th>Expiry</th>
              <th>Status</th>
              <th>Invoice</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($results as $r): 
              $warranty_months = (int)($r->warranty_months ?? 0);
              $sale_date = $r->sales_date;
              $warranty_end = $r->effective_warranty_end ?? null;
              $is_stock_unit = ($r->sales_code ?? '') === 'In Stock';
              $expiry_date = '';
              $status = '<span class="label label-default">No Warranty</span>';
              if($is_stock_unit && $warranty_months > 0){
                $status = '<span class="label label-info">Starts at sale</span>';
              } elseif($warranty_months > 0 && (is_valid_date($warranty_end) || is_valid_date($sale_date))){
                $expiry = is_valid_date($warranty_end) ? $warranty_end : date('Y-m-d', strtotime($sale_date . ' + ' . $warranty_months . ' months'));
                $expiry_date = $expiry;
                if(strtotime($expiry) >= strtotime(date('Y-m-d'))){
                  $status = '<span class="label label-success">Under Warranty</span>';
                } else {
                  $status = '<span class="label label-danger">Expired</span>';
                }
              }
            ?>
            <tr>
              <td><?= htmlspecialchars($r->item_name ?? ''); ?></td>
              <td><?= htmlspecialchars($r->sold_serial_number ?? ''); ?></td>
              <td><?= htmlspecialchars($r->sold_imei_number ?? ''); ?></td>
              <td><?= htmlspecialchars($r->sold_imei_number_2 ?? ''); ?></td>
              <td><?= is_valid_date($sale_date) ? date('d-m-Y', strtotime($sale_date)) : ''; ?></td>
              <td><?= htmlspecialchars($r->customer_name ?? 'Walk-in'); ?> <?= !empty($r->mobile) ? '<br><small>'.htmlspecialchars($r->mobile).'</small>' : ''; ?></td>
              <td><?= $warranty_months; ?></td>
              <td><?= !empty($expiry_date) ? date('d-m-Y', strtotime($expiry_date)) : '-'; ?></td>
              <td><?= $status; ?></td>
              <td>
                <?php if(!empty($r->sales_id) && $r->sales_id != 0): ?>
                  <a href="<?= base_url('sales/print_invoice_pos/'.$r->sales_id); ?>" target="_blank" class="mp-qa-btn blue" style="padding:6px 12px;font-size:13px;"><?= htmlspecialchars($r->sales_code ?? ''); ?></a>
                  <?php if(!empty($r->id) && !empty($expiry_date) && strtotime($expiry_date)>=strtotime(date('Y-m-d'))): ?><br><a href="<?= base_url('operations/warranty_claims?new='.(int)$r->id); ?>" class="mp-qa-btn" style="padding:6px 12px;font-size:13px;margin-top:5px;"><i class="fa fa-wrench"></i> Open case</a><?php endif; ?>
                <?php else: ?>
                  <span class="label label-info">In Stock</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="mp-empty-state"><i class="fa fa-exclamation-circle"></i> No records found for "<strong><?= htmlspecialchars($search); ?></strong>".</div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
