<?php $this->load->view('marketing/desktop/_styles'); ?>
<style>
.mp-camp-status{font-size:11px!important;font-weight:700!important;padding:3px 8px!important;border-radius:8px!important;text-transform:uppercase!important}
.mp-camp-draft{background:rgba(217,119,6,.15)!important;color:#B45309!important}
.mp-camp-sent{background:rgba(5,150,105,.15)!important;color:#059669!important}
.mp-camp-partial{background:rgba(220,38,38,.15)!important;color:#DC2626!important}
.mp-provider-flag{font-size:12px!important;padding:6px 10px!important;border-radius:8px!important;display:inline-block!important}
.mp-prov-ok{background:rgba(5,150,105,.12)!important;color:#059669!important}
.mp-prov-off{background:rgba(220,38,38,.12)!important;color:#DC2626!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Campaigns'); ?></h2>
    <div class="mp-page-sub">One-off Email &amp; SMS campaigns to customer segments</div>
  </div>
</div>

<div class="mp-card-form" style="margin-bottom:20px!important">
  <div class="mp-card-head"><h3>New Campaign</h3></div>
  <div class="mp-card-body">
    <div style="margin-bottom:12px!important">
      <?php if($email_ready): ?>
        <span class="mp-provider-flag mp-prov-ok"><i class="fa fa-check-circle"></i> Email provider configured</span>
      <?php else: ?>
        <span class="mp-provider-flag mp-prov-off"><i class="fa fa-exclamation-circle"></i> Email not configured — email sends will fail until SMTP/provider settings are saved in Site Settings</span>
      <?php endif; ?>
      <?php if($sms_ready): ?>
        <span class="mp-provider-flag mp-prov-ok" style="margin-left:8px!important"><i class="fa fa-check-circle"></i> SMS enabled</span>
      <?php else: ?>
        <span class="mp-provider-flag mp-prov-off" style="margin-left:8px!important"><i class="fa fa-exclamation-circle"></i> SMS not enabled — SMS sends will fail until an SMS provider is configured</span>
      <?php endif; ?>
      <div class="mp-form-hint" style="margin-top:8px!important">SMS campaigns incur provider charges per message sent. Email may incur provider charges depending on your mail plan. Always use <b>Test send</b> (sends to your store email/phone only) before a full send.</div>
    </div>
    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label>Campaign name *</label>
        <input type="text" id="camp-name" class="mp-form-control" placeholder="e.g. Weekend promo — Lagos customers">
      </div>
      <div class="mp-form-group">
        <label>Audience *</label>
        <select id="camp-segment" class="mp-form-control">
          <option value="">— choose —</option>
          <optgroup label="Segments">
          <?php foreach($segments as $key => $seg): ?>
            <option value="key:<?= $key; ?>"><?= htmlspecialchars($seg[0]); ?></option>
          <?php endforeach; ?>
          </optgroup>
          <?php if(!empty($saved)): ?>
          <optgroup label="Saved segments">
          <?php foreach($saved as $s): ?>
            <option value="saved:<?= (int)$s->id; ?>"><?= htmlspecialchars($s->name); ?></option>
          <?php endforeach; ?>
          </optgroup>
          <?php endif; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label>Channel *</label>
        <select id="camp-channel" class="mp-form-control">
          <option value="email">Email</option>
          <option value="sms">SMS</option>
        </select>
      </div>
      <div class="mp-form-group" id="camp-subject-wrap">
        <label>Subject *</label>
        <input type="text" id="camp-subject" class="mp-form-control" placeholder="Subject line">
      </div>
      <div class="mp-form-group" style="grid-column:1/-1">
        <label>Message *</label>
        <textarea id="camp-message" class="mp-form-control" rows="4" placeholder="Hi {name}, ..."></textarea>
        <div class="mp-form-hint">Use {name} for the customer's name. Keep SMS under ~160 characters per segment to control cost.</div>
      </div>
    </div>
    <div class="mp-form-actions">
      <button class="mp-btn-primary" id="camp-save"><i class="fa fa-save"></i> Save draft</button>
    </div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Campaigns</h3></div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead><tr><th>Name</th><th>Channel</th><th>Audience</th><th>Status</th><th>Sent / Failed</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach($campaigns as $c):
          $stats = (isset($stats_map) && isset($stats_map[$c->id])) ? $stats_map[$c->id] : null; ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($c->name); ?></td>
            <td><?= htmlspecialchars(strtoupper($c->channel)); ?></td>
            <td><?= htmlspecialchars($c->segment_id ? ('Saved #' . $c->segment_id) : $c->segment_key); ?></td>
            <td><span class="mp-camp-status mp-camp-<?= htmlspecialchars($c->status); ?>"><?= htmlspecialchars($c->status); ?></span></td>
            <td><?= (int)$c->sent_count; ?> / <?= (int)$c->failed_count; ?></td>
            <td><?= htmlspecialchars($c->created_at); ?></td>
            <td class="mp-actions">
              <a class="mp-btn-secondary" href="<?= base_url('marketing/campaign_detail/' . (int)$c->id); ?>"><i class="fa fa-list"></i> Detail</a>
              <?php if($c->status === 'draft'): ?>
              <button class="mp-btn-secondary mp-camp-test" data-id="<?= (int)$c->id; ?>"><i class="fa fa-flask"></i> Test send</button>
              <button class="mp-btn-primary mp-camp-send" data-id="<?= (int)$c->id; ?>"><i class="fa fa-paper-plane"></i> Send</button>
              <?php elseif(in_array($c->status, ['partial', 'sending'], true)): ?>
              <button class="mp-btn-primary mp-camp-send" data-id="<?= (int)$c->id; ?>"><i class="fa fa-paper-plane"></i> Resume</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($campaigns)): ?>
          <tr><td colspan="7" class="mp-empty-state">
            <div class="mp-empty-icon"><i class="fa fa-bullhorn"></i></div>
            <h4>No campaigns yet</h4>
            <p>Create your first campaign above.</p>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
var CSRF = {name: '<?= $this->security->get_csrf_token_name(); ?>', hash: '<?= $this->security->get_csrf_hash(); ?>'};
function mpPost(url, data, cb){
  data[CSRF.name] = CSRF.hash;
  $.post(url, data, function(r){
    if(r && r.csrf_hash) CSRF.hash = r.csrf_hash;
    cb(r);
  }, 'json');
}
$('#camp-channel').on('change', function(){
  $('#camp-subject-wrap').toggle($(this).val() === 'email');
}).trigger('change');
$('#camp-save').on('click', function(){
  var sel = $('#camp-segment').val() || '';
  var data = {
    name: $('#camp-name').val(),
    channel: $('#camp-channel').val(),
    subject: $('#camp-subject').val(),
    message: $('#camp-message').val()
  };
  if(sel.indexOf('key:') === 0) data.segment_key = sel.substr(4);
  else if(sel.indexOf('saved:') === 0){ data.segment_id = sel.substr(6); data.segment_key = ''; }
  mpPost('<?= base_url('marketing/save_campaign'); ?>', data, function(r){
    alert(r.message || (r.status === 'success' ? 'Saved' : 'Failed'));
    if(r.status === 'success') location.reload();
  });
});
$('.mp-camp-test').on('click', function(){
  mpPost('<?= base_url('marketing/send_campaign'); ?>', {id: $(this).data('id'), test: 1}, function(r){
    alert(r.message || 'Done');
  });
});
$('.mp-camp-send').on('click', function(){
  var id = $(this).data('id');
  if(!confirm('Send this campaign to the whole audience? This action cannot be undone.')) return;
  mpPost('<?= base_url('marketing/send_campaign'); ?>', {id: id}, function(r){
    alert(r.message || 'Done');
    if(r.status === 'success') location.reload();
  });
});
$(".marketing-campaigns-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
