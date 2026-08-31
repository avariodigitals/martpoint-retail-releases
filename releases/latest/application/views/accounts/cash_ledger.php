<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
  .cash-dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
  .cash-card { background: #fff; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .cash-card .label { font-size: 11px; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
  .cash-card .value { font-size: 20px; font-weight: 700; }
  .cash-card.opening .value { color: #3B82F6; }
  .cash-card.in .value { color: #059669; }
  .cash-card.out .value { color: #DC2626; }
  .cash-card.net .value { color: #7C3AED; }
  .cash-card.current { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: #fff; border: none; }
  .cash-card.current .label { color: rgba(255,255,255,0.8); }
  .ledger-table th { background: #f8fafc; font-weight: 600; color: #475569; }
  .ledger-table .in-row td { color: #059669; }
  .ledger-table .out-row td { color: #DC2626; }
  .ledger-table .in-row .in-amt { font-weight: 600; }
  .ledger-table .out-row .out-amt { font-weight: 600; }
  .ledger-table .balance-col { font-weight: 700; color: #1E293B; }
  .ledger-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
  .badge-in { background: #D1FAE5; color: #059669; }
  .badge-out { background: #FEE2E2; color: #DC2626; }
  .opening-row { background: #F1F5F9; font-weight: 600; }
  .empty-msg { padding: 40px; text-align: center; color: #94A3B8; font-size: 15px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?= $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Cash Ledger</li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <?php $this->load->view('comman/code_flashdata');?>

          <div class="box box-primary">
            <div class="box-header">
              <form method="get" action="<?= base_url('accounts/cash_ledger'); ?>" class="form-inline" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div class="form-group">
                  <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control datepicker" name="from_date" value="<?= show_date(date('d-m-Y', strtotime($from_date))); ?>" style="width:130px;">
                  </div>
                </div>
                <div class="form-group">
                  <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">To Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control datepicker" name="to_date" value="<?= show_date(date('d-m-Y', strtotime($to_date))); ?>" style="width:130px;">
                  </div>
                </div>
                <button type="submit" class="btn btn-primary">View Ledger</button>
                <button type="button" class="btn btn-warning" onclick="$('#moveCashModal').modal('show')"><i class="fa fa-exchange"></i> Move Cash</button>
              </form>
            </div>

            <!-- Move Cash Modal -->
            <div class="modal fade" id="moveCashModal" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-exchange"></i> Move Cash</h4>
                  </div>
                  <div class="modal-body">
                    <form id="move-cash-form">
                      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                      <div class="form-group">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="mc_amount" placeholder="0.00" required>
                      </div>
                      <div class="row">
                        <?php
                        // Fetch accounts directly if controller didn't pass them
                        if(!isset($accounts) || empty($accounts)){
                          $accounts = $CI->db->where('store_id', get_current_store_id())
                                             ->where('status', 1)
                                             ->order_by('account_name')
                                             ->get('ac_accounts')
                                             ->result();
                        }
                        ?>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>From (Debit) <span class="text-danger">*</span></label>
                            <select class="form-control" name="debit_account_id" id="mc_debit">
                              <option value="">— Select Cash Account —</option>
                              <?php if(empty($accounts)): ?>
                              <option value="" disabled>No accounts found. Please create accounts first.</option>
                              <?php else: ?>
                                <?php foreach($accounts as $acc):
                                  $sel = (!empty($cash_account_id) && $cash_account_id == $acc->id) ? 'selected' : '';
                                ?>
                                <option value="<?= $acc->id; ?>" <?= $sel; ?>><?= htmlspecialchars($acc->account_name); ?></option>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>To (Credit) <span class="text-danger">*</span></label>
                            <select class="form-control" name="credit_account_id" id="mc_credit">
                              <option value="">— Select Destination —</option>
                              <?php if(!empty($accounts)): ?>
                                <?php foreach($accounts as $acc): ?>
                                <option value="<?= $acc->id; ?>"><?= htmlspecialchars($acc->account_name); ?></option>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label>Note / Reference</label>
                        <input type="text" class="form-control" name="note" id="mc_note" placeholder="e.g. Cash deposited to bank">
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="submitMoveCash()">Move Cash</button>
                  </div>
                </div>
              </div>
            </div>

            <?php
            $total_in = array_sum(array_column($ledger, 'amount_in'));
            $total_out = array_sum(array_column($ledger, 'amount_out'));
            $closing = floatval($opening_balance) + $total_in - $total_out;
            $closing = max(0, $closing);
            ?>

            <div class="box-body">
              <!-- Summary Cards -->
              <div class="cash-dashboard">
                <div class="cash-card opening">
                  <div class="label">Opening Balance</div>
                  <div class="value"><?= $CI->currency(floatval($opening_balance)); ?></div>
                  <div style="font-size:11px;color:#94A3B8;margin-top:4px;">Before <?= show_date(date('d-m-Y', strtotime($from_date))); ?></div>
                </div>
                <div class="cash-card in">
                  <div class="label">Total Cash In</div>
                  <div class="value"><?= $CI->currency($total_in); ?></div>
                  <div style="font-size:11px;color:#94A3B8;margin-top:4px;">Sales + Returns</div>
                </div>
                <div class="cash-card out">
                  <div class="label">Total Cash Out</div>
                  <div class="value"><?= $CI->currency($total_out); ?></div>
                  <div style="font-size:11px;color:#94A3B8;margin-top:4px;">Expenses + Payments + Deposits</div>
                </div>
                <div class="cash-card net">
                  <div class="label">Net Movement</div>
                  <div class="value"><?= $CI->currency($total_in - $total_out); ?></div>
                  <div style="font-size:11px;color:#94A3B8;margin-top:4px;">In minus Out</div>
                </div>
                <div class="cash-card current">
                  <div class="label"><?= $cash_acc_name; ?> Balance</div>
                  <div class="value"><?= $CI->currency($closing); ?></div>
                  <div style="font-size:11px;color:rgba(255,255,255,0.8);margin-top:4px;">As of <?= show_date(date('d-m-Y', strtotime($to_date))); ?></div>
                </div>
              </div>

              <!-- Ledger Table -->
              <?php
              // Build account name lookup
              $acc_names = array();
              $all_accounts = $CI->db->where('store_id', get_current_store_id())->where('status', 1)->get('ac_accounts')->result();
              foreach($all_accounts as $a){ $acc_names[$a->id] = $a->account_name; }
              $cash_acc_id = get_cash_account_id();
              $cash_acc_name = isset($acc_names[$cash_acc_id]) ? $acc_names[$cash_acc_id] : 'Cash Account';
              ?>
              <div class="table-responsive no-padding">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                  <div style="font-size:13px;color:#64748B;">Showing <?= count($ledger); ?> transaction<?= count($ledger) !== 1 ? 's' : ''; ?> for <?= show_date(date('d-m-Y', strtotime($from_date))); ?> - <?= show_date(date('d-m-Y', strtotime($to_date))); ?></div>
                  <button type="button" class="btn btn-sm btn-success" onclick="exportLedgerCSV()"><i class="fa fa-download"></i> Export CSV</button>
                </div>
                <table class="table table-bordered table-hover ledger-table" id="ledger-table" style="margin:0;">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Transaction Type</th>
                      <th>Mode</th>
                      <th>Account</th>
                      <th>Type</th>
                      <th class="text-right">Cash In</th>
                      <th class="text-right">Cash Out</th>
                      <th class="text-right">Balance</th>
                      <th>Note</th>
                      <th>By</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $running = floatval($opening_balance);
                    if($running > 0):
                    ?>
                    <tr class="opening-row">
                      <td colspan="5">Opening Balance (carried forward before <?= show_date(date('d-m-Y', strtotime($from_date))); ?>)</td>
                      <td class="text-right">—</td>
                      <td class="text-right">—</td>
                      <td class="text-right balance-col"><?= $CI->currency($running); ?></td>
                      <td colspan="2"></td>
                    </tr>
                    <?php endif; ?>

                    <?php if(empty($ledger)): ?>
                    <tr><td colspan="10" class="empty-msg">No new cash transactions in this date range. Balance remains <?= $CI->currency(floatval($opening_balance)); ?>.</td></tr>
                    <?php endif; ?>

                    <?php foreach($ledger as $row):
                      $row_class = ($row['direction'] === 'IN') ? 'in-row' : 'out-row';
                      $running += floatval($row['amount_in']) - floatval($row['amount_out']);
                      $running = max(0, $running);
                      $acc_name = isset($acc_names[$row['account_id']]) ? $acc_names[$row['account_id']] : '<span class="text-muted">Not linked</span>';
                    ?>
                    <tr class="<?= $row_class; ?>">
                      <td><?= show_date(date('d-m-Y', strtotime($row['txn_date']))); ?><br><small class="text-muted"><?= $row['txn_time']; ?></small></td>
                      <td><?= $row['description']; ?></td>
                      <td><?= $row['mode']; ?></td>
                      <td><?= $acc_name; ?></td>
                      <td><span class="ledger-badge <?= ($row['direction'] === 'IN') ? 'badge-in' : 'badge-out'; ?>"><?= $row['direction']; ?></span></td>
                      <td class="text-right in-amt"><?= $row['amount_in'] > 0 ? $CI->currency($row['amount_in']) : '—'; ?></td>
                      <td class="text-right out-amt"><?= $row['amount_out'] > 0 ? $CI->currency($row['amount_out']) : '—'; ?></td>
                      <td class="text-right balance-col"><?= $CI->currency($running); ?></td>
                      <td><?= $row['note']; ?></td>
                      <td><?= $row['created_by']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot style="background:#f8fafc;font-weight:600;">
                    <tr>
                      <td colspan="5" class="text-right">Period Totals:</td>
                      <td class="text-right" style="color:#059669;"><?= $CI->currency($total_in); ?></td>
                      <td class="text-right" style="color:#DC2626;"><?= $CI->currency($total_out); ?></td>
                      <td class="text-right"><?= $CI->currency($running); ?></td>
                      <td colspan="2"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer.php');?>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script>
function submitMoveCash(){
  var amount = $('#mc_amount').val();
  var debit = $('#mc_debit').val();
  var credit = $('#mc_credit').val();
  var note = $('#mc_note').val();

  if(!amount || parseFloat(amount) <= 0){
    toastr['error']('Please enter a valid amount'); return;
  }
  if(!debit){ toastr['error']('Please select From (Debit) account'); return; }
  if(!credit){ toastr['error']('Please select To (Credit) account'); return; }
  if(debit === credit){ toastr['error']('Debit and Credit accounts cannot be the same'); return; }

  var data = {
    deposit_date: '<?= date('d-m-Y'); ?>',
    amount: amount,
    debit_account_id: debit,
    credit_account_id: credit,
    note: note,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  };

  $.ajax({
    url: '<?= base_url("money_deposit/new_money_deposit"); ?>',
    type: 'POST',
    data: data,
    beforeSend: function(){
      $('#moveCashModal .btn-warning').prop('disabled', true).text('Processing...');
    },
    success: function(result){
      $('#moveCashModal .btn-warning').prop('disabled', false).text('Move Cash');
      if(result === 'success' || result.indexOf('success') !== -1){
        toastr['success']('Cash moved successfully!');
        $('#move-cash-form')[0].reset();
        $('#moveCashModal').modal('hide');
        // Reload page to show updated ledger
        setTimeout(function(){ location.reload(); }, 800);
      } else {
        toastr['error'](result);
      }
    },
    error: function(xhr){
      $('#moveCashModal .btn-warning').prop('disabled', false).text('Move Cash');
      toastr['error']('Server error. Please try again.');
    }
  });
}

function exportLedgerCSV(){
  var table = document.getElementById('ledger-table');
  var rows = table.querySelectorAll('tr');
  var csv = [];
  for(var i=0; i<rows.length; i++){
    var row = [], cols = rows[i].querySelectorAll('td, th');
    for(var j=0; j<cols.length; j++){
      var text = cols[j].innerText.replace(/"/g, '""').replace(/\n/g, ' ');
      row.push('"' + text + '"');
    }
    csv.push(row.join(','));
  }
  var blob = new Blob([csv.join('\n')], {type: 'text/csv'});
  var url = window.URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.href = url;
  a.download = 'Cash_Ledger_<?= date('Y-m-d'); ?>.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
}
$('.ledger-active-li').addClass('active');
</script>
</body>
</html>
