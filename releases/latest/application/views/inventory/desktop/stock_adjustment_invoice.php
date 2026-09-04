<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance();

$q3 = $this->db->query("SELECT b.store_id, b.adjustment_date, b.created_time, b.reference_no, b.adjustment_note, b.warehouse_id
                        FROM db_stockadjustment b
                        WHERE b.id = ? AND b.store_id = ?", array($adjustment_id, get_current_store_id()));
if($q3->num_rows() == 0){
  $CI->show_access_denied_page();
  exit();
}

$res3 = $q3->row();
$adjustment_date  = $res3->adjustment_date;
$created_time     = $res3->created_time;
$reference_no     = $res3->reference_no;
$adjustment_note  = $res3->adjustment_note;
$warehouse_id     = $res3->warehouse_id;

$store = $this->db->query("SELECT * FROM db_store WHERE id = ?", array($res3->store_id))->row();
?>

<div class="mp-page-head no-print">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Reference: <?= htmlspecialchars($reference_no); ?></div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <?php if($CI->permissions('stock_adjustment_edit')): ?>
    <a href="<?= base_url('stock_adjustment/update/'.$adjustment_id); ?>" class="mp-btn-primary"><i class="fa fa-edit"></i> Edit</a>
    <?php endif; ?>
    <a href="<?= base_url('stock_adjustment'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    <button class="mp-btn-secondary" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
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
      <span class="inv-meta-value"><?= show_date($adjustment_date) . ' ' . $created_time; ?></span>
    </div>
    <div class="inv-meta-group">
      <span class="inv-meta-label">Reference No</span>
      <span class="inv-meta-value"><?= htmlspecialchars($reference_no); ?></span>
    </div>
    <?php if(warehouse_module()): ?>
    <div class="inv-meta-group">
      <span class="inv-meta-label">Warehouse</span>
      <span class="inv-meta-value"><?= htmlspecialchars(get_warehouse_name($warehouse_id)); ?></span>
    </div>
    <?php endif; ?>
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
        $q2 = $this->db->query("SELECT a.description, c.item_name, a.adjustment_qty
                                FROM db_stockadjustmentitems a
                                JOIN db_items c ON c.id = a.item_id
                                WHERE a.adjustment_id = ?", array($adjustment_id));
        foreach ($q2->result() as $res2) {
          echo '<tr>';
          echo '<td>'.(++$i).'</td>';
          echo '<td>'.htmlspecialchars($res2->item_name);
          if(!empty($res2->description)){
            echo '<br><small class="text-muted">['.nl2br(htmlspecialchars($res2->description)).']</small>';
          }
          echo '</td>';
          echo '<td style="text-align:right;">'.format_qty($res2->adjustment_qty).'</td>';
          echo '</tr>';
          $tot_qty += $res2->adjustment_qty;
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

  <?php if(!empty($adjustment_note)): ?>
  <div class="mp-form-group full" style="margin-top:24px;">
    <label><?= $this->lang->line('note'); ?></label>
    <div class="mp-form-control" style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($adjustment_note)); ?></div>
  </div>
  <?php endif; ?>
</div>

<script>$(".stock_adjustment_list-active-li").addClass("active");</script>
