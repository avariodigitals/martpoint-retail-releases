<?php $this->load->view('suppliers/desktop/_styles'); ?>
<?php
$CI =& get_instance();
if(!isset($supplier_name)){
   $supplier_name=$mobile=$phone=$email=$country_id=$state_id=$city=
   $postcode=$address=$supplier_code=$gstin=$pan=$state_code=
   $store_name=$company_mobile=$tax_number=$country_id=$state_id=$store_id='';
   $opening_balance=0;
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update supplier details' : 'Create a new supplier'; ?></div>
  </div>
</div>

<div class="mp-card-form box">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Supplier' : 'Add Supplier'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="suppliers-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="supplier_name"><?= $this->lang->line('supplier_name'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="form-control mp-form-control" id="supplier_name" name="supplier_name" placeholder="" value="<?php print $supplier_name; ?>" autofocus>
          <span id="supplier_name_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="mobile"><?= $this->lang->line('mobile'); ?></label>
          <input type="text" class="form-control no_special_char_no_space mp-form-control" id="mobile" name="mobile" placeholder="+1234567890" value="<?php print $mobile; ?>">
          <span id="mobile_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="email"><?= $this->lang->line('email'); ?></label>
          <input type="text" class="form-control mp-form-control" id="email" name="email" placeholder="" value="<?php print $email; ?>">
          <span id="email_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="phone"><?= $this->lang->line('phone'); ?></label>
          <input type="text" class="form-control no_special_char_no_space mp-form-control" id="phone" name="phone" placeholder="" value="<?php print $phone; ?>">
          <span id="phone_msg" style="display:none" class="text-danger"></span>
        </div>

        <?php if(gst_number()) { ?>
        <div class="mp-form-group">
          <label for="gstin"><?= $this->lang->line('gst_number'); ?></label>
          <input type="text" class="form-control mp-form-control" id="gstin" name="gstin" placeholder="" value="<?php print $gstin; ?>">
          <span id="gstin_msg" style="display:none" class="text-danger"></span>
        </div>
        <?php } ?>

        <div class="mp-form-group">
          <label for="tax_number"><?= $this->lang->line('tax_number'); ?></label>
          <input type="text" class="form-control mp-form-control" id="tax_number" name="tax_number" placeholder="" value="<?php print $tax_number; ?>">
          <span id="tax_number_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="opening_balance"><?= $this->lang->line('opening_balance'); ?></label>
          <input type="text" class="form-control mp-form-control" id="opening_balance" name="opening_balance" placeholder="" value="<?php print store_number_format($opening_balance,0); ?>">
          <span id="opening_balance_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="country"><?= $this->lang->line('country'); ?></label>
          <select class="form-control select2 mp-form-control" id="country" name="country" style="width:100%;">
            <?= get_country_select_list($country_id,true); ?>
          </select>
          <span id="country_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="state"><?= $this->lang->line('state'); ?></label>
          <select class="form-control select2 mp-form-control" id="state" name="state" style="width:100%;">
            <?php
              $country_name = '';
              if(!empty($country_id)){
                $country_row = get_country_details($country_id);
                if($country_row) $country_name = $country_row->country;
              }
              echo get_state_select_list_by_country($country_name, $state_id);
            ?>
          </select>
          <span id="state_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="city"><?= $this->lang->line('city'); ?></label>
          <select class="form-control select2 mp-form-control" id="city" name="city" style="width:100%;">
            <?= get_city_select_list($state_id, $city); ?>
          </select>
          <span id="city_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="postcode"><?= $this->lang->line('postcode'); ?></label>
          <input type="text" class="form-control no_special_char_no_space mp-form-control" id="postcode" name="postcode" placeholder="" value="<?php print $postcode; ?>">
          <span id="postcode_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="address"><?= $this->lang->line('address'); ?></label>
          <textarea class="form-control mp-form-control" id="address" name="address" rows="3"><?php print $address; ?></textarea>
          <span id="address_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('suppliers'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<?php if($is_edit){ ?>
<div class="mp-card box" style="margin-top:24px;">
  <div class="mp-card-head">
    <h3><?= $this->lang->line('opening_balance_payments'); ?></h3>
  </div>
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
          $q3 = $this->db->query("select * from db_purchasepayments where supplier_id=$q_id and short_code='OPENING BALANCE PAID'");
          if($q3->num_rows()>0){
            $i=1;
            $total_paid = 0;
            foreach ($q3->result() as $res3) {
              $total_paid +=$res3->payment;
              echo "<tr>";
              echo "<td>".$i."</td>";
              echo "<td>".show_date($res3->payment_date)."</td>";
              echo "<td class='text-right'>".$CI->currency($res3->payment)."</td>";
              echo "<td>".$res3->payment_type."</td>";
              echo "<td>".$res3->payment_note."</td>";
              echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_opening_balance_entry('.$res3->id.')"> Delete</i></td>';
              echo "</tr>";
              $i++;
            }
            echo "<tr class='text-bold'>
                    <td colspan=2 class='text-right '>Total</td>
                    <td class='text-right'>".$CI->currency($total_paid)."</td>
                    <td colspan=3></td>
                  </tr>";
          }
          else{
            echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Stock Entry Found!!</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<script src="<?= htmlspecialchars($theme_link); ?>js/suppliers.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  <?php if ($is_edit): ?>
  $('.suppliers_list-active-li').addClass('active');
  <?php else: ?>
  $('.suppliers_add-active-li').addClass('active');
  <?php endif; ?>
  $('.suppliers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
