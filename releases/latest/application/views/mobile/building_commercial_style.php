<?php defined('BASEPATH') OR exit('No direct script access allowed'); if((mp_get_store_profile()['industry_type'] ?? '')!=='building_materials')return; ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
<style>
:root{--mp-primary:#262626;--mp-primary-dark:#151515;--mp-bg:#f5f5f5;--mp-surface:#fff;--mp-text:#262626;--mp-ink:#333;--mp-muted:#777;--mp-border:#e5e5e5}
html,body,#sales-form,input,textarea,select,button{font-family:Manrope,-apple-system,BlinkMacSystemFont,sans-serif!important}
body,.content{background:#f5f5f5!important;color:#262626}#app{background:#f5f5f5}
.screen{padding-top:18px}.topbar h1,.mp-mobile-topbar h1{letter-spacing:-.6px;color:#262626}.store-name{color:#777;font-size:11px;line-height:1.6}
.card,.sale-card,.quote-card,.mp-mobile-box{border-color:#e5e5e5;border-radius:14px;box-shadow:none}
.mp-select-options{position:static!important;max-height:220px;overflow-y:auto;box-shadow:none!important}.mp-option.active{background:#f1f1f1;color:#333}
.totals,.mp-total-row,.mp-search-bar{background:#fafafa;border-color:#e5e5e5}.totals .value,.mp-total-row .value{color:#262626}
.actions a,.action,.mp-save-btn,button[type=submit],.btn-primary{background:#262626!important;border-color:#262626!important;color:#fff!important}
.badge,.status,.mp-mobile-topbar .avatar{background:#eee!important;color:#555!important}
.mp-mobile-topbar .sub{color:#777}.mp-mobile-topbar{background:#fff;border-color:#e5e5e5}
.mp-select-trigger,.form-control,input,textarea{border-color:#dedede!important;border-radius:8px!important}
.select2-container--default .select2-selection--single{border-color:#dedede!important;border-radius:8px!important}
</style>
