<footer class="copyright">
    &copy; <?=date("Y")?> <?= isset($SITE_TITLE) ? htmlspecialchars($SITE_TITLE) : 'MartPoint Retail' ?>. Powered by MartPoint Retail v<?= isset($VERSION) ? $VERSION : app_version(); ?>.
  </footer>
  <?php $this->load->view('idle_lock'); ?>
  <!-- Control Sidebar -->
  
