<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$reviewCount = (int)($aggregate['count'] ?? 0);
$average = $aggregate['average'] ?? null;
$filledStars = $average === null ? 0 : max(0, min(5, (int)round((float)$average)));
$h = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<section class="mp-product-reviews" aria-labelledby="mp-product-reviews-title">
  <div class="mp-product-reviews-head">
    <div>
      <h2 id="mp-product-reviews-title">Customer reviews</h2>
      <?php if($product): ?><p><?= $h($product->item_name); ?></p><?php endif; ?>
    </div>
    <div class="mp-product-reviews-score" aria-label="<?= $average === null ? 'No ratings yet' : $h(number_format((float)$average, 1) . ' out of 5'); ?>">
      <span class="mp-product-review-stars" aria-hidden="true"><?= str_repeat('&#9733;', $filledStars) . str_repeat('&#9734;', 5 - $filledStars); ?></span>
      <strong><?= $average === null ? 'New' : $h(number_format((float)$average, 1)); ?></strong>
      <span><?= $reviewCount; ?> <?= $reviewCount === 1 ? 'review' : 'reviews'; ?></span>
    </div>
  </div>

  <div class="mp-product-reviews-grid">
    <div class="mp-product-review-list">
      <?php if(empty($reviews)): ?>
        <p class="mp-product-review-empty">No approved reviews yet.</p>
      <?php else: foreach($reviews as $review): ?>
        <?php $rating = max(1, min(5, (int)$review->rating)); ?>
        <article class="mp-product-review">
          <div class="mp-product-review-meta">
            <strong><?= $h($review->reviewer_name); ?></strong>
            <?php if(!empty($review->is_verified)): ?><span class="mp-product-review-verified">Verified purchase</span><?php endif; ?>
            <span class="mp-product-review-stars" aria-label="<?= $rating; ?> out of 5"><?= str_repeat('&#9733;', $rating) . str_repeat('&#9734;', 5 - $rating); ?></span>
          </div>
          <?php if(!empty($review->title)): ?><h3><?= $h($review->title); ?></h3><?php endif; ?>
          <?php if(!empty($review->review_text)): ?><p><?= nl2br($h($review->review_text)); ?></p><?php endif; ?>
          <time datetime="<?= $h($review->created_at); ?>"><?= $h(date('M j, Y', strtotime($review->created_at))); ?></time>
        </article>
      <?php endforeach; endif; ?>
    </div>

    <form class="mp-product-review-form" id="mp-product-review-form" action="<?= $h($post_url); ?>" method="post">
      <h3>Write a review</h3>
      <input type="hidden" name="<?= $h($csrf_name); ?>" value="<?= $h($csrf_hash); ?>">
      <label for="mp-review-name">Name</label>
      <input id="mp-review-name" name="name" maxlength="120" autocomplete="name" required>
      <div class="mp-product-review-contact">
        <div><label for="mp-review-email">Email</label><input id="mp-review-email" name="email" type="email" maxlength="150" autocomplete="email"></div>
        <div><label for="mp-review-phone">Phone</label><input id="mp-review-phone" name="phone" type="tel" maxlength="30" autocomplete="tel"></div>
      </div>
      <fieldset>
        <legend>Rating</legend>
        <div class="mp-product-review-rating">
          <?php for($rating = 5; $rating >= 1; $rating--): ?>
            <label><input type="radio" name="rating" value="<?= $rating; ?>" required><span aria-label="<?= $rating; ?> stars">&#9733;</span></label>
          <?php endfor; ?>
        </div>
      </fieldset>
      <label for="mp-review-title">Title</label>
      <input id="mp-review-title" name="title" maxlength="<?= Reviews_model::TITLE_MAX; ?>">
      <label for="mp-review-text">Review</label>
      <textarea id="mp-review-text" name="text" maxlength="<?= Reviews_model::TEXT_MAX; ?>" rows="4"></textarea>
      <button type="submit">Submit review</button>
      <p class="mp-product-review-message" id="mp-product-review-message" aria-live="polite"></p>
    </form>
  </div>
</section>

