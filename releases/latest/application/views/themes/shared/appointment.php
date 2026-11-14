<?php
$apSkinora = ($theme_key ?? '') === 'skinora';
if ($apSkinora) {
    require_once APPPATH . 'views/themes/skinora/_helpers.php';
    $apImage = sn_image($service->service_image ?? '', 1200);
} else {
    require_once APPPATH . 'views/themes/lumiere/_helpers.php';
    $apImage = lm_item_image($service->service_image ?? '', 1200);
}
$apEsc = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$apSlug = $settings->store_slug ?? '';
$apHome = base_url('store/' . $apSlug);
$apPrice = (float)($service->effective_price ?? $service->price ?? 0);
?>
<style>
.mp-appointment{--ap-ink:<?= $apSkinora ? '#2b1c20' : '#14291e'; ?>;--ap-paper:<?= $apSkinora ? '#fbf5f2' : '#f7f6f0'; ?>;--ap-soft:<?= $apSkinora ? '#ead8d6' : '#e6e8dc'; ?>;--ap-accent:<?= $apSkinora ? '#8b4d55' : '#244c36'; ?>;color:var(--ap-ink);background:var(--ap-paper);padding:42px max(24px,calc((100vw - 1240px)/2)) 96px;font-family:Arial,sans-serif}
.mp-appointment *{box-sizing:border-box}.mp-appointment a{color:inherit}.ap-breadcrumb{display:flex;gap:10px;align-items:center;font-size:12px;margin-bottom:36px;color:#756c67}.ap-breadcrumb a{text-decoration:none}.ap-breadcrumb a:hover{text-decoration:underline}.ap-grid{display:grid;grid-template-columns:minmax(0,1.03fr) minmax(370px,.97fr);gap:clamp(24px,5vw,72px);align-items:start}.ap-visual{position:relative;min-height:660px;background:var(--ap-soft);overflow:hidden}.ap-visual img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.ap-visual:after{content:"";position:absolute;inset:35% 0 0;background:linear-gradient(transparent,rgba(13,29,21,.62))}.ap-visual-copy{position:absolute;z-index:1;bottom:38px;left:38px;right:38px;color:#fff}.ap-visual-copy span,.ap-eyebrow{font-size:10px;font-weight:700;letter-spacing:.22em;text-transform:uppercase}.ap-visual-copy h2{font-family:Georgia,serif;font-size:clamp(36px,4vw,60px);font-weight:400;line-height:1.05;margin:14px 0}.ap-visual-copy p{font-size:14px;line-height:1.6;max-width:440px}.ap-form-wrap{padding:24px 0 0}.ap-eyebrow{color:var(--ap-accent)}.ap-form-wrap h1{font:400 clamp(40px,4vw,64px)/1.06 Georgia,serif;letter-spacing:-.035em;margin:18px 0 16px}.ap-intro{color:#5c635b;line-height:1.7;font-size:15px;max-width:510px;margin:0 0 26px}.ap-treatment{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:22px 0;border-top:1px solid #d6d8cf;border-bottom:1px solid #d6d8cf;margin-bottom:27px}.ap-treatment b{font:400 23px Georgia,serif}.ap-treatment small{display:block;font-size:12px;color:#697069;margin-top:7px}.ap-treatment strong{white-space:nowrap;font-size:14px}.ap-section-title{font:400 25px Georgia,serif;margin:28px 0 15px}.ap-fields{display:grid;grid-template-columns:1fr 1fr;gap:16px}.ap-field{display:flex;flex-direction:column;gap:8px}.ap-field.wide{grid-column:1/-1}.ap-field label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.ap-field input,.ap-field textarea{width:100%;border:1px solid #c9cfc5;background:#fff;color:var(--ap-ink);border-radius:0;padding:14px 15px;min-height:50px;font:inherit;font-size:14px;outline:none}.ap-field input:focus,.ap-field textarea:focus{border-color:var(--ap-accent);box-shadow:0 0 0 2px color-mix(in srgb,var(--ap-accent) 18%,transparent)}.ap-field textarea{min-height:104px;resize:vertical}.ap-help{font-size:12px;color:#697069;line-height:1.6;margin:15px 0 24px}.ap-submit{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;min-height:54px;background:var(--ap-accent);border:1px solid var(--ap-accent);color:#fff;font-weight:700;font-size:13px;letter-spacing:.05em;cursor:pointer}.ap-submit:hover{filter:brightness(.9)}.ap-submit:disabled{opacity:.55;cursor:wait}.ap-message{display:none;padding:13px 15px;margin:0 0 16px;background:#fff0ec;border:1px solid #d1a49b;color:#8c2f25;font-size:13px;line-height:1.5}.ap-message.visible{display:block}.ap-return{display:inline-block;margin-top:20px;font-size:13px;text-underline-offset:4px}.ap-hp{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}
@media(max-width:900px){.mp-appointment{padding:25px 22px 72px}.ap-grid{grid-template-columns:1fr;gap:22px}.ap-visual{min-height:360px}.ap-form-wrap{padding:0}.ap-form-wrap h1{font-size:44px}.ap-visual-copy{left:24px;right:24px;bottom:20px}.ap-visual-copy h2{font-size:37px}}
@media(max-width:560px){.mp-appointment{padding:18px 16px 64px}.ap-breadcrumb{margin-bottom:18px}.ap-visual{min-height:290px}.ap-visual-copy p{display:none}.ap-form-wrap h1{font-size:39px}.ap-fields{grid-template-columns:1fr}.ap-field.wide{grid-column:auto}.ap-treatment{align-items:flex-start}.ap-treatment b{font-size:20px}}
</style>
<main class="mp-appointment">
  <nav class="ap-breadcrumb" aria-label="Breadcrumb"><a href="<?= $apEsc($apHome); ?>">Home</a><span>›</span><a href="<?= $apEsc($apHome . '/services'); ?>">Treatments</a><span>›</span><span>Appointment</span></nav>
  <div class="ap-grid">
    <div class="ap-visual">
      <?php if ($apImage): ?><img src="<?= $apEsc($apImage); ?>" alt="<?= $apEsc($service->service_name); ?> treatment" loading="eager"><?php endif; ?>
      <div class="ap-visual-copy"><span><?= $apSkinora ? 'A MOMENT FOR YOUR SKIN' : 'THE LUMIÈRE EXPERIENCE'; ?></span><h2>Time for<br>your skin.</h2><p>Thoughtful care begins with a conversation. Tell us when you would like to visit.</p></div>
    </div>
    <div class="ap-form-wrap">
      <div class="ap-eyebrow">PERSONALISED CARE · APPOINTMENT REQUEST</div>
      <h1>Make time<br>for you.</h1>
      <p class="ap-intro">Choose a preferred date and time for your treatment. The team will contact you to confirm availability and the final details.</p>
      <div class="ap-treatment"><div><b><?= $apEsc($service->service_name); ?></b><small><?= $apEsc($service->service_duration ?? 'Personalised treatment'); ?></small></div><strong><?= $apPrice > 0 ? 'From ' . sf_currency($apPrice, $store_currency ?? null) : 'Price on request'; ?></strong></div>
      <form id="ap-form" action="<?= $apEsc(base_url('storefront/place_order')); ?>" method="post">
        <input type="hidden" name="store_id" value="<?= (int)$settings->store_id; ?>">
        <input type="hidden" name="payment_method" value="appointment">
        <input type="hidden" name="<?= $apEsc($csrf_name); ?>" value="<?= $apEsc($csrf_hash); ?>">
        <div class="ap-hp" aria-hidden="true"><label for="ap-website">Website</label><input id="ap-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
        <h2 class="ap-section-title">Your preferred visit</h2>
        <div class="ap-fields"><div class="ap-field"><label for="ap-date">Date *</label><input id="ap-date" name="service_date" type="date" min="<?= date('Y-m-d'); ?>" required></div><div class="ap-field"><label for="ap-time">Time *</label><input id="ap-time" name="service_time" type="time" required></div></div>
        <h2 class="ap-section-title">About you</h2>
        <div class="ap-fields"><div class="ap-field wide"><label for="ap-name">Full name *</label><input id="ap-name" name="customer_name" type="text" autocomplete="name" maxlength="200" required></div><div class="ap-field"><label for="ap-phone">Phone number *</label><input id="ap-phone" name="customer_phone" type="tel" autocomplete="tel" maxlength="30" required></div><div class="ap-field"><label for="ap-email">Email address</label><input id="ap-email" name="customer_email" type="email" autocomplete="email" maxlength="100"></div><div class="ap-field wide"><label for="ap-note">Anything we should know?</label><textarea id="ap-note" name="service_note" maxlength="1000" placeholder="Skin concerns, preferences or questions (optional)"></textarea></div></div>
        <p class="ap-help">This is a request for your preferred time. Your appointment is confirmed when the team contacts you. No online payment is taken now.</p>
        <div class="ap-message" id="ap-message" role="alert"></div>
        <button class="ap-submit" id="ap-submit" type="submit">REQUEST APPOINTMENT <span aria-hidden="true">↗</span></button>
      </form>
      <a class="ap-return" href="<?= $apEsc($apHome . '/service/' . (int)$service->id); ?>">← Back to treatment details</a>
    </div>
  </div>
</main>
<script>
(function(){
  var form=document.getElementById('ap-form'),button=document.getElementById('ap-submit'),message=document.getElementById('ap-message');
  form.addEventListener('submit',function(event){
    event.preventDefault();
    if(!form.reportValidity())return;
    button.disabled=true;button.textContent='SENDING YOUR REQUEST…';message.classList.remove('visible');
    var data=new FormData(form);
    data.append('service_id',<?= (int)$service->id; ?>);
    fetch(form.action,{method:'POST',body:data,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(response){return response.json()})
      .then(function(result){
        if(result.csrf_hash){form.elements[<?= json_encode($csrf_name); ?>].value=result.csrf_hash;}
        if(result.status && result.redirect_url){window.location.assign(result.redirect_url);return;}
        message.textContent=result.message||'Your request could not be sent. Please try again.';message.classList.add('visible');message.scrollIntoView({block:'nearest',behavior:'smooth'});
      })
      .catch(function(){message.textContent='Your request could not be sent. Please try again.';message.classList.add('visible')})
      .finally(function(){button.disabled=false;button.textContent='REQUEST APPOINTMENT ↗'});
  });
})();
</script>
