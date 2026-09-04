<?php $this->load->view('customers/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
if (!isset($customer_name)) {
    $customer_name = $mobile = $phone = $email =
    $country_id = $state_id = $city = $postcode = $address = $shipping_location_link =
    $shipping_country = $shipping_state = $shipping_city = $shipping_postcode = $shipping_address =
    $supplier_code = $gstin = $tax_number = $location_link = $attachment_1 =
    $state_code = $customer_code = $store_name = $company_mobile = $store_id = '';
    $price_level_type = 'Increase';
    $price_level = '0';
    $opening_balance = 0;
    $credit_limit = '0';
    $q_id = '';
    $notes = '';
    $birthday = '';
    $nin_bvn = '';
    $nin_verified = 0;
    $nin_verified_at = '';
    $loyalty_points = 0;
    $loyalty_tier = 'Bronze';
    $store_credit_balance = 0;
    $gift_card_balance = 0;
    $referral_code = '';
}
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Enter User Information</div>
  </div>
</div>

<form class="form-horizontal box" id="customers-form" method="post">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?= $base_url; ?>">
  <input type="hidden" name="store_id" id="store_id" value="<?= !empty($store_id) ? $store_id : get_current_store_id(); ?>">
  <?php if (isset($q_id) && $q_id != ''): ?>
    <input type="hidden" name="q_id" id="q_id" value="<?= $q_id; ?>">
  <?php endif; ?>

  <!-- Customer Information -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Customer Information</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="customer_name"><?= mp_label('customer'); ?> Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control mp-form-control" id="customer_name" name="customer_name" value="<?php print $customer_name; ?>">
          <span id="customer_name_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="mobile"><?= $this->lang->line('mobile'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="form-control mp-form-control no_special_char_no_space" id="mobile" name="mobile" placeholder="+1234567890" value="<?php print $mobile; ?>" required>
          <span id="mobile_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="email"><?= $this->lang->line('email'); ?></label>
          <input type="text" class="form-control mp-form-control" id="email" name="email" value="<?php print $email; ?>">
          <span id="email_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="phone"><?= $this->lang->line('phone'); ?></label>
          <input type="text" class="form-control mp-form-control no_special_char_no_space" id="phone" name="phone" value="<?php print $phone; ?>">
          <span id="phone_msg" style="display:none" class="text-danger"></span>
        </div>
        <?php if (gst_number()): ?>
          <div class="mp-form-group">
            <label for="gstin"><?= $this->lang->line('gst_number'); ?></label>
            <input type="text" class="form-control mp-form-control" id="gstin" name="gstin" value="<?php print $gstin; ?>">
            <span id="gstin_msg" style="display:none" class="text-danger"></span>
          </div>
        <?php endif; ?>
        <div class="mp-form-group">
          <label for="tax_number"><?= $this->lang->line('tax_number'); ?></label>
          <input type="text" class="form-control mp-form-control" id="tax_number" name="tax_number" value="<?php print $tax_number; ?>">
          <span id="tax_number_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="credit_limit"><?= $this->lang->line('credit_limit'); ?></label>
          <input type="text" class="form-control mp-form-control only_currency" id="credit_limit" name="credit_limit" value="<?php print store_number_format($credit_limit, 0); ?>">
          <p class="mp-form-hint">0 = No Credit Allowed, -1 = No Limit</p>
          <span id="credit_limit_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="opening_balance"><?= $this->lang->line('previous_due'); ?></label>
          <input type="text" class="form-control mp-form-control only_currency" id="opening_balance" name="opening_balance" value="<?php print store_number_format($opening_balance, 0); ?>">
          <span id="opening_balance_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="nin_bvn">NIN / BVN <small class="mp-form-hint">(Nigeria)</small></label>
          <div class="input-group">
            <input type="text" class="form-control mp-form-control" id="nin_bvn" name="nin_bvn" placeholder="Enter NIN or BVN" value="<?php print $nin_bvn; ?>" maxlength="11">
            <span class="input-group-btn">
              <button type="button" class="btn btn-info" id="btn_verify_nin" title="Verify NIN/BVN"><i class="fa fa-search"></i> Verify</button>
            </span>
          </div>
          <div id="nin_status" style="margin-top:4px;"></div>
          <input type="hidden" id="nin_verified" name="nin_verified" value="<?php print ($nin_verified ? 1 : 0); ?>">
          <?php if ($nin_verified): ?>
            <span class="mp-pill paid"><i class="fa fa-check"></i> NIN Verified</span>
            <?php if (!empty($nin_verified_at)): ?>
              <small class="mp-form-hint"><?= show_date($nin_verified_at); ?></small>
            <?php endif; ?>
          <?php endif; ?>
          <span id="nin_bvn_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="attachment_1"><?= $this->lang->line('attachment_1'); ?></label>
          <input type="file" class="mp-form-control" name="attachment_1" id="attachment_1">
          <span id="attachment_1_msg" style="display:block;" class="text-danger">Size: 2MB</span>
          <span onclick="show_attachment('<?= (empty($attachment_1)) ? "" : base_url($attachment_1); ?>')" class="mp-pill ok" style="cursor:pointer;">Click to view</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Loyalty & Rewards -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Loyalty & Rewards</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="loyalty_points">Loyalty Points</label>
          <input type="text" class="form-control mp-form-control" id="loyalty_points" name="loyalty_points" value="<?= isset($loyalty_points) ? $loyalty_points : 0; ?>" readonly>
        </div>
        <div class="mp-form-group">
          <label for="loyalty_tier">Current Tier</label>
          <input type="text" class="form-control mp-form-control" id="loyalty_tier" name="loyalty_tier" value="<?= isset($loyalty_tier) ? $loyalty_tier : 'Bronze'; ?>" readonly>
        </div>
        <div class="mp-form-group">
          <label for="store_credit_balance">Store Credit</label>
          <input type="text" class="form-control mp-form-control" id="store_credit_balance" name="store_credit_balance" value="<?= isset($store_credit_balance) ? store_number_format($store_credit_balance) : 0; ?>" readonly>
        </div>
        <div class="mp-form-group">
          <label for="gift_card_balance">Gift Card Balance</label>
          <input type="text" class="form-control mp-form-control" id="gift_card_balance" name="gift_card_balance" value="<?= isset($gift_card_balance) ? store_number_format($gift_card_balance) : 0; ?>" readonly>
        </div>
        <div class="mp-form-group">
          <label for="birthday">Birthday</label>
          <input type="date" class="form-control mp-form-control" id="birthday" name="birthday" value="<?= isset($birthday) ? $birthday : ''; ?>">
        </div>
        <div class="mp-form-group">
          <label for="referral_code">Referral Code</label>
          <input type="text" class="form-control mp-form-control" id="referral_code" name="referral_code" value="<?= isset($referral_code) ? $referral_code : ''; ?>" readonly>
        </div>
      </div>
    </div>
  </div>

  <!-- Address Details -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Address Details</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="country"><?= $this->lang->line('country'); ?></label>
          <select class="form-control select2" id="country" name="country" style="width:100%;">
            <?= get_country_select_list($country_id, true); ?>
          </select>
          <span id="country_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="state"><?= $this->lang->line('state'); ?></label>
          <select class="form-control select2" id="state" name="state" style="width:100%;">
            <?php
              $country_name = '';
              if (!empty($country_id)) {
                $country_row = get_country_details($country_id);
                if ($country_row) $country_name = $country_row->country;
              }
              echo get_state_select_list_by_country($country_name, $state_id);
            ?>
          </select>
          <span id="state_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="city"><?= $this->lang->line('city'); ?></label>
          <select class="form-control select2" id="city" name="city" style="width:100%;">
            <?= get_city_select_list($state_id, $city); ?>
          </select>
          <span id="city_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="postcode"><?= $this->lang->line('postcode'); ?></label>
          <input type="text" class="form-control mp-form-control no_special_char_no_space" id="postcode" name="postcode" value="<?php print $postcode; ?>">
          <span id="postcode_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group full">
          <label for="address"><?= $this->lang->line('address'); ?></label>
          <textarea class="form-control mp-form-control" id="address" name="address" rows="3"><?php print $address; ?></textarea>
          <span id="address_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group full">
          <label for="location_link"><?= $this->lang->line('location_link'); ?></label>
          <input type="text" class="form-control mp-form-control" id="location_link" name="location_link" value="<?php print $location_link; ?>">
          <span id="location_link_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Shipping Address -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Shipping Address</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid" style="margin-bottom:12px;">
        <div class="mp-form-group">
          <label for="copy_address" class="checkbox">
            <input type="checkbox" id="copy_address" name="copy_address"> <?= $this->lang->line('copy_address'); ?>
          </label>
          <span id="copy_address_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="shipping_country"><?= $this->lang->line('country'); ?></label>
          <select class="form-control select2" id="shipping_country" name="shipping_country" style="width:100%;">
            <?= get_country_select_list($shipping_country, true); ?>
          </select>
          <span id="shipping_country_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="shipping_state"><?= $this->lang->line('state'); ?></label>
          <select class="form-control select2" id="shipping_state" name="shipping_state" style="width:100%;">
            <?php
              $shipping_country_name = '';
              if (!empty($shipping_country)) {
                $shipping_country_row = get_country_details($shipping_country);
                if ($shipping_country_row) $shipping_country_name = $shipping_country_row->country;
              }
              echo get_state_select_list_by_country($shipping_country_name, $shipping_state);
            ?>
          </select>
          <span id="shipping_state_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="shipping_city"><?= $this->lang->line('city'); ?></label>
          <select class="form-control select2" id="shipping_city" name="shipping_city" style="width:100%;">
            <?= get_city_select_list($shipping_state, $shipping_city); ?>
          </select>
          <span id="shipping_city_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="shipping_postcode"><?= $this->lang->line('postcode'); ?></label>
          <input type="text" class="form-control mp-form-control no_special_char_no_space" id="shipping_postcode" name="shipping_postcode" value="<?php print $shipping_postcode; ?>">
          <span id="shipping_postcode_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group full">
          <label for="shipping_address"><?= $this->lang->line('address'); ?></label>
          <textarea class="form-control mp-form-control" id="shipping_address" name="shipping_address" rows="3"><?php print $shipping_address; ?></textarea>
          <span id="shipping_address_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group full">
          <label for="shipping_location_link"><?= $this->lang->line('location_link'); ?></label>
          <input type="text" class="form-control mp-form-control" id="shipping_location_link" name="shipping_location_link" value="<?php print $shipping_location_link; ?>">
          <span id="shipping_location_link_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Advanced Pricing -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Advanced</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="price_level_type"><?= $this->lang->line('price_level_type'); ?></label>
          <select class="form-control select2" id="price_level_type" name="price_level_type" style="width:100%;">
            <option value="Increase">Increase</option>
            <option value="Decrease">Decrease</option>
          </select>
          <span id="price_level_type_msg" style="display:none" class="text-danger"></span>
        </div>
        <div class="mp-form-group">
          <label for="price_level"><?= $this->lang->line('price_level'); ?></label>
          <div class="input-group">
            <input type="text" class="form-control mp-form-control" id="price_level" name="price_level" value="<?php print $price_level; ?>">
            <span class="input-group-addon"><i class="fa fa-percent text-primary fa-lg"></i></span>
          </div>
          <span id="price_level_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Notes -->
  <div class="mp-card">
    <div class="mp-card-head"><h3>Notes & History</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-group full">
        <label for="notes">Customer Notes</label>
        <textarea class="form-control mp-form-control" id="notes" name="notes" rows="8" placeholder="Add notes about this customer (e.g., complaints, preferences, treatment history)"><?php print (isset($notes) ? $notes : ''); ?></textarea>
        <span id="notes_msg" style="display:none" class="text-danger"></span>
        <p class="mp-form-hint">Notes are automatically appended with timestamps when entered during pharmacy sales.</p>
      </div>
    </div>
  </div>

  <!-- Submit -->
  <div class="mp-form-actions" style="margin-bottom:24px;">
    <?php
      if ($customer_name != "") {
        $btn_name = "Update";
        $btn_id = "update";
      } else {
        $btn_name = "Save";
        $btn_id = "save";
      }
    ?>
    <button type="button" id="<?= $btn_id; ?>" class="mp-btn-primary" title="Save Data"><?= $btn_name; ?></button>
    <a href="<?= base_url('customers'); ?>" class="mp-btn-secondary">Close</a>
  </div>
</form>

<!-- Opening Balance Payments -->
<?php if (isset($q_id) && $q_id != ''): ?>
<div class="mp-card">
  <div class="mp-card-head"><h3><?= $this->lang->line('opening_balance_payments'); ?></h3></div>
  <div class="mp-card-body" style="padding:0;">
    <table class="table mp-static-table" id="report-data">
      <thead>
        <tr>
          <th>#</th>
          <th><?= $this->lang->line('payment_date'); ?></th>
          <th><?= $this->lang->line('payment'); ?></th>
          <th><?= $this->lang->line('payment_type'); ?></th>
          <th><?= $this->lang->line('payment_note'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $q3 = $CI->db->query("select * from db_salespayments where customer_id=$q_id and short_code='OPENING BALANCE PAID'");
          if ($q3->num_rows() > 0) {
            $i = 1;
            $total_paid = 0;
            foreach ($q3->result() as $res3) {
              $total_paid += $res3->payment;
              echo "<tr>";
              echo "<td>" . $i . "</td>";
              echo "<td>" . show_date($res3->payment_date) . "</td>";
              echo "<td class='text-right'>" . $CI->currency($res3->payment) . "</td>";
              echo "<td>" . $res3->payment_type . "</td>";
              echo "<td>" . $res3->payment_note . "</td>";
              echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_opening_balance_entry(' . $res3->id . ')"> Delete</i></td>';
              echo "</tr>";
              $i++;
            }
            echo "<tr class='text-bold'><td colspan='2' class='text-right'>Total</td><td class='text-right'>" . $CI->currency($total_paid) . "</td><td colspan='3'></td></tr>";
          } else {
            echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Stock Entry Found!!</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<script src="<?= $theme_link; ?>js/customers.js?v=2"></script>
<script type="text/javascript">
  function show_attachment(imagepath){
    if (imagepath == '' || imagepath == null) {
      toastr["warning"]("No Attachment Available!");
      if (typeof failed !== 'undefined') { failed.currentTime = 0; failed.play(); }
      return false;
    } else {
      window.open(imagepath, "_blank");
    }
  }

  $(document).ready(function(){
    <?php if (isset($q_id) && $q_id != ''): ?>
      $("#store_id").attr('readonly', true);
    <?php endif; ?>

    $("#price_level_type").val('<?= $price_level_type; ?>').trigger('change');

    $("#copy_address").on("ifChanged", function(event){
      if (event.target.checked) {
        $("#shipping_country").val($("#country").val()).select2();
        $("#shipping_state").val($("#state").val()).select2();
        $("#shipping_postcode").val($("#postcode").val());
        $("#shipping_city").val($("#city").val());
        $("#shipping_address").val($("#address").val());
        $("#shipping_location_link").val($("#location_link").val());
      } else {
        $("#shipping_country").val('').select2();
        $("#shipping_state").val('').select2();
        $("#shipping_postcode").val('');
        $("#shipping_city").val('');
        $("#shipping_address").val('');
        $("#shipping_location_link").val('');
      }
    });
  });
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  <?php if ($is_edit): ?>
  $('.customers_list-active-li').addClass('active');
  <?php else: ?>
  $('.customers_add-active-li').addClass('active');
  <?php endif; ?>
  $('.customers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
