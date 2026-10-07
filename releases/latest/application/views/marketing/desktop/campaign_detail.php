<?php $this->load->view('marketing/desktop/_styles'); ?>
<style>
.mp-send-status{font-size:11px!important;font-weight:700!important;padding:2px 7px!important;border-radius:8px!important}
.mp-send-pending{background:rgba(217,119,6,.15)!important;color:#B45309!important}
.mp-send-sending{background:rgba(59,130,246,.15)!important;color:#2563EB!important}
.mp-send-sent{background:rgba(5,150,105,.15)!important;color:#059669!important}
.mp-send-failed{background:rgba(220,38,38,.15)!important;color:#DC2626!important}
.mp-send-suppressed{background:rgba(107,114,128,.15)!important;color:#6B7280!important}
.mp-camp-stats{display:flex!important;gap:16px!important;margin-bottom:16px!important}
.mp-camp-stat{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;padding:12px 18px!important}
.mp-camp-stat b{display:block!important;font-size:20px!important}
.mp-camp-stat span{font-size:12px!important;color:var(--mp-muted)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($campaign->name); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars(strtoupper($campaign->channel)); ?> ·
      <?= htmlspecialchars($campaign->segment_id ? ('Saved segment #' . $campaign->segment_id) : $campaign->segment_key); ?> ·
      Status: <?= htmlspecialchars($campaign->status); ?>
    </div>
  </div>
  <div>
    <?php if($campaign->status !== 'sent'): ?>
    <button class="mp-btn-primary" id="mp-camp-resume"><i class="fa fa-paper-plane"></i> <?= $campaign->status === 'draft' ? 'Send' : 'Resume dispatch'; ?></button>
    <?php endif; ?>
    <?php if((int)$stats['failed'] > 0): ?>
    <button class="mp-btn-secondary" id="mp-camp-retry"><i class="fa fa-refresh"></i> Retry failed (<?= (int)$stats['failed']; ?>)</button>
    <?php endif; ?>
    <a href="<?= base_url('marketing/campaigns'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Campaigns</a>
  </div>
</div>

<div class="mp-camp-stats">
  <div class="mp-camp-stat"><b><?= (int)$stats['sent']; ?></b><span>Accepted</span></div>
  <div class="mp-camp-stat"><b><?= (int)$stats['failed']; ?></b><span>Failed</span></div>
  <div class="mp-camp-stat"><b><?= (int)$stats['suppressed']; ?></b><span>Opted out</span></div>
  <div class="mp-camp-stat"><b><?= (int)$stats['pending'] + (int)$stats['sending']; ?></b><span>Queued</span></div>
</div>
<p class="mp-form-hint" style="margin-top:-8px!important;margin-bottom:16px!important">"Accepted" means the provider accepted the message — confirmed delivery requires a delivery-status integration and is not claimed here. Failed sends can be requeued with Retry; accepted rows are never re-contacted. Opted-out rows were suppressed by an unsubscribe — including opt-outs that arrived after the audience was staged.</p>
<div id="mp-detail-msg" class="mp-form-hint" style="margin-bottom:12px!important"></div>

<?php if($campaign->subject || $campaign->message): ?>
<div class="mp-card-form" style="margin-bottom:16px!important">
  <div class="mp-card-head"><h3>Message</h3></div>
  <div class="mp-card-body">
    <?php if($campaign->subject): ?><p><b>Subject:</b> <?= htmlspecialchars($campaign->subject); ?></p><?php endif; ?>
    <p style="white-space:pre-wrap"><?= htmlspecialchars($campaign->message); ?></p>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Recipients</h3></div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead><tr><th>Recipient</th><th>Name</th><th>Status</th><th>Sent at</th><th>Error</th></tr></thead>
        <tbody>
        <?php foreach($sends as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s->recipient); ?></td>
            <td><?= htmlspecialchars($s->customer_name); ?></td>
            <td><span class="mp-send-status mp-send-<?= htmlspecialchars($s->status); ?>"><?= $s->status === 'sent' ? 'accepted' : ($s->status === 'suppressed' ? 'opted out' : htmlspecialchars($s->status)); ?></span></td>
            <td><?= htmlspecialchars($s->sent_at ?: '—'); ?></td>
            <td><?= htmlspecialchars($s->error ?: ''); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($sends)): ?>
          <tr><td colspan="5" class="mp-empty-state"><h4>No recipients staged yet</h4><p>Drafts stage their audience when sent.</p></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
var CSRF = {name: '<?= $this->security->get_csrf_token_name(); ?>', hash: '<?= $this->security->get_csrf_hash(); ?>'};
function mpPost(url, data, cb){ data[CSRF.name] = CSRF.hash; $.post(url, data, function(r){ if(r && r.csrf_hash) CSRF.hash = r.csrf_hash; cb(r); }, 'json'); }
$('#mp-camp-resume').on('click', function(){
  if(!confirm('Dispatch this campaign?')) return;
  mpPost('<?= base_url('marketing/send_campaign'); ?>', {id: <?= (int)$campaign->id; ?>}, function(r){
    $('#mp-detail-msg').text(r.message || 'Done');
    if(r.status === 'success') setTimeout(function(){ location.reload(); }, 1200);
  });
});
$('#mp-camp-retry').on('click', function(){
  mpPost('<?= base_url('marketing/retry_campaign'); ?>', {id: <?= (int)$campaign->id; ?>}, function(r){
    $('#mp-detail-msg').text(r.message || 'Done');
    if(r.status === 'success') setTimeout(function(){ location.reload(); }, 1000);
  });
});
$(".marketing-campaigns-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
