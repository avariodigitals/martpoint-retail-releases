<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Private feedback</h3>
  <p class="muted">This goes to the clinic team privately — it is never published automatically.</p>
  <form method="post" action="<?= site_url('portal/feedback') ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <label>Overall rating</label>
    <div style="display:flex;gap:8px" id="stars">
      <?php for($i=1;$i<=5;$i++): ?>
      <label style="font-size:22px;margin:0;cursor:pointer"><input type="radio" name="rating" value="<?= $i ?>" style="width:auto" <?= $i===5?'checked':'' ?>> <?= $i ?>★</label>
      <?php endfor; ?>
    </div>
    <label>About (optional)</label>
    <input name="ref_type" value="encounter" hidden>
    <input name="ref_id" value="0" hidden>
    <label>Comments</label>
    <textarea name="comment" rows="4" placeholder="How was your visit?"></textarea>
    <div style="margin-top:12px"><button class="btn">Send feedback</button></div>
  </form>
</div>
<div class="card">
  <h3>Happy to recommend us?</h3>
  <p class="muted">A testimonial is separate — it asks for your publication consent and is moderated before it can display. <a href="<?= site_url('portal/testimonials') ?>">Write a testimonial</a></p>
</div>
<?php $this->load->view('portal/_foot'); ?>
