<?php $beCheckout=!empty($botanical_checkout);$beRoot=base_url('store/'.$settings->store_slug); ?>
<main class="be-commerce be-order-page <?= $beCheckout?'be-checkout-page':'be-cart-page'; ?>">
<div class="be-shell"><nav class="be-crumbs" aria-label="Breadcrumb"><a href="<?= $beRoot; ?>">Home</a><span>/</span><a href="<?= $beRoot.'/cart'; ?>">Your bag</a><?php if($beCheckout): ?><span>/</span><span>Checkout</span><?php endif; ?></nav>
<header class="be-page-heading"><div><span class="be-eyebrow"><?= $beCheckout?$beCopy('checkout_eyebrow'):$beCopy('bag_eyebrow'); ?></span><h1><?= $beCheckout?$beCopy('checkout_heading'):$beCopy('bag_heading'); ?></h1><p><?= $beCheckout?$beCopy('checkout_intro'):$beCopy('bag_intro'); ?></p></div><a class="be-textlink" href="<?= $beRoot.'/products'; ?>"><?= $beCopy('continue_exploring'); ?> ↗</a></header>
<nav class="be-order-steps" aria-label="Order progress"><a href="<?= $beRoot.'/cart'; ?>" <?= !$beCheckout?'aria-current="step"':''; ?>><span>01</span>Your bag</a><i aria-hidden="true"></i><a href="<?= $beRoot.'/checkout'; ?>" <?= $beCheckout?'aria-current="step"':''; ?>><span>02</span>Details & payment</a><i aria-hidden="true"></i><span><span>03</span>Confirmation</span></nav>
<?php if($beCheckout): ?><div class="be-guest-note"><span>Already have an account?</span><a href="<?= $beRoot.'/verify'; ?>">Sign in to your account ↗</a><small>You can also continue below as a guest.</small></div><?php endif; ?>
</div>
<?php $this->load->view('themes/shared/cart',['cart_review_only'=>!$beCheckout]); ?>
<div class="be-shell be-order-support"><span><?= $beCopy('order_help'); ?></span><a href="<?= $beRoot.(!empty($site_content['contact_enabled'])?'/contact':'#be-connect'); ?>"><?= $beCopy('order_help_cta'); ?> ↗</a></div>
</main>
