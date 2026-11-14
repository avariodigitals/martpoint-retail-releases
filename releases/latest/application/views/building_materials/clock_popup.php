<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(defined('MP_BUILDING_CLOCK_POPUP')) return;
if(!function_exists('mp_get_store_profile')) $this->load->helper('business_profile');
if((mp_get_store_profile()['industry_type'] ?? '') !== 'building_materials') return;
define('MP_BUILDING_CLOCK_POPUP',true);
?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/building_clock.css?v='.filemtime(FCPATH.'theme/dist/css/building_clock.css')); ?>">
<dialog id="bm-clock-dialog" aria-labelledby="bm-clock-title" aria-describedby="bm-clock-help"
 data-base="<?= htmlspecialchars(base_url('attendance/'),ENT_QUOTES,'UTF-8'); ?>">
  <div class="bm-clock-heading"><div><small>Staff attendance</small><h2 id="bm-clock-title">Clock In</h2></div><button type="button" data-bm-clock-close aria-label="Close clock dialog">&times;</button></div>
  <p class="bm-clock-name"><?= htmlspecialchars(mp_current_user_full_name(),ENT_QUOTES,'UTF-8'); ?></p>
  <p id="bm-clock-help">Allow camera and microphone access, then confirm with your account password. No audio or video is recorded or uploaded.</p>
  <video id="bm-clock-video" autoplay playsinline muted aria-label="Your camera preview"></video>
  <p id="bm-clock-device-status" role="status">Waiting for device access.</p>
  <button type="button" id="bm-clock-device-retry" hidden>Try camera &amp; microphone again</button>
  <form id="bm-clock-form">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" data-bm-clock-csrf>
    <label for="bm-clock-password">Your account password</label>
    <input type="password" id="bm-clock-password" name="password" autocomplete="current-password" required maxlength="255">
    <p id="bm-clock-result" role="status" aria-live="polite"></p>
    <button type="submit" id="bm-clock-confirm" disabled>Confirm Clock In</button>
  </form>
  <a class="bm-clock-history" href="<?= base_url('attendance/workspace'); ?>">View my attendance history &rarr;</a>
</dialog>
<div id="bm-clock-toast" role="status" aria-live="polite" hidden></div>
<script defer src="<?= base_url('theme/js/building_clock.js?v='.filemtime(FCPATH.'theme/js/building_clock.js')); ?>"></script>
