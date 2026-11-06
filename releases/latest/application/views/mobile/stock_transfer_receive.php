<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $store_name ?? $SITE_TITLE ?? 'MartPoint'; ?> — Count In Load</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .banner { border-radius: 14px; padding: 12px 14px; font-size: 13px; line-height: 1.5; margin-bottom: 12px; }
    .banner.warn { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; }
    .banner.danger { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
    .banner.ok { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
    .route { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; }
    .route .arrow { color: var(--mp-muted); }
    .subtle { font-size: 12px; color: var(--mp-muted); margin-top: 6px; }
    .line { border-top: 1px solid var(--mp-border); padding: 14px 0; }
    .line:first-of-type { border-top: none; }
    .line .name { font-weight: 600; font-size: 15px; margin-bottom: 4px; }
    .line .unit { color: var(--mp-muted); font-weight: 400; font-size: 12px; }
    .line .sent { font-size: 12px; color: var(--mp-muted); margin-bottom: 8px; }
    .line .row2 { display: flex; align-items: center; gap: 10px; }
    .line input { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 16px; font-weight: 600; background: #fff; color: var(--mp-text); outline: none; min-width: 0; }
    .line input:focus { border-color: var(--mp-primary); }
    .line .short { font-size: 12px; font-weight: 600; white-space: nowrap; min-width: 74px; text-align: right; }
    .short.bad { color: var(--mp-danger); }
    .short.over { color: var(--mp-warning); }
    .short.good { color: var(--mp-muted); }
    .total { display: flex; justify-content: space-between; align-items: center; padding: 12px 0 0; font-weight: 700; font-size: 15px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; resize: vertical; min-height: 76px; outline: none; color: var(--mp-text); }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-primary); border: 1px solid var(--mp-border); margin-top: 10px; }
    .btn-danger { background: #fff; color: var(--mp-danger); border: 1px solid #FECACA; margin-top: 10px; }
    .hint { font-size: 12px; color: var(--mp-muted); margin-top: 10px; line-height: 1.5; }
    .section-title { font-size: 15px; font-weight: 700; margin: 0 0 10px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/stock_transfers'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Count In Load</h1>
        </div>
      </div>

      <?php $status = (string)$transfer->receipt_status; ?>
      <?php if ($status === 'In Transit'): ?>
        <div class='banner warn'>
          <strong>Waiting to be counted in.</strong><br>
          This load is not part of <?= htmlspecialchars($to_name); ?> stock yet. Enter what physically arrived &mdash; only the counted quantity gets added.
        </div>
      <?php elseif ($status === 'Awaiting Approval'): ?>
        <div class='banner danger'>
          <strong>Held for a manager.</strong><br>
          Counted short by more than the <?= $tolerance_text; ?>% allowed in transit, so none of it has landed at <?= htmlspecialchars($to_name); ?> yet.
        </div>
      <?php elseif ($status === 'Received'): ?>
        <div class='banner ok'>
          <strong>Already counted in.</strong><br>
          Recorded <?= show_date($transfer->received_date); ?><?= $received_first ? ' by ' . htmlspecialchars($received_first) : ''; ?>.
        </div>
      <?php endif; ?>

      <div class='card'>
        <div class='route'>
          <span><?= htmlspecialchars($from_name); ?></span>
          <i class='fa fa-long-arrow-right arrow'></i>
          <span><?= htmlspecialchars($to_name); ?></span>
        </div>
        <div class='subtle'>Sent <?= show_date($transfer->transfer_date); ?> · <?= count($lines); ?> line<?= count($lines) === 1 ? '' : 's'; ?> · <?= format_qty($sent_total); ?> total<?= $transfer_note !== '' ? ' · Note: ' . htmlspecialchars($transfer_note) : ''; ?></div>
      </div>

      <?php if ($can_receive): ?>
      <div class='card'>
        <div class='section-title'>What actually arrived</div>
        <form id='receive_form'>
          <input type='hidden' name='stocktransfer_id' value='<?= (int)$stocktransfer_id; ?>'>
          <?php foreach ($lines as $l): ?>
            <div class='line' data-sent='<?= (float)$l->transfer_qty; ?>'>
              <div class='name'><?= htmlspecialchars($l->item_name); ?> <span class='unit'><?= htmlspecialchars($l->unit_name); ?></span></div>
              <div class='sent'>Sent: <?= format_qty($l->transfer_qty); ?></div>
              <div class='row2'>
                <input type='text' inputmode='decimal' class='counted_qty' name='received[<?= (int)$l->id; ?>]'
                       aria-label='Quantity arrived for <?= htmlspecialchars($l->item_name); ?>'
                       value='<?= $l->received_qty !== null ? format_qty($l->received_qty) : format_qty($l->transfer_qty); ?>'>
                <div class='short good'>&mdash;</div>
              </div>
            </div>
          <?php endforeach; ?>
          <div class='total'><span>Counted total</span><span id='counted_total'>0</span></div>
          <div class='hint' id='variance_hint'></div>
          <div style='margin-top:14px;'>
            <label for='variance_reason'>Why is it short? <span style='font-weight:400;color:var(--mp-muted);'>Only if something is missing</span></label>
            <textarea id='variance_reason' name='variance_reason' placeholder='Leaked in transit, miscounted at the depot, meter reading off&hellip;'><?= htmlspecialchars((string)$transfer->variance_reason); ?></textarea>
          </div>
          <button type='submit' class='btn btn-primary' id='save_btn' style='margin-top:16px;'><i class='fa fa-check'></i> Save arrival count</button>
        </form>
      </div>
      <?php elseif ($status !== 'Received'): ?>
        <div class='card'>
          <div class='hint'>You are not assigned to <?= htmlspecialchars($to_name); ?>, so this load cannot be counted in by you. Ask someone at the receiving site.</div>
        </div>
      <?php endif; ?>

      <?php if ($status === 'Awaiting Approval' && $can_decide): ?>
      <div class='card'>
        <div class='section-title'>Manager decision</div>
        <label for='decision_note'>Your note</label>
        <textarea id='decision_note' placeholder='Accepted: evaporative loss on a hot day, documented.'></textarea>
        <div class='hint'>This goes in the approval history, so write it in plain English.</div>
        <button type='button' class='btn btn-primary' style='margin-top:14px;' onclick='decide("approve")'><i class='fa fa-thumbs-up'></i> Accept the loss and post the stock</button>
        <button type='button' class='btn btn-danger' onclick='decide("reject")'><i class='fa fa-thumbs-down'></i> Reject and recount</button>
      </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var sentTotal = <?= (float)$sent_total; ?>;
    var tolerance = <?= (float)$tolerance; ?>;
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

    function mpNum(v){ v = (v || '').toString().replace(/,/g, '').trim(); var n = parseFloat(v); return isNaN(n) ? null : n; }
    function mpFmt(v){ return v.toLocaleString(undefined, { maximumFractionDigits: 3 }); }

    function recompute(){
      var counted = 0, state = 'good', text = '';
      document.querySelectorAll('#receive_form .line').forEach(function(line){
        var sent = parseFloat(line.getAttribute('data-sent')) || 0;
        var input = line.querySelector('.counted_qty');
        var badge = line.querySelector('.short');
        var val = mpNum(input.value);
        if(val === null){ badge.textContent = '—'; badge.className = 'short good'; return; }
        counted += val;
        var diff = sent - val;
        if(diff > 0.0001){ badge.textContent = '-' + mpFmt(diff); badge.className = 'short bad'; }
        else if(diff < -0.0001){ badge.textContent = '+' + mpFmt(-diff); badge.className = 'short over'; }
        else { badge.textContent = 'match'; badge.className = 'short good'; }
      });
      var totalEl = document.getElementById('counted_total');
      totalEl.textContent = mpFmt(counted);
      totalEl.style.color = (counted < sentTotal - 0.0001) ? 'var(--mp-danger)' : 'var(--mp-success)';

      var hint = document.getElementById('variance_hint');
      var pct = sentTotal > 0 ? ((sentTotal - counted) / sentTotal) * 100 : 0;
      if(pct > 0.0001 && pct > tolerance){
        hint.textContent = mpFmt(sentTotal - counted) + ' short (' + pct.toFixed(2) + '%) — past your ' + tolerance + '% limit, so a manager must approve it before the stock lands.';
        state = 'bad';
      } else if(pct > 0.0001){
        hint.textContent = mpFmt(sentTotal - counted) + ' short — within your ' + tolerance + '% transit loss, so it posts on save.';
        state = 'over';
      } else if(counted > sentTotal + 0.0001){
        hint.textContent = 'More arrived than was sent. Check the figures with the sending site before saving.';
        state = 'over';
      } else {
        hint.textContent = 'Everything matches. Saving adds the full load to this site’s stock.';
      }
      hint.style.color = state === 'bad' ? 'var(--mp-danger)' : (state === 'over' ? 'var(--mp-warning)' : 'var(--mp-muted)');
    }

    var form = document.getElementById('receive_form');
    if(form){
      form.addEventListener('input', recompute);
      recompute();
      form.addEventListener('submit', function(ev){
        ev.preventDefault();
        var btn = document.getElementById('save_btn');
        btn.disabled = true;
        var data = new FormData(form);
        data.append(csrfName, csrfHash);
        mpFetchJson('<?= base_url('mobile/save_stock_transfer_receive'); ?>', { method: 'POST', body: data })
          .then(function(res){
            btn.disabled = false;
            if(res && res.status === 'success'){
              mpSuccess(res.message || 'Counted in.');
              setTimeout(function(){ window.location.href = res.redirect || '<?= base_url('mobile/stock_transfers'); ?>'; }, 900);
            } else {
              mpError((res && res.message) || 'The count could not be saved. Please try again.');
            }
          })
          .catch(function(err){ btn.disabled = false; mpError(mpErrorText(err)); });
      });
    }

    function decide(action){
      var note = document.getElementById('decision_note').value.trim();
      if(!note){ mpError('Please write a note before deciding on this load.'); return; }
      var msg = action === 'approve'
        ? 'Accept the shortfall and add the counted stock to this site?'
        : 'Reject the count and send this load back to be counted again?';
      mpConfirm(msg, function(){
        var data = new FormData();
        data.append('stocktransfer_id', '<?= (int)$stocktransfer_id; ?>');
        data.append('decision', action);
        data.append('decision_note', note);
        data.append(csrfName, csrfHash);
        mpFetchJson('<?= base_url('mobile/decide_transfer_variance'); ?>', { method: 'POST', body: data })
          .then(function(res){
            if(res && res.status === 'success'){
              mpSuccess(res.message || 'Decision recorded.');
              setTimeout(function(){ window.location.reload(); }, 900);
            } else {
              mpError((res && res.message) || 'The decision could not be saved. Please try again.');
            }
          })
          .catch(function(err){ mpError(mpErrorText(err)); });
      }, null, { danger: action === 'reject' });
    }
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
