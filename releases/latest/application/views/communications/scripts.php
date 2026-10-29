<?php $this->load->view('components/dialog'); ?>
<script>window.COMM_STORE_NAME=<?= json_encode($store_name??'Our store',JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;window.COMM_SMS_OPTOUT=<?= json_encode(' Opt out: '.base_url('storefront/campaign_unsubscribe/'.str_repeat('a',64)),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;</script>
<script src="<?= base_url('theme/js/communications_sms.js'); ?>?v=218"></script>
<script src="<?= base_url('theme/js/communications.js'); ?>?v=220-audience-lists-1"></script>
