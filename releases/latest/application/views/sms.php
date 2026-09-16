<?php
/* Send SMS — modern clean view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head">
  <h2><?= $this->lang->line('send_sms'); ?></h2>
  <div class="mp-page-sub">Send a message to one or more mobile numbers.</div>
</div>

<div class="mp-card" style="max-width: 640px;">
  <div class="mp-card-body">
    <form role="form" id="sms-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= $base_url; ?>">

      <div class="mp-form-grid" style="display:grid!important;gap:16px!important;">
        <div class="mp-form-group" style="display:flex!important;flex-direction:column!important;gap:6px!important;">
          <label for="mobile"><?= $this->lang->line('mobile'); ?> <span class="text-danger">*</span></label>
          <input type="tel" class="mp-form-control" id="mobile" name="mobile" placeholder="Mobile 1, Mobile 2, ..." style="padding:12px 14px!important;border-radius:10px!important;border:1px solid var(--mp-border)!important;font-size:14px!important;">
          <span id="mobile_msg" class="text-danger" style="display:none;font-size:13px;"></span>
        </div>

        <div class="mp-form-group" style="display:flex!important;flex-direction:column!important;gap:6px!important;">
          <label for="message"><?= $this->lang->line('message'); ?> <span class="text-danger">*</span></label>
          <textarea class="mp-form-control" id="message" name="message" rows="4" placeholder="Type your message here..." style="padding:12px 14px!important;border-radius:10px!important;border:1px solid var(--mp-border)!important;font-size:14px!important;resize:vertical!important;"></textarea>
          <span id="message_msg" class="text-danger" style="display:none;font-size:13px;"></span>
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
        <a href="<?= base_url('dashboard'); ?>" class="mp-btn" style="text-decoration:none!important;">Close</a>
        <button type="button" id="send" class="mp-btn mp-btn-primary"><i class="fa fa-paper-plane"></i> Send SMS</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= $theme_link; ?>js/sms.js"></script>
<script>$(".sms-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
