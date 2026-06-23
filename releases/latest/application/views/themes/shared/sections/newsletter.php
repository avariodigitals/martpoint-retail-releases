<div class="mp-section">
  <div class="mp-newsletter">
    <h3><?= htmlspecialchars($settings->newsletter_title ?? 'Stay in the Loop'); ?></h3>
    <p><?= htmlspecialchars($settings->newsletter_subtitle ?? 'Subscribe for updates, deals and new arrivals.'); ?></p>
    <div class="mp-newsletter-form">
      <input type="email" placeholder="Your email address" id="newsletter-email">
      <button onclick="const e=document.getElementById('newsletter-email').value; if(e){ showToast('Subscribed!'); document.getElementById('newsletter-email').value=''; }else{ showToast('Please enter your email'); }">Subscribe</button>
    </div>
  </div>
</div>
