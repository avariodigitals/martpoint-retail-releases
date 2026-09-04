<?php
/* Send Email — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<div class="mp-page-head"><h1 class="mp-page-title"><?= $this->lang->line('send_email'); ?></h1></div>
<div class="mp-card">
  <div class="mp-card-body">
    <!-- ********** ALERT MESSAGE START******* -->
    <?php include"comman/code_flashdata.php"; ?>
    <!-- ********** ALERT MESSAGE END******* -->
    <form role="form" id="email-form" method="post" action='<?=base_url('email/send_message')?>' onkeypress="return event.keyCode != 13;">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
    <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
    <div class="col-md-9">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><?= $this->lang->line('send_email'); ?></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div class="form-group">
            <label>Template (optional)</label>
            <select class="form-control" id="email_template_select">
              <option value="">-- Free-form email --</option>
              <?php foreach($email_templates as $tpl): ?>
                <option value="<?= htmlspecialchars($tpl->template_key) ?>" data-subject="<?= htmlspecialchars($tpl->subject) ?>" data-body="<?= htmlspecialchars($tpl->html_body) ?>"><?= htmlspecialchars($tpl->template_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <input class="form-control" name='email_to' id='email_to' placeholder="To:">
            <span id="email_to_msg" style="display:none" class="text-danger"></span>
          </div>
          <div class="form-group">
            <input class="form-control" name='email_subject' id='email_subject' placeholder="Subject:">
            <span id="email_subject_msg" style="display:none" class="text-danger"></span>
          </div>
          <div class="form-group">
                <textarea id="compose-textarea" name='email_content'  class="form-control" style="height: 300px"></textarea>
          </div>
          <div class="form-group">
            <div class="btn btn-default btn-file">
              <i class="fa fa-paperclip"></i> Attachment
              <input type="file" name="attachment">
            </div>
            <p class="help-block">Max. 32MB</p>
          </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="pull-right">
            <button type="button" class="btn btn-primary send"><i class="fa fa-envelope-o"></i> Send</button>
          </div>
          <button type="reset" class="btn btn-default"><i class="fa fa-times"></i> Discard</button>
        </div>
        <!-- /.box-footer -->
      </div>
      <!-- /. box -->
    </div>
    <!-- right column -->
    <!--/.col (right) -->
    </form>
  </div>
</div>
<script src="<?php echo $theme_link; ?>js/email.js"></script>
<script src="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");$(".<?php echo basename(__FILE__,'.php');?>-active-li").closest(".mp-nav-group").addClass("open");</script>
<script>
  $(function () {
    //Add text editor
    $("#compose-textarea").wysihtml5();

    // Template selector handler
    $('#email_template_select').on('change', function(){
      var option = $(this).find('option:selected');
      var subject = option.data('subject') || '';
      var body = option.data('body') || '';
      $('#email_subject').val(subject);
      // wysihtml5 textarea update
      var editor = $("#compose-textarea").data('wysihtml5');
      if(editor && editor.editor){
        editor.editor.setValue(body);
      } else {
        $("#compose-textarea").val(body);
      }
    });
  });
</script>
