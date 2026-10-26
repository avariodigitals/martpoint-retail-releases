<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$reviewCount = (int)($aggregate['count'] ?? 0);
$restaurantReview = !empty($restaurant_review);
$average = $aggregate['average'] ?? null;
$filledStars = $average === null ? 0 : max(0, min(5, (int)round((float)$average)));
$h = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$reviewKind = function_exists('mp_food_product_kind') && $product
    ? mp_food_product_kind($product->item_name ?? '', $product->category_name ?? '', $product->description ?? '')
    : 'dish';
$reviewItemLabel = $reviewKind === 'drink' ? 'this drink' : ($reviewKind === 'sweet' ? 'this treat' : 'this meal');
?>
<section class="mp-product-reviews fx-review-section" aria-labelledby="mp-product-reviews-title">
  <div class="mp-product-reviews-head">
    <div>
      <h2 id="mp-product-reviews-title"><?= $restaurantReview ? 'Guest reviews' : 'What guests say'; ?></h2>
      <?php if($product && !$restaurantReview): ?><p><?= $h($product->item_name); ?></p><?php endif; ?>
    </div>
    <div class="mp-product-reviews-score" aria-label="<?= $average === null ? 'No ratings yet' : $h(number_format((float)$average, 1) . ' out of 5'); ?>">
      <?php if(!$restaurantReview || $reviewCount > 0): ?><span class="mp-product-review-stars" aria-hidden="true"><?= str_repeat('&#9733;', $filledStars) . str_repeat('&#9734;', 5 - $filledStars); ?></span><?php endif; ?>
      <strong><?= $average === null ? ($restaurantReview ? 'No reviews yet' : 'New') : $h(number_format((float)$average, 1)); ?></strong>
      <?php if(!$restaurantReview || $reviewCount > 0): ?><span><?= $reviewCount; ?> <?= $reviewCount === 1 ? 'review' : 'reviews'; ?></span><?php endif; ?>
    </div>
  </div>

  <div class="mp-product-reviews-grid">
    <div class="mp-product-review-list">
      <?php if(empty($reviews)): ?>
        <p class="mp-product-review-empty">Be the first to share your experience with <?= $reviewItemLabel; ?>.</p>
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

    <?php if($restaurantReview): ?>
    <details class="fx-review-compose">
      <summary><span>Write a review</span><span aria-hidden="true">+</span></summary>
    <?php endif; ?>
    <form class="mp-product-review-form" id="mp-product-review-form" action="<?= $h($post_url); ?>" method="post">
      <h3>Share your experience</h3>
      <?php if($restaurantReview): ?><p class="fx-review-help">Use the email or phone number from your order. Reviews may be checked before appearing here.</p><?php endif; ?>
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
      <label for="mp-review-title">Title<?= $restaurantReview ? ' (optional)' : ''; ?></label>
      <input id="mp-review-title" name="title" maxlength="<?= Reviews_model::TITLE_MAX; ?>">
      <label for="mp-review-text">Review</label>
      <textarea id="mp-review-text" name="text" maxlength="<?= Reviews_model::TEXT_MAX; ?>" rows="4"></textarea>
      <button type="submit">Submit review</button>
      <p class="mp-product-review-message" id="mp-product-review-message" aria-live="polite"></p>
    </form>
    <?php if($restaurantReview): ?></details><?php endif; ?>
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

