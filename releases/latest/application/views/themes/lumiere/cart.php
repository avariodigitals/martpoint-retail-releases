<?php require_once APPPATH . 'views/themes/lumiere/_helpers.php'; $lmHome = base_url('store/' . ($settings->store_slug ?? '')); ?>
<main class="lm-cart-page lm-cart-review"><div class="wrap"><div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span>Your Bag</div><div class="lm-page-intro"><div class="eyebrow">YOUR LUMIÈRE BAG</div><h1>Your glow is on its way.</h1><p>Review your selections before checkout.</p></div></div>
<?php $this->load->view('themes/shared/cart', ['cart_review_only' => true]); ?>
</main>