<style>
.mp-product-reviews{max-width:1280px;margin:0 auto;padding:40px 24px 56px;color:var(--mp-dark,#172033)}
.mp-product-reviews-head{display:flex;justify-content:space-between;align-items:center;gap:24px;border-bottom:1px solid var(--mp-border,#dce2ea);padding-bottom:18px;margin-bottom:20px}
.mp-product-reviews-head h2{font-size:22px;line-height:1.25}.mp-product-reviews-head p{margin-top:5px;color:var(--mp-gray,#667085);font-size:14px}
.mp-product-reviews-score{display:grid;grid-template-columns:auto auto;align-items:center;column-gap:9px;color:var(--mp-gray,#667085);font-size:13px}.mp-product-reviews-score strong{font-size:20px;color:var(--mp-dark,#172033)}.mp-product-review-stars{color:#c27a12;white-space:nowrap}.mp-product-reviews-score>span:last-child{grid-column:2}
.mp-product-reviews-grid{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(280px,.8fr);gap:28px;align-items:start}
.mp-product-review{padding:18px 0;border-bottom:1px solid var(--mp-border,#dce2ea)}.mp-product-review-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.mp-product-review-meta strong{font-size:14px}.mp-product-review-verified{font-size:11px;color:#176b4d;background:#e8f5ee;padding:3px 7px;border-radius:4px}.mp-product-review h3{font-size:15px;margin-top:10px}.mp-product-review p{font-size:14px;line-height:1.6;margin-top:6px;overflow-wrap:anywhere}.mp-product-review time{display:block;color:var(--mp-gray,#667085);font-size:12px;margin-top:10px}.mp-product-review-empty{color:var(--mp-gray,#667085);font-size:14px;padding:16px 0}
.mp-product-review-form{border:1px solid var(--mp-border,#dce2ea);border-radius:6px;padding:20px;display:grid;gap:9px}.mp-product-review-form h3{font-size:17px;margin-bottom:5px}.mp-product-review-form label,.mp-product-review-form legend{font-size:13px;font-weight:600}.mp-product-review-form input:not([type=radio]),.mp-product-review-form textarea{width:100%;min-width:0;border:1px solid var(--mp-border,#dce2ea);border-radius:4px;padding:10px;font:inherit;font-size:14px}.mp-product-review-contact{display:grid;grid-template-columns:1fr 1fr;gap:12px}.mp-product-review-contact>div{display:grid;gap:9px;min-width:0}.mp-product-review-form fieldset{border:0;padding:4px 0}.mp-product-review-rating{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:3px;margin-top:5px}.mp-product-review-rating input{position:absolute;opacity:0;width:1px;height:1px}.mp-product-review-rating span{font-size:25px;color:#cbd2dc;cursor:pointer}.mp-product-review-rating label:hover span,.mp-product-review-rating label:hover~label span,.mp-product-review-rating input:checked~span{color:#c27a12}.mp-product-review-rating input:focus-visible+span{outline:2px solid var(--mp-primary,#2563eb);outline-offset:2px}.mp-product-review-form button{border:0;border-radius:4px;padding:11px 15px;background:var(--mp-primary,#2563eb);color:white;font-weight:700;cursor:pointer}.mp-product-review-message{min-height:18px;font-size:13px;color:#176b4d}.mp-product-review-message.is-error{color:#a12622}
@media(max-width:700px){.mp-product-reviews{padding:30px 16px}.mp-product-reviews-head{align-items:flex-start;flex-direction:column;gap:12px}.mp-product-reviews-grid{grid-template-columns:1fr}.mp-product-review-contact{grid-template-columns:1fr}}
</style>
<script>
(function(){
  var form=document.getElementById('mp-product-review-form');
  if(!form)return;
  form.addEventListener('submit',function(event){
    event.preventDefault();
    var button=form.querySelector('button[type="submit"]');
    var message=document.getElementById('mp-product-review-message');
    button.disabled=true;message.classList.remove('is-error');message.textContent='Submitting...';
    fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(response){return response.json();})
      .then(function(result){
        message.textContent=result.message||'Unable to submit your review.';
        if(!result.status){message.classList.add('is-error');button.disabled=false;return;}
        form.reset();
        var csrf=form.querySelector('input[type="hidden"]');if(csrf&&result.csrf_hash)csrf.value=result.csrf_hash;
      })
      .catch(function(){message.classList.add('is-error');message.textContent='Unable to submit your review. Please try again.';button.disabled=false;});
  });
})();
</script>
