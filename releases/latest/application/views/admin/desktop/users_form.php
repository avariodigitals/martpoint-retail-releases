<?php
/* User create/edit form — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
  .mp-user-form { display:grid; grid-template-columns:1fr 1fr; gap:24px 32px; }
  .mp-user-form .mp-form-full { grid-column:1 / -1; }
  .mp-user-form .mp-form-field { display:flex; flex-direction:column; gap:6px; }
  .mp-user-form .mp-form-field label { font-size:13px; font-weight:600; color:var(--mp-ink); margin:0; }
  .mp-user-form .mp-form-field .req { color:var(--mp-danger); margin-left:2px; }
  .mp-user-form .mp-form-field .form-control { width:100%; }
  .mp-user-form .mp-form-field .field-msg { font-size:12px; color:var(--mp-danger); display:none; }
  .mp-user-form .mp-form-field .help { font-size:12px; color:var(--mp-muted); }
  .mp-user-photo { display:flex; flex-direction:column; align-items:center; gap:12px; }
  .mp-user-photo img { width:160px; height:160px; border-radius:16px; object-fit:cover; border:3px solid var(--mp-border); background:var(--mp-bg); }
  .mp-user-photo .photo-help { font-size:12px; color:var(--mp-muted); text-align:center; }
  .mp-user-photo input[type=file] { font-size:13px; }
  @media (max-width:768px){ .mp-user-form{ grid-template-columns:1fr; } }
</style>
<div class="mp-page-head">
  <h1 class="mp-page-title"><?= htmlspecialchars($page_title); ?></h1>
  <p class="mp-page-sub"><?= (!empty($q_id)) ? 'Update user account details and access.' : 'Enter user information to create a new account.'; ?></p>
</div>
<div class="mp-card">
<div class="mp-card-body">
  <form id="users-form" onkeypress="return event.keyCode != 13;" enctype="multipart/form-data">
    <input type="hidden" id="base_url" value="<?= $base_url; ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id ?? ''); ?>">

    <div class="mp-user-form">
      <!-- LEFT COLUMN: Form fields -->
      <div class="mp-user-form-left">
        <?php if(store_module() && is_admin()) {
          $this->load->view('store/store_code', array('show_store_select_box'=>true,'store_id'=>$store_id ?? '','label_length'=>'','div_length'=>''));
        } else { ?>
          <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <?php } ?>

        <div class="mp-form-field">
          <label for="username"><?= $this->lang->line('username'); ?><span class="req">*</span></label>
          <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username ?? ''); ?>" autocomplete="off" <?= (!empty($q_id)) ? 'readonly' : ''; ?> autofocus>
          <span id="username_msg" class="field-msg"></span>
        </div>

        <div class="mp-form-field">
          <label for="new_user"><?= $this->lang->line('first_name'); ?><span class="req">*</span></label>
          <input type="text" class="form-control" id="new_user" name="new_user" value="<?= htmlspecialchars($first_name ?? ''); ?>" autocomplete="off" <?= (!empty($q_id)) ? 'readonly' : ''; ?>>
          <span id="new_user_msg" class="field-msg"></span>
        </div>

        <div class="mp-form-field">
          <label for="last_name"><?= $this->lang->line('last_name'); ?></label>
          <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name ?? ''); ?>" autocomplete="off">
          <span id="last_name_msg" class="field-msg"></span>
        </div>

        <div class="mp-form-field">
          <label for="mobile"><?= $this->lang->line('mobile'); ?></label>
          <input type="text" class="form-control no_special_char_no_space" id="mobile" name="mobile" value="<?= htmlspecialchars($mobile ?? ''); ?>" autocomplete="off">
          <span id="mobile_msg" class="field-msg"></span>
        </div>

        <div class="mp-form-field">
          <label for="email"><?= $this->lang->line('email'); ?><span class="req">*</span></label>
          <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? ''); ?>" autocomplete="off">
          <span id="email_msg" class="field-msg"></span>
        </div>

        <?php
        $selection = '';
        if(isset($q_id) && !empty($q_id) && !is_admin()) {
          $role_row = $this->db->select("store_id,role_name")->from("db_roles")->where("id", $role_id ?? '')->get()->row();
          if(!empty($role_row->store_id) && $role_row->store_id != get_current_store_id()){
            $selection = '<option value="'.($role_id ?? '').'">'.htmlspecialchars($role_row->role_name)."</option>";
          }
        }
        if(empty($q_id) || $q_id != 1):
        ?>
        <div class="mp-form-field">
          <label for="role_id"><?= $this->lang->line('role'); ?><span class="req">*</span></label>
          <select class="form-control select2" id="role_id" name="role_id" style="width:100%;">
            <?php
            if(!empty($selection)){
              echo $selection;
            } else {
              echo get_roles_select_list($role_id ?? '', get_current_store_id());
            }
            ?>
          </select>
          <span id="role_id_msg" class="field-msg"></span>
        </div>
        <?php endif; ?>

        <div class="mp-form-field">
          <label for="pass"><?= $this->lang->line('password'); ?><?php if(empty($q_id)): ?><span class="req">*</span><?php endif; ?></label>
          <input type="password" class="form-control" id="pass" name="pass" placeholder="" autocomplete="off" <?= (!empty($q_id)) ? 'readonly' : ''; ?>>
          <span id="pass_msg" class="field-msg"></span>
        </div>

        <div class="mp-form-field">
          <label for="confirm"><?= $this->lang->line('confirm_password'); ?><?php if(empty($q_id)): ?><span class="req">*</span><?php endif; ?></label>
          <input type="password" class="form-control" id="confirm" name="confirm" placeholder="" autocomplete="off" <?= (!empty($q_id)) ? 'readonly' : ''; ?>>
          <span id="confirm_msg" class="field-msg"></span>
        </div>

        <?php if(mp_feature_enabled('manager_approvals')): ?>
        <div class="mp-form-field">
          <label for="approval_pin">Approval PIN <i class="fa fa-info-circle text-info" title="4-6 digit PIN for manager/owner approval overrides. Leave blank to disable." data-toggle="tooltip"></i></label>
          <input type="password" class="form-control" id="approval_pin" name="approval_pin" maxlength="10" placeholder="4-6 digits" autocomplete="off" <?= (!empty($q_id)) ? 'readonly' : ''; ?>>
          <span id="approval_pin_msg" class="field-msg"></span>
        </div>
        <?php endif; ?>

        <?php
        // Warehouse assignment (only for non-admin users)
        if(empty($q_id) || $q_id != 1):
          $ids = [];
          if(!empty($q_id) && warehouse_module()){
            $q1 = $this->db->select("warehouse_id")->where("user_id", $q_id)->get("db_userswarehouses");
            if($q1->num_rows() > 0){
              foreach($q1->result() as $res1){ $ids[] = $res1->warehouse_id; }
            }
          }
          $wh_store_id = (isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();
          if(warehouse_module()):
            $this->load->view('warehouse/warehouse_code', array(
              'show_warehouse_select_box'=>true,
              'store_id'=>$wh_store_id,
              'custom_id'=>'warehouses',
              'custom_name'=>'warehouses[]',
              'label'=>mp_label('branch','Branches'),
              'div_length'=>'',
              'label_length'=>'',
              'show_select_option'=>false,
              'multiple'=>'multiple',
              'data_placeholder'=>'Multiple',
              'ids' => $ids
            ));
          else:
        ?>
          <input type="hidden" name="warehouses" id="warehouses" value="<?= get_store_warehouse_id(); ?>">
        <?php endif; ?>

        <?php
          // Branch warning
          $diag_store_id = (isset($store_id) && !empty($store_id)) ? $store_id : get_current_store_id();
          $total_wh = $this->db->where('store_id', $diag_store_id)->count_all_results('db_warehouse');
          $active_wh = $this->db->where('store_id', $diag_store_id)->where('status', 1)->count_all_results('db_warehouse');
          $branch_label = mp_label('warehouse','Branch');
          if($total_wh == 0):
        ?>
          <div class="mp-form-full" style="margin-top:8px;">
            <p class="help" style="color:var(--mp-danger);"><i class="fa fa-warning"></i> No <?= htmlspecialchars($branch_label); ?> found for this store. Please create a <?= htmlspecialchars($branch_label); ?> in <a href="<?= base_url('warehouse'); ?>">Settings &gt; <?= htmlspecialchars(mp_label('branch','Branches')); ?></a>.</p>
          </div>
        <?php elseif($active_wh == 0): ?>
          <div class="mp-form-full" style="margin-top:8px;">
            <p class="help" style="color:var(--mp-danger);"><i class="fa fa-warning"></i> All <?= htmlspecialchars($branch_label); ?> are inactive. Please activate a <?= htmlspecialchars($branch_label); ?> in <a href="<?= base_url('warehouse'); ?>">Settings &gt; <?= htmlspecialchars(mp_label('branch','Branches')); ?></a>.</p>
          </div>
        <?php endif; ?>

        <?php
          // Default warehouse
          if(empty($q_id) || $q_id != 1):
        ?>
        <div class="mp-form-field warehouse_parent">
          <label for="default_warehouse_id"><?= $this->lang->line('default_warehouse'); ?><span class="req">*</span></label>
          <select class="form-control select2" id="default_warehouse_id" name="default_warehouse_id" style="width:100%;">
            <option value="">-Select-</option>
            <?= get_warehouse_select_list($default_warehouse_id ?? '', $wh_store_id); ?>
          </select>
          <span id="default_warehouse_id_msg" class="field-msg"></span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- RIGHT COLUMN: Profile picture -->
      <div class="mp-user-form-right">
        <div class="mp-user-photo">
          <img id="profile_preview" src="<?= base_url($profile_picture ?? 'uploads/profile_pictures/no_image.png'); ?>" alt="Profile Picture">
          <div class="photo-help">Max Width/Height: 500px x 500px<br>Size: 500KB</div>
          <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
        </div>
      </div>
    </div>

    <div class="mp-form-actions" style="margin-top:24px; display:flex; gap:12px; justify-content:center;">
      <?php $is_edit = (!empty($q_id)); ?>
      <button type="button" id="<?= $is_edit ? 'update' : 'save'; ?>" class="mp-qa-btn green"><i class="fa fa-save"></i> <?= $is_edit ? 'Update' : 'Save'; ?></button>
      <a href="<?= base_url('users/view'); ?>" class="mp-qa-btn">Close</a>
    </div>
  </form>
</div>
</div>

<script src="<?= $theme_link; ?>js/users.js"></script>
<script type="text/javascript">
  <?php if(isset($q_id) && !empty($q_id)): ?>
  $("#store_id").attr('readonly', true);
  <?php endif; ?>

  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    var store_id = $(this).val();
    $.post(base_url + "sales/get_warehouse_select_list", {store_id: store_id}, function(result){
      $("#warehouses").html('').append(result).select2();
      $("#default_warehouse_id").html('').append(result).select2();
      load_roles();
    });
  });

  function load_roles() {
    var store_id = $("#store_id").val();
    $.post(base_url + "users/get_roles_select_list", {store_id: store_id}, function(result){
      $("#role_id").html('').append(result).select2();
    });
  }

  function hide_if_admin_and_store_admin(){
    var role_id = $("#role_id").val();
    if(role_id == <?= store_admin_id(); ?>){
      $(".warehouse_parent").hide();
    } else {
      $(".warehouse_parent").show();
    }
  }
  $("#role_id").on("change", function(){ hide_if_admin_and_store_admin(); });
  hide_if_admin_and_store_admin();

  // Profile picture preview
  $("#profile_picture").on("change", function(e){
    var reader = new FileReader();
    reader.onload = function(e){ $("#profile_preview").attr("src", e.target.result); };
    reader.readAsDataURL(e.target.files[0]);
  });

  // Sidebar active state
  $(".users-view-active-li").addClass("active");
  $(".users-view-active-li").closest(".mp-nav-group").addClass("open");
</script>
