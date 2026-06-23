<!DOCTYPE html>
<html>
   <head>
      <!-- TABLES CSS CODE -->
      <?php include"comman/code_css.php"; ?>
      <!-- </copy> -->  
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php include"sidebar.php"; ?>
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1>
                  <?= $page_title;?>
               </h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li class="active"><?= $page_title;?></li>
               </ol>
            </section>
            <!-- Main content -->
            <section class="content">
               <div class="row">
                  <!-- ********** ALERT MESSAGE START******* -->
                  <?php include"comman/code_flashdata.php"; ?>
                  <!-- ********** ALERT MESSAGE END******* -->
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- Horizontal Form -->
                     <div class="box box-primary">
                        <form class="form-horizontal" id="category-form" onkeypress="return event.keyCode != 13;">
                           <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <div class="box-body">
                              <div class="row">
                                 <div class="col-sm-8 col-sm-offset-2">
                                    <div class="form-group">
                                       <label for="current_pass" class="col-sm-4 control-label"><?= $this->lang->line('current_password'); ?><label class="text-danger">*</label></label>
                                       <div class="col-sm-8">
                                          <div class="input-group">
                                            <input type="password" class="form-control" id="current_pass" name="current_pass" placeholder="Enter current password" onkeyup="shift_cursor(event,'pass')" autofocus>
                                            <span class="input-group-btn">
                                              <button type="button" class="btn btn-default toggle-pass" data-target="current_pass" title="Show/Hide"><i class="fa fa-eye"></i></button>
                                            </span>
                                          </div>
                                          <span id="current_pass_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>
                                    <div class="form-group">
                                       <label for="pass" class="col-sm-4 control-label"><?= $this->lang->line('new_password'); ?><label class="text-danger">*</label></label>
                                       <div class="col-sm-8">
                                          <div class="input-group">
                                            <input type="password" class="form-control" id="pass" name="pass" placeholder="Enter new password" onkeyup="shift_cursor(event,'confirm')">
                                            <span class="input-group-btn">
                                              <button type="button" class="btn btn-default toggle-pass" data-target="pass" title="Show/Hide"><i class="fa fa-eye"></i></button>
                                            </span>
                                          </div>
                                          <span id="pass_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>
                                    <div class="form-group">
                                       <label for="confirm" class="col-sm-4 control-label"><?= $this->lang->line('confirm_password'); ?><label class="text-danger">*</label></label>
                                       <div class="col-sm-8">
                                          <div class="input-group">
                                            <input type="password" class="form-control" id="confirm" name="confirm" placeholder="Retype new password">
                                            <span class="input-group-btn">
                                              <button type="button" class="btn btn-default toggle-pass" data-target="confirm" title="Show/Hide"><i class="fa fa-eye"></i></button>
                                            </span>
                                          </div>
                                          <span id="confirm_msg" style="display:none" class="text-danger"></span>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="box-footer">
                              <div class="row">
                                 <div class="col-sm-8 col-sm-offset-2 text-center">
                                    <div class="col-xs-6">
                                       <button type="button" id="save" class="btn btn-block btn-success" title="Save Data">Save</button>
                                    </div>
                                    <div class="col-xs-6">
                                       <a href="<?=base_url('dashboard');?>">
                                          <button type="button" class="btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                       </a>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </form>
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
               </div>
               <!-- /.row -->
            </section>
            <!-- /.content -->
         </div>
         <!-- /.content-wrapper -->
         <?php include"footer.php"; ?>
         <!-- Add the sidebar's background. This div must be placed
            immediately after the control sidebar -->
         <div class="control-sidebar-bg"></div>
      </div>
      <!-- ./wrapper -->
      <!-- SOUND CODE -->
      <?php include"comman/code_js_sound.php"; ?>
      <!-- TABLES CODE -->
      <?php include"comman/code_js.php"; ?>
      <script src="<?php echo $theme_link; ?>js/changepass.js"></script>
      <script>
        // Eye toggle for password fields
        $(document).on('click', '.toggle-pass', function(){
          var targetId = $(this).data('target');
          var input = document.getElementById(targetId);
          var icon = $(this).find('i');
          if(input.type === 'password'){
            input.type = 'text';
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
          } else {
            input.type = 'password';
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
          }
        });
      </script>
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
   </body>
</html>
