<?php $shift = $detail['shift']; $counts = $detail['counts']; $CI =& get_instance(); ?>
<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
<style>
  .cs-card { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
  .cs-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
  .cs-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:20px; }
  .cs-kpi { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:14px; }
  .cs-kpi .lbl { font-size:11px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; }
  .cs-kpi .val { font-size:18px; font-weight:700; color:#1E293B; }
  .cs-kpi .val.pos { color:#10B981; }
  .cs-kpi .val.neg { color:#EF4444; }
  @media print {
    .main-header, .main-sidebar, .cs-actions, .btn { display:none !important; }
    .content-wrapper { margin-left:0 !important; }
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include"sidebar.php"; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?=$page_title;?><small></small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('cashier_shifts');?>"><?=$this->lang->line('z_report');?></a></li>
        <li class="active"><?=htmlspecialchars($shift->shift_code);?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">

          <div class="cs-actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            <button type="button" class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
            <a href="<?=base_url('cashier_shifts');?>" class="btn btn-default"><i class="fa fa-list"></i> All Shifts</a>
          </div>

          <div class="cs-card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
              <div>
                <h3 style="margin:0;font-weight:700;"><?=htmlspecialchars($shift->shift_code);?></h3>
                <div style="color:#64748B;font-size:13px;">
                  <?=htmlspecialchars($shift->cashier_username);?> &middot; <?=htmlspecialchars($shift->till_label ?: 'No till label');?>
                </div>
              </div>
              <?php if($shift->status === 'open'): ?>
                <span class="label label-warning" style="font-size:13px;padding:6px 12px;">OPEN</span>
              <?php else: ?>
                <span class="label label-success" style="font-size:13px;padding:6px 12px;">CLOSED</span>
              <?php endif; ?>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-4"><strong>Opened:</strong><br><?=date('d-m-Y H:i', strtotime($shift->opened_at));?></div>
              <div class="col-sm-4"><strong>Closed:</strong><br><?=$shift->closed_at ? date('d-m-Y H:i', strtotime($shift->closed_at)) : '-';?></div>
              <div class="col-sm-4"><strong>Transactions:</strong><br><?=number_format($shift->transactions);?></div>
            </div>
            <div class="row" style="margin-top:10px;">
              <div class="col-sm-4"><strong>Opening Float:</strong><br><?=store_number_format($shift->opening_float);?></div>
              <div class="col-sm-4"><strong>Manager Sign-off:</strong><br>
                <?php if($shift->approval_status === 'approved'): ?>
                  <span class="text-success"><i class="fa fa-check-circle"></i> <?=htmlspecialchars($shift->manager_username);?></span>
                <?php elseif($shift->approval_status === 'pending'): ?>
                  <span class="text-warning"><i class="fa fa-clock-o"></i> Pending</span>
                <?php else: ?>
                  <span class="text-muted">Not required</span>
                <?php endif; ?>
              </div>
            </div>
            <?php if(!empty($shift->close_note)): ?>
            <div style="margin-top:10px;background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:10px;font-size:13px;">
              <strong>Note:</strong> <?=htmlspecialchars($shift->close_note);?>
            </div>
            <?php endif; ?>
          </div>

          <div class="cs-kpi-grid">
            <div class="cs-kpi"><div class="lbl">Expected Cash</div><div class="val"><?=store_number_format($shift->total_expected_cash);?></div></div>
            <div class="cs-kpi"><div class="lbl">Counted Cash</div><div class="val"><?=store_number_format($shift->total_counted_cash);?></div></div>
            <div class="cs-kpi"><div class="lbl">Cash Variance</div><div class="val <?=floatval($shift->cash_variance)>=0?'pos':'neg';?>"><?=store_number_format($shift->cash_variance);?></div></div>
            <div class="cs-kpi"><div class="lbl">Non-Cash Variance</div><div class="val <?=floatval($shift->other_variance)>=0?'pos':'neg';?>"><?=store_number_format($shift->other_variance);?></div></div>
          </div>

          <div class="cs-card">
            <h3 style="margin-top:0;font-weight:700;">Per-Method Reconciliation</h3>
            <div class="box-body table-responsive no-padding">
              <table class="table table-bordered table-hover" id="report-data">
                <thead>
                  <tr class="bg-blue">
                    <th>Payment Method</th>
                    <th>Type</th>
                    <th class='text-right'>Expected</th>
                    <th class='text-right'>Counted</th>
                    <th class='text-right'>Variance</th>
                    <th class='text-right'>Txn</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($counts) > 0): foreach($counts as $c): $var = floatval($c->variance); ?>
                  <tr>
                    <td><?=htmlspecialchars($c->payment_type);?></td>
                    <td><?=$c->affects_cash_in_hand ? '<span class="label label-success">Cash</span>' : '<span class="label label-info">Bank/POS</span>';?></td>
                    <td class='text-right'><?=store_number_format($c->expected_amount);?></td>
                    <td class='text-right'><?=store_number_format($c->counted_amount);?></td>
                    <td class='text-right <?=abs($var)>0.001?($var<0?'text-danger':'text-success'):'text-muted';?> text-bold'><?=store_number_format($var);?></td>
                    <td class='text-right'><?=number_format($c->txn_count);?></td>
                  </tr>
                  <?php endforeach; else: ?>
                  <tr><td colspan="6" class="text-center text-muted">No per-method counts recorded.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>
  <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>
<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>
<?php include"comman/code_js_export.php"; ?>
<script src="<?php echo $theme_link; ?>js/sheetjs.js" type="text/javascript"></script>
<script>$(".report-z-active-li").addClass("active");</script>
</body>
</html>
