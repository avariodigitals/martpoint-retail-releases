<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php $this->load->view('comman/code_css'); ?>
<!-- </copy> -->

</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <!-- Notification sound -->
  <audio id="login">
    <source src="<?php echo $theme_link; ?>sound/login.mp3" type="audio/mpeg">
    <source src="<?php echo $theme_link; ?>sound/login.ogg" type="audio/ogg">
  </audio>
  <script type="text/javascript">
    var login_sound = document.getElementById("login");
  </script>
  <!-- Notification end -->
  <script type="text/javascript">
  <?php if($this->session->flashdata('success')!=''){ ?>
        login_sound.play();
  <?php } ?>
  </script>

  <?php
  $this->load->view('sidebar');
  ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
        <small>Overall Information on Single Screen</small>
      </h1>
    </section>

    <section class="content">
      <div class="mp-dashboard-wrapper">
        <div class="col-md-12">
          <!-- ********** ALERT MESSAGE START******* -->
           <?php $this->load->view('comman/code_flashdata'); ?>
           <!-- ********** ALERT MESSAGE END******* -->
         </div>

        <div class="mp-section">
          <div class="mp-card">
            <div class="mp-card-body">
              <div class="mp-empty-state" style="padding:60px 20px;">
                <i class="fa fa-lock" style="font-size:40px;color:#94A3B8;margin-bottom:14px;display:block;"></i>
                <div style="font-size:16px;font-weight:600;color:#475569;margin-bottom:6px;">No dashboard access</div>
                <div style="font-size:14px;color:#94A3B8;">Your role does not have permission to view the dashboard. Contact your administrator if you believe this is an error.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- /.content-wrapper -->

  <?php $this->load->view('footer'); ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>

</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php $this->load->view('comman/code_js_sound'); ?>
<!-- TABLES CODE -->
<?php $this->load->view('comman/code_js'); ?>
<!-- bootstrap datepicker -->


</body>
</html>
