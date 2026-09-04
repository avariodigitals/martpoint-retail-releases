<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance();

$q3 = $this->db->query("SELECT * FROM db_stocktransfer WHERE id = ? AND store_id = ?", array($stocktransfer_id, get_current_store_id()));
if($q3->num_rows() == 0){
  $CI->show_access_denied_page();
  exit();
}

$res3 = $q3->row();
$transfer_date = $res3->transfer_date;
$note          = $res3->note;
$warehouse_from = $res3->warehouse_from;
$warehouse_to   = $res3->warehouse_to;
$created_time   = $res3->created_time;

$store = $this->db->query("SELECT * FROM db_store WHERE id = ?", array($res3->store_id))->row();
?>

<div class="mp-page-head no-print">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Transfer #: <?= $stocktransfer_id; ?></div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <?php if($CI->permissions('stock_transfer_edit')): ?>
    <a href="<?= base_url('stock_transfer/update/'.$stocktransfer_id); ?>" class="mp-btn-primary"><i class="fa fa-edit"></i> Edit</a>
    <?php endif; ?>
    <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    <a href="<?= base_url('stock_transfer/print_invoice/'.$stocktransfer_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-print"></i> Print</a>
    <a href="<?= base_url('stock_transfer/pdf/'.$stocktransfer_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-file-pdf-o"></i> PDF</a>
  </div>
</div>

<div class="inv-printable">
  <div class="inv-meta-row">
    <div class="inv-meta-group">
      <span class="inv-meta-label">Store</span>
      <span class="inv-meta-value"><?= htmlspecialchars($store->store_name); ?></span>
    </div>
    <div class="inv-meta-group">
      <span class="inv-meta-label">Date</span>
      <span class="inv-meta-value"><?= show_date($transfer_date) . ' ' . $created_time; ?></span>
    </div>
    <div class="inv-meta-group">
      <span class="inv-meta-label">From Warehouse</span>
      <span class="inv-meta-value"><?= htmlspecialchars(get_warehouse_name($warehouse_from)); ?></span>
    </div>
    <div class="inv-meta-group">
      <span class="inv-meta-label">To Warehouse</span>
      <span class="inv-meta-value"><?= htmlspecialchars(get_warehouse_name($warehouse_to)); ?></span>
    </div>
  </div>

  <h3>Items</h3>
  <div class="mp-dt-scroll">
    <table class="table inv-item-table" style="width:100%;">
      <thead>
        <tr>
          <th>#</th>
          <th><?= $this->lang->line('item_name'); ?></th>
          <th style="width:140px;text-align:right;"><?= $this->lang->line('quantity'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 0;
        $tot_qty = 0;
        $q2 = $this->db->query("SELECT c.item_name, a.transfer_qty
                                FROM db_stocktransferitems a
                                JOIN db_items c ON c.id = a.item_id
                                WHERE a.stocktransfer_id = ?", array($stocktransfer_id));
        foreach ($q2->result() as $res2) {
          echo '<tr>';
          echo '<td>'.(++$i).'</td>';
          echo '<td>'.htmlspecialchars($res2->item_name).'</td>';
          echo '<td style="text-align:right;">'.format_qty($res2->transfer_qty).'</td>';
          echo '</tr>';
          $tot_qty += $res2->transfer_qty;
        }
        if($i == 0){
          echo '<tr><td colspan="3" class="mp-empty-state">No items found</td></tr>';
        }
        ?>
      </tbody>
      <tfoot>
        <tr style="font-weight:700;">
          <td colspan="2" style="text-align:right;">Total</td>
          <td style="text-align:right;"><?= format_qty($tot_qty); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <?php if(!empty($note)): ?>
  <div class="mp-form-group full" style="margin-top:24px;">
    <label><?= $this->lang->line('note'); ?></label>
    <div class="mp-form-control" style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($note)); ?></div>
  </div>
  <?php endif; ?>
</div>

<script>$(".stock_transfer_list-active-li").addClass("active");</script>
