<?php $CI =& get_instance(); ?>
<style>
  #sales_item .modal-dialog{ max-width: 560px; }
  #sales_item .modal-content{ border-radius: 16px; border: 1px solid var(--mp-border); box-shadow: var(--mp-shadow); overflow: hidden; }
  #sales_item .modal-header{ background: var(--mp-surface); border-bottom: 1px solid var(--mp-border); padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; }
  #sales_item .modal-header .modal-title{ font-size: 16px; font-weight: 700; color: var(--mp-text); margin: 0; }
  #sales_item .modal-header .close{ opacity: .6; font-size: 22px; }
  #sales_item .modal-body{ padding: 20px; background: var(--mp-surface); }
  #sales_item .mp-modal-item-name{ font-size: 15px; font-weight: 700; color: var(--mp-ink); margin-bottom: 16px; padding: 12px 14px; background: var(--mp-bg); border-radius: 10px; }
  #sales_item .mp-modal-grid{ display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  #sales_item .mp-modal-full{ grid-column: 1 / -1; }
  #sales_item .mp-modal-field label{ display: block; font-size: 12px; font-weight: 600; color: var(--mp-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .03em; }
  #sales_item .mp-modal-field input, #sales_item .mp-modal-field select, #sales_item .mp-modal-field textarea{
    width: 100%; border: 1px solid var(--mp-border); border-radius: 10px; padding: 10px 12px;
    font-size: 14px; font-family: inherit; color: var(--mp-text); background: var(--mp-surface); outline: none; transition: border .15s, box-shadow .15s;
  }
  #sales_item .mp-modal-field input:focus, #sales_item .mp-modal-field select:focus, #sales_item .mp-modal-field textarea:focus{ border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.08); }
  #sales_item .mp-modal-field textarea{ min-height: 70px; resize: vertical; }
  #sales_item .modal-footer{ border-top: 1px solid var(--mp-border); padding: 14px 20px; background: var(--mp-surface); display: flex; gap: 10px; justify-content: flex-end; margin-top: 0; }
  #sales_item .mp-modal-btn{ display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 10px; border: 1px solid transparent; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
  #sales_item .mp-modal-btn.ghost{ background: var(--mp-surface); color: var(--mp-ink); border-color: var(--mp-border); }
  #sales_item .mp-modal-btn.ghost:hover{ background: var(--mp-bg); }
  #sales_item .mp-modal-btn.primary{ background: var(--mp-primary); color: #fff; }
  #sales_item .mp-modal-btn.primary:hover{ background: var(--mp-primary-dark); }
  @media (max-width: 520px){ #sales_item .mp-modal-grid{ grid-template-columns: 1fr; } }
</style>
<div class="sales_item_modal">
   <div class="modal fade" id="sales_item" tabindex='-1'>
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title"><i class="fa fa-cube" style="color:var(--mp-primary);margin-right:8px;"></i><?= $this->lang->line('manage_sales_item'); ?></h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
               <div class="mp-modal-item-name"><span id='popup_item_name'></span></div>
               <div class="mp-modal-grid">
                  <div class="mp-modal-field">
                     <label for="popup_tax_type"><?= $this->lang->line('tax_type'); ?></label>
                     <select class="form-control select2" id="popup_tax_type" name="popup_tax_id" style="width: 100%;">
                        <option value="Exclusive">Exclusive</option>
                        <option value="Inclusive">Inclusive</option>
                     </select>
                  </div>
                  <div class="mp-modal-field">
                     <label for="popup_tax_id"><?= $this->lang->line('tax'); ?></label>
                     <select class="form-control select2" id="popup_tax_id" name="popup_tax_id" style="width: 100%;">
                        <?php
                        $query2="select * from db_tax where status=1 and store_id=".get_current_store_id();
                        $q2=$this->db->query($query2);
                        if($q2->num_rows()>0)
                         {
                          echo '<option value="">-Select-</option>';
                          foreach($q2->result() as $res1)
                           {
                             echo "<option data-tax='".$res1->tax."' data-tax-value='".$res1->tax_name."' value='".$res1->id."'>".$res1->tax_name."</option>";
                           }
                         }
                        else
                         {
                            ?>
                            <option value="">No Records Found</option>
                            <?php
                         }
                        ?>
                     </select>
                  </div>
                  <div class="mp-modal-field">
                     <label for="popup_sales_price">Unit Price</label>
                     <input type="text" class="form-control only_currency" id="popup_sales_price" name="popup_sales_price" placeholder="0" value="0">
                  </div>
                  <div class="mp-modal-field">
                     <label for="item_discount_type"><?= $this->lang->line('discount_type'); ?></label>
                     <select class="form-control" id="item_discount_type" name="item_discount_type" style="width: 100%;">
                        <option value='Percentage'>Percentage(%)</option>
                        <option value='Fixed'>Fixed(<?= $CI->currency() ?>)</option>
                     </select>
                  </div>
                  <div class="mp-modal-field">
                     <label for="item_discount_input"><?= $this->lang->line('discount'); ?></label>
                     <input type="text" class="form-control only_currency" id="item_discount_input" name="item_discount_input" placeholder="0" value="0" onkeyup="click_this(event,'.set_options')">
                  </div>
                  <div class="mp-modal-field mp-modal-full">
                     <label for="popup_description"><?= $this->lang->line('description'); ?></label>
                     <textarea class="form-control" id="popup_description" placeholder="Optional note for this line item..."></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
              <input type="hidden" id="popup_row_id">
               <button type="button" class="mp-modal-btn ghost" data-dismiss="modal">Close</button>
               <button type="button" onclick="set_info()" class="mp-modal-btn primary set_options"><i class="fa fa-check"></i> Apply</button>
            </div>
         </div>
      </div>
   </div>
</div>