/* Food Express review block: editorial and quiet, so it belongs to the menu. */
.fx-review-section { max-width:1320px; padding:82px 34px 94px; color:var(--fx-ink,#1b1d18); background:var(--fx-cream,#f3efe7); }
.fx-review-section .mp-product-reviews-head { align-items:end; margin-bottom:34px; padding-bottom:22px; border-color:var(--fx-border,rgba(27,29,24,.18)); }
.fx-review-section .mp-product-reviews-head h2 { margin:0; color:var(--fx-ink,#1b1d18); font-family:'Cormorant Garamond',Georgia,serif; font-size:clamp(48px,6vw,78px); font-weight:600; letter-spacing:-.055em; line-height:.86; }
.fx-review-section .mp-product-reviews-head p { margin:13px 0 0; color:var(--fx-muted,#77736a); font-size:13px; }
.fx-review-section .mp-product-reviews-score { min-width:190px; padding-left:22px; border-left:1px solid var(--fx-border,rgba(27,29,24,.18)); color:var(--fx-muted,#77736a); }
.fx-review-section .mp-product-reviews-score strong { color:var(--fx-green-deep,#1b1d18); font-family:'Cormorant Garamond',Georgia,serif; font-size:32px; font-weight:600; }
.fx-review-section .mp-product-review-stars { color:var(--fx-green,#9e4d35); font-size:14px; letter-spacing:.08em; }
.fx-review-section .mp-product-reviews-grid { grid-template-columns:minmax(0,1fr) 360px; gap:72px; }
.fx-review-section .mp-product-review { padding:25px 0; border-color:var(--fx-border,rgba(27,29,24,.18)); }
.fx-review-section .mp-product-review-meta strong { color:var(--fx-ink,#1b1d18); font-family:'Cormorant Garamond',Georgia,serif; font-size:25px; font-weight:600; }
.fx-review-section .mp-product-review-verified { padding:0; border-radius:0; background:transparent; color:var(--fx-green,#9e4d35); font-size:9px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
.fx-review-section .mp-product-review h3 { margin-top:11px; color:var(--fx-ink,#1b1d18); font-family:'Cormorant Garamond',Georgia,serif; font-size:27px; font-weight:600; }
.fx-review-section .mp-product-review p { max-width:640px; color:var(--fx-muted,#77736a); font-size:14px; line-height:1.7; }
.fx-review-section .mp-product-review time { color:var(--fx-muted,#77736a); font-size:10px; letter-spacing:.1em; text-transform:uppercase; }
.fx-review-section .mp-product-review-form { border:0; border-top:2px solid var(--fx-green-deep,#1b1d18); border-radius:0; padding:22px 0 0; background:transparent; }
.fx-review-section .mp-product-review-form h3 { margin-bottom:9px; color:var(--fx-ink,#1b1d18); font-family:'Cormorant Garamond',Georgia,serif; font-size:31px; font-weight:600; }
.fx-review-section .mp-product-review-form label,.fx-review-section .mp-product-review-form legend { color:var(--fx-ink,#1b1d18); font-size:10px; letter-spacing:.12em; text-transform:uppercase; }
.fx-review-section .mp-product-review-form input:not([type=radio]),.fx-review-section .mp-product-review-form textarea { padding:12px 0; border:0; border-bottom:1px solid var(--fx-border,rgba(27,29,24,.18)); border-radius:0; background:transparent; color:var(--fx-ink,#1b1d18); font-size:14px; }
.fx-review-section .mp-product-review-form input:focus,.fx-review-section .mp-product-review-form textarea:focus { border-color:var(--fx-green,#9e4d35); outline:none; }
.fx-review-section .mp-product-review-rating span { color:var(--fx-cream-deep,#e7dfd1); }
.fx-review-section .mp-product-review-rating label:hover span,.fx-review-section .mp-product-review-rating label:hover~label span,.fx-review-section .mp-product-review-rating input:checked~span { color:var(--fx-green,#9e4d35); }
.fx-review-section .mp-product-review-form button { border-radius:0; background:var(--fx-green-deep,#1b1d18); font-size:11px; letter-spacing:.1em; text-transform:uppercase; }
.fx-review-section .mp-product-review-form button:hover { background:var(--fx-green,#9e4d35); }
.fx-review-section .mp-product-review-empty { padding:25px 0; color:var(--fx-muted,#77736a); font-size:14px; }

@media(max-width:700px){
  .fx-review-section { padding:44px 16px 68px; }
  .fx-review-section .mp-product-reviews-head { align-items:flex-start; gap:20px; margin-bottom:24px; padding-bottom:18px; }
  .fx-review-section .mp-product-reviews-head h2 { color:var(--fx-green-deep,#1b1d18); font-family:'DM Sans','Inter',sans-serif; font-size:32px; font-weight:900; letter-spacing:-.06em; line-height:1; }
  .fx-review-section .mp-product-reviews-score { display:flex; align-items:center; flex-wrap:wrap; width:100%; min-width:0; gap:9px 14px; padding:14px 0 0; border-top:1px solid var(--fx-border,rgba(27,29,24,.18)); border-left:0; }
  .fx-review-section .mp-product-reviews-score>span:last-child { grid-column:auto; }
  .fx-review-section .mp-product-reviews-grid { display:grid; grid-template-columns:minmax(0,1fr) !important; gap:24px; width:100%; }
  .fx-review-section .mp-product-review-list,.fx-review-section .mp-product-review-form { width:100%; min-width:0; box-sizing:border-box; }
  .fx-review-section .mp-product-review { padding:21px 0; }
  .fx-review-section .mp-product-review-form { padding:22px 18px; border:1px solid var(--fx-border,rgba(27,29,24,.18)); border-radius:20px; background:#fffefa; box-shadow:0 10px 26px rgba(16,23,20,.06); }
  .fx-review-section .mp-product-review-form h3 { margin-bottom:5px; color:var(--fx-green-deep,#1b1d18); font-family:'DM Sans','Inter',sans-serif; font-size:23px; font-weight:900; letter-spacing:-.045em; }
  .fx-review-section .mp-product-review-form input:not([type=radio]),.fx-review-section .mp-product-review-form textarea { min-height:44px; padding:10px 0; font-size:14px; }
  .fx-review-section .mp-product-review-form button { min-height:48px; border-radius:14px; background:var(--fx-green,#0c6b4f); font-family:'DM Sans','Inter',sans-serif; font-size:12px; letter-spacing:.08em; }
}
</style>
<script>
(function(){
  var form=document.getElementById('mp-product-review-form');
  if(!form)return;
  form.addEventListener('submit',function(event){
    event.preventDefault();
    var button=form.querySelector('button[type="submit"]');
    var message=document.getElementById('mp-product-review-message');
    if(!form.reportValidity())return;
    var email=form.querySelector('[name="email"]'),phone=form.querySelector('[name="phone"]');
    if(!email.value.trim()&&!phone.value.trim()){message.classList.add('is-error');message.textContent='Enter your email or phone number.';email.focus();return;}
    button.disabled=true;message.classList.remove('is-error');message.textContent='Submitting...';
    var controller=typeof AbortController!=='undefined'?new AbortController():null;
    var timer=controller?setTimeout(function(){controller.abort();},15000):null;
    fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'},signal:controller?controller.signal:undefined})
      .then(function(response){return response.json();})
      .then(function(result){
        message.textContent=result.message||'Unable to submit your review.';
        var csrf=form.querySelector('input[type="hidden"]');if(csrf&&result.csrf_hash)csrf.value=result.csrf_hash;
        if(!result.status){message.classList.add('is-error');button.disabled=false;return;}
        form.reset();
        button.textContent='Review sent';
      })
      .catch(function(){message.classList.add('is-error');message.textContent='Unable to submit your review. Please try again.';button.disabled=false;})
      .finally(function(){if(timer)clearTimeout(timer);});
  });
})();
</script>
