<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$ws = $building_workspace ?? [];
$leadStats = $ws['lead_stats'] ?? [];
$quoteStats = $ws['quote_stats'] ?? [];
$deliveryStats = $ws['delivery_stats'] ?? [];
$storeName = (string)($ws['store_name'] ?? 'MartPoint');
$bmHour = (int)date('G');
$bmGreeting = $bmHour < 12 ? 'Good morning' : ($bmHour < 17 ? 'Good afternoon' : 'Good evening');
$bmFirstName = function_exists('mp_current_user_first_name') ? mp_current_user_first_name('team') : ($this->session->userdata('first_name') ?: 'team');
$money = function($amount){
    $CI =& get_instance();
    return $CI->currency((float)$amount);
};
$number = function($value){ return number_format((float)$value); };
$attentionCount = (int)($leadStats['new'] ?? 0) + (int)($ws['pending_purchases'] ?? 0) + (int)($deliveryStats['pending'] ?? 0) + (int)($ws['low_stock'] ?? 0);
$pipeline = (float)($quoteStats['pipeline'] ?? 0);
$maxQuote = 1.0;
foreach((array)($ws['recent_quotes'] ?? []) as $quote){ $maxQuote = max($maxQuote, (float)($quote->grand_total ?? 0)); }
$flow = [
    ['label'=>'Enquiry', 'value'=>(int)($leadStats['total'] ?? 0), 'url'=>base_url('leads')],
    ['label'=>'Survey', 'value'=>(int)($ws['survey_count'] ?? 0), 'url'=>base_url('building_workspace/surveys')],
    ['label'=>'Quotation', 'value'=>(int)($quoteStats['issued'] ?? 0), 'url'=>base_url('quotation')],
    ['label'=>'Approved', 'value'=>(int)($quoteStats['accepted'] ?? 0), 'url'=>base_url('quotation')],
    ['label'=>'Delivery', 'value'=>(int)($deliveryStats['pending'] ?? 0), 'url'=>base_url('operations/delivery_scheduling')],
];
?>
<div class="bm-workspace" id="bm-workspace">
  <div class="bm-workspace-head">
    <div>
      <div class="bm-overline">Building Materials Pro <span class="bm-live-dot"></span> Live workspace</div>
      <h1><?= htmlspecialchars($bmGreeting, ENT_QUOTES, 'UTF-8'); ?>, <?= htmlspecialchars($bmFirstName, ENT_QUOTES, 'UTF-8'); ?>.</h1>
      <p>One operating view for enquiries, projects, delivery and invoice control at <?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?>.</p>
    </div>
    <div class="bm-head-actions">
      <a class="bm-button bm-button-quiet" href="<?= base_url('online_store'); ?>"><i class="fa fa-external-link"></i> View B2B site</a>
      <?php if($CI->permissions('sales_view') || $CI->permissions('service_jobs_view') || is_store_admin()): ?><a class="bm-button bm-button-quiet" href="<?= base_url('building_projects'); ?>"><i class="fa fa-sitemap"></i> Project control</a><?php endif; ?>
      <?php if($CI->permissions('quotation_add')): ?><a class="bm-button bm-button-primary" href="<?= base_url('quotation/add'); ?>"><i class="fa fa-plus"></i> New quotation</a><?php endif; ?>
    </div>
  </div>

  <div class="bm-flow" aria-label="Commercial workflow">
    <div class="bm-flow-label">Operating flow</div>
    <?php foreach($flow as $index => $step): ?>
      <a class="bm-flow-step" href="<?= $step['url']; ?>">
        <span class="bm-flow-number"><?= $index + 1; ?></span>
        <span><strong><?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?= $number($step['value']); ?> <?= $step['label']==='Survey'?'records':'open'; ?></small></span>
        <?php if($index < count($flow) - 1): ?><i class="fa fa-angle-right bm-flow-arrow" aria-hidden="true"></i><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="bm-kpi-grid">
    <a class="bm-kpi bm-kpi-blue" href="<?= base_url('building_projects'); ?>">
      <span class="bm-kpi-icon"><i class="fa fa-briefcase"></i></span>
      <span class="bm-kpi-label">Active projects</span>
      <strong><?= $number($ws['active_projects'] ?? 0); ?></strong>
      <small>Open project records <i class="fa fa-arrow-up"></i></small>
    </a>
    <a class="bm-kpi bm-kpi-violet" href="<?= base_url('quotation'); ?>">
      <span class="bm-kpi-icon"><i class="fa fa-line-chart"></i></span>
      <span class="bm-kpi-label">Pipeline value</span>
      <strong><?= $money($pipeline); ?></strong>
      <small><?= $number($quoteStats['issued'] ?? 0); ?> quotations awaiting a decision</small>
    </a>
    <a class="bm-kpi bm-kpi-orange" href="<?= base_url('operations/delivery_scheduling'); ?>">
      <span class="bm-kpi-icon"><i class="fa fa-truck"></i></span>
      <span class="bm-kpi-label">Logistics attention</span>
      <strong><?= $number(($deliveryStats['today'] ?? 0) + ($ws['pending_purchases'] ?? 0)); ?></strong>
      <small><?= $number($deliveryStats['today'] ?? 0); ?> deliveries today · <?= $number($ws['pending_purchases'] ?? 0); ?> procurement</small>
    </a>
    <a class="bm-kpi bm-kpi-red" href="<?= base_url('building_workspace/actions'); ?>">
      <span class="bm-kpi-icon"><i class="fa fa-bell-o"></i></span>
      <span class="bm-kpi-label">Invoice &amp; action queue</span>
      <strong><?= $number($attentionCount); ?></strong>
      <small><?= $money($ws['outstanding'] ?? 0); ?> in outstanding balances</small>
    </a>
  </div>

  <div class="bm-grid bm-grid-main">
    <section class="bm-panel bm-panel-wide">
      <div class="bm-panel-head">
        <div><span class="bm-overline">Commercial health</span><h2>Workstream momentum</h2></div>
        <a href="<?= base_url('reports/profit_loss'); ?>" class="bm-panel-link">Business insights <i class="fa fa-arrow-right"></i></a>
      </div>
      <div class="bm-momentum">
        <div class="bm-momentum-axis"><span>Volume</span><span>Value</span></div>
        <div class="bm-momentum-bars">
          <?php
          $bars = [
              ['label'=>'Enquiries', 'value'=>(int)($leadStats['total'] ?? 0), 'color'=>'blue'],
              ['label'=>'Quotations', 'value'=>(int)($quoteStats['issued'] ?? 0), 'color'=>'violet'],
              ['label'=>'Approved', 'value'=>(int)($quoteStats['accepted'] ?? 0), 'color'=>'green'],
              ['label'=>'Fulfilment', 'value'=>(int)($deliveryStats['pending'] ?? 0), 'color'=>'orange'],
          ];
          $maxBar = 1;
          foreach($bars as $bar){ $maxBar = max($maxBar, $bar['value']); }
          foreach($bars as $bar): $height = max(14, round(($bar['value'] / $maxBar) * 100));
          ?>
            <a class="bm-bar-column" href="<?= $bar['label'] === 'Enquiries' ? base_url('leads') : base_url('quotation'); ?>">
              <span class="bm-bar-value"><?= $number($bar['value']); ?></span><span class="bm-bar <?= $bar['color']; ?>" style="height:<?= $height; ?>%"></span><small><?= $bar['label']; ?></small>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="bm-momentum-note"><span class="bm-legend blue"></span> Current operating volume <span class="bm-legend pale"></span> Across the connected commercial flow</div>
      </div>
    </section>

    <section class="bm-panel">
      <div class="bm-panel-head"><div><span class="bm-overline">Management queue</span><h2>Needs attention</h2></div><a class="bm-panel-link" href="<?= base_url('building_workspace/actions'); ?>">Action Centre <i class="fa fa-arrow-right"></i></a></div>
      <div class="bm-attention-list" id="bm-action-centre">
        <a class="bm-attention-item" href="<?= base_url('leads?status=new'); ?>"><span class="bm-attention-icon red"><i class="fa fa-user-plus"></i></span><span><strong><?= $number($leadStats['new'] ?? 0); ?> new enquiries</strong><small>Review scope and assign next action</small></span><i class="fa fa-angle-right"></i></a>
        <a class="bm-attention-item" href="<?= base_url('purchase'); ?>"><span class="bm-attention-icon orange"><i class="fa fa-cubes"></i></span><span><strong><?= $number($ws['pending_purchases'] ?? 0); ?> procurement items</strong><small>Ordered or partially received</small></span><i class="fa fa-angle-right"></i></a>
        <a class="bm-attention-item" href="<?= base_url('items'); ?>"><span class="bm-attention-icon yellow"><i class="fa fa-exclamation-triangle"></i></span><span><strong><?= $number($ws['low_stock'] ?? 0); ?> materials below threshold</strong><small>Review depot availability</small></span><i class="fa fa-angle-right"></i></a>
        <a class="bm-attention-item" href="<?= base_url('sales'); ?>"><span class="bm-attention-icon blue"><i class="fa fa-credit-card"></i></span><span><strong><?= $money($ws['outstanding'] ?? 0); ?> outstanding</strong><small>Open invoice balances</small></span><i class="fa fa-angle-right"></i></a>
      </div>
    </section>
  </div>

  <div class="bm-grid bm-grid-bottom">
    <section class="bm-panel">
      <div class="bm-panel-head"><div><span class="bm-overline">Quotation desk</span><h2>Latest proposals</h2></div><a href="<?= base_url('quotation'); ?>" class="bm-panel-link">Open desk <i class="fa fa-arrow-right"></i></a></div>
      <div class="bm-quote-list">
        <?php if(!empty($ws['recent_quotes'])): foreach($ws['recent_quotes'] as $quote):
          $status = (string)($quote->lifecycle_status ?? $quote->quotation_status ?? 'Issued');
          $statusClass = in_array($status, ['accepted','Converted'], true) ? 'green' : ($status === 'Draft' ? 'grey' : 'blue');
        ?>
          <a class="bm-quote-row" href="<?= base_url('quotation'); ?>"><span class="bm-quote-code"><?= htmlspecialchars((string)($quote->quotation_code ?: '#' . $quote->id), ENT_QUOTES, 'UTF-8'); ?></span><span class="bm-quote-client"><?= htmlspecialchars((string)($quote->customer_name ?: 'Client'), ENT_QUOTES, 'UTF-8'); ?></span><span class="bm-quote-total"><?= $money($quote->grand_total ?? 0); ?></span><span class="bm-status <?= $statusClass; ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span></a>
        <?php endforeach; else: ?><div class="bm-empty">Your latest quotations will appear here as the commercial desk gets moving.</div><?php endif; ?>
      </div>
    </section>

    <section class="bm-panel">
      <div class="bm-panel-head"><div><span class="bm-overline">Operational pulse</span><h2>Recent activity</h2></div><a href="<?= base_url('leads'); ?>" class="bm-panel-link">View CRM <i class="fa fa-arrow-right"></i></a></div>
      <div class="bm-activity-list">
        <?php if(!empty($ws['activity'])): foreach($ws['activity'] as $item): $kind = $item['kind'] ?? 'other'; ?>
          <a class="bm-activity-row" href="<?= $item['url']; ?>"><span class="bm-activity-dot <?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-<?= $kind === 'lead' ? 'user' : ($kind === 'quote' ? 'file-text-o' : 'truck'); ?>"></i></span><span><strong><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?= htmlspecialchars((string)$item['detail'], ENT_QUOTES, 'UTF-8'); ?></small></span><i class="fa fa-angle-right"></i></a>
        <?php endforeach; else: ?><div class="bm-empty">No activity yet. Enquiries and quotations will land here.</div><?php endif; ?>
      </div>
    </section>

    <section class="bm-panel bm-panel-timeline">
      <div class="bm-panel-head"><div><span class="bm-overline">Today's operations</span><h2>What happens next</h2></div><span class="bm-date"><?= date('D, j M'); ?></span></div>
      <div class="bm-timeline">
        <div class="bm-timeline-item"><span class="bm-timeline-dot green"></span><span><strong><?= $number($deliveryStats['today'] ?? 0); ?> delivery runs</strong><small>Ready for dispatch or proof of delivery</small></span></div>
        <div class="bm-timeline-item"><span class="bm-timeline-dot blue"></span><span><strong><?= $number($quoteStats['issued'] ?? 0); ?> proposals to move</strong><small>Follow up, revise or request approval</small></span></div>
        <div class="bm-timeline-item"><span class="bm-timeline-dot orange"></span><span><strong><?= $number($leadStats['qualified'] ?? 0); ?> qualified opportunities</strong><small>Convert the next best-fit enquiry</small></span></div>
      </div>
    </section>
  </div>
</div>
