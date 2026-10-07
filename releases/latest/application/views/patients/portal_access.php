<?php $this->load->view('admin/desktop/_styles');
$pol = array(); foreach($policies as $k => $row){ $pol[$k] = $row->policy_value; } ?>
<style>
.pa-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
.pa-card h4{margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted)}
.pa-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-block}
.pa-badge.active{background:#D1FAE5;color:#065F46}.pa-badge.invited{background:#DBEAFE;color:#1D4ED8}
.pa-badge.revoked{background:#FEE2E2;color:#B91C1C}.pa-badge.pending{background:#FEF3C7;color:#B45309}
.pa-link{word-break:break-all;font-family:monospace;font-size:12px;background:#F1F5F9;border:1px solid var(--mp-border);border-radius:8px;padding:8px 10px;margin-top:8px}
.pa-scope{font-size:11px;background:#F1F5F9;border-radius:6px;padding:2px 8px;margin-right:4px;display:inline-block}
table.pa{width:100%;border-collapse:collapse;font-size:13px}
table.pa th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--mp-muted);padding:6px 4px;border-bottom:1px solid var(--mp-border)}
table.pa td{padding:8px 4px;border-bottom:1px solid var(--mp-border)}
.pa-btn{background:var(--mp-primary,#2563EB);color:#fff;border:0;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer}
.pa-btn.ghost{background:#F1F5F9;color:#1F2937}.pa-btn.danger{background:#B91C1C}
.pa-in{padding:8px 10px;border:1px solid var(--mp-border);border-radius:8px;font-size:13px}
label.chk{font-size:13px;margin-right:14px;display:inline-block}
</style>

<div class="mp-page-head">
  <div>
    <h2>Patient Portal Access</h2>
    <div class="mp-page-sub"><a href="<?= base_url('patients/profile/'.$patient->id); ?>">&larr; <?= htmlspecialchars($patient->customer_name); ?></a></div>
  </div>
</div>

<?php if($invite): ?>
<div class="pa-card" style="border-color:#2563EB">
  <h4>Patient invitation issued</h4>
  <p style="font-size:13px;margin:0">Send to <b><?= htmlspecialchars($invite['identity']); ?></b> — expires in <?= (int)($pol['invite_expiry_hours'] ?? 72); ?>h and works once.</p>
  <div class="pa-link"><?= htmlspecialchars($invite['invite_url']); ?></div>
</div>
<?php endif; ?>
<?php if($proxy_invite): ?>
<div class="pa-card" style="border-color:#2563EB">
  <h4>Caregiver invitation issued</h4>
  <p style="font-size:13px;margin:0">Share this one-time link — the caregiver sees only the scopes granted below.</p>
  <div class="pa-link"><?= htmlspecialchars($proxy_invite['invite_url']); ?></div>
</div>
<?php endif; ?>

<div class="pa-card">
  <h4>Patient account</h4>
  <?php if($account): ?>
    <table class="pa">
      <tr><th>Identity</th><th>Status</th><th>Verified</th><th>Last sign-in</th><th></th></tr>
      <tr>
        <td><?= htmlspecialchars($account->identity); ?></td>
        <td><span class="pa-badge <?= $account->status; ?>"><?= htmlspecialchars(mp_code_label($account->status)); ?></span></td>
        <td><?= $account->verified_at ? date('M j, Y g:ia', strtotime($account->verified_at)) : '—'; ?></td>
        <td><?= $account->last_login_at ? date('M j, Y g:ia', strtotime($account->last_login_at)) : 'never'; ?></td>
        <td>
          <?php if($account->status !== 'revoked'): ?>
            <button class="pa-btn danger" onclick="post('patients/portal_revoke/<?= (int)$patient->id; ?>', {})">Revoke</button>
          <?php else: ?>
            <button class="pa-btn ghost" onclick="post('patients/portal_reinstate/<?= (int)$patient->id; ?>', {})">Reinstate</button>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  <?php else: ?>
    <p style="font-size:13px;color:var(--mp-muted)">No portal account. Send an invitation — imported patients are only invited when staff choose to.</p>
  <?php endif; ?>
  <?php if(!$account || $account->status !== 'revoked'): ?>
  <div style="margin-top:10px"><button class="pa-btn" onclick="post('patients/portal_invite/<?= (int)$patient->id; ?>', {})"><?= $account ? 'Re-issue invitation' : 'Invite patient'; ?></button></div>
  <?php endif; ?>
</div>

<div class="pa-card">
  <h4>Caregivers / proxies</h4>
  <?php if($proxies): ?>
  <table class="pa">
    <tr><th>Caregiver</th><th>Scopes</th><th>Status</th><th>Verified</th><th></th></tr>
    <?php foreach($proxies as $px): ?>
    <tr>
      <td><?= htmlspecialchars($px->name ?: $px->identity); ?><br><span style="font-size:11px;color:var(--mp-muted)"><?= htmlspecialchars($px->identity); ?><?= $px->relationship ? ' · '.htmlspecialchars($px->relationship) : ''; ?></span></td>
      <td><?php foreach(explode(',', $px->scope_csv) as $s): if($s==='') continue; ?><span class="pa-scope"><?= htmlspecialchars($s); ?></span><?php endforeach; ?></td>
      <td><span class="pa-badge <?= $px->status==='active'?'active':($px->status==='revoked'?'revoked':'pending') ?>"><?= htmlspecialchars(mp_code_label($px->status)); ?></span></td>
      <td><?= $px->verified_at ? date('M j, Y', strtotime($px->verified_at)) : 'pending'; ?></td>
      <td><?php if($px->status !== 'revoked'): ?><button class="pa-btn danger" onclick="post('patients/proxy_revoke/<?= (int)$px->id; ?>', {})">Revoke</button><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?><p style="font-size:13px;color:var(--mp-muted)">No caregivers linked.</p><?php endif; ?>
  <form id="pxForm" style="margin-top:12px">
    <input class="pa-in" id="pxName" placeholder="caregiver name" style="max-width:240px">
    <input class="pa-in" id="pxIdentity" placeholder="caregiver email or phone" style="max-width:240px">
    <input class="pa-in" id="pxRel" placeholder="relationship (e.g. spouse, parent)" style="max-width:200px"><br><br>
    <label class="chk"><input type="checkbox" name="scopes[]" value="appointments" checked> appointments</label>
    <label class="chk"><input type="checkbox" name="scopes[]" value="progress" checked> progress</label>
    <label class="chk"><input type="checkbox" name="scopes[]" value="bills"> bills &amp; receipts</label>
    <label class="chk"><input type="checkbox" name="scopes[]" value="funds"> funds</label>
    <label class="chk"><input type="checkbox" name="scopes[]" value="documents"> documents</label>
    <label class="chk"><input type="checkbox" name="scopes[]" value="feedback"> feedback</label>
    <br><br><button class="pa-btn" type="submit">Invite caregiver</button>
  </form>
</div>

<div class="pa-card">
  <h4>Portal policies (this store)</h4>
  <form id="polForm">
    <label style="font-size:12px;color:var(--mp-muted)">Invitation expiry (hours)</label><br>
    <input class="pa-in" name="invite_expiry_hours" type="number" min="1" value="<?= (int)($pol['invite_expiry_hours'] ?? 72); ?>" style="width:120px"><br><br>
    <label style="font-size:12px;color:var(--mp-muted)">Feedback cooldown (days between requests)</label><br>
    <input class="pa-in" name="feedback_cooldown_days" type="number" min="0" value="<?= (int)($pol['feedback_cooldown_days'] ?? 7); ?>" style="width:120px"><br><br>
    <label style="font-size:12px;color:var(--mp-muted)">Appointment reminder (hours before)</label><br>
    <input class="pa-in" name="reminder_hours_before" type="number" min="1" value="<?= (int)($pol['reminder_hours_before'] ?? 24); ?>" style="width:120px"><br><br>
    <label class="chk"><input type="checkbox" name="portal_enabled" value="1" <?= ($pol['portal_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> portal enabled</label>
    <label class="chk"><input type="checkbox" name="feedback_enabled" value="1" <?= ($pol['feedback_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> feedback requests enabled</label>
    <label class="chk"><input type="checkbox" name="testimonials_enabled" value="1" <?= ($pol['testimonials_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> testimonials enabled</label>
    <br><br><button class="pa-btn" type="submit">Save policies</button>
  </form>
</div>

<script src="<?= base_url('theme/assets/js/jquery.min.js'); ?>"></script>
<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
function post(url, d){ $.post('<?= base_url(); ?>'+url, Object.assign(d||{}, CSRF), function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json'); }
$('#pxForm').on('submit', function(e){
  e.preventDefault();
  var scopes = $('#pxForm input[name="scopes[]"]:checked').map(function(){ return $(this).val(); }).get();
  var d = {identity: $('#pxIdentity').val(), name: $('#pxName').val(), relationship: $('#pxRel').val()};
  scopes.forEach(function(s,i){ d['scopes['+i+']'] = s; });
  post('patients/proxy_invite/<?= (int)$patient->id; ?>', d);
});
$('#polForm').on('submit', function(e){ e.preventDefault(); var d={}; $(this).serializeArray().forEach(function(i){d[i.name]=i.value;}); post('patients/portal_policy_save', d); });
</script>
