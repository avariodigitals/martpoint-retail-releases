<?php
/**
 * Variants form — CONTENT ONLY (rendered inside mp_layout).
 * See application/views/services/services.php for the porting notes:
 * the legacy doctype/head/code_css/sidebar/footer/code_js chrome is gone,
 * mp_layout supplies Bootstrap 3 + select2 + DataTables, and it dispatches
 * to physio_layout on clinical stores. Control ids are unchanged because
 * theme/js/variants/variants.js binds to them by name.
 */
?>
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?=$page_title;?></h2>
      <div class="mp-page-sub">Add/Update Variant</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?= base_url('variants/view'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        Back to <?= $this->lang->line('variants_list'); ?>
      </a>
    </div>
  </div>
</div>
         <?php
         $CI =& get_instance();
            if(!isset($variant_name)){
                 $variant_code=$variant_name=$description=$store_id="";
            }
            ?>
<section class="mp-section">
            <div class="row">
                  <!-- right column -->
                  <div class="col-md-12">
                     <!-- Horizontal Form -->
                     <div class="box box-info mp-items-box">
                        <div class="box-header with-border">
                           <h3 class="box-title">Please Enter Valid Data</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form class="form-horizontal" id="variant-form" onkeypress="return event.keyCode != 13;">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <div class="box-body">
                              <!-- Store Code -->
                               <?php /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>$store_id)); }else{*/
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              /*}*/ ?>
                              <!-- Store Code end -->
                              <div class="form-group">
                                 <label for="variant" class="col-sm-2 control-label"><?= $this->lang->line('variant_name'); ?><label class="text-danger">*</label></label>
                                 <div class="col-sm-4">
                                    <input type="text" class="form-control input-sm" id="variant" name="variant" placeholder="" onkeyup="shift_cursor(event,'description')" value="<?php print $variant_name; ?>" autofocus >
                                    <span id="variant_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="attribute_type" class="col-sm-2 control-label"><?= $this->lang->line('attribute_type'); ?></label>
                                 <div class="col-sm-4">
                                    <select class="form-control select2" id="attribute_type" name="attribute_type">
                                       <option value="">-None-</option>
                                       <option value="size" <?=($attribute_type ?? '')=='size'?'selected':'';?>>Size</option>
                                       <option value="colour" <?=($attribute_type ?? '')=='colour'?'selected':'';?>>Colour</option>
                                       <option value="material" <?=($attribute_type ?? '')=='material'?'selected':'';?>>Material</option>
                                       <option value="pattern" <?=($attribute_type ?? '')=='pattern'?'selected':'';?>>Pattern</option>
                                       <option value="fit" <?=($attribute_type ?? '')=='fit'?'selected':'';?>>Fit</option>
                                    </select>
                                    <span class="help-block text-muted" style="font-size:12px;">Tag this variant with an attribute type for size/colour reporting.</span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="attribute_value" class="col-sm-2 control-label"><?= $this->lang->line('attribute_value'); ?></label>
                                 <div class="col-sm-4">
                                    <input type="text" class="form-control input-sm" id="attribute_value" name="attribute_value" value="<?php print $attribute_value ?? ''; ?>" placeholder="e.g. Medium, Red, Cotton">
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="description" class="col-sm-2 control-label"><?= $this->lang->line('description'); ?></label>
                                 <div class="col-sm-4">
                                    <textarea type="text" class="form-control" id="description" name="description" placeholder=""><?php print $description; ?></textarea>
                                    <span id="description_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                           </div>
                           <!-- /.box-footer -->
                           <div class="box-footer mp-form-actions">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <!-- <div class="col-sm-4"></div> -->
                                 <?php
                                    if(isset($q_id)){
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
                                    <button type="button" id="<?php echo $btn_id;?>" class=" btn btn-block btn-success" title="Save Data"><?php echo $btn_name;?></button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <!-- /.box-footer -->
                        </form>
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
               </div>
               <!-- /.row -->
</section>

<!-- variants.js is enqueued by the controller via $data['extra_js_files'] -->
<style>
.box.mp-items-box { border: none !important; background: transparent !important; box-shadow: none !important; border-radius: 0 !important; }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); border-radius: 0 0 16px 16px; }
</style>
      <script type="text/javascript">
        <?php if(isset($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
      </script>
      <!-- Make sidebar menu highlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
