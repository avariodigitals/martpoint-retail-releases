<div class="col-md-12">
      <!-- ********** ALERT MESSAGE START******* -->
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
                  <?= $this->lang->line('subscription_msg_1'); ?> Please click <a href='<?=base_url('subscription')?>'>here</a> to Activate!
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

            $expire_date = get_subscription_rec($subscription_id)->expire_date;
            if($expire_date<date('Y-m-d')){
              $message = "Store Subscription expired!!";
            }

            if(!empty($message)){ ?>
              <div class="alert alert-success  text-left">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>
                  <?=$message?>, Click <a href='<?=base_url('subscription')?>'>here</a> to Activate!
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
            if($this->session->flashdata('success')!=''):
              ?>
                <div class="alert alert-success alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= $this->session->flashdata('success') ?></strong>
              </div> 
               <?php 
            endif;
            if($this->session->flashdata('error')!=''):
              ?>
                <div class="alert alert-danger alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= $this->session->flashdata('error') ?></strong>
              </div> 
               <?php
            endif;
            if($this->session->flashdata('warning')!=''):
              ?>
                <div class="alert alert-warning alert-dismissable text-center">
                 <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?= $this->session->flashdata('warning') ?></strong>
              </div> 
               <?php
            endif;
            ?>
            <!-- ********** ALERT MESSAGE END******* -->
     </div>