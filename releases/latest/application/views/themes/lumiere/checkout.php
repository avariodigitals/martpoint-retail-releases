<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmHome = base_url('store/' . ($settings->store_slug ?? ''));
?>
<main class="lm-cart-page lm-checkout-page"><div class="wrap"><div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span><a href="<?= lm_escape($lmHome . '/cart'); ?>">Your Bag</a><span>›</span>Checkout</div><div class="lm-page-intro"><div class="eyebrow">SECURE CHECKOUT</div><h1>Almost glowing.</h1><p>Confirm your details and choose how you would like to pay.</p></div></div>
<?php $this->load->view('themes/shared/cart', ['cart_review_only' => false]); ?>
</main>
