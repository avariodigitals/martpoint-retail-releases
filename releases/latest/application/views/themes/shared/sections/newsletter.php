<div class="mp-section">
  <div class="mp-newsletter">
    <h3><?= htmlspecialchars(sf_sec_title($section, $settings->newsletter_title ?? 'Stay in the Loop')); ?></h3>
    <p><?= htmlspecialchars(sf_sec_sub($section, $settings->newsletter_subtitle ?? 'Subscribe for updates, deals and new arrivals.')); ?></p>
    <form class="mp-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
      <input type="email" placeholder="Your email address" required>
      <button type="submit">Subscribe</button>
    </form>
  </div>
</div>
