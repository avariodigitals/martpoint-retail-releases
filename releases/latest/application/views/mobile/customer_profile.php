<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Customer Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .profile-header { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
    .avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; }
    .profile-title { flex: 1; }
    .profile-title .name { font-size: 18px; font-weight: 700; }
    .profile-title .code { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .profile-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEE2E2; color: #991B1B; }
    .badge.tier { background: #E0E7FF; color: var(--mp-primary); }
    .badge.payplan { background: #FEF3C7; color: #B45309; }
    .tab-bar { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 12px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .tab-bar::-webkit-scrollbar { display: none; }
    .tab-btn { flex: 0 0 auto; display: flex; align-items: center; gap: 5px; padding: 8px 12px; border: 1px solid var(--mp-border); border-radius: 20px; background: #fff; color: var(--mp-muted); font-size: 12px; font-weight: 600; white-space: nowrap; cursor: pointer; }
    .tab-btn.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .details-list { display: grid; grid-template-columns: 1fr; gap: 0; }
    .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { font-size: 13px; color: var(--mp-muted); }
    .detail-row .value { font-size: 14px; font-weight: 600; text-align: right; }
    .detail-row .value a { color: var(--mp-primary); text-decoration: none; }
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 12px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .stat-card .label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px; }
    .stat-card .value { font-size: 18px; font-weight: 700; }
    .stat-card .value.due { color: var(--mp-danger); }
    .section-title { font-size: 15px; font-weight: 700; margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }
    .list-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: inherit; }
    .list-item:last-child { border-bottom: none; }
    .list-item .left { flex: 1; min-width: 0; }
    .list-item .name { font-weight: 600; font-size: 15px; }
    .list-item .desc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .list-item .right { text-align: right; }
    .list-item .amount { font-weight: 700; font-size: 15px; }
    .list-item .status { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 12px; }
    .status.Paid { background: #D1FAE5; color: #065F46; }
    .status.Partial { background: #FEF3C7; color: #92400E; }
    .status.Unpaid { background: #FEE2E2; color: #991B1B; }
    .empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
    .notes-box { background: #F8FAFC; border-radius: 10px; padding: 12px; font-size: 13px; line-height: 1.5; color: var(--mp-text); white-space: pre-wrap; }
    .add-form { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); display: none; }
    .add-form.active { display: block; }
    .add-form .form-group { margin-bottom: 12px; }
    .add-form label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
    .add-form input, .add-form textarea, .add-form select { width: 100%; padding: 10px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    .add-form textarea { min-height: 70px; }
    .add-form .btn { width: 100%; padding: 10px; border: none; border-radius: 10px; background: var(--mp-success); color: #fff; font-weight: 700; }
    .add-btn { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; border-radius: 10px; padding: 8px 12px; font-size: 12px; font-weight: 700; margin-bottom: 12px; cursor: pointer; }
    .btn-block { display: block; width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; margin-top: 12px; }
    .btn-block.danger { background: var(--mp-danger); }
    .btn-row { display: flex; gap: 10px; margin-top: 12px; }
    .btn-row .btn-block { margin-top: 0; }
    .id-card { width: 324px; max-width: 100%; height: 204px; border-radius: 12px; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.15); margin: 20px auto; background: linear-gradient(135deg, #fdfbf7 0%, #f5f0e8 100%); border: 1px solid #e0d5c5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .id-card-brand { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 42px; font-weight: 900; color: #8b7355; opacity: 0.06; letter-spacing: 2px; text-transform: uppercase; white-space: nowrap; pointer-events: none; }
    .id-card-body { padding: 20px 16px 16px 16px; text-align: center; }
    .id-card-name { font-size: 18px; font-weight: 700; color: #3d3229; margin-bottom: 4px; }
    .id-card-phone { font-size: 14px; color: #6b5b4f; margin-bottom: 8px; }
    .id-card-id { font-size: 11px; color: #9e8e7e; letter-spacing: 2px; font-family: monospace; }
    .id-card-barcode { text-align: center; margin-top: 6px; }
    .id-card-barcode img { height: 42px; }
    .id-card-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5); border-top: 1px solid rgba(0,0,0,0.05); }
    .id-card-footer div { font-size: 10px; color: #8b7355; }
    .id-card-actions { display: flex; gap: 10px; justify-content: center; margin-bottom: 14px; }
    .id-card-actions a, .id-card-actions button { padding: 10px 16px; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
    .id-card-actions .print { background: #fff; color: var(--mp-text); border: 1px solid var(--mp-border); }
    .id-card-actions .png { background: var(--mp-success); color: #fff; }
    .payplan-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .payplan-card .plan-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .payplan-card .plan-code { font-weight: 700; font-size: 16px; }
    .payplan-card .plan-meta { font-size: 12px; color: var(--mp-muted); margin-bottom: 8px; }
    .payplan-card .plan-progress { background: var(--mp-bg); border-radius: 8px; height: 8px; overflow: hidden; margin-top: 8px; }
    .payplan-card .plan-progress-fill { background: var(--mp-primary); height: 100%; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media print {
      body * { visibility: hidden; }
      #idCardSection, #idCardSection * { visibility: visible; }
      #idCardSection { position: absolute; left: 0; top: 0; width: 100%; display: flex; flex-direction: column; align-items: center; }
    }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/customers'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Customer Profile</h1>
        </div>
      </div>

      <div class="profile-header">
        <div class="avatar"><?= strtoupper(substr($customer->customer_name, 0, 1)); ?></div>
        <div class="profile-title">
          <div class="name"><?= $customer->customer_name; ?></div>
          <div class="code"><?= $customer->customer_code; ?></div>
          <div class="profile-tags">
            <span class="badge <?= $customer->status == 1 ? 'active' : 'inactive'; ?>"><?= $customer->status == 1 ? 'Active' : 'Inactive'; ?></span>
            <span class="badge tier"><?= $customer->loyalty_tier ?: 'Bronze'; ?></span>
            <?php if(!empty($payplans)): ?><span class="badge payplan"><i class="fa fa-hourglass-half"></i> On Payplan</span><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="tab-bar" id="tabBar">
        <button type="button" class="tab-btn active" data-tab="overview"><i class="fa fa-user"></i> Overview</button>
        <button type="button" class="tab-btn" data-tab="purchases"><i class="fa fa-shopping-cart"></i> Purchases</button>
        <button type="button" class="tab-btn" data-tab="statements"><i class="fa fa-money"></i> Statements</button>
        <?php if(!empty($gift_cards)): ?><button type="button" class="tab-btn" data-tab="giftcards"><i class="fa fa-ticket"></i> Gift</button><?php endif; ?>
        <?php if(!empty($store_credits)): ?><button type="button" class="tab-btn" data-tab="storecredit"><i class="fa fa-credit-card"></i> Credit</button><?php endif; ?>
        <?php if(!empty($coupons)): ?><button type="button" class="tab-btn" data-tab="coupons"><i class="fa fa-tags"></i> Coupons</button><?php endif; ?>
        <?php if(!empty($memberships)): ?><button type="button" class="tab-btn" data-tab="memberships"><i class="fa fa-id-card"></i> Member</button><?php endif; ?>
        <?php if(!empty($service_history)): ?><button type="button" class="tab-btn" data-tab="services"><i class="fa fa-inbox"></i> Services</button><?php endif; ?>
        <?php if(!empty($treatment_notes)): ?><button type="button" class="tab-btn" data-tab="treatment"><i class="fa fa-file-text-o"></i> Treat</button><?php endif; ?>
        <?php if(!empty($medical_notes) || !empty($medical_enabled)): ?><button type="button" class="tab-btn" data-tab="medical"><i class="fa fa-file-medical-o"></i> Medical</button><?php endif; ?>
        <?php if(!empty($custom_orders)): ?><button type="button" class="tab-btn" data-tab="custom"><i class="fa fa-pencil-square-o"></i> Orders</button><?php endif; ?>
        <button type="button" class="tab-btn" data-tab="notes"><i class="fa fa-sticky-note"></i> Notes</button>
        <button type="button" class="tab-btn" data-tab="idcard"><i class="fa fa-id-card"></i> ID Card</button>
        <?php if(!empty($payplans)): ?><button type="button" class="tab-btn" data-tab="payplan"><i class="fa fa-hourglass-half"></i> Payplan</button><?php endif; ?>
      </div>

      <div id="overview" class="tab-panel active">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="label">Total Due</div>
            <div class="value due" title="<?= strip_tags(mp_format_money($total_due)); ?>"><?= mp_format_money_compact($total_due); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Advance</div>
            <div class="value" title="<?= strip_tags(mp_format_money($customer->tot_advance ?? 0)); ?>"><?= mp_format_money_compact($customer->tot_advance ?? 0); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Points</div>
            <div class="value"><?= number_format($customer->loyalty_points ?? 0); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Credit Limit</div>
            <div class="value" title="<?= ($customer->credit_limit == -1) ? 'No Limit' : strip_tags(mp_format_money($customer->credit_limit ?? 0)); ?>"><?= ($customer->credit_limit == -1) ? 'No Limit' : mp_format_money_compact($customer->credit_limit ?? 0); ?></div>
          </div>
        </div>

        <div class="card">
          <div class="details-list">
            <div class="detail-row"><span class="label">Phone</span><span class="value"><?= !empty($customer->mobile) ? '<a href="tel:' . $customer->mobile . '">' . $customer->mobile . '</a>' : '-'; ?></span></div>
            <div class="detail-row"><span class="label">Email</span><span class="value"><?= !empty($customer->email) ? '<a href="mailto:' . $customer->email . '">' . $customer->email . '</a>' : '-'; ?></span></div>
            <div class="detail-row"><span class="label">Store Credit</span><span class="value" style="color:var(--mp-warning);" title="<?= strip_tags(mp_format_money($customer->store_credit_balance ?? 0)); ?>"><?= mp_format_money_compact($customer->store_credit_balance ?? 0); ?></span></div>
            <div class="detail-row"><span class="label">Gift Card Balance</span><span class="value" style="color:var(--mp-danger);" title="<?= strip_tags(mp_format_money($customer->gift_card_balance ?? 0)); ?>"><?= mp_format_money_compact($customer->gift_card_balance ?? 0); ?></span></div>
            <div class="detail-row"><span class="label">Membership</span><span class="value"><?= !empty($active_membership) ? $active_membership->plan_name : 'None'; ?></span></div>
            <div class="detail-row"><span class="label">Treatment Notes</span><span class="value"><?= count($treatment_notes ?? []); ?></span></div>
            <div class="detail-row"><span class="label">Custom Orders</span><span class="value"><?= count($custom_orders ?? []); ?></span></div>
          </div>
        </div>

        <?php if(permissions('customers_edit')): ?>
          <a href="<?= base_url('mobile/edit_customer/' . $customer->id); ?>" class="btn-block"><i class="fa fa-edit"></i> Edit Customer</a>
        <?php endif; ?>
        <?php if(permissions('customers_delete')): ?>
          <a href="<?= base_url('mobile/delete_customer/' . $customer->id); ?>" class="btn-block danger" onclick="return mpConfirmAction(this, 'Remove this customer? This will deactivate the record.', event, {danger: true});"><i class="fa fa-trash"></i> Remove Customer</a>
        <?php endif; ?>
      </div>

      <div id="purchases" class="tab-panel">
        <div class="card">
          <?php if(!empty($purchases)): ?>
            <?php foreach($purchases as $s): ?>
              <a href="<?= base_url('mobile/sales_invoice/' . $s->id); ?>" class="list-item">
                <div class="left">
                  <div class="name"><?= $s->sales_code; ?></div>
                  <div class="desc"><?= !empty($s->sales_date) ? show_date($s->sales_date) : 'N/A'; ?></div>
                </div>
                <div class="right">
                  <div class="amount"><?= mp_format_money($s->grand_total); ?></div>
                  <div class="desc"><?= mp_format_money($s->grand_total - $s->paid_amount); ?> due</div>
                  <span class="status <?= $s->payment_status; ?>"><?= $s->payment_status; ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No purchase records found</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="statements" class="tab-panel">
        <a href="<?= base_url('mobile/statement/' . $customer->id); ?>" class="btn-block" style="margin-bottom:12px;"><i class="fa fa-file-text-o"></i> View Full Statement</a>
        <div class="stats-grid" style="margin-bottom:12px;">
          <div class="stat-card">
            <div class="label">Opening</div>
            <div class="value due" title="<?= strip_tags(mp_format_money($opening)); ?>"><?= mp_format_money_compact($opening); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Sales</div>
            <div class="value due" title="<?= strip_tags(mp_format_money($statement_summary['total_sales'])); ?>"><?= mp_format_money_compact($statement_summary['total_sales']); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Paid</div>
            <div class="value" style="color:var(--mp-success);" title="<?= strip_tags(mp_format_money($statement_summary['total_payments'])); ?>"><?= mp_format_money_compact($statement_summary['total_payments']); ?></div>
          </div>
          <div class="stat-card">
            <div class="label">Balance</div>
            <div class="value due" title="<?= strip_tags(mp_format_money($statement_summary['closing_balance'])); ?>"><?= mp_format_money_compact($statement_summary['closing_balance']); ?></div>
          </div>
        </div>
        <div class="card">
          <?php if(!empty($statement)): ?>
            <?php foreach(array_slice($statement, -5) as $row): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $row['description']; ?> · <?= $row['reference']; ?></div>
                  <div class="desc"><?= !empty($row['date']) ? show_date($row['date']) : '-'; ?> · <?= $row['type']; ?></div>
                </div>
                <div class="right">
                  <div class="amount" style="font-weight:700;"><?= mp_format_money($row['balance']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No statement records</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="giftcards" class="tab-panel">
        <div class="card">
          <?php if(!empty($gift_cards)): ?>
            <?php foreach($gift_cards as $g): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $g->card_number; ?></div>
                  <div class="desc">Bal: <?= mp_format_money($g->current_balance); ?> · Exp: <?= !empty($g->expiry_date) ? show_date($g->expiry_date) : 'Never'; ?></div>
                </div>
                <div class="right">
                  <span class="badge <?= $g->status == 'active' ? 'active' : 'inactive'; ?>"><?= ucfirst($g->status); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No gift cards found</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="storecredit" class="tab-panel">
        <div class="card">
          <?php if(!empty($store_credits)): ?>
            <?php foreach($store_credits as $sc): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $sc->credit_code; ?></div>
                  <div class="desc">Bal: <?= mp_format_money($sc->balance); ?> · Exp: <?= !empty($sc->expiry_date) ? show_date($sc->expiry_date) : 'Never'; ?></div>
                </div>
                <div class="right">
                  <span class="badge <?= $sc->status == 'active' ? 'active' : 'inactive'; ?>"><?= ucfirst($sc->status); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No store credit found</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="coupons" class="tab-panel">
        <div class="card">
          <?php if(!empty($coupons)): ?>
            <?php foreach($coupons as $c): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $c->code; ?></div>
                  <div class="desc"><?= ucfirst($c->type); ?> · Exp: <?= !empty($c->expire_date) ? show_date($c->expire_date) : 'Never'; ?></div>
                </div>
                <div class="right">
                  <div class="amount"><?= $c->type == 'percentage' ? $c->value . '%' : mp_format_money($c->value); ?></div>
                  <span class="badge <?= $c->status == 1 ? 'active' : 'inactive'; ?>"><?= $c->status == 1 ? 'Active' : 'Expired'; ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No coupons found</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="memberships" class="tab-panel">
        <div class="card">
          <?php if(!empty($memberships)): ?>
            <?php foreach($memberships as $m): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $m->plan_name; ?></div>
                  <div class="desc"><?= show_date($m->start_date); ?> - <?= show_date($m->end_date); ?></div>
                </div>
                <div class="right">
                  <span class="badge <?= $m->status == 'active' ? 'active' : 'inactive'; ?>"><?= ucfirst($m->status); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No membership records</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="services" class="tab-panel">
        <div class="card">
          <?php if(!empty($service_history)): ?>
            <?php foreach($service_history as $sh): ?>
              <a href="<?= base_url('mobile/sales_invoice/' . $sh->sales_id); ?>" class="list-item">
                <div class="left">
                  <div class="name"><?= $sh->sales_code; ?></div>
                  <div class="desc"><?= $sh->items_list ?: '-'; ?> · <?= str_replace('_', ' ', $sh->status); ?></div>
                </div>
                <div class="right">
                  <div class="amount"><?= mp_format_money($sh->grand_total); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No service history</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="treatment" class="tab-panel">
        <button type="button" class="add-btn" data-form="treatmentForm"><i class="fa fa-plus"></i> Add Treatment Note</button>
        <form action="<?= base_url('mobile/save_treatment_note/' . $customer->id); ?>" method="post" id="treatmentForm" class="add-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group"><label>Date</label><input type="date" name="treatment_date" value="<?= date('Y-m-d'); ?>"></div>
          <div class="form-group"><label>Service</label><input type="text" name="service_type" placeholder="Service type" required></div>
          <div class="form-group"><label>Staff</label><input type="text" name="staff_name" placeholder="Staff name"></div>
          <div class="form-group"><label>Notes</label><textarea name="notes" placeholder="Treatment notes"></textarea></div>
          <div class="form-group"><label>Products Used</label><input type="text" name="products_used" placeholder="Products used"></div>
          <button type="submit" class="btn"><i class="fa fa-save"></i> Save Note</button>
        </form>
        <div class="card">
          <?php if(!empty($treatment_notes)): ?>
            <?php foreach($treatment_notes as $tn): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $tn->service_type; ?></div>
                  <div class="desc"><?= !empty($tn->treatment_date) ? show_date($tn->treatment_date) : 'N/A'; ?> · <?= $tn->staff_name ?? '-'; ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No treatment notes</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="medical" class="tab-panel">
        <?php if(!empty($medical_allergies)): ?>
          <div class="card" style="border-color:#FECACA; background:#FEF2F2;">
            <div class="section-title" style="color:#991B1B;"><i class="fa fa-exclamation-triangle"></i> Known Allergies</div>
            <?php foreach($medical_allergies as $al): ?>
              <div class="list-item" style="border-bottom:1px solid #FECACA;">
                <div class="left">
                  <div class="name"><?= $al->allergies_flagged; ?></div>
                  <div class="desc">Flagged <?= !empty($al->note_date) ? show_date($al->note_date) : 'N/A'; ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <button type="button" class="add-btn" data-form="medicalForm"><i class="fa fa-plus"></i> Add Medical Note</button>
        <form action="<?= base_url('mobile/save_medical_note/' . $customer->id); ?>" method="post" id="medicalForm" class="add-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group"><label>Date</label><input type="date" name="note_date" value="<?= date('Y-m-d'); ?>"></div>
          <div class="form-group"><label>Doctor</label><input type="text" name="prescribing_doctor" placeholder="Prescribing doctor"></div>
          <div class="form-group"><label>Diagnosis</label><input type="text" name="diagnosis" placeholder="Diagnosis" required></div>
          <div class="form-group"><label>Allergies Flagged</label><input type="text" name="allergies_flagged" placeholder="Any allergies?"></div>
          <div class="form-group"><label>Notes</label><textarea name="notes" placeholder="Medical notes"></textarea></div>
          <div class="form-group"><label>Refills Remaining</label><input type="number" name="refills_remaining" value="0"></div>
          <div class="form-group"><label>Next Refill Date</label><input type="date" name="next_refill_date"></div>
          <button type="submit" class="btn"><i class="fa fa-save"></i> Save Note</button>
        </form>
        <div class="card">
          <?php if(!empty($medical_notes)): ?>
            <?php foreach($medical_notes as $mn): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= $mn->diagnosis ?: '-'; ?></div>
                  <div class="desc"><?= !empty($mn->note_date) ? show_date($mn->note_date) : 'N/A'; ?> · Dr. <?= $mn->prescribing_doctor ?? '-'; ?></div>
                  <?php if($mn->allergies_flagged): ?><span class="badge" style="background:#FECACA; color:#991B1B;"><i class="fa fa-warning"></i> <?= $mn->allergies_flagged; ?></span><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No medical notes</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="custom" class="tab-panel">
        <button type="button" class="add-btn" data-form="customForm"><i class="fa fa-plus"></i> New Custom Order</button>
        <form action="<?= base_url('mobile/save_custom_order/' . $customer->id); ?>" method="post" id="customForm" class="add-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group"><label>Item / Description</label><input type="text" name="item_name" placeholder="Item / description" required></div>
          <div class="form-group"><label>Status</label>
            <select name="status">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="ready">Ready</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="<?= date('Y-m-d'); ?>"></div>
          <div class="form-group"><label>Total Amount</label><input type="number" step="0.01" name="total_amount" value="0" required></div>
          <div class="form-group"><label>Deposit Required</label><input type="number" step="0.01" name="deposit_amount" value="0"></div>
          <div class="form-group"><label>Deposit Paid</label><input type="number" step="0.01" name="deposit_paid" value="0"></div>
          <button type="submit" class="btn"><i class="fa fa-save"></i> Save Order</button>
        </form>
        <div class="card">
          <?php if(!empty($custom_orders)): ?>
            <?php foreach($custom_orders as $co): ?>
              <div class="list-item">
                <div class="left">
                  <div class="name"><?= !empty($co->order_code) ? $co->order_code : ($co->order_number ?? '#'); ?></div>
                  <div class="desc"><?= !empty($co->due_date) ? show_date($co->due_date) : 'N/A'; ?> · <?= $co->status ?? '-'; ?></div>
                </div>
                <div class="right">
                  <div class="amount"><?= !empty($co->total_amount) ? mp_format_money($co->total_amount) : '-'; ?></div>
                  <div class="desc"><?= mp_format_money($co->deposit_paid ?? 0); ?> / <?= mp_format_money($co->deposit_amount ?? 0); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No custom orders</div>
          <?php endif; ?>
        </div>
      </div>

      <div id="notes" class="tab-panel">
        <form action="<?= base_url('mobile/save_customer_notes/' . $customer->id); ?>" method="post">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group" style="margin-bottom:12px;">
            <textarea name="notes" rows="6" class="notes-box" style="width:100%; border:none; resize:vertical;"><?= htmlspecialchars($customer->notes ?? ''); ?></textarea>
          </div>
          <button type="submit" class="btn-block"><i class="fa fa-save"></i> Save Notes</button>
        </form>
      </div>

      <div id="idcard" class="tab-panel">
        <div id="idCardSection">
          <div class="id-card" id="idCardPreview">
            <div class="id-card-body">
              <div class="id-card-name"><?= $customer->customer_name; ?></div>
              <div class="id-card-phone"><?= $customer->mobile; ?></div>
              <div class="id-card-id">ID: <?= str_pad($customer->id, 6, '0', STR_PAD_LEFT); ?></div>
              <div class="id-card-barcode">
                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?= $customer->id; ?>&scale=2&height=8" alt="barcode">
              </div>
            </div>
            <div class="id-card-brand"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
            <div class="id-card-footer">
              <div><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
              <div><?= date('M Y'); ?></div>
            </div>
          </div>
          <div class="id-card-actions">
            <button type="button" class="print" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
            <a class="png" href="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?= $customer->id; ?>&scale=3&height=10" download="id-card-<?= str_pad($customer->id, 6, '0', STR_PAD_LEFT); ?>.png"><i class="fa fa-download"></i> PNG</a>
          </div>
        </div>
      </div>

      <div id="payplan" class="tab-panel">
        <?php if(!empty($payplans)): ?>
          <?php foreach($payplans as $plan): ?>
            <div class="payplan-card">
              <div class="plan-head">
                <span class="plan-code"><?= $plan->plan_code; ?></span>
                <span class="badge active"><?= ucfirst($plan->status); ?></span>
              </div>
              <div class="plan-meta">
                <?= $plan->installment_count; ?> <?= $plan->frequency; ?> installments of <?= mp_format_money($plan->installment_amount); ?> · Next due <?= show_date($plan->first_due_date); ?>
              </div>
              <div class="detail-row">
                <span class="label">Total</span><span class="value"><?= mp_format_money($plan->total_amount); ?></span>
              </div>
              <div class="detail-row">
                <span class="label">Paid</span><span class="value" style="color:var(--mp-success);"><?= mp_format_money($plan->total_paid); ?></span>
              </div>
              <div class="detail-row">
                <span class="label">Balance</span><span class="value due" style="color:var(--mp-danger);"><?= mp_format_money($plan->total_amount - $plan->total_paid); ?></span>
              </div>
              <div class="detail-row">
                <span class="label">Down Payment</span><span class="value"><?= mp_format_money($plan->down_payment_amount); ?></span>
              </div>
              <div class="plan-progress">
                <?php $pct = ($plan->total_amount > 0) ? round(($plan->total_paid / $plan->total_amount) * 100, 1) : 0; ?>
                <div class="plan-progress-fill" style="width:<?= $pct; ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No active payplans</div>
        <?php endif; ?>
      </div>
    </section>


  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function switchTab(tab){
      document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
      var btn = document.querySelector('.tab-btn[data-tab="'+tab+'"]');
      if(btn) btn.classList.add('active');
      document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
      var panel = document.getElementById(tab);
      if(panel) panel.classList.add('active');
    }
    document.querySelectorAll('.tab-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        switchTab(this.dataset.tab);
      });
    });
    document.querySelectorAll('.add-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var form = document.getElementById(this.dataset.form);
        if(form) form.classList.toggle('active');
      });
    });
    var params = new URLSearchParams(window.location.search);
    if(params.get('tab')){
      switchTab(params.get('tab'));
    }
  </script>
</body>
</html>
