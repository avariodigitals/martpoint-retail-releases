<?php
/* Send SMS — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head"><h1 class="mp-page-title"><?= $this->lang->line('send_sms'); ?></h1></div>
<div class="mp-card">
   <div class="mp-card-body">
      <div class="col-md-6">
         <form role="form" id="sms-form" onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
            <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
            <div class="box-body">
              <div class="form-group">
                <label for="mobile"><?= $this->lang->line('mobile'); ?> <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile 1,Mobile 2,...">
                <span id="mobile_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group">
                <label for="message"><?= $this->lang->line('message'); ?> <span class="text-danger">*</span></label>
                <textarea type="text" class="form-control" id="message" name="message" placeholder=""></textarea>
                <span id="message_msg" style="display:none" class="text-danger"></span>
              </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
              <div class="col-md-3 col-md-offset-3">
                <button type="button" id="send" class="mp-btn-primary" title="Save Data">Send</button>
              </div>
              <div class="col-sm-3">
                <a href="<?=base_url('dashboard');?>">
                  <button type="button" class="col-sm-3 mp-btn-secondary close_btn" title="Go Dashboard">Close</button>
                </a>
              </div>
            </div>
         </form>
      </div>
      <!-- /.box -->
   </div>
</div>
<script src="<?php echo $theme_link; ?>js/sms.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");$(".<?php echo basename(__FILE__,'.php');?>-active-li").closest(".mp-nav-group").addClass("open");</script>
