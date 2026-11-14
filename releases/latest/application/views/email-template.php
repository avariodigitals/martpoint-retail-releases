<?php
/* Email Template — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<?php
	if(!isset($template_name)){
      $template_name=$content=$undelete_bit= $variables="";
	}


 ?>

<div class="mp-page-head">
  <h1 class="mp-page-title"><?= $this->lang->line('email_template'); ?></h1>
</div>

<div class="mp-card">
  <div class="mp-card-body">
    <form class="form-horizontal" id="template-form" >
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
      <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
              <label for="template_name" class="col-sm-4 control-label"><?= $this->lang->line('template_name'); ?><label class="text-danger">*</label></label>

          <div class="col-sm-8">
            <input type="text" class="form-control input-sm" id="template_name" name="template_name" placeholder=""   value="<?php print $template_name; ?>" autofocus >
      <span id="template_name_msg" style="display:none" class="text-danger"></span>
          </div>
          </div>
          
          <div class="form-group">
              <label for="content" class="col-sm-4 control-label"><?= $this->lang->line('content'); ?><label class="text-danger">*</label></label>

          <div class="col-sm-8">
            <textarea type="text" spellcheck="false" class="form-control" rows="6" id="content" name="content" placeholder=""><?php print $content; ?></textarea>
    <span id="content_msg" style="display:none" class="text-danger"></span>
          </div>
          </div>
         
          
          <!-- ########### -->
       </div>

       <?php if(!empty($variables)){ ?>
       <div class="col-md-5">
            <div class="form-group">
                <div class="col-sm-6 col-md-offset-2">
                  <label class="control-label"><u>Email CONTENT VARIABLES</u></label><br>
                  <?= $variables; ?>
                </div>
            </div>
        </div>
      <?php } ?>
          <!-- ########### -->
</div>

      <?php
          if($template_name!=""){
               $btn_name="Update";
               $btn_id="update";
              ?>
                <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id;?>"/>
                <?php
          }
                    else{
                        $btn_name="Save";
                        $btn_id="save";
                    }
          
                    ?>
                     
      <div class="col-sm-8 col-sm-offset-2 text-center">
         <div class="col-md-3 col-md-offset-3">
            <button type="button" id="<?php echo $btn_id;?>" class="mp-btn-primary" title="Save Data"><?php echo $btn_name;?></button>
         </div>
         <div class="col-sm-3">
          <a href="<?=base_url('dashboard');?>">
            <button type="button" class="col-sm-3 mp-btn-secondary close_btn" title="Go Dashboard">Close</button>
          </a>
         </div>
      </div>
    </form>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/email_templates.js"></script>
<script src="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".email-templates-list-active-li").addClass("active");$(".email-templates-list-active-li").closest(".mp-nav-group").addClass("open");</script>

<script type="text/javascript">
  $(document).submit(function(e){
    e.preventDefault();
  });
</script>
