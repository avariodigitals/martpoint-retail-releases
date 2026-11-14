<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>My testimonials</h3>
  <?php if(!$mine): ?><p class="muted">None yet.</p><?php else: ?>
  <table>
    <tr><th>Date</th><th>Shown as</th><th>Status</th><th></th></tr>
    <?php foreach($mine as $t): ?>
    <tr><td><?= htmlspecialchars(substr($t->created_at,0,10)) ?></td>
      <td><?= htmlspecialchars($t->display_mode==='anonymous' ? 'Anonymous' : ($t->display_mode==='first_name' ? 'First name' : ($t->display_name ?: 'Custom'))) ?></td>
      <td><span class="badge <?= $t->status==='approved'?'b-ok':($t->status==='withdrawn'||$t->status==='rejected'?'b-bad':'b-warn') ?>"><?= htmlspecialchars($t->status) ?></span></td>
      <td><?php if($t->status!=='withdrawn'): ?>
        <form method="post" action="<?= site_url('portal/withdraw_testimonial/'.$t->id) ?>" onsubmit="return confirm('Withdraw this testimonial? It stops displaying immediately.')">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
          <button class="btn danger" style="padding:4px 10px;font-size:12px">Withdraw</button>
        </form><?php endif; ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<div class="card">
  <h3>Write a testimonial</h3>
  <p class="muted">Separate from private feedback — it is published only after the clinic moderates it, and you can withdraw it anytime.</p>
  <form method="post" action="<?= site_url('portal/testimonials') ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <label>Your words</label>
    <textarea name="body" rows="4" required minlength="10"></textarea>
    <label>Display name</label>
    <input type="hidden" name="display_mode" id="dm" value="anonymous">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn ghost dm-opt" data-m="anonymous">Anonymous</button>
      <button type="button" class="btn ghost dm-opt" data-m="first_name">First name only</button>
      <button type="button" class="btn ghost dm-opt" data-m="custom">Custom</button>
    </div>
    <input name="display_name" id="dn" placeholder="Name to display" style="display:none;margin-top:8px">
    <label><input type="checkbox" name="publish_consent" value="1" style="width:auto"> I consent to this being published after clinic moderation.</label>
    <div style="margin-top:12px"><button class="btn">Submit for moderation</button></div>
  </form>
</div>
<script>
document.querySelectorAll('.dm-opt').forEach(function(b){b.addEventListener('click',function(){
  document.getElementById('dm').value=b.dataset.m;
  document.getElementById('dn').style.display = b.dataset.m==='custom' ? 'block' : 'none';
});});
</script>
<?php $this->load->view('portal/_foot'); ?>
