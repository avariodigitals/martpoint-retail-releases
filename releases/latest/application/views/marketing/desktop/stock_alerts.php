<?php $this->load->view('marketing/desktop/_styles'); ?>
<style>
.mp-alert-pill{font-size:11px!important;font-weight:700!important;padding:3px 8px!important;border-radius:8px!important}
.mp-alert-pending{background:rgba(37,99,235,.12)!important;color:#2563EB!important}
.mp-alert-done{background:rgba(5,150,105,.15)!important;color:#059669!important}
.mp-alert-unsub{background:rgba(220,38,38,.15)!important;color:#DC2626!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Back-in-Stock Alerts'); ?></h2>
    <div class="mp-page-sub">Customers who subscribed on out-of-stock storefront products — emailed automatically once stock returns</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Subscriptions</h3></div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead><tr><th>Item</th><th>Current stock</th><th>Subscriber</th><th>Status</th><th>Subscribed</th><th>Notified</th></tr></thead>
        <tbody>
        <?php foreach($alerts as $a):
          $status = $a->unsubscribed ? 'unsubscribed' : ($a->notified ? 'notified' : 'pending'); ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($a->live_name ?: $a->item_name); ?></td>
            <td><?= isset($a->stock) ? store_number_format($a->stock) : '—'; ?></td>
            <td><?= htmlspecialchars($a->email ?: $a->phone); ?></td>
            <td><span class="mp-alert-pill mp-alert-<?= $a->unsubscribed ? 'unsub' : ($a->notified ? 'done' : 'pending'); ?>"><?= $status; ?></span></td>
            <td><?= htmlspecialchars($a->created_at); ?></td>
            <td><?= htmlspecialchars($a->notified_at ?: '—'); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($alerts)): ?>
          <tr><td colspan="6" class="mp-empty-state">
            <div class="mp-empty-icon"><i class="fa fa-bell"></i></div>
            <h4>No stock alert subscriptions</h4>
            <p>Customers can subscribe on out-of-stock product pages on your storefront.</p>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>$(".marketing-stock-alerts-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
