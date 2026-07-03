<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style>
  .ds-wrapper { padding: 20px; }
  .ds-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
  .ds-header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #1E293B; }
  .ds-header .meta { color: #64748B; font-size: 14px; }
  .ds-date-form { display: flex; gap: 8px; align-items: center; }
  .ds-date-form input[type="date"] { border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px 12px; height: 38px; font-size: 14px; }
  .ds-date-form .btn { border-radius: 8px; padding: 8px 16px; font-size: 14px; }

  .ds-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .ds-kpi-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; }
  .ds-kpi-card .label { font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
  .ds-kpi-card .value { font-size: 22px; font-weight: 700; color: #1E293B; }
  .ds-kpi-card .value.positive { color: #10B981; }
  .ds-kpi-card .value.negative { color: #EF4444; }
  .ds-kpi-card .value.orange { color: #F97316; }
  .ds-kpi-card .sub { font-size: 12px; color: #94A3B8; margin-top: 4px; }

  .ds-section { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; margin-bottom: 20px; }
  .ds-section h3 { margin: 0 0 14px 0; font-size: 16px; font-weight: 700; color: #1E293B; }
  .ds-section table { width: 100%; border-collapse: collapse; }
  .ds-section table th { text-align: left; padding: 10px 12px; font-size: 12px; text-transform: uppercase; color: #64748B; border-bottom: 1px solid #E2E8F0; font-weight: 600; }
  .ds-section table td { padding: 10px 12px; font-size: 14px; color: #334155; border-bottom: 1px solid #F1F5F9; }
  .ds-section table tr:last-child td { border-bottom: none; }

  .ds-insights { display: flex; flex-wrap: wrap; gap: 10px; }
  .ds-insight { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 8px; padding: 10px 14px; font-size: 13px; }
  .ds-insight.warning { background: #FFFBEB; border-color: #FDE68A; color: #92400E; }
  .ds-insight.info { background: #EFF6FF; border-color: #BFDBFE; color: #1E40AF; }

  .ds-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
  .ds-actions .btn { border-radius: 8px; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; }
  .btn-whatsapp { background: #25D366; border-color: #25D366; color: #fff; }
  .btn-whatsapp:hover { background: #128C7E; border-color: #128C7E; color: #fff; }
  .btn-pdf { background: #EF4444; border-color: #EF4444; color: #fff; }
  .btn-pdf:hover { background: #DC2626; border-color: #DC2626; color: #fff; }
  .btn-email { background: #3B82F6; border-color: #3B82F6; color: #fff; }
  .btn-email:hover { background: #2563EB; border-color: #2563EB; color: #fff; }

  .ds-empty { text-align: center; padding: 60px 20px; color: #94A3B8; }
  .ds-empty i { font-size: 48px; margin-bottom: 12px; display: block; color: #CBD5E1; }

  /* Print styles */
  @media print {
    .main-header, .main-sidebar, .ds-actions, .ds-date-form, .btn { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .ds-wrapper { padding: 0; }
    .ds-section, .ds-kpi-card { box-shadow: none; border: 1px solid #ddd; }
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">

<div class="wrapper">
  <?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?=$page_title;?></h1>
    </section>

    <div class="row">
    <div class="col-md-12">
    <div class="ds-wrapper">

      <!-- Actions & Date -->
      <div class="ds-header">
        <div>
          <div class="meta"><?=htmlspecialchars($store_name);?> &mdash; <?=($selected_date !== $selected_date_to) ? show_date($selected_date).' — '.show_date($selected_date_to) : show_date($selected_date);?></div>
        </div>
        <div class="ds-date-form">
          <form method="get" action="<?=base_url('dashboard/daily_summary');?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <div style="display:flex;gap:6px;align-items:center;">
              <span style="font-size:12px;color:#64748B;white-space:nowrap;">From</span>
              <input type="date" name="date_from" value="<?=$selected_date;?>" class="form-control" style="width:140px;">
            </div>
            <div style="display:flex;gap:6px;align-items:center;">
              <span style="font-size:12px;color:#64748B;white-space:nowrap;">To</span>
              <input type="date" name="date_to" value="<?=$selected_date_to;?>" class="form-control" style="width:140px;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> View</button>
          </form>
        </div>
      </div>

      <!-- Share Actions -->
      <div class="ds-actions">
        <button type="button" class="btn btn-whatsapp" onclick="shareToWhatsApp()"><i class="fa fa-whatsapp"></i> Share to WhatsApp</button>
        <button type="button" class="btn btn-pdf" onclick="downloadPDF()"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
        <button type="button" class="btn btn-email" onclick="sendEmail()"><i class="fa fa-envelope"></i> Send by Email</button>
      </div>

      <?php if(!$summary['has_data']): ?>
      <div class="ds-empty">
        <i class="fa fa-inbox"></i>
        <h3>No business activity recorded for this period.</h3>
        <p>Select a different date range or come back after making some sales.</p>
      </div>
      <?php else: ?>

      <!-- KPI Cards -->
      <div class="ds-kpi-grid">
        <div class="ds-kpi-card">
          <div class="label">Total Sales</div>
          <div class="value orange"><?=kmb($summary['sales']['total']);?></div>
          <div class="sub"><?=$summary['sales']['transactions'];?> transactions</div>
        </div>
        <div class="ds-kpi-card">
          <div class="label">Profit</div>
          <div class="value <?=$summary['profit']['available'] && $summary['profit']['gross_profit'] >= 0 ? 'positive' : 'negative';?>">
            <?=$summary['profit']['available'] ? kmb($summary['profit']['gross_profit']) : 'Not Available';?>
          </div>
          <div class="sub"><?=$summary['profit']['available'] ? $summary['profit']['margin'].'% margin' : '';?></div>
        </div>
        <div class="ds-kpi-card">
          <div class="label">Expenses</div>
          <div class="value negative"><?=kmb($summary['expenses']['total']);?></div>
          <div class="sub"><?=$summary['is_range'] ? 'Recorded in period' : 'Recorded today';?></div>
        </div>
        <div class="ds-kpi-card">
          <div class="label">Net Position</div>
          <div class="value <?=$summary['net_position'] >= 0 ? 'positive' : 'negative';?>"><?=kmb($summary['net_position']);?></div>
          <div class="sub">Sales &minus; Expenses</div>
        </div>
        <div class="ds-kpi-card">
          <div class="label">Outstanding Debts</div>
          <div class="value negative"><?=kmb($summary['outstanding_debts']['total']);?></div>
          <div class="sub"><?=$summary['outstanding_debts']['count'];?> customers owing</div>
        </div>
        <div class="ds-kpi-card">
          <div class="label">Cash Expected</div>
          <div class="value orange"><?=kmb($summary['sales']['cash_expected']);?></div>
          <div class="sub">Total collected today</div>
        </div>
      </div>

      <!-- Staff Attendance -->
      <?php if($summary['attendance']['total_staff'] > 0): ?>
      <div class="ds-section">
        <h3><i class="fa fa-users text-green"></i> Staff Attendance (<?=$summary['attendance']['present'];?>/<?=$summary['attendance']['total_staff'];?> Present)</h3>
        <table>
          <thead>
            <tr><th>Name</th><th>Position</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach($summary['attendance']['staff_list'] as $staff): ?>
            <tr>
              <td><?=htmlspecialchars($staff['name']);?></td>
              <td><?=htmlspecialchars($staff['position']);?></td>
              <td>
                <?php if($staff['status'] === 'Present'): ?>
                <span class="label label-success"><i class="fa fa-check"></i> Present</span>
                <?php else: ?>
                <span class="label label-danger"><i class="fa fa-times"></i> Absent</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Insights -->
      <?php if(count($summary['insights']) > 0): ?>
      <div class="ds-section">
        <h3><i class="fa fa-lightbulb-o text-warning"></i> Business Notes</h3>
        <div class="ds-insights">
          <?php foreach($summary['insights'] as $insight): ?>
          <div class="ds-insight"><?=htmlspecialchars($insight);?></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Top Selling Products -->
      <div class="ds-section">
        <h3><i class="fa fa-trophy text-warning"></i> Top Selling Products</h3>
        <?php if(count($summary['top_products']) > 0): ?>
        <table>
          <thead>
            <tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr>
          </thead>
          <tbody>
            <?php foreach($summary['top_products'] as $p): ?>
            <tr>
              <td><?=htmlspecialchars($p['name']);?></td>
              <td><?=number_format($p['qty']);?></td>
              <td><?=kmb($p['revenue']);?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:#94A3B8;margin:0;">No products sold on this date.</p>
        <?php endif; ?>
      </div>

      <!-- Low Stock Items -->
      <div class="ds-section">
        <h3><i class="fa fa-exclamation-triangle text-danger"></i> Top 10 Low Stock Items</h3>
        <?php if(count($summary['low_stock_items']) > 0): ?>
        <div style="margin-bottom:12px;">
          <input type="text" class="form-control" id="low-stock-filter" placeholder="Search low stock items..." style="max-width:320px;height:34px;font-size:13px;">
        </div>
        <table>
          <thead>
            <tr><th>Product</th><th>Current Stock</th><th>Reorder Level</th></tr>
          </thead>
          <tbody>
            <?php foreach($summary['low_stock_items'] as $item): ?>
            <tr>
              <td><?=htmlspecialchars($item['name']);?></td>
              <td><?=number_format($item['qty']);?></td>
              <td><?=number_format($item['min']);?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:#94A3B8;margin:0;">No low stock items. Great job!</p>
        <?php endif; ?>
      </div>

      <!-- Expiry Alerts -->
      <?php
      try {
        $CI->load->model('expiry_settings_model');
        $exp_settings = $CI->expiry_settings_model->get_settings();
        $expired_items = $CI->expiry_settings_model->get_expired_items();
        $expiring_items = $CI->expiry_settings_model->get_expiring_items();
        if(count($expired_items) > 0 || count($expiring_items) > 0):
      ?>
      <div class="ds-section">
        <h3><i class="fa fa-calendar-times-o text-danger"></i> Expiry Alerts</h3>
        <?php if(count($expired_items) > 0): ?>
        <div class="alert alert-danger" style="margin-bottom:8px;">
          <strong><?= count($expired_items); ?> Expired Items</strong> — Blocked from sale
        </div>
        <?php endif; ?>
        <?php if(count($expiring_items) > 0): ?>
        <div class="alert alert-warning" style="margin-bottom:8px;">
          <strong><?= count($expiring_items); ?> Items Expiring Soon</strong> (within <?= $exp_settings->alert_before_days; ?> days)
        </div>
        <?php endif; ?>
        <a href="<?= base_url('expired_items_report'); ?>" class="btn btn-sm btn-primary">View Full Report</a>
      </div>
      <?php
        endif;
      } catch (Exception $e) { /* Expiry table not ready yet */ }
      ?>

      <!-- Payment Breakdown -->
      <?php if(count($summary['sales']['payment_breakdown']) > 0): ?>
      <div class="ds-section">
        <h3><i class="fa fa-credit-card text-info"></i> Payment Breakdown</h3>
        <table>
          <thead>
            <tr><th>Payment Method</th><th>Txn</th><th>Amount</th><th>Type</th><th>Pending</th></tr>
          </thead>
          <tbody>
            <?php foreach($summary['sales']['payment_breakdown'] as $pay): ?>
            <tr>
              <td><?=htmlspecialchars($pay['type']);?></td>
              <td><?=number_format($pay['txn_count']);?></td>
              <td><?=kmb($pay['amount']);?></td>
              <td><?=($pay['affects_cash']==1) ? '<span class="label label-success">Cash</span>' : '<span class="label label-info">Bank/POS</span>';?></td>
              <td><?=($pay['pending_count']>0) ? '<span class="label label-warning">'.$pay['pending_count'].'</span>' : '-';?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="margin-top:8px;">
          <span class="label label-success">Cash In Hand: <?=kmb($summary['sales']['cash_expected']);?></span>
          <span class="label label-info" style="margin-left:6px;">Bank/POS/Transfer: <?=kmb($summary['sales']['bank_pos_expected'] ?? 0);?></span>
        </div>
      </div>
      <?php endif; ?>

      <!-- Purchase Due -->
      <?php if($summary['purchase_due'] > 0): ?>
      <div class="ds-section">
        <h3><i class="fa fa-truck text-muted"></i> Purchase Due</h3>
        <p style="margin:0;font-size:16px;color:#1E293B;"><strong><?=kmb($summary['purchase_due']);?></strong> owed to suppliers.</p>
      </div>
      <?php endif; ?>

      <?php endif; ?>

      <div style="text-align:center;margin-top:30px;color:#94A3B8;font-size:12px;">
        Generated by MartPoint Retail &mdash; Powered by Avario Digitals
      </div>

    </div><!-- /.ds-wrapper -->
    </div><!-- /.col-md-12 -->
    </div><!-- /.row -->
  </div><!-- /.content-wrapper -->

  <?php $this->load->view('footer'); ?>
  <div class="control-sidebar-bg"></div>
</div><!-- ./wrapper -->

<?php include"comman/code_js.php"; ?>

<script>
  var summaryData = <?=json_encode($summary);?>;
  var storeName = <?=json_encode($store_name);?>;
  var selectedDate = <?=json_encode($summary['date_label']);?>;
  var reportUrl = <?=json_encode(base_url('dashboard/daily_summary?date_from='.$selected_date.'&date_to='.$selected_date_to));?>;

  function buildReportMessage(){
    var msg = "*MartPoint Business Report*\n\n";
    msg += "Store: " + storeName + "\n";
    msg += "Period: " + selectedDate + "\n\n";
    msg += "*Sales:* " + (summaryData.sales.total ? summaryData.sales.total.toLocaleString() : '0') + "\n";
    msg += "*Profit:* " + (summaryData.profit.available ? summaryData.profit.gross_profit.toLocaleString() : 'Not Available') + "\n";
    msg += "*Expenses:* " + (summaryData.expenses.total ? summaryData.expenses.total.toLocaleString() : '0') + "\n";
    msg += "*Net Position:* " + (summaryData.net_position ? summaryData.net_position.toLocaleString() : '0') + "\n\n";
    msg += "*Transactions:* " + summaryData.sales.transactions + "\n";
    if(summaryData.attendance && summaryData.attendance.total_staff > 0){
      msg += "\n*Staff Attendance:*\n";
      var staffList = summaryData.attendance.staff_list || [];
      for(var s=0; s<staffList.length; s++){
        var st = staffList[s];
        msg += st.name + " (" + st.position + ") — " + st.status + "\n";
      }
      msg += "\n";
    }
    if(summaryData.top_products.length > 0){
      msg += "\n*Best Selling:*\n" + summaryData.top_products[0].name + "\n";
    }
    if(summaryData.low_stock_items.length > 0){
      msg += "\n*Low Stock:*\n";
      var limit = Math.min(5, summaryData.low_stock_items.length);
      for(var i=0; i<limit; i++){
        msg += summaryData.low_stock_items[i].name + " - " + summaryData.low_stock_items[i].qty + " left\n";
      }
    }
    msg += "\n*Outstanding Debts:* " + (summaryData.outstanding_debts.total ? summaryData.outstanding_debts.total.toLocaleString() : '0') + "\n";
    msg += "*Cash Expected:* " + (summaryData.sales.cash_expected ? summaryData.sales.cash_expected.toLocaleString() : '0') + "\n\n";
    msg += "View Full Report:\n" + reportUrl + "\n\n";
    msg += "Powered by MartPoint Retail";
    return msg;
  }

  function shareToWhatsApp(){
    if(!summaryData.has_data){
      toastr.info('No data to share for this period.');
      return;
    }

    var msg = buildReportMessage();

    // Step 1: Copy full report to clipboard
    function doCopyAndOpen(){
      if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(msg).then(function(){
          toastr.success('Report copied! Opening WhatsApp — please paste the message.');
          setTimeout(openWhatsApp, 800);
        }).catch(function(){
          toastr.warning('Could not copy automatically. Please copy manually.');
          openWhatsApp();
        });
      } else {
        toastr.warning('Please copy the report manually, then paste in WhatsApp.');
        openWhatsApp();
      }
    }

    // Step 2: Open WhatsApp directly (avoid generic share sheet)
    function openWhatsApp(){
      var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
      if(isMobile){
        // Open WhatsApp app / mobile web
        window.location.href = 'https://wa.me/';
      } else {
        // Desktop
        window.open('https://web.whatsapp.com/', '_blank');
      }
    }

    doCopyAndOpen();
  }

  function downloadPDF(){
    window.print();
  }

  function sendEmail(){
    if(typeof swal === 'undefined'){
      var email = prompt("Enter email address to send the summary:");
      if(!email) return;
      doSendEmail(email);
      return;
    }
    swal({
      title: "Send Summary by Email",
      text: "Enter the recipient email address:",
      icon: "info",
      content: "input",
      buttons: true
    }).then(function(email){
      if(!email) return;
      doSendEmail(email);
    });
  }

  function doSendEmail(email){
    if(!summaryData.has_data){
      toastr.info('No data to send for this period.');
      return;
    }

    // Try server-side email first (SMTP)
    var btn = document.querySelector('.btn-email');
    if(btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...'; btn.disabled = true; }

    $.ajax({
      type: 'POST',
      url: base_url + 'dashboard/send_summary_email',
      data: {
        to_email: email,
        date: '<?=$selected_date;?>',
        date_to: '<?=$selected_date_to;?>',
        '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>'
      },
      dataType: 'json',
      success: function(res){
        if(btn) { btn.innerHTML = '<i class="fa fa-envelope"></i> Send by Email'; btn.disabled = false; }
        if(res.status === 'success'){
          toastr.success('Email sent successfully to ' + email);
        } else {
          // Fallback to mailto
          mailtoFallback(email);
        }
      },
      error: function(){
        if(btn) { btn.innerHTML = '<i class="fa fa-envelope"></i> Send by Email'; btn.disabled = false; }
        // Fallback to mailto
        mailtoFallback(email);
      }
    });
  }

  function mailtoFallback(email){
    var subject = "MartPoint Daily Business Summary — " + storeName + " — " + selectedDate;
    var body = "MartPoint Daily Business Summary\n\n";
    body += "Store: " + storeName + "\n";
    body += "Date: " + selectedDate + "\n\n";
    body += "Total Sales: " + (summaryData.sales.total ? summaryData.sales.total.toLocaleString() : '0') + "\n";
    body += "Profit: " + (summaryData.profit.available ? summaryData.profit.gross_profit.toLocaleString() : 'Not Available') + "\n";
    body += "Expenses: " + (summaryData.expenses.total ? summaryData.expenses.total.toLocaleString() : '0') + "\n";
    body += "Net Position: " + (summaryData.net_position ? summaryData.net_position.toLocaleString() : '0') + "\n";
    body += "Transactions: " + summaryData.sales.transactions + "\n";
    if(summaryData.attendance && summaryData.attendance.total_staff > 0){
      body += "\nStaff Attendance:\n";
      var staffList3 = summaryData.attendance.staff_list || [];
      for(var s3=0; s3<staffList3.length; s3++){
        var st3 = staffList3[s3];
        body += "- " + st3.name + " (" + st3.position + ") — " + st3.status + "\n";
      }
      body += "\n";
    }
    body += "Outstanding Debts: " + (summaryData.outstanding_debts.total ? summaryData.outstanding_debts.total.toLocaleString() : '0') + "\n";
    body += "Cash Expected: " + (summaryData.sales.cash_expected ? summaryData.sales.cash_expected.toLocaleString() : '0') + "\n\n";
    body += "View full report: " + reportUrl + "\n\n";
    body += "Powered by MartPoint Retail";

    window.open("mailto:" + encodeURIComponent(email) + "?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body), '_blank');
  }

  // Low stock live filter
  document.getElementById('low-stock-filter').addEventListener('input', function(){
    var term = this.value.toLowerCase();
    var section = this.closest('.ds-section');
    var rows = section.querySelectorAll('table tbody tr');
    rows.forEach(function(row){
      var name = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
      row.style.display = name.indexOf(term) > -1 ? '' : 'none';
    });
  });
</script>

</body>
</html>
