<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= $report['title']; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group input, .form-group select { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 12px 14px; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-ink); border: 1px solid var(--mp-border); }
    .btn-whatsapp { background: #25D366; color: #fff; border: 1px solid #25D366; }
    .action-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px; }
    .action-bar .btn { font-size: 13px; padding: 10px 4px; }
    .report-scroll { overflow-x: auto; background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); margin-top: 12px; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 600px; }
    .report-table th, .report-table td { padding: 10px 12px; border-bottom: 1px solid var(--mp-border); white-space: nowrap; text-align: left; }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table a { color: var(--mp-primary); text-decoration: none; }
    .loading, .empty-state { text-align: center; padding: 30px 20px; color: var(--mp-muted); }
    .pl-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .pl-card { background: var(--mp-bg); border-radius: 12px; padding: 14px; }
    .pl-card .label { font-size: 12px; color: var(--mp-muted); margin-bottom: 4px; }
    .pl-card .value { font-size: 18px; font-weight: 700; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: relative; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/reports'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $report['title']; ?></h1>
        </div>
      </div>

      <form id="report-form" class="card" method="post">
        <input type="hidden" id="base_url" value="<?= base_url(); ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="view_all" value="no">
        <div class="form-row">
          <div class="form-group">
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= in_array($report['type'], ['receivables_aging','inventory_aging']) ? date('Y-m-d') : date('Y-m-01'); ?>" required>
          </div>
          <div class="form-group">
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= date('Y-m-d'); ?>" required>
          </div>
        </div>

        <?php if(warehouse_module() && warehouse_count() > 0): ?>
        <div class="form-group">
          <label>Branch</label>
          <select class="mp-select" name="warehouse_id">
            <option value="" selected>All</option>
            <?= get_warehouse_select_list('none', get_current_store_id(), false); ?>
          </select>
        </div>
        <?php else: ?>
          <input type="hidden" name="warehouse_id" value="<?= get_store_warehouse_id(); ?>">
        <?php endif; ?>

        <?php if($report['type'] === 'stock_transfer' && warehouse_module() && warehouse_count() > 0): ?>
        <div class="form-row">
          <div class="form-group">
            <label>From Branch</label>
            <select class="mp-select" name="from_warehouse">
              <option value="" selected>All</option>
              <?= get_warehouse_select_list('none', get_current_store_id(), false); ?>
            </select>
          </div>
          <div class="form-group">
            <label>To Branch</label>
            <select class="mp-select" name="to_warehouse">
              <option value="" selected>All</option>
              <?= get_warehouse_select_list('none', get_current_store_id(), false); ?>
            </select>
          </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Run Report</button>

        <div class="action-bar" id="actions" style="display:none;">
          <button type="button" class="btn btn-whatsapp" id="btn-share"><i class="fa fa-whatsapp"></i> WhatsApp</button>
          <button type="button" class="btn btn-secondary" id="btn-csv"><i class="fa fa-download"></i> CSV</button>
          <button type="button" class="btn btn-secondary" id="btn-print"><i class="fa fa-print"></i> Print</button>
        </div>
      </form>

      <div id="report-result">
        <div class="empty-state">Tap <strong>Run Report</strong> to load data.</div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var base_url = document.getElementById('base_url').value;
    var reportType = '<?= $report['type']; ?>';
    var endpoint = '<?= $report['endpoint']; ?>';
    var reportTitle = '<?= addslashes($report['title']); ?>';
    var reportHeaders = <?= json_encode($report['headers'] ?? []); ?>;
    var showStore = <?= (store_module() && is_admin()) ? 'true' : 'false'; ?>;
    var baseHeaders = {
      'sales': ['#','Branch','Invoice','Date','Customer ID','Customer','Total','Paid','Due','User'],
      'sales_return': ['#','Branch','Invoice','Date','Sales Ref','Customer','Total','Paid','Due'],
      'purchase': ['#','Branch','Invoice','Date','Supplier ID','Supplier','Total','Paid','Due'],
      'purchase_return': ['#','Branch','Invoice','Date','Purchase','Supplier','Total','Paid','Due'],
      'expense': ['#','Code','Date','Category','Reference','For','Amount','Note','User'],
      'item_sales': ['#','Invoice','Date','Customer','Item','Category','Qty','Unit Price','Total'],
      'return_item': ['#','Invoice','Date','Status','Customer','Item','Qty','Total'],
      'sales_and_payments': ['#','Date','Invoice','Ref Bill','Description','Qty','Bill Amt','Receive','Balance'],
      'sales_tax': ['#','Invoice','Date','Customer','Tax No','Rate','Discount','Tax','Round Off','Total'],
      'sales_return_payments': ['#','Invoice','Date','Customer ID','Customer','Payment Type','Note','Paid','User'],
      'stock_transfer': ['#','Date','From','To','Item','Category','Brand','Qty'],
      'expired_items': ['#','Item Code','Item','Lot','Expiry','Stock'],
      'variant_attribute': ['#','Attribute','Value','Qty Sold','Revenue','Transactions'],
      'sell_through': ['#','Item','Category','Qty Sold','Stock','Received','Sell-Through','Status','Revenue'],
      'reorder_suggestion': ['#','Item','Category','Stock','Sold','Avg Daily','Reorder Point','Suggested','Urgency','Est. Cost'],
      'stock': ['#','Item Code','Item','Brand','Category','Unit Price','Tax','Purchase Cost','Sale Price','Stock','Stock Value','Purchase Value'],
      'stock_brand': ['#','Brand','Qty']
    };
    function getDefaultHeaders(type){
      var h = (baseHeaders[type] || []).slice();
      if(showStore && h.length){ h.splice(1,0,'Store'); }
      return h;
    }
    if(!reportHeaders.length){ reportHeaders = getDefaultHeaders(reportType); }
    var reportBrandHeaders = getDefaultHeaders('stock_brand');
    function renderThead(headers){
      return '<thead><tr>' + (headers || []).map(function(h){ return '<th>' + h + '</th>'; }).join('') + '</tr></thead>';
    }

    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        var options = Array.from(sel.options);
        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', closeAllMpSelects);
    }
    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }
    initMpSelects();

    var form = document.getElementById('report-form');
    var result = document.getElementById('report-result');
    var actionBar = document.getElementById('actions');
    var reportData = null;

    form.addEventListener('submit', async function(e){
      e.preventDefault();
      result.innerHTML = '<div class="loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
      actionBar.style.display = 'none';
      reportData = null;

      var fd = new FormData(form);
      if(reportType === 'receivables_aging' || reportType === 'inventory_aging'){
        fd.set('as_of_date', form.elements['to_date'].value);
      }
      try {
        var res = await fetch(base_url + endpoint, {method: 'POST', body: fd});
        if(reportType === 'stock' || reportType === 'profit_loss' || reportType === 'cash_flow'){
          var data = await res.json();
          reportData = data;
          if(reportType === 'stock'){
            result.innerHTML = '<h3 style="font-size:15px;margin:16px 0 8px;">Item Wise</h3>' +
              '<div class="report-scroll"><table class="report-table">' + renderThead(reportHeaders) + '<tbody>' + (data.item_wise_report || '') + '</tbody></table></div>' +
              '<h3 style="font-size:15px;margin:16px 0 8px;">Brand Wise</h3>' +
              '<div class="report-scroll"><table class="report-table">' + renderThead(reportBrandHeaders) + '<tbody>' + (data.brand_wise_stock || '') + '</tbody></table></div>';
          } else if(reportType === 'cash_flow'){
            result.innerHTML = renderCashFlow(data);
          } else {
            result.innerHTML = renderProfitLoss(data);
          }
          actionBar.style.display = 'grid';
        } else {
          var html = await res.text();
          var table = '<div class="report-scroll"><table id="report-table" class="report-table">';
          if(reportHeaders && reportHeaders.length){
            table += '<thead><tr>' + reportHeaders.map(function(h){ return '<th>' + h + '</th>'; }).join('') + '</tr></thead>';
          }
          table += '<tbody>' + html + '</tbody></table></div>';
          result.innerHTML = table;
          actionBar.style.display = 'grid';
        }
      } catch(err){
        result.innerHTML = '<div class="empty-state">Could not load report. Please try again.</div>';
      }
    });

    function renderProfitLoss(data){
      var items = [
        {label:'Opening Stock', value:data.opening_stock_price || '0'},
        {label:'Closing Stock', value:data.closing_stock_price || '0'},
        {label:'Sales', value:data.sal_total || '0'},
        {label:'Sales Return', value:data.sales_return_total || '0'},
        {label:'Purchase', value:data.pur_total || '0'},
        {label:'Purchase Return', value:data.pur_return_total || '0'},
        {label:'Expense', value:data.exp_total || '0'},
        {label:'Gross Profit', value:data.gross_profit || '0'},
        {label:'Net Profit', value:data.tot_net_profit || '0'}
      ];
      var html = '<div class="pl-grid">';
      items.forEach(function(it){
        html += '<div class="pl-card"><div class="label">' + it.label + '</div><div class="value">' + mpFormatNumber(it.value) + '</div></div>';
      });
      html += '</div>';
      return html;
    }

    function renderCashFlow(data){
      var html = '<div class="pl-grid">';
      html += '<div class="pl-card"><div class="label">Cash In</div><div class="value">' + mpFormatNumber(data.in_total) + '</div></div>';
      html += '<div class="pl-card"><div class="label">Cash Out</div><div class="value">' + mpFormatNumber(data.out_total) + '</div></div>';
      html += '<div class="pl-card"><div class="label">Net Cash</div><div class="value">' + mpFormatNumber(data.net) + '</div></div>';
      html += '</div>';
      html += '<div class="report-scroll" style="margin-top:12px;"><table class="report-table"><tbody>';
      if(data.lines && data.lines.length){
        data.lines.forEach(function(line){
          html += '<tr><td>' + (line.label || '') + '</td><td>' + (line.direction || '') + '</td><td class="text-right">' + mpFormatNumber(line.amount) + '</td></tr>';
        });
      }
      html += '</tbody></table></div>';
      return html;
    }

    function tableToCSV(){
      var rows = [];
      if(reportType === 'profit_loss' && reportData){
        rows.push(['Report', reportTitle]);
        rows.push(['From', form.querySelector('[name=from_date]').value, 'To', form.querySelector('[name=to_date]').value]);
        var items = [
          ['Opening Stock', reportData.opening_stock_price || '0'],
          ['Closing Stock', reportData.closing_stock_price || '0'],
          ['Sales', reportData.sal_total || '0'],
          ['Sales Return', reportData.sales_return_total || '0'],
          ['Purchase', reportData.pur_total || '0'],
          ['Purchase Return', reportData.pur_return_total || '0'],
          ['Expense', reportData.exp_total || '0'],
          ['Gross Profit', reportData.gross_profit || '0'],
          ['Net Profit', reportData.tot_net_profit || '0']
        ];
        return rows.concat(items);
      }
      if(reportType === 'cash_flow' && reportData){
        rows.push(['Report', reportTitle]);
        rows.push(['From', form.querySelector('[name=from_date]').value, 'To', form.querySelector('[name=to_date]').value]);
        rows.push(['', '', '']);
        rows.push(['Cash In', reportData.in_total || '0']);
        rows.push(['Cash Out', reportData.out_total || '0']);
        rows.push(['Net Cash', reportData.net || '0']);
        if(reportData.lines){
          rows.push(['', '', '']);
          rows.push(['Label', 'Direction', 'Amount']);
          reportData.lines.forEach(function(line){
            rows.push([line.label, line.direction, line.amount]);
          });
        }
        return rows;
      }
      rows.push(['Report', reportTitle, 'From', form.querySelector('[name=from_date]').value, 'To', form.querySelector('[name=to_date]').value]);
      if(reportHeaders && reportHeaders.length){
        rows.push(reportHeaders);
      }
      document.querySelectorAll('.report-table tbody tr').forEach(function(tr){
        var row = [];
        tr.querySelectorAll('td, th').forEach(function(cell){
          row.push(cell.innerText.replace(/"/g, '""').trim());
        });
        if(row.length) rows.push(row);
      });
      return rows;
    }

    function downloadCSV(){
      var rows = tableToCSV();
      var csv = rows.map(function(r){ return r.map(function(c){ return '"' + (c || '') + '"'; }).join(','); }).join('\n');
      var blob = new Blob([csv], {type: 'text/csv'});
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = '<?= strtolower(str_replace(' ', '_', $report['title'])); ?>_' + new Date().toISOString().slice(0,10) + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    document.getElementById('btn-csv').addEventListener('click', downloadCSV);

    document.getElementById('btn-print').addEventListener('click', function(){
      var w = window.open('', '_blank');
      w.document.write('<html><head><title>' + reportTitle + '</title></head><body>');
      w.document.write('<h2>' + reportTitle + '</h2>');
      w.document.write(result.innerHTML);
      w.document.write('</body></html>');
      w.document.close();
      w.print();
    });

    document.getElementById('btn-share').addEventListener('click', shareToWhatsApp);

    function buildReportMessage(){
      var from = form.querySelector('[name=from_date]').value;
      var to = form.querySelector('[name=to_date]').value;
      var storeName = <?= json_encode($SITE_TITLE ?? 'MartPoint'); ?>;
      var msg = '*' + storeName + '*\n';
      msg += '*' + reportTitle + '*\n';
      msg += 'Period: ' + from + ' to ' + to + '\n\n';

      if(reportType === 'profit_loss' && reportData){
        msg += 'Opening Stock: ' + (reportData.opening_stock_price || 0) + '\n';
        msg += 'Closing Stock: ' + (reportData.closing_stock_price || 0) + '\n';
        msg += 'Sales: ' + (reportData.sal_total || 0) + '\n';
        msg += 'Sales Return: ' + (reportData.sales_return_total || 0) + '\n';
        msg += 'Purchase: ' + (reportData.pur_total || 0) + '\n';
        msg += 'Purchase Return: ' + (reportData.pur_return_total || 0) + '\n';
        msg += 'Expense: ' + (reportData.exp_total || 0) + '\n';
        msg += 'Gross Profit: ' + (reportData.gross_profit || 0) + '\n';
        msg += 'Net Profit: ' + (reportData.tot_net_profit || 0) + '\n';
      } else if(reportType === 'cash_flow' && reportData){
        msg += 'Cash In: ' + (reportData.in_total || 0) + '\n';
        msg += 'Cash Out: ' + (reportData.out_total || 0) + '\n';
        msg += 'Net Cash: ' + (reportData.net || 0) + '\n';
        if(reportData.lines && reportData.lines.length){
          msg += '\nDetails:\n';
          reportData.lines.forEach(function(line){
            msg += (line.label || '') + ' | ' + (line.direction || '') + ' | ' + (line.amount || 0) + '\n';
          });
        }
      } else {
        var trs = document.querySelectorAll('.report-table tbody tr');
        var count = 0;
        trs.forEach(function(tr){
          if(count >= 30) return;
          var cells = Array.from(tr.querySelectorAll('td, th')).map(function(c){ return c.innerText.trim(); }).join(' | ');
          if(cells){
            msg += cells + '\n';
            count++;
          }
        });
      }

      msg += '\n' + window.location.href;
      return msg;
    }

    function shareToWhatsApp(){
      var text = encodeURIComponent(buildReportMessage());
      window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
    }
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
