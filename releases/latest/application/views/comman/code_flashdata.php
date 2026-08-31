<div class="col-md-12">
      <!-- ********** ALERT MESSAGE START******* -->
      <style>
        /* Ensure alert close icon has breathing room from message text */
        .alert .close {
          margin-left: 15px;
          padding: 0 5px;
        }
      </style>
          <?php if(demo_app()){ ?>
            <div class="alert alert-info text-left">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>
                  MartPoint Retail new version <?= app_version(); ?> released , Faster and Customizable Application Software. If you have any queries please message <a target='_blank' href='https://codecanyon.net/item/billing-book-ultimate-inventory-management-billing-software-with-pos/23552741/comments'>here</a>.[Some features are disabled in demo and it will be reset after each hour]. <label class="text-blue" >GST Invoice & GSTR-1 & GSTR-2 Reports added, for GST Invoice you need to change settings.<span class="text-uppercase">[Sidebar->Store->Sales Tab->Sales Invoice Format]</span></label>
                </strong>
              </div>
          <?php } ?>

          <?php if(!get_current_subcription_id() && !is_admin()){ ?>
            <div class="alert alert-success  text-left">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>
                  <?= $this->lang->line('subscription_msg_1'); ?> Please click <a href='<?=base_url('subscription_license/activate_form')?>'>here</a> to Activate!
                </strong>
              </div>
          <?php } ?>
          <?php if(!is_admin() && store_module() && !empty(get_current_subcription_id())){ 
            //validate subscription
            $message = '';
            $subscription_id = get_current_subcription_id();
            if(empty($subscription_id)){
              $message = "This store don't have any subscrtions!!";
            }

            $subscription_rec = get_subscription_rec($subscription_id);
            if($subscription_rec){
              $expire_date = $subscription_rec->expire_date;
              if($expire_date<date('Y-m-d')){
                $message = "Store Subscription expired!!";
              }
            }

            if(!empty($message)){ ?>
              <div class="alert alert-success  text-left">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>
                  <?=$message?>, Click <a href='<?=base_url('subscription_license/activate_form')?>'>here</a> to Activate!
                </strong>
              </div>
            <?php }
           } ?>

          <?php
            // New License Subscription Warnings
            if(isset($subscription_status) && $subscription_status['status'] === 'NOT_ACTIVATED' && is_admin()):
            ?>
              <div class="alert alert-danger text-center">
                <strong><i class="fa fa-exclamation-circle"></i> MartPoint Retail has not been activated.</strong>
                Please <a href="<?=base_url('subscription_license/activate_form');?>">activate your subscription</a>.
              </div>
            <?php endif; ?>
            <?php if(isset($subscription_status) && $subscription_status['status'] === 'EXPIRED'): ?>
              <div class="alert alert-danger text-center">
                <strong><i class="fa fa-exclamation-circle"></i> Your MartPoint subscription has expired.</strong>
                Please contact support to renew. <a href="<?=base_url('subscription_license');?>">View Details</a>
              </div>
            <?php endif; ?>
            <?php if(isset($subscription_status) && $subscription_status['status'] === 'SUSPENDED'): ?>
              <div class="alert alert-warning text-center">
                <strong><i class="fa fa-ban"></i> Your MartPoint subscription has been suspended.</strong>
                Please contact support. <a href="<?=base_url('subscription_license');?>">View Details</a>
              </div>
            <?php endif; ?>
            <?php if(isset($subscription_status) && $subscription_status['status'] === 'EXPIRING_SOON' && $subscription_status['days_left'] <= 30): ?>
              <div class="alert alert-warning text-center">
                <strong><i class="fa fa-clock-o"></i> Your subscription expires in <?= $subscription_status['days_left']; ?> days.</strong>
                Please renew before expiry. <a href="<?=base_url('subscription_license');?>">View Details</a>
              </div>
            <?php endif; ?>

          <?php
            // Welcome alert: session-flag based; shown once per login then cleared
            $welcome_alert = $this->session->userdata('welcome_alert');
            if(!empty($welcome_alert)):
              $this->session->unset_userdata('welcome_alert');
              ?>
                <div class="alert alert-success alert-dismissable text-center" id="welcome-alert">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= htmlspecialchars($welcome_alert); ?></strong>
              </div>
               <?php
            endif;
            // Capture flashdata once (it's consumed on first read)
            $flash_success = $this->session->flashdata('success');
            $flash_error   = $this->session->flashdata('error');
            $flash_warning = $this->session->flashdata('warning');
            ?>
            <?php if(!empty($flash_success)): ?>
                <div class="alert alert-success alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= htmlspecialchars($flash_success); ?></strong>
              </div>
            <?php endif; ?>
            <?php if(!empty($flash_error)): ?>
                <div class="alert alert-danger alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= htmlspecialchars($flash_error); ?></strong>
              </div>
            <?php endif; ?>
            <?php if(!empty($flash_warning)): ?>
                <div class="alert alert-warning alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= htmlspecialchars($flash_warning); ?></strong>
              </div>
            <?php endif; ?>
            <?php
            // DB update warning: session-flag based, not flashdata
            // Only shows if DB version mismatches AND user hasn't dismissed it
            if(is_admin() && !$this->session->userdata('db_update_dismissed')):
              $CI =& get_instance();
              $db_version = $CI->get_current_version_of_db();
              if($db_version != app_version()):
              ?>
                <div class="alert alert-warning alert-dismissable text-center" id="db-update-warning">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close" onclick="dismiss_update_warning()">&times;</a>
                <strong>Database update available. Please use Settings &rarr; System Update.</strong>
              </div>
              <?php
              endif;
            endif;
            ?>
            <script>
            function dismiss_update_warning(){
              $.post('<?=base_url("dashboard/dismiss_update_warning")?>', {
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
              }, function(){
                $('#db-update-warning').fadeOut();
              }).fail(function(){
                console.error('Failed to dismiss database update warning');
              });
            }
            </script>
            <!-- ********** ALERT MESSAGE END******* -->
     </div>
     <script>
     <?php if(!empty($flash_success)): ?>
     if(typeof toastr !== 'undefined'){ toastr.success(<?= json_encode($flash_success); ?>); }
     <?php endif; ?>
     <?php if(!empty($flash_error)): ?>
     if(typeof toastr !== 'undefined'){ toastr.error(<?= json_encode($flash_error); ?>); }
     <?php endif; ?>
     <?php if(!empty($flash_warning)): ?>
     if(typeof toastr !== 'undefined'){ toastr.warning(<?= json_encode($flash_warning); ?>); }
     <?php endif; ?>
     </script>