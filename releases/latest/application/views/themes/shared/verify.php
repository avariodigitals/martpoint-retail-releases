<?php
$root=base_url('store/'.($settings->store_slug ?? ''));
$returnUrl=!empty($wishlist_return) ? $root.'/wishlist'.(!empty($wishlist_return_item)?'?item='.(int)$wishlist_return_item:'') : $root.($this->input->get('return')==='cart'?'/cart':'/account');
?>
<section class="mp-portal" data-mp-auth data-preview="<?= !empty($html_theme_preview)?'1':'0'; ?>" data-send="<?= htmlspecialchars($root.'/send_otp',ENT_QUOTES); ?>" data-verify="<?= htmlspecialchars($root.'/verify_otp',ENT_QUOTES); ?>" data-return="<?= htmlspecialchars($returnUrl,ENT_QUOTES); ?>" data-csrf-name="<?= htmlspecialchars($csrf_name ?? '',ENT_QUOTES); ?>" data-csrf-hash="<?= htmlspecialchars($csrf_hash ?? '',ENT_QUOTES); ?>">
  <p class="mp-portal-kicker"><?= htmlspecialchars($store->store_name ?? 'Your store'); ?> · Your account</p>
  <h1><?= !empty($wishlist_return)?'Keep your favourites close.':'Welcome back.'; ?></h1>
  <p class="mp-portal-intro">Sign in with a one-time code to see your orders, track deliveries and keep your wishlist.</p>
  <div class="mp-portal-grid"><div class="mp-portal-card">
    <h2>Sign in securely</h2><p data-auth-status role="status">Choose where to receive your code.</p>
    <form data-auth-contact>
      <div class="mp-portal-tabs" role="group" aria-label="Receive code by"><button type="button" class="mp-portal-button mp-portal-secondary" data-auth-method="phone" aria-pressed="true">Phone</button><button type="button" class="mp-portal-button mp-portal-secondary" data-auth-method="email" aria-pressed="false">Email</button></div>
      <div data-auth-phone><label>Phone number<input name="phone" type="tel" autocomplete="tel" minlength="7" required value="<?= htmlspecialchars($prefill_phone ?? '',ENT_QUOTES); ?>"></label></div>
      <div data-auth-email hidden><label>Your name<input name="name" autocomplete="name" required disabled></label><label>Email address<input name="email" type="email" autocomplete="email" required disabled></label></div>
      <button class="mp-portal-button" type="submit">Send code</button>
    </form>
    <form data-auth-code hidden><label>Six-digit code<input name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required></label><div class="mp-portal-actions"><button type="submit" class="mp-portal-button">Sign in</button><button type="button" class="mp-portal-button mp-portal-secondary" data-auth-change>Change contact / resend</button></div></form>
    <p data-auth-error role="alert" hidden></p>
  </div><aside class="mp-portal-card"><h2>Your store, in one place.</h2><p>Your account keeps purchases and saved products together at <?= htmlspecialchars($store->store_name ?? 'this store'); ?>.</p><a href="<?= htmlspecialchars($root.'/products',ENT_QUOTES); ?>">Continue shopping as a guest ↗</a></aside></div>
</section>
<script src="<?= base_url('theme/js/html_theme_auth.js'); ?>?v=4.0.9.280" defer></script>
