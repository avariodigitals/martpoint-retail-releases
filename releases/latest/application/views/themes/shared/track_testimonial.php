<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if(!empty($can_testimonial) && !empty($order)): ?>
<section class="mp-track-review" aria-labelledby="mp-track-review-title">
  <?php if(!empty($testimonial_submitted)): ?>
    <p role="status" class="mp-track-review-success">Thank you. Your review has been submitted and is awaiting approval.</p>
  <?php else: ?>
    <h2 id="mp-track-review-title">How was your experience?</h2>
    <p class="mp-track-review-intro">Leave a review about your order. It will appear in the store’s customer experiences after approval.</p>
    <form class="mp-track-review-form" action="<?= htmlspecialchars($testimonial_post_url ?? '', ENT_QUOTES, 'UTF-8'); ?>" method="post">
      <input type="hidden" name="<?= htmlspecialchars($csrf_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($csrf_hash ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="order_code" value="<?= htmlspecialchars($order->order_code ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="customer_phone" value="<?= htmlspecialchars($order->customer_phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <label>Name<input type="text" name="customer_name" value="<?= htmlspecialchars($order->customer_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" autocomplete="name" required></label>
      <fieldset><legend>Your rating</legend><div class="mp-track-review-rating">
        <?php for($rating=5; $rating>=1; $rating--): ?>
          <label><input type="radio" name="rating" value="<?= $rating; ?>" <?= $rating===5 ? 'checked' : ''; ?>><span><?= $rating; ?> star<?= $rating===1 ? '' : 's'; ?></span></label>
        <?php endfor; ?>
      </div></fieldset>
      <label>Your review<textarea name="testimonial_text" required maxlength="2000" rows="4"></textarea></label>
      <button type="submit">Submit review</button>
      <p class="mp-track-review-message" role="status" aria-live="polite"></p>
    </form>
  <?php endif; ?>
</section>
<style>
.mp-track-review{margin:28px auto 0;padding:24px;max-width:900px;background:var(--mp-soft,#f0fdf4);border:1px solid var(--mp-border,#bbf7d0);border-radius:var(--mp-radius-sm,12px);color:var(--mp-dark,#172033)}.mp-track-review h2{margin:0 0 6px;font-size:19px}.mp-track-review-intro{margin:0 0 18px;color:var(--mp-gray,#667085);font-size:14px;line-height:1.6}.mp-track-review-form{display:grid;gap:14px}.mp-track-review-form>label{display:grid;gap:6px;font-size:13px;font-weight:600}.mp-track-review-form input[type=text],.mp-track-review-form textarea{width:100%;min-width:0;padding:11px;border:1px solid var(--mp-border,#d0d5dd);border-radius:8px;background:#fff;color:var(--mp-dark,#172033);font:inherit;font-size:14px}.mp-track-review-form textarea{resize:vertical}.mp-track-review-form fieldset{border:0;padding:0;margin:0}.mp-track-review-form legend{margin-bottom:7px;font-size:13px;font-weight:600}.mp-track-review-rating{display:flex;flex-wrap:wrap;gap:7px}.mp-track-review-rating label{cursor:pointer}.mp-track-review-rating input{position:absolute;opacity:0}.mp-track-review-rating span{display:block;padding:8px 11px;border:1px solid var(--mp-border,#d0d5dd);border-radius:999px;background:#fff;font-size:13px}.mp-track-review-rating input:checked+span{border-color:var(--mp-primary,#155eef);background:var(--mp-primary,#155eef);color:#fff}.mp-track-review-rating input:focus-visible+span{outline:2px solid var(--mp-primary,#155eef);outline-offset:2px}.mp-track-review-form button{justify-self:start;min-height:44px;padding:10px 17px;border:0;border-radius:8px;background:var(--mp-primary,#155eef);color:#fff;font:inherit;font-weight:700;cursor:pointer}.mp-track-review-form button:disabled{opacity:.65;cursor:wait}.mp-track-review-message{min-height:18px;margin:0;color:#a12622;font-size:13px}.mp-track-review-success{margin:0;color:#166534;font-weight:700;font-size:15px}
</style>
<script>
(function(){
  var forms=document.querySelectorAll('.mp-track-review-form');
  forms.forEach(function(form){
    form.addEventListener('submit',function(event){
      event.preventDefault();
      if(!form.reportValidity())return;
      var button=form.querySelector('button[type="submit"]');
      var message=form.querySelector('.mp-track-review-message');
      var csrf=form.querySelector('input[type="hidden"]');
      button.disabled=true;button.textContent='Submitting…';message.textContent='';
      fetch(form.action,{method:'POST',body:new URLSearchParams(new FormData(form)),headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(response){if(!response.ok)throw new Error('Request failed');return response.json();})
        .then(function(result){
          if(result.csrf_hash&&csrf)csrf.value=result.csrf_hash;
          if(!result.status){message.textContent=result.message||'Could not submit your review. Please try again.';button.disabled=false;button.textContent='Submit review';return;}
          var success=document.createElement('p');
          success.setAttribute('role','status');
          success.className='mp-track-review-success';
          success.textContent=result.message||'Thank you. Your review was submitted and is awaiting approval.';
          form.replaceChildren(success);
        })
        .catch(function(){message.textContent='We could not reach the store. Please try again.';button.disabled=false;button.textContent='Submit review';});
    });
  });
})();
</script>
<?php endif; ?>
