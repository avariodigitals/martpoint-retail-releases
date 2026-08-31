<?php
  // MartPoint Assist chat launcher + panel
  // Load on every mobile screen so users can chat/support from anywhere
?>
<link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=13">
<script>
  window.csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
  window.csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
</script>
<script src="<?= $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?= $theme_link; ?>js/assist.js?v=13"></script>
<?php $this->load->view('assist/panel'); ?>

<style>
  /* Force a consistent white background on every mobile screen */
  html, body, #app { background: #FFFFFF !important; }
</style>
