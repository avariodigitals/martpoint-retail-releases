<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(defined('MP_BUILDING_LONG_TEXT_VOICE')) return;
if(!function_exists('mp_get_store_profile')) $this->load->helper('business_profile');
if((mp_get_store_profile()['industry_type'] ?? '') !== 'building_materials') return;
define('MP_BUILDING_LONG_TEXT_VOICE',true);
?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/building_voice.css?v='.filemtime(FCPATH.'theme/dist/css/building_voice.css')); ?>">
<script defer data-bm-long-text-voice src="<?= base_url('theme/js/building_long_text_voice.js?v='.filemtime(FCPATH.'theme/js/building_long_text_voice.js')); ?>"></script>
