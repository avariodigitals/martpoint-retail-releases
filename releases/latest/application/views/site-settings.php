<?php
/* Site Settings form — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head"><h1 class="mp-page-title"><?= $this->lang->line('site_settings'); ?></h1></div>
<?= form_open('#', array('class' => 'form-horizontal', 'id' => 'site-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
<!-- ********** ALERT MESSAGE START******* -->
<?php include"comman/code_flashdata.php"; ?>
<!-- ********** ALERT MESSAGE END******* -->
<div class="mp-card">
   <div class="mp-card-body">
      <!-- Custom Tabs -->
      <div class="nav-tabs-custom">
         <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab"><?= $this->lang->line('site'); ?></a></li>
            
         </ul>
         <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
               <div class="row">
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- form start -->
                        <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                        <div class="box-body">
                           <div class="row">
                              <div class="col-md-5">
                                 <?php
                                   /*
                                    * site_name is db_sitesettings.site_name — ONE row for the
                                    * whole installation (MY_Controller reads it as
                                    * $SITE_TITLE, which every shell and the login page render).
                                    * It is not a per-store value, so a client store admin must
                                    * not be able to rename the platform for every tenant.
                                    * Only the vendor's central install sees this field.
                                    */
                                   $mp_can_edit_site_name = function_exists('mp_is_central') && mp_is_central();
                                 ?>
                                 <?php if($mp_can_edit_site_name): ?>
                                 <div class="form-group">
                                    <label for="site_name" class="col-sm-4 control-label"><?= $this->lang->line('site_name'); ?><label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                       <input type="text" class="form-control" id="site_name" name="site_name" placeholder="" onkeyup="shift_cursor(event,'sales_target')" value="<?php print $site_name; ?>" >
                                       <span id="site_name_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                                 <?php else: ?>
                                 <?php /* The value still has to be submitted: update_site() validates
                                          site_name as required and Site_model writes the whole row, so
                                          the current value is carried through unchanged. */ ?>
                                 <input type="hidden" name="site_name" value="<?= htmlspecialchars($site_name); ?>">
                                 <?php endif; ?>
                                 <div class="form-group">
                                    <label for="sales_target" class="col-sm-4 control-label">Daily Sales Target</label>
                                    <div class="col-sm-8">
                                       <input type="number" step="any" min="0" class="form-control" id="sales_target" name="sales_target" placeholder="0" value="<?php print (float)($sales_target ?? 0); ?>" >
                                       <span class="text-muted"><small>Used by dashboard target progress bar.</small></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-5">
                                 <div class="form-group">
                                    <label for="address" class="col-sm-4 control-label"><?= $this->lang->line('site_logo'); ?></label>
                                    <div class="col-sm-8">
                                       <input type="file" id="logo" name="logo">
                                       <span id="logo_msg" style="display:block;" class="text-danger">Max Width/Height: 300px * 300px & Size: 300px </span>
                                    </div>
                                 </div>
                                 <?php 
                                 if(empty($logo)){
                                   $logo = base_url('uploads/no_logo/nologo.png');
                                 }
                                 else{
                                   $logo = base_url($logo);
                                 }
                                 ?>
                                 <div class="form-group">
                                    <div class="col-sm-8 col-sm-offset-4">
                                       <img class='img-responsive' style='border:3px solid #d2d6de;' src="<?php echo $logo;?>">
                                    </div>
                                 </div>
                              </div>
                              <!-- ########### -->
                           </div>
                        </div>
                        <!-- /.box-body -->
                        <!-- /.box-footer -->
                     
                  </div>
                  <!--/.col (right) -->
               </div>
               <!-- /.row -->
            </div>
            <!-- /.tab-pane -->
         </div>
         <!-- /.tab-content -->
      </div>
      <!-- nav-tabs-custom -->
      <hr style="border-top:1px solid #eee;margin:20px 0;">
      <div class="row">
         <div class="col-md-12">
            <h4 style="margin:0 0 14px;"><i class="fa fa-comment"></i> MartPoint Assist — AI Understanding <span class="label label-default" style="font-weight:400;">Optional</span></h4>
            <p class="text-muted" style="margin-bottom:14px;">When enabled, Assist understands free-form instructions via an AI provider and routes them to its built-in actions. The AI never writes data directly — every action still goes through the normal permission checks. Without a key, Assist keeps working with its built-in commands.</p>
            <div class="row">
               <div class="col-md-5">
                  <div class="form-group">
                     <label for="assist_ai_enabled" class="col-sm-4 control-label">Enable AI</label>
                     <div class="col-sm-8" style="padding-top:7px;">
                        <input type="checkbox" id="assist_ai_enabled" name="assist_ai_enabled" value="1" <?= !empty($assist_ai_enabled) ? 'checked' : ''; ?>>
                        <span class="text-muted"><small>Turn on AI understanding</small></span>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="assist_ai_provider" class="col-sm-4 control-label">Provider</label>
                     <div class="col-sm-8">
                        <select class="form-control" id="assist_ai_provider" name="assist_ai_provider">
                           <option value="groq"    <?= ($assist_ai_provider ?? '')=='groq'    ? 'selected' : ''; ?>>Groq — Free tier (recommended)</option>
                           <option value="gemini"  <?= ($assist_ai_provider ?? '')=='gemini'  ? 'selected' : ''; ?>>Google Gemini — Free tier</option>
                           <option value="openai"  <?= ($assist_ai_provider ?? '')=='openai'  ? 'selected' : ''; ?>>OpenAI — Paid</option>
                           <option value="custom"  <?= ($assist_ai_provider ?? '')=='custom'  ? 'selected' : ''; ?>>Custom OpenAI-compatible endpoint</option>
                        </select>
                        <span class="text-muted"><small>Groq &amp; Gemini have free tiers — sign up, copy an API key, paste below.</small></span>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="assist_ai_key" class="col-sm-4 control-label">API Key</label>
                     <div class="col-sm-8">
                        <input type="password" autocomplete="new-password" class="form-control" id="assist_ai_key" name="assist_ai_key" placeholder="<?= !empty($assist_ai_key_set) ? '•••••••• saved — leave blank to keep' : 'Paste API key here'; ?>" value="">
                        <span id="assist_ai_key_msg" style="display:none" class="text-danger"></span>
                     </div>
                  </div>
               </div>
               <div class="col-md-5">
                  <div class="form-group">
                     <label for="assist_ai_endpoint" class="col-sm-4 control-label">Endpoint</label>
                     <div class="col-sm-8">
                        <input type="text" class="form-control" id="assist_ai_endpoint" name="assist_ai_endpoint" value="<?php print htmlspecialchars($assist_ai_endpoint ?? ''); ?>" placeholder="https://api.groq.com/openai/v1/chat/completions">
                        <span class="text-muted"><small>OpenAI-compatible chat-completions URL.</small></span>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="assist_ai_model" class="col-sm-4 control-label">Model</label>
                     <div class="col-sm-8">
                        <input type="text" class="form-control" id="assist_ai_model" name="assist_ai_model" value="<?php print htmlspecialchars($assist_ai_model ?? ''); ?>" placeholder="openai/gpt-oss-120b">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php if(function_exists('mp_is_central') && mp_is_central()): ?>
      <hr style="border-top:1px solid #eee;margin:20px 0;">
      <div class="row">
         <div class="col-md-12">
            <h4 style="margin:0 0 14px;"><i class="fa fa-exclamation-circle"></i> Service Status — Incident Banner</h4>
            <p class="text-muted" style="margin-bottom:14px;">While active, a status strip shows at the top of every admin screen — desktop and mobile — linking to your status page in a new tab. This controls the incident notice on <strong>this install only</strong> — the fleet-wide banner is driven by the status API on martpoint.com.ng.</p>
            <div class="row">
               <div class="col-md-5">
                  <div class="form-group">
                     <label for="incident_active" class="col-sm-4 control-label">Show banner</label>
                     <div class="col-sm-8" style="padding-top:7px;">
                        <input type="checkbox" id="incident_active" name="incident_active" value="1" <?= !empty($incident['active']) ? 'checked' : ''; ?>>
                        <span class="text-muted"><small>Display the incident notice</small></span>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="incident_severity" class="col-sm-4 control-label">Severity</label>
                     <div class="col-sm-8">
                        <select class="form-control" id="incident_severity" name="incident_severity">
                           <option value="investigating" <?= ($incident['severity'] ?? '')=='investigating' ? 'selected' : ''; ?>>Investigating</option>
                           <option value="identified"    <?= ($incident['severity'] ?? '')=='identified'    ? 'selected' : ''; ?>>Identified</option>
                           <option value="monitoring"    <?= ($incident['severity'] ?? '')=='monitoring'    ? 'selected' : ''; ?>>Monitoring</option>
                           <option value="maintenance"   <?= ($incident['severity'] ?? '')=='maintenance'   ? 'selected' : ''; ?>>Scheduled maintenance</option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="col-md-5">
                  <div class="form-group">
                     <label for="incident_message" class="col-sm-4 control-label">Message</label>
                     <div class="col-sm-8">
                        <input type="text" class="form-control" id="incident_message" name="incident_message" maxlength="255" value="<?php print htmlspecialchars($incident['message'] ?? ''); ?>" placeholder="We are investigating a technical issue.">
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="incident_url" class="col-sm-4 control-label">Status page URL</label>
                     <div class="col-sm-8">
                        <input type="url" class="form-control" id="incident_url" name="incident_url" maxlength="255" value="<?php print htmlspecialchars($incident['url'] ?? 'https://www.martpoint.com.ng/status'); ?>" placeholder="https://www.martpoint.com.ng/status">
                        <span class="text-muted"><small>Opens in a new tab.<?php if(!empty($incident['active']) && !empty($incident['started_at'])): ?> Showing since <?= htmlspecialchars($incident['started_at']); ?>.<?php endif; ?></small></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; // mp_is_central — incident control is central-only ?>
      <div>
         <div class="col-sm-8 col-sm-offset-2 text-center">
            <center>
               <?php
                  if($site_name!=""){
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
               <div class="col-md-3 col-md-offset-3">
                  <button type="button" id="<?php echo $btn_id;?>" class="mp-btn-primary" title="Save Data"><?php echo $btn_name;?></button>
               </div>
               <div class="col-sm-3">
                 <a href="<?=base_url('dashboard');?>">
                  <button type="button" class="col-sm-3 mp-btn-secondary close_btn" title="Go Dashboard">Close</button>
                </a>
               </div>
            </center>
         </div>
      </div>
   </div>
</div>
<?= form_close(); ?>
<script type="text/javascript">
   $(document).submit(function(event) {
     event.preventDefault();
     if($("#update").length){
       $("#update").trigger('click');
     }
   });
</script>
<script src="<?php echo $theme_link; ?>js/site-settings.js"></script>
<script>
// MartPoint Assist AI — provider presets autofill endpoint + model
(function(){
  var presets = {
    groq:   { endpoint: 'https://api.groq.com/openai/v1/chat/completions', model: 'openai/gpt-oss-120b' },
    gemini: { endpoint: 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions', model: 'gemini-2.0-flash' },
    openai: { endpoint: 'https://api.openai.com/v1/chat/completions', model: 'gpt-4o-mini' }
  };
  var sel = document.getElementById('assist_ai_provider');
  if(!sel) return;
  function fill(){
    var p = presets[sel.value];
    if(!p) return; // custom — leave fields as-is
    document.getElementById('assist_ai_endpoint').value = p.endpoint;
    document.getElementById('assist_ai_model').value = p.model;
  }
  sel.addEventListener('change', fill);
  // On first load, fill blanks so a fresh install gets sane free defaults
  if(document.getElementById('assist_ai_endpoint').value === '' && presets[sel.value]) fill();
})();
</script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");$(".<?php echo basename(__FILE__,'.php');?>-active-li").closest(".mp-nav-group").addClass("open");</script>
