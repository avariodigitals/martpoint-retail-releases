<?php
$w = $pharmacy_workspace ?? [];
$CI =& get_instance();
$storeName = $this->session->userdata('store_name') ?: 'Pharmacy';
$money = function($amount) use ($CI){ return $CI->currency((float)$amount); };
$pharma_icons = [
  'plus' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
  'prescription' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5M10 13h4M12 11v4"/></svg>',
  'chart' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 5-6"/></svg>',
  'alert' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 17H3z"/><path d="M12 9v4M12 17h.01"/></svg>',
  'expiry' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M12 13v3l2 1"/></svg>',
  'clock' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
  'orders' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1 12H6zM8 8a4 4 0 0 1 8 0"/></svg>',
  'transfer' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h13M14 4l3 3-3 3M20 17H7M10 14l-3 3 3 3"/></svg>',
  'patient' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5"/></svg>',
  'receipt' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>',
  'inbox' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16l2 10v4H2v-4z"/><path d="M2 15h5l2 2h6l2-2h5"/></svg>',
  'check' => '<svg class="pharma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
  'arrow' => '<svg class="pharma-icon pharma-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h13M13 6l6 6-6 6"/></svg>',
];
$pharma_icon = function($key) use ($pharma_icons){ return $pharma_icons[$key] ?? $pharma_icons['prescription']; };
?>
<div class="pharma-workspace">
  <section class="pharma-hero">
    <div>
      <div class="pharma-kicker"><span class="pharma-pulse"></span> Pharmacy operations</div>
      <h1>Good morning, keep care moving.</h1>
      <p><?= htmlspecialchars($storeName); ?> · <?= date('l, j F Y'); ?></p>
    </div>
    <div class="pharma-hero-actions">
      <a href="<?= base_url('operations/medical_note'); ?>" class="pharma-btn pharma-btn-light"><?= $pharma_icon('prescription'); ?> New prescription</a>
      <a href="<?= base_url('pos'); ?>" class="pharma-btn pharma-btn-primary"><?= $pharma_icon('plus'); ?> Start dispensing</a>
    </div>
  </section>

  <section class="pharma-kpis" aria-label="Today's pharmacy snapshot">
    <div class="pharma-kpi pharma-kpi-blue"><span class="pharma-kpi-icon"><?= $pharma_icon('chart'); ?></span><div><small>Sales today</small><strong><?= $money($w['sales_today'] ?? 0); ?></strong><em><?= (int)($w['invoices_today'] ?? 0); ?> invoices</em></div></div>
    <div class="pharma-kpi pharma-kpi-purple"><span class="pharma-kpi-icon"><?= $pharma_icon('prescription'); ?></span><div><small>Prescriptions today</small><strong><?= (int)($w['prescriptions_today'] ?? 0); ?></strong><em><?= (int)($w['pending_prescriptions'] ?? 0); ?> awaiting dispensing</em></div></div>
    <div class="pharma-kpi pharma-kpi-amber"><span class="pharma-kpi-icon"><?= $pharma_icon('alert'); ?></span><div><small>Stock attention</small><strong><?= (int)($w['low_stock'] ?? 0); ?></strong><em>medicines below reorder level</em></div></div>
    <div class="pharma-kpi pharma-kpi-red"><span class="pharma-kpi-icon"><?= $pharma_icon('expiry'); ?></span><div><small>Expiry watch</small><strong><?= (int)($w['expiring_soon'] ?? 0); ?></strong><em><?= (int)($w['expired'] ?? 0); ?> expired items</em></div></div>
  </section>

  <section class="pharma-action-grid">
    <a href="<?= base_url('operations/medical_notes'); ?>" class="pharma-action-card"><span class="pharma-action-icon blue"><?= $pharma_icon('prescription'); ?></span><span><strong>Prescription queue</strong><small>Review, dispense and track refills</small></span><?= $pharma_icon('arrow'); ?></a>
    <a href="<?= base_url('expired_items_report'); ?>" class="pharma-action-card"><span class="pharma-action-icon red"><?= $pharma_icon('clock'); ?></span><span><strong>Expiry & batch watch</strong><small><?= (int)($w['expiring_soon'] ?? 0); ?> items need attention</small></span><?= $pharma_icon('arrow'); ?></a>
    <a href="<?= base_url('online_store/orders'); ?>" class="pharma-action-card"><span class="pharma-action-icon teal"><?= $pharma_icon('orders'); ?></span><span><strong>Patient orders</strong><small><?= (int)($w['online_orders'] ?? 0); ?> online orders in progress</small></span><?= $pharma_icon('arrow'); ?></a>
    <a href="<?= base_url('stock_transfer/view'); ?>" class="pharma-action-card"><span class="pharma-action-icon violet"><?= $pharma_icon('transfer'); ?></span><span><strong>Branch transfers</strong><small><?= (int)($w['branch_count'] ?? 0); ?> active locations</small></span><?= $pharma_icon('arrow'); ?></a>
  </section>

  <section class="pharma-columns">
    <div class="pharma-panel">
      <div class="pharma-panel-head"><div><span class="pharma-eyebrow">Clinical queue</span><h2>Recent prescriptions</h2></div><a href="<?= base_url('operations/medical_notes'); ?>">View all <?= $pharma_icon('arrow'); ?></a></div>
      <?php if(empty($w['recent_notes'])): ?><div class="pharma-empty"><?= $pharma_icon('check'); ?><strong>No prescriptions waiting</strong><span>The queue is clear for now.</span></div><?php else: foreach($w['recent_notes'] as $note): ?>
        <div class="pharma-list-row"><span class="pharma-list-avatar purple"><?= $pharma_icon('patient'); ?></span><div class="pharma-list-main"><strong><?= htmlspecialchars($note->customer_name ?: 'Patient'); ?></strong><small><?= htmlspecialchars($note->diagnosis ?: 'Prescription note'); ?><?= !empty($note->prescribing_doctor) ? ' · Dr. '.htmlspecialchars($note->prescribing_doctor) : ''; ?></small></div><span class="pharma-status <?= ($note->status === 'active' ? 'pending' : 'done'); ?>"><?= htmlspecialchars(ucfirst($note->status ?: 'active')); ?></span></div>
      <?php endforeach; endif; ?>
    </div>
    <div class="pharma-panel">
      <div class="pharma-panel-head"><div><span class="pharma-eyebrow">Dispensing desk</span><h2>Latest invoices</h2></div><a href="<?= base_url('sales'); ?>">Open sales <?= $pharma_icon('arrow'); ?></a></div>
      <?php if(empty($w['recent_sales'])): ?><div class="pharma-empty"><?= $pharma_icon('inbox'); ?><strong>No invoices yet today</strong><span>Start a dispensing sale when a patient is ready.</span></div><?php else: foreach($w['recent_sales'] as $sale): ?>
        <div class="pharma-list-row"><span class="pharma-list-avatar teal"><?= $pharma_icon('receipt'); ?></span><div class="pharma-list-main"><strong><?= htmlspecialchars($sale->customer_name ?: 'Walk-in patient'); ?></strong><small><?= htmlspecialchars($sale->sales_code ?: 'Invoice'); ?> · <?= !empty($sale->created_time) ? date('H:i', strtotime($sale->created_time)) : ''; ?></small></div><strong class="pharma-amount"><?= $money($sale->grand_total ?? 0); ?></strong></div>
      <?php endforeach; endif; ?>
    </div>
  </section>
</div>
