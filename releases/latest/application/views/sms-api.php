<?php
/* SMS API settings — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head"><h1 class="mp-page-title"><?= $this->lang->line('sms_api'); ?></h1></div>
<div class="mp-card">
   <div class="mp-card-body">
      <?php 
         $store_rec = get_store_details();
      ?>
      <!-- ********** ALERT MESSAGE START******* -->
      <?php include"comman/code_flashdata.php"; ?>
      <!-- ********** ALERT MESSAGE END******* -->

      <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'api-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>

      <div class="nav-tabs-custom">
         <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">HTTP/URL SMS API</a></li>
            <li><a href="#tab_2" data-toggle="tab">Twilio SMS API</a></li>
            <li><a href="#tab_4" data-toggle="tab">FiveMojo WhatsApp API</a></li>
            <li><a href="#tab_5" data-toggle="tab">Brevo SMS API</a></li>
            <li><a href="#tab_6" data-toggle="tab">Sendchamp SMS API</a></li>
            <li><a href="#tab_7" data-toggle="tab">BulkSMSNigeria API</a></li>
            <li><a href="#tab_3" data-toggle="tab">Action</a></li>
         
         </ul>
         <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
               
            <input type="hidden" name="hidden_rowcount" id="hidden_rowcount" value="">
           
            <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
            <!-- <div class="box-body"> -->
               <div class="form-group " >

                  <div class="col-sm-12">
                   <div class="callout callout-info">
                     <h4>HTTP/URL</h4>
                       <p>
                         <u>Example</u> : <br>https://www.example.com/api/mt/SendSMS?APIKey=QWERTYUIOP123456&senderid=ABCDEF&channel=2&DCS=0&flashsms=0&<b class='bg-yellow'>mobiles</b>=91989xxxxxxx&<b class='bg-yellow'>message</b>=test message&route=1
                         <br>
                         Note: You need to verify the message key & mobile number key from your API, each SMS service providers may have different keys.<br>
                         In above example API 'message' & 'mobiles' keys refers Message & Mobile number, where you need to change the value of the Message & Number.
                         <br>
                         <u>Example:</u>
                         <ol>
                           <li>URL : https://www.example.com/api/mt/SendSMS</li>
                           <ul>
                              <li>NOTE: (Don't add ? Question mark inside input box)</li>
                           </ul>
                           <li>Mobile Key : mobiles</li>
                           <li>Message Key : message</li>
                           <li>APIKey : QWERTYUIOP123456</li>
                           <li>senderid : ABCDEF</li>
                           <li>channel : 2</li>
                           <li>DCS : 0</li>
                           <li>flashsms : 0</li>
                           <li>route : 1</li>
                         </ol>

                         This is a just example, each SMS Service providers may have different attributes, based on that you need to arrange it.
                     

                       </p>
                   </div>
                  </div>
                  <div class="col-sm-12  table-responsive">
                   
                     <table class="table" id='api_table'>
                        <thead>
                           <th width="15%"></th>
                           <th width="20%" class="text-center">Key</th>
                           <th width="40%" class="text-center">Key Value</th>
                           <th><input type="button" class="btn btn-success" name="new_row" onclick="addrow();" value="+" title="New Line"  ></th>
                        </thead>
                        <tbody>
                           <?php 
                              $q2=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_smsapi");
                              $i=0; 
                              foreach($q2->result() as $res2){
                                $i++;
                                if($res2->info == 'url'){
                              ?>
                           <tr id="row_<?= $i; ?>" data-row='<?= $i; ?>'>
                              <td  class="text-right"><label class="control-label">URL<label class="text-danger">*</label></label>
                                 <input type="hidden" id="info_<?= $i; ?>" name="info_<?= $i; ?>" value="<?php echo  $res2->info; ?>">
                              </td>
                              <td >
                                 <input id="key_<?= $i; ?>" name="key_<?= $i; ?>"  type="text" class="form-control " placeholder="" value='<?= $res2->key; ?>' readonly="true" />
                              </td>
                              <td><input id="key_val_<?= $i; ?>" name="key_val_<?= $i; ?>"  type="text" class="form-control " placeholder=""  value='<?= $res2->key_value; ?>' /></td>
                              <td><input type="button" class="btn btn-danger" name="btn_<?= $i; ?>" id="btn_<?= $i; ?>" value="-" title="Cant' Remove" disabled='true' ></td">
                           </tr>
                           <?php 
                              }//url end
                              else if($res2->info == 'mobile'){
                                ?>
                           <tr id="row_<?= $i; ?>" data-row='<?= $i; ?>'>
                              <td  class="text-right"><label class="control-label">Mobile Key<label class="text-danger">*</label></label>
                                 <input type="hidden" id="info_<?= $i; ?>" name="info_<?= $i; ?>" value="<?php echo  $res2->info; ?>">
                              </td>
                              <td >
                                 <input id="key_<?= $i; ?>" name="key_<?= $i; ?>"  type="text" class="form-control " placeholder="" value='<?= $res2->key; ?>' />
                              </td>
                              <td><input id="key_val_<?= $i; ?>" name="key_val_<?= $i; ?>"  type="text" class="form-control " placeholder="" readonly="true" value='<?= $res2->key_value; ?>' /></td>
                              <td><input type="button" class="btn btn-danger" name="btn_<?= $i; ?>" id="btn_<?= $i; ?>" value="-" title="Cant' Remove" disabled='true' ></td">
                           </tr>
                           <?php 
                              }//mobile end
                              else if($res2->info == 'message'){
                                ?>
                           <tr id="row_<?= $i; ?>" data-row='<?= $i; ?>'>
                              <td  class="text-right"><label class="control-label">Message Key<label class="text-danger">*</label></label>
                                 <input type="hidden" id="info_<?= $i; ?>" name="info_<?= $i; ?>" value="<?php echo  $res2->info; ?>">
                              </td>
                              <td >
                                 <input id="key_<?= $i; ?>" name="key_<?= $i; ?>"  type="text" class="form-control " placeholder="" value='<?= $res2->key; ?>' />
                              </td>
                              <td><input id="key_val_<?= $i; ?>" name="key_val_<?= $i; ?>"  type="text" class="form-control " placeholder="" readonly="true" value='<?= $res2->key_value; ?>' /></td>
                              <td><input type="button" class="btn btn-danger" name="btn_<?= $i; ?>" id="btn_<?= $i; ?>" value="-" title="Cant' Remove" disabled='true' ></td">
                           </tr>
                           <?php 
                              }//mobile end
                              else{
                                ?>
                           <tr id="row_<?= $i; ?>" data-row='<?= $i; ?>'>
                              <td>
                                 <input type="hidden" id="info_<?= $i; ?>" name="info_<?= $i; ?>" value="<?php echo  $res2->info; ?>">
                              </td>
                              <td >
                                 <input id="key_<?= $i; ?>" name="key_<?= $i; ?>"  type="text" class="form-control " placeholder="" value='<?= $res2->key; ?>' />
                              </td>
                              <td><input id="key_val_<?= $i; ?>" name="key_val_<?= $i; ?>"  type="text" class="form-control " placeholder="" value='<?= $res2->key_value; ?>' /></td>
                              <td><input type="button" class="btn btn-danger" name="btn_<?= $i; ?>" id="btn_<?= $i; ?>" value="-" title="Remove ?" onclick="removerow('<?= $i; ?>')"  ></td">
                           </tr>
                           <?php 
                              }//mobile end
                              }//foreach end
                              ?>
                        </tbody>
                     </table>
                  </div>
                  

                  <?php
               $btn_name="Update";
                   $btn_id="update";
               ?>
         

               </div>
               <!-- server code -->
            <!-- </div> -->
            <!-- /.box-body -->
            
            <!-- /.box-footer -->
         
            </div>
            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_2">
             <?php
             $account_sid = $auth_token = $twilio_phone =''; 
             $q1=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_twilio");
             if($q1->num_rows()>0){
               $account_sid = $q1->row()->account_sid;
               $auth_token = $q1->row()->auth_token;
               $twilio_phone = $q1->row()->twilio_phone;
             } 
             ?>
               <div class="row">
                  <!-- right column -->
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>Twilio SMS API</h4>
                                   Website Link: <a href='https://www.twilio.com/' target="_blank">https://www.twilio.com/</a>

                                 <p>Where <b>Account SID, Auth Token</b> and <b>Twilio Phone</b> number are neccessary for SMS Sending Feature.</p>
                                 <p><b>Note: Twilio requires the complete number to send SMS. Ex: +919876543210, +188888888888.</b></p>
                               </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="account_sid" class="col-sm-4 control-label">Account SID</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="account_sid" name="account_sid" placeholder="" value="<?php print $account_sid; ?>" >
                                       <span id="account_sid_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="auth_token" class="col-sm-4 control-label">Auth Token</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="auth_token" name="auth_token" placeholder="" value="<?php print $auth_token; ?>" >
                                       <span id="auth_token_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="twilio_phone" class="col-sm-4 control-label">Twilio Phone Number</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="twilio_phone" name="twilio_phone" placeholder="" value="<?php print $twilio_phone; ?>" >
                                       <span id="twilio_phone_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              


                           </div>
                        </div>
                  </div>
                  <!--/.col (right) -->
               </div>
            </div>

            <div class="tab-pane" id="tab_4">
             <?php
             $whatsAppUrl  = 'https://app.fivemojo.com/api/send.php';
             $whatsAppInstanceId = $whatsAppToken =''; 
             $q1=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_fivemojo");
             if($q1->num_rows()>0){
               $account_sid = $q1->row()->url;
               $whatsAppInstanceId = $q1->row()->instance_id;
               $whatsAppToken = $q1->row()->token;
               $whatsAppUrl = $q1->row()->url;
             } 
             ?>
               <div class="row">
                  <!-- right column -->
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>FiveMojo WhatsApp API</h4>
                                   Website Link: <a href='https://app.fivemojo.com/' target="_blank">https://app.fivemojo.com/</a>

                                 <p>Where <b>URL, Instance ID</b> and <b>Token</b> number are neccessary for message Sending Feature.</p>
                                 <b>Note: </b> Number should be full, example: 91989xxxxxxx (Don't use + symbol).
                                 
                               </div>
                              <div class="col-md-8 hide">
                                 <div class="form-group">
                                    <label for="whatsAppUrl" class="col-sm-4 control-label">URL</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="whatsAppUrl" name="whatsAppUrl" placeholder="" value="<?php print $whatsAppUrl; ?>" readonly >
                                       <span id="whatsAppUrl_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="whatsAppInstanceId" class="col-sm-4 control-label">Instance ID</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="whatsAppInstanceId" name="whatsAppInstanceId" placeholder="" value="<?php print $whatsAppInstanceId; ?>" >
                                       <span id="whatsAppInstanceId_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="whatsAppToken" class="col-sm-4 control-label">Token</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="whatsAppToken" name="whatsAppToken" placeholder="" value="<?php print $whatsAppToken; ?>" >
                                       <span id="whatsAppToken_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              


                           </div>
                        </div>
                  </div>
                  <!--/.col (right) -->
               </div>
            </div>

            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_5">
             <?php
             $brevo_api_key = $brevo_sender_name ='';
             $q1=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_brevo");
             if($q1->num_rows()>0){
               $brevo_api_key = $q1->row()->api_key;
               $brevo_sender_name = $q1->row()->sender_name;
             }
             ?>
               <div class="row">
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>Brevo SMS API (Sendinblue)</h4>
                                   Website Link: <a href='https://www.brevo.com/' target="_blank">https://www.brevo.com/</a>

                                 <p>Where <b>API Key</b> and <b>Sender Name</b> are neccessary for SMS Sending Feature.</p>
                                 <p><b>Note: Sign up at Brevo, verify your sender name, and generate an API key from your account settings.</b></p>
                               </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="brevo_api_key" class="col-sm-4 control-label">API Key</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="brevo_api_key" name="brevo_api_key" placeholder="xkeysib-..." value="<?php print $brevo_api_key; ?>" >
                                       <span id="brevo_api_key_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="brevo_sender_name" class="col-sm-4 control-label">Sender Name</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="brevo_sender_name" name="brevo_sender_name" placeholder="YourBrand" value="<?php print $brevo_sender_name; ?>" >
                                       <span id="brevo_sender_name_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
            </div>

            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_6">
             <?php
             $sendchamp_api_key = $sendchamp_sender_id = $sendchamp_route ='';
             if($this->db->table_exists('db_sendchamp')){
               $q1=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_sendchamp");
               if($q1->num_rows()>0){
                 $sendchamp_api_key   = $q1->row()->api_key;
                 $sendchamp_sender_id = $q1->row()->sender_id;
                 $sendchamp_route     = $q1->row()->route;
               }
             }
             ?>
               <div class="row">
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>Sendchamp SMS API</h4>
                                   Website Link: <a href='https://www.sendchamp.com/' target="_blank">https://www.sendchamp.com/</a>
                                 <p>Where <b>API Key</b>, <b>Sender ID</b> and <b>Route</b> are neccessary for SMS Sending Feature.</p>
                               </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="sendchamp_api_key" class="col-sm-4 control-label">API Key</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="sendchamp_api_key" name="sendchamp_api_key" placeholder="sendchamp_live_..." value="<?php print $sendchamp_api_key; ?>" >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="sendchamp_sender_id" class="col-sm-4 control-label">Sender ID</label>
                                    <div class="col-sm-4">
                                       <input type="text" class="form-control" id="sendchamp_sender_id" name="sendchamp_sender_id" placeholder="MartPoint" value="<?php print $sendchamp_sender_id; ?>" >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="sendchamp_route" class="col-sm-4 control-label">Route</label>
                                    <div class="col-sm-4">
                                       <select class="form-control" id="sendchamp_route" name="sendchamp_route">
                                         <option value="non_dnd_nigeria" <?= $sendchamp_route==='non_dnd_nigeria'?'selected':''; ?>>non_dnd_nigeria</option>
                                         <option value="dnd_nigeria" <?= $sendchamp_route==='dnd_nigeria'?'selected':''; ?>>dnd_nigeria</option>
                                         <option value="international" <?= $sendchamp_route==='international'?'selected':''; ?>>international</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
            </div>

            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_7">
             <?php
             $bulksmsng_api_token = $bulksmsng_sender_id = $bulksmsng_gateway ='';
             $bulksmsng_base_url = 'https://www.bulksmsnigeria.com/api';
             if($this->db->table_exists('db_bulksmsng')){
               $q1=$this->db->select("*")->where("store_id",get_current_store_id())->get("db_bulksmsng");
               if($q1->num_rows()>0){
                 $bulksmsng_api_token = $q1->row()->api_token;
                 $bulksmsng_sender_id = $q1->row()->sender_id;
                 $bulksmsng_base_url  = $q1->row()->base_url ?: $bulksmsng_base_url;
                 $bulksmsng_gateway   = $q1->row()->gateway;
               }
             }
             ?>
               <div class="row">
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>BulkSMSNigeria API (v2)</h4>
                                   Website Link: <a href='https://www.bulksmsnigeria.com/' target="_blank">https://www.bulksmsnigeria.com/</a>
                                 <p>Get your API token from <a href='https://www.bulksmsnigeria.com/user/api-tokens' target="_blank">/user/api-tokens</a>.</p>
                                 <p><b>Important:</b> your <b>Sender ID</b> must be registered and <u>approved</u> at
                                    <a href='https://www.bulksmsnigeria.com/sender-ids' target="_blank">/sender-ids</a> before messages will send.
                                    Sender IDs are 3&ndash;11 alphanumeric characters (hyphens allowed), max 5 per account.</p>
                                 <p>Ensure your wallet has sufficient balance &mdash; sending fails with
                                    <code>BSNG-3000</code> when it does not.</p>
                                 <p>Phone numbers may be local (<code>08012345678</code>) or international
                                    (<code>+2348012345678</code>); both are normalised automatically.</p>
                               </div>
                              <div class="col-md-9">
                                 <div class="form-group">
                                    <label for="bulksmsng_api_token" class="col-sm-4 control-label">API Token</label>
                                    <div class="col-sm-5">
                                       <input type="password" class="form-control" id="bulksmsng_api_token" name="bulksmsng_api_token" placeholder="Bearer token" value="<?php print htmlspecialchars($bulksmsng_api_token, ENT_QUOTES); ?>" autocomplete="new-password" >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-9">
                                 <div class="form-group">
                                    <label for="bulksmsng_sender_id" class="col-sm-4 control-label">Sender ID</label>
                                    <div class="col-sm-5">
                                       <input type="text" class="form-control" id="bulksmsng_sender_id" name="bulksmsng_sender_id" placeholder="BulkSMS" maxlength="11" value="<?php print htmlspecialchars($bulksmsng_sender_id, ENT_QUOTES); ?>" >
                                       <p class="help-block" style="margin-bottom:0">3&ndash;11 alphanumeric characters, must be approved.</p>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-9">
                                 <div class="form-group">
                                    <label for="bulksmsng_gateway" class="col-sm-4 control-label">Gateway (optional)</label>
                                    <div class="col-sm-5">
                                       <select class="form-control" id="bulksmsng_gateway" name="bulksmsng_gateway">
                                         <option value="" <?= empty($bulksmsng_gateway)?'selected':''; ?>>Auto (best route)</option>
                                         <option value="mtn" <?= $bulksmsng_gateway==='mtn'?'selected':''; ?>>MTN</option>
                                         <option value="airtel" <?= $bulksmsng_gateway==='airtel'?'selected':''; ?>>Airtel</option>
                                         <option value="glo" <?= $bulksmsng_gateway==='glo'?'selected':''; ?>>Glo</option>
                                         <option value="9mobile" <?= $bulksmsng_gateway==='9mobile'?'selected':''; ?>>9mobile</option>
                                       </select>
                                       <p class="help-block" style="margin-bottom:0">Leave on Auto unless you need a specific network.</p>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-9">
                                 <div class="form-group">
                                    <label for="bulksmsng_base_url" class="col-sm-4 control-label">API Base URL</label>
                                    <div class="col-sm-5">
                                       <input type="text" class="form-control" id="bulksmsng_base_url" name="bulksmsng_base_url" value="<?php print htmlspecialchars($bulksmsng_base_url, ENT_QUOTES); ?>" >
                                       <p class="help-block" style="margin-bottom:0">Sends to <code>&lt;base&gt;/v2/sms</code>. Only change if the vendor moves the endpoint.</p>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-9">
                                 <div class="form-group">
                                    <label class="col-sm-4 control-label">&nbsp;</label>
                                    <div class="col-sm-5">
                                       <button type="button" class="btn btn-default" id="bulksmsng_test_btn" onclick="bulksmsngTest();">
                                          <i class="fa fa-plug"></i> Test connection
                                       </button>
                                       <span id="bulksmsng_test_msg" style="margin-left:8px;"></span>
                                       <p class="help-block">Checks your token by reading the wallet balance &mdash; sends no SMS and spends no credit.</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
            </div>

            <!-- /.tab-pane -->
            <div class="tab-pane" id="tab_3">
            
               <div class="row">
                  <!-- right column -->
                  <div class="col-md-12">
                        <div class="box-body">
                           <div class="row">
                             <div class="callout callout-info">
                                 <h4>Enable or Disable the SMS Sending Feature.</h4>
                                 <p>
                                   <ul>
                                     <li>Disable: If you don't want to send SMS</li>
                                     <li>HTTP/URL API : Which will allow to send SMS by using HTTP/URL Based SMS API.</li>
                                     <li>Twilio API : Which will allow to send SMS by using Twilio SMS API.</li>
                                     <li>Brevo API : Which will allow to send SMS by using Brevo SMS API.</li>
                                     <li>Sendchamp API : Which will allow to send SMS by using Sendchamp SMS API.</li>
                                     <li>BulkSMSNigeria API : Which will allow to send SMS by using BulkSMSNigeria API v2.</li>
                                   </ul>
                                   <b>Note:</b> whichever provider is selected here is also used for storefront
                                   customer OTP and for SMS campaigns.
                                 </p>
                               </div>
                              <div class="col-md-8">
                                 <div class="form-group">
                                    <label for="sms_status" class="col-sm-4 control-label">SMS Status</label>
                                    <div class="col-sm-4">
                                       <select class="form-control select2" id="sms_status" name="sms_status"  style="width: 100%;" >
                                       <option value="0">Disable</option>
                                       <option value="1">HTTP/URL API</option>
                                       <option value="2">Twilio API</option>
                                       <option value="3">Fivemojo WhatsApp API</option>
                                       <option value="4">Brevo SMS API</option>
                                       <option value="5">Sendchamp SMS API</option>
                                       <option value="6">BulkSMSNigeria API</option>
                                    </select>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
                  <!--/.col (right) -->
               </div>
            </div>
            <!-- /.tab-pane -->
         </div>
         <!-- /.tab-content -->

      </div>
      <div class="col-sm-8 col-sm-offset-2 text-center">
            <center>
             <?php
               $btn_name="Update";
                   $btn_id="update";
               ?>
              <div class="col-md-3 col-md-offset-3">
               <button type="button" id="<?php echo $btn_id;?>" class=" mp-btn-primary" title="Save Data"><?php echo $btn_name;?></button>
            </div>
             <div class="col-sm-3">
               <a href="<?=base_url('dashboard');?>">
                 <button type="button" class="col-sm-3 mp-btn-secondary close_btn" title="Go Dashboard">Close</button>
               </a>
            </div>
            </center>
         </div>
      <!-- /.box -->
      <?= form_close(); ?>
   </div>
</div>
<script src="<?php echo $theme_link; ?>js/sms.js"></script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");$(".<?php echo basename(__FILE__,'.php');?>-active-li").closest(".mp-nav-group").addClass("open");</script>
<script>
   //UPDATE ROW COUNT
   $("#hidden_rowcount").val("<?= $i;?>");
   
   //UPDATE current sms_status — structured notification setting wins, with
   //db_store as the fallback for installs that predate that table.
   $("#sms_status").val("<?= (int)(function_exists('mp_get_store_notification_setting') ? mp_get_store_notification_setting(get_current_store_id(),'sms_status',($store_rec->sms_status ?? 0)) : ($store_rec->sms_status ?? 0)); ?>").select2();

   //Test BulkSMSNigeria credentials without spending credit (wallet balance).
   function bulksmsngTest(){
     var $btn = $("#bulksmsng_test_btn"), $msg = $("#bulksmsng_test_msg");
     var endpoint = "<?= base_url('sms/test_bulksmsng_connection'); ?>";
     $btn.prop('disabled', true);
     $msg.html('<span class="text-muted">Checking…</span>');
     $.post(endpoint, {
       api_token: $("#bulksmsng_api_token").val(),
       base_url:  $("#bulksmsng_base_url").val(),
       '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
     }, function(res){
       $btn.prop('disabled', false);
       try {
         var d = (typeof res === 'string') ? JSON.parse(res) : res;
         if(d.status){
           $msg.html('<span class="text-success"><i class="fa fa-check"></i> ' + d.message + '</span>');
         } else {
           $msg.html('<span class="text-danger"><i class="fa fa-times"></i> ' + (d.message || 'Connection failed') + '</span>');
         }
       } catch(e){
         $msg.html('<span class="text-danger">Unexpected response</span>');
       }
     }).fail(function(){
       $btn.prop('disabled', false);
       $msg.html('<span class="text-danger">Request failed — check the API base URL</span>');
     });
   }
   </script>
<script type="text/javascript">
   function removerow(id){//id=Rowid
   
       $("#row_"+id).remove();
      // final_total();
       }
     function addrow(id){
       
         var rowcount=$("#hidden_rowcount").val();
         rowcount=parseInt(rowcount)+1;
           $("#hidden_rowcount").val(rowcount);
   
           var str='<tr id="row_'+rowcount+'" data-row="'+rowcount+'">';
           
           
           str+='<td><input type="hidden" id="info_'+rowcount+'" name="info_'+rowcount+'" value=""></td>';
         str+='<td><input id="key_'+rowcount+'" name="key_'+rowcount+'"  type="text" class="form-control" /></td>';
         str+='<td><input id="key_val_'+rowcount+'" name="key_val_'+rowcount+'"  type="text" class="form-control" /></td>';
         str+='<td><input type="button" class="btn btn-danger" name="btn_'+rowcount+'" id="btn_'+rowcount+'" value="-" title="Remove Record" onclick="removerow('+rowcount+')"></td>';
           str+='</tr>';
            //console.log(str);
                 $('#api_table tbody').append(str);
   
       //return;
     }
   
   
</script>
