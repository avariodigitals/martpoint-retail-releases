<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Account Book</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }

    .account-card { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); border-radius: 20px; padding: 22px 18px; color: #fff; margin-bottom: 18px; }
    .account-card .code { font-size: 12px; opacity: 0.85; margin-bottom: 4px; }
    .account-card .name { font-size: 20px; font-weight: 700; margin-bottom: 10px; }
    .account-card .balance { font-size: 26px; font-weight: 800; }
    .account-card .label { font-size: 13px; opacity: 0.85; margin-top: 4px; }

    .filters { display: flex; gap: 8px; margin-bottom: 16px; }
    .filters input[type="date"] { flex: 1; padding: 11px 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; color: var(--mp-ink); }
    .filters button { padding: 11px 18px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }

    .summary { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }
    .summary-box { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; }
    .summary-box .label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 6px; }
    .summary-box .value { font-size: 18px; font-weight: 700; }
    .summary-box .debit { color: var(--mp-danger); }
    .summary-box .credit { color: var(--mp-success); }

    .tx-list { display: flex; flex-direction: column; gap: 10px; }
    .tx-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; }
    .tx-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .tx-date { font-size: 12px; color: var(--mp-muted); font-weight: 500; }
    .tx-type { font-size: 12px; font-weight: 600; color: var(--mp-primary); background: #EFF6FF; padding: 4px 10px; border-radius: 20px; }
    .tx-desc { font-size: 14px; font-weight: 600; color: var(--mp-ink); margin-bottom: 10px; }
    .tx-line { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-top: 1px solid var(--mp-border); }
    .tx-amount { font-size: 15px; font-weight: 700; }
    .tx-amount.debit { color: var(--mp-danger); }
    .tx-amount.credit { color: var(--mp-success); }
    .tx-balance { font-size: 13px; color: var(--mp-muted); }
    .tx-note { font-size: 12px; color: var(--mp-muted); margin-top: 8px; line-height: 1.4; }
    .tx-meta { font-size: 12px; color: var(--mp-muted); margin-top: 6px; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .actions { display: flex; gap: 10px; margin: 18px 0; }
    .actions .btn-whatsapp { flex: 1; background: #25D366; color: #fff; border: none; border-radius: 12px; padding: 12px 14px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
  </style>
</head>
<body>
  <div id="app">
    <div class="topbar">
      <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
        <h1>Account Book</h1>
      </div>
    </div>

    <section class="screen">
      <div class="account-card">
        <div class="code"><?= htmlspecialchars($account->account_code ?? ''); ?></div>
        <div class="name"><?= htmlspecialchars($account->account_name ?? ''); ?></div>
        <div class="balance" title="<?= strip_tags(mp_format_money($account->balance ?? 0)); ?>"><?= mp_format_money_compact($account->balance ?? 0); ?></div>
        <div class="label">Current Balance</div>
      </div>

      <form method="get" class="filters" action="<?= base_url('mobile/account_book/' . (int)($account->id ?? 0)); ?>">
        <input type="date" name="from" value="<?= $from; ?>" max="<?= date('Y-m-d'); ?>">
        <input type="date" name="to" value="<?= $to; ?>" max="<?= date('Y-m-d'); ?>">
        <button type="submit">Go</button>
      </form>

      <div class="summary">
        <div class="summary-box">
          <div class="label">Total Debit</div>
          <div class="value debit" title="<?= strip_tags(mp_format_money($total_debit)); ?>"><?= mp_format_money_compact($total_debit); ?></div>
        </div>
        <div class="summary-box">
          <div class="label">Total Credit</div>
          <div class="value credit" title="<?= strip_tags(mp_format_money($total_credit)); ?>"><?= mp_format_money_compact($total_credit); ?></div>
        </div>
      </div>

      <div class="actions">
        <button type="button" class="btn-whatsapp" onclick="shareToWhatsApp(mpAccountBookShare)"><i class="fa fa-whatsapp"></i> Share to WhatsApp</button>
      </div>

      <?php if(!empty($transactions)): ?>
        <div class="tx-list">
          <?php foreach($transactions as $tx): ?>
            <div class="tx-card">
              <div class="tx-header">
                <div class="tx-date"><?= show_date($tx->transaction_date); ?></div>
                <div class="tx-type"><?= htmlspecialchars($tx->transaction_type); ?></div>
              </div>
              <div class="tx-desc"><?= htmlspecialchars($tx->description); ?></div>
              <div class="tx-line">
                <div class="tx-amount <?= $tx->is_debit ? 'debit' : 'credit'; ?>">
                  <?= $tx->is_debit ? '-' : '+'; ?> <?= mp_format_money($tx->amount); ?>
                </div>
                <div class="tx-balance">Bal <?= mp_format_money($tx->balance); ?></div>
              </div>
              <?php if(!empty($tx->note)): ?>
                <div class="tx-note"><?= htmlspecialchars($tx->note); ?></div>
              <?php endif; ?>
              <?php if(!empty($tx->created_by)): ?>
                <div class="tx-meta">By <?= htmlspecialchars($tx->created_by); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">No transactions in this period.</div>
      <?php endif; ?>
    </section>
  </div>

  <?php
    $share_lines = [];
    $share_lines[] = '*' . ($SITE_TITLE ?? 'MartPoint') . '*';
    $share_lines[] = 'Account Book: ' . ($account->account_name ?? '');
    $share_lines[] = 'Code: ' . ($account->account_code ?? '');
    $share_lines[] = 'Period: ' . $from . ' to ' . $to;
    $share_lines[] = 'Current Balance: ' . mp_format_money($account->balance ?? 0);
    $share_lines[] = 'Total Debit: ' . mp_format_money($total_debit);
    $share_lines[] = 'Total Credit: ' . mp_format_money($total_credit);
    $share_lines[] = '';
    $tx_count = 0;
    if(!empty($transactions)){
      foreach($transactions as $tx){
        if($tx_count >= 30) break;
        $share_lines[] = show_date($tx->transaction_date) . ' | ' . $tx->transaction_type . ' | ' . ($tx->is_debit ? '-' : '+') . ' ' . mp_format_money($tx->amount) . ' | Bal ' . mp_format_money($tx->balance) . (!empty($tx->note) ? ' | ' . $tx->note : '');
        $tx_count++;
      }
    }
    $share_text = implode("\n", $share_lines);
  ?>
  <script>
    var mpAccountBookShare = <?= json_encode($share_text, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    function shareToWhatsApp(text){
      window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
    }
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => '']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
