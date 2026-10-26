<?php
$contact = $contact ?? '';
$error = $error ?? '';
?>
<div class="fx-section" style="padding-top:16px; padding-bottom:60px;">
  <div class="fx-section-inner" style="max-width:480px; margin:0 auto; text-align:center;">
    <div class="fx-empty-mark" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="6" y="2.5" width="12" height="19" rx="3"/><path d="M10 18.5h4M9 6.5h6"/></svg></div>
    <h1 style="font-size:clamp(24px, 3vw, 30px); font-weight:800; color:var(--fx-ink); margin-bottom:8px;">Verify Your Code</h1>
    <p style="color:var(--fx-muted); margin-bottom:32px;">We've sent a 6-digit code to <strong><?= htmlspecialchars($contact); ?></strong>. Enter it below to sign in.</p>

    <?php if($error): ?>
    <div style="background:rgba(220,38,38,0.1); border:1px solid var(--fx-danger); color:var(--fx-danger); padding:14px 16px; border-radius:var(--fx-radius); margin-bottom:24px; font-size:14px; font-weight:500;"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('store/' . ($settings->store_slug ?? '') . '/verify_otp'); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="contact" value="<?= htmlspecialchars($contact); ?>">
      
      <div style="display:flex; gap:10px; justify-content:center; margin-bottom:24px;">
        <?php for($i=1; $i<=6; $i++): ?>
        <input type="text" name="otp_<?= $i; ?>" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required style="width:52px; height:56px; font-size:24px; font-weight:700; text-align:center; border:2px solid var(--fx-border); border-radius:var(--fx-radius); background:var(--fx-white); color:var(--fx-ink); outline:none; transition:border-color .15s, box-shadow .15s;" oninput="if(this.value.length===1 && this.nextElementSibling) this.nextElementSibling.focus();" onkeydown="if(event.key==='Backspace' && !this.value && this.previousElementSibling) this.previousElementSibling.focus();" aria-label="Digit <?= $i; ?>">
        <?php endfor; ?>
      </div>

      <button type="submit" class="fx-card-add" style="width:100%; padding:16px; font-size:16px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12V7H5v5"/><path d="M7 12l5 5 10-10"/></svg>
        Verify & Sign In
      </button>
    </form>

    <div style="margin-top:24px; font-size:13px; color:var(--fx-muted);">
      Didn't receive the code? <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/verify'); ?>" style="color:var(--fx-green); font-weight:600;">Resend</a>
      <br>Having trouble? <a href="tel:<?= $settings->store_phone ?? ''; ?>" style="color:var(--fx-green); font-weight:600;">Call us</a> or <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>" target="_blank" style="color:#25D366; font-weight:600;">WhatsApp</a>
    </div>
  </div>
</div>

<script>
// Auto-focus first input
document.querySelector('input[name="otp_1"]')?.focus();

// Paste handling
document.querySelectorAll('input[name^="otp_"]').forEach((input, idx, inputs) => {
  input.addEventListener('paste', e => {
    e.preventDefault();
    const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
    paste.split('').forEach((char, i) => {
      if(inputs[i]) inputs[i].value = char;
    });
    inputs[Math.min(paste.length, 5)]?.focus();
  });
});
</script>
