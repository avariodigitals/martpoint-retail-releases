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
         <?php
            if(!isset($username)){
              $username=$mobile=$email=$q_id=$role_id=$store_id='';
              $readonly=$warehouses=
              $first_name=
              $last_name=
              $default_warehouse_id='';
            }else{
              $readonly= '';//'readonly="readonly"';
            }
            if(empty($profile_picture)){
              $profile_picture = 'theme/dist/img/avatar5.png';
            }
            
            //  $disabled = ($q_id==1)? 'disabled' : '';
            
            ?>
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1>
                  <?= $page_title; ?>
                  <small>Enter User Information</small>
               </h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li><a href="<?php echo $base_url; ?>users/view"><?= $this->lang->line('view_users'); ?></a></li>
                  <li class="active"><?= $page_title; ?></li>
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
                     <div class="box box-primary ">
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form class="form-horizontal" id="users-form"  onkeypress="return event.keyCode != 13;">
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <div class="box-body">
                            <div class="row">
                              <div class="col-md-6">
                                <!-- Store Code -->
                               <?php if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>$store_id,'label_length'=>'col-sm-4','div_length'=>'col-sm-8')); }else{
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              }?>
                              <!-- Store Code end -->

                              <div class="form-group">
                                 <label for="username" class="col-sm-4 control-label"><?= $this->lang->line('username'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm" id="username" name="username" placeholder="" value="<?php print $username; ?>" autocomplete='off' <?=$readonly;?> autofocus>
                                    <span id="username_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>

                              <div class="form-group">
                                 <label for="new_user" class="col-sm-4 control-label"><?= $this->lang->line('first_name'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm" id="new_user" name="new_user" placeholder="" value="<?php print $first_name; ?>" autocomplete='off' <?=$readonly;?> autofocus>
                                    <span id="new_user_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="last_name" class="col-sm-4 control-label"><?= $this->lang->line('last_name'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm "  id="last_name" name="last_name" autocomplete='off' placeholder="" value="<?= $last_name; ?>"  >
                                    <span id="last_name_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="mobile" class="col-sm-4 control-label"><?= $this->lang->line('mobile'); ?></label>
                                 <div class="col-sm-8">
                                    <input type="text" class="form-control input-sm no_special_char_no_space"  id="mobile" name="mobile" autocomplete='off' placeholder="" value="<?php print $mobile; ?>">
                                    <span id="mobile_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="email" class="col-sm-4 control-label"><?= $this->lang->line('email'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-8">
                                    <input type="text" autocomplete='off' class="form-control input-sm" value="<?php print $email; ?>"  id="email" name="email" placeholder="" >
                                    <span id="email_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <?php 
                              $selection = '';
                              if(isset($q_id) && !empty($q_id) && !is_admin()) {

                                $this->db->select("role_id")->where("id",$q_id)->from("db_users");
                                
                                $role_id =$this->db->get()->row()->role_id;
                                if(!empty($role_id)){
                                  $q3 = $this->db->select("store_id,role_name")->from("db_roles")->where("id",$role_id)->get()->row();

                                  if(!empty($q3->store_id) && $q3->store_id!=get_current_store_id()){
                                    $selection = '<option value="'.$role_id.'">'.$q3->role_name."</option>";
                                  }
                                }
                                
                              }

                              if($q_id!=1){

                                ?>
                              
                              <div class="form-group">
                                 <label for="role_id" class="col-sm-4 control-label"><?= $this->lang->line('role'); ?><label class="text-danger">*</label> </label>
                                 <div class="col-sm-8">
                                    <select class="form-control select2" <?=$readonly;?> id="role_id" name="role_id"  style="width: 100%;">
                                       <?php
                                       if(!empty($selection)){
                                        echo $selection;
                                       }
                                       else{

                                          $store_id = get_current_store_id();

                                          echo get_roles_select_list($role_id,$store_id);

                                        
                                       }
                                          
                                          ?>
                                    </select>
                                    <span id="role_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                            <?php } ?>

                              <div class="form-group">
                                 <label for="pass" class="col-sm-4 control-label"><?= $this->lang->line('password'); ?>
                                 <?php if(empty($username)){ ?>
                                 <label class="text-danger">*</label>
                                <?php } ?>
                               </label>
                                 <div class="col-sm-8">
                                    <input type="password" autocomplete='off' class="form-control input-sm"  <?php print $readonly; ?> id="pass" name="pass" placeholder="">
                                    <span id="pass_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="confirm" class="col-sm-4 control-label"><?= $this->lang->line('confirm_password'); ?>
                                 <?php if(empty($username)){ ?>
                                 <label class="text-danger">*</label>
                                <?php } ?></label>
                                 <div class="col-sm-8">
                                    <input type="password" autocomplete="off" class="form-control input-sm" <?php print $readonly; ?> id="confirm" name="confirm" placeholder="">
                                    <span id="confirm_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <?php if(mp_feature_enabled('manager_approvals')) { ?>
                              <div class="form-group">
                                 <label for="approval_pin" class="col-sm-4 control-label">Approval PIN <i class="fa fa-info-circle text-info" title="4-6 digit PIN for manager/owner approval overrides. Leave blank to disable." data-toggle="tooltip"></i></label>
                                 <div class="col-sm-8">
                                    <input type="password" autocomplete="off" maxlength="10" class="form-control input-sm" <?php print $readonly; ?> id="approval_pin" name="approval_pin" placeholder="4-6 digits">
                                    <span id="approval_pin_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <?php } ?>

                              <!-- Warehouse Code -->
                              <?php 

                              //For Selection Box while updating
                              if($q_id!=1){
                                $ids = array();
                                if(!empty($username) && warehouse_module()){
                                    $q1 = $this->db->select("warehouse_id")->where("user_id",$q_id)->get("db_userswarehouses");
                                    if($q1->num_rows()>0){
                                      foreach ($q1->result() as $res1) { 
                                        $ids[] = $res1->warehouse_id; 
                                      }
                                    } 
                                  }

                                  $store_id = (isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();
                                 if(warehouse_module()) {$this->load->view('warehouse/warehouse_code',
                                    array(
                                          'show_warehouse_select_box'=>true,
                                          'store_id'=>$store_id,
                                          'custom_id'=>'warehouses',
                                          'custom_name'=>'warehouses[]',
                                          'label'=>$this->lang->line('warehouses_group'),
                                          'div_length'=>'col-sm-8',
                                          'label_length'=>'col-sm-4',
                                          'show_select_option'=>true,
                                          'multiple'=>'multiple',
                                          'show_select_option'=>false,
                                          'data_placeholder'=>'Multiple',
                                          'ids' => $ids
                                        )); 
                               }else{
                                  echo "<input type='hidden' name='warehouses' id='warehouses' value='".get_store_warehouse_id()."'>";
                                 }
                             }
                              ?>
                              <!-- Warehouse Code end -->
                              <?php
                                $diag_store_id = (isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();
                                $total_wh = $this->db->where('store_id',$diag_store_id)->count_all_results('db_warehouse');
                                $active_wh = $this->db->where('store_id',$diag_store_id)->where('status',1)->count_all_results('db_warehouse');
                                if($total_wh==0){
                                  echo '<div class="col-sm-8 col-sm-offset-4"><p class="text-danger"><i class="fa fa-warning"></i> No warehouses found for this store. Please create a warehouse in <a href="'.base_url('warehouse').'">Settings &gt; Warehouse</a>.</p></div>';
                                } else if($active_wh==0){
                                  echo '<div class="col-sm-8 col-sm-offset-4"><p class="text-danger"><i class="fa fa-warning"></i> All warehouses are inactive. Please activate a warehouse in <a href="'.base_url('warehouse').'">Settings &gt; Warehouse</a>.</p></div>';
                                }
                              ?>

                              <!-- Assign Default warehouse to user-->
                               <?php 
                              if($q_id!=1){
                                    $store_id = (isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();
                                 ?>
                                 <div class="form-group warehouse_parent">
                                 <label for="default_warehouse_id" class="col-sm-4 control-label"><?= $this->lang->line('default_warehouse'); ?><label class="text-danger">*</label> </label>
                                 <div class="col-sm-8">
                                    <select class="form-control select2" <?=$readonly;?> id="default_warehouse_id" name="default_warehouse_id"  style="width: 100%;">
                                       <option>-Select-</option>
                                       <?= get_warehouse_select_list($default_warehouse_id,$store_id) ?>
                                    </select>
                                    <span id="default_warehouse_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>

                              <?php }
                              ?>
                              <!-- Assign Default warehouse to user END-->
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                     <label for="address" class="col-sm-4 control-label"><?= $this->lang->line('profile_picture'); ?></label>
                                     <div class="col-sm-8">
                                        <input type="file" id="profile_picture" name="profile_picture">
                                        <span id="logo_msg" style="display:block;" class="text-danger">Max Width/Height: 500px * 500px & Size: 500Kb </span>
                                     </div>
                                  </div>
                               </div>
                               <div class="col-md-6 ">
                                  <div class="form-group">
                                     <div class="col-sm-8 col-sm-offset-4">
                                        <img width="200px" height="200px" class='img-responsive' style='border:3px solid #d2d6de;' src="<?php echo base_url($profile_picture);?>">
                                     </div>
                                  </div>
                               </div>
                              </div>
                              
                            </div>
                              

                            <div class="box-footer">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <!-- <div class="col-sm-4"></div> -->
                                 <?php
                                    if($username!=""){
                                         $btn_name="Update";
                                         $btn_id="update";
                                    
                                    }
                                              else{
                                                  $btn_name="Save";
                                                  $btn_id="save";
                                              }
                                    
                                              ?>
                                 <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id;?>"/>
                                 <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="<?php echo $btn_id;?>" class=" btn btn-block btn-success" title="Save Data"><?php echo $btn_name;?></button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           
                        </form>
                           </div>
                           <!-- /.box-body -->
                           
                           <!-- /.box-footer -->
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
              
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
      <script src="<?php echo $theme_link; ?>js/users.js"></script>
      <script type="text/javascript">
        <?php if(isset($q_id) && !empty($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
      </script>
      <script type="text/javascript">
        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$(this).val();
          $.post(base_url+"sales/get_warehouse_select_list",{store_id:store_id},function(result){
              $("#warehouses").html('').append(result).select2();
              $("#default_warehouse_id").html('').append(result).select2();

              console.log("load_roles();");
              load_roles();
          });


        });

        function load_roles() {
           var store_id=$("#store_id").val();
             $.post(base_url+"users/get_roles_select_list",{store_id:store_id},function(result){
                 $("#role_id").html('').append(result).select2();
             });
        }

        function hide_if_admin_and_store_admin(){
          var role_id=$("#role_id").val();
          if(role_id==<?=store_admin_id()?>){
            $(".warehouse_parent").hide()
          }
          else{
            $(".warehouse_parent").show()
          }
        }
        $("#role_id").on("change",function(){
          hide_if_admin_and_store_admin();
        });
        hide_if_admin_and_store_admin();
        <?php 

        ?>
      </script>
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
   </body>
</html>
