<?php if(!empty($settings->whatsapp_number)): ?>
<div class="mp-section" style="text-align:center; padding:40px 24px;">
  <h3 style="font-size:22px; font-weight:700; margin-bottom:8px;">Need Help?</h3>
  <p style="color:var(--mp-gray); margin-bottom:20px;">Chat with us on WhatsApp for quick assistance.</p>
  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="mp-wa-btn">Chat on WhatsApp</a>
</div>
<?php endif; ?>
