<?php if(!empty($settings->store_id)): ?>
<script src="<?= base_url('theme/js/storefront_commerce.js'); ?>?v=263"></script>
<script>
if(!window.MPCommerce){
  window.MPCommerce = new window.MPCommerceClient(<?= json_encode([
    'storeId'=>(int)$settings->store_id,
    'quoteUrl'=>base_url('storefront/cart_quote'),
    'signInUrl'=>base_url('store/'.rawurlencode((string)$settings->store_slug).'/verify').'?return=cart',
    'pricingNoticeEnabled'=>(bool)($settings->customer_pricing_notice_enabled ?? 1),
    'pricingNoticeTitle'=>(string)($settings->customer_pricing_notice_title ?? 'Customer pricing'),
    'pricingNoticeText'=>(string)($settings->customer_pricing_notice_text ?? 'Sign in to My Account to check whether the store has assigned your account to a customer pricing group.'),
    'pricingNoticeLinkText'=>(string)($settings->customer_pricing_notice_link_text ?? 'Sign in to My Account'),
    'csrfName'=>$this->security->get_csrf_token_name(),
    'csrfHash'=>$this->security->get_csrf_hash(),
  ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
}
</script>
<?php endif; ?>
