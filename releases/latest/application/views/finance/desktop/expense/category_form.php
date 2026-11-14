<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(!isset($category_name)){
    $category_code = $category_name = $description = $store_id = '';
}
$is_edit = !empty($category_code) || (isset($q_id) && $q_id !== '');
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update expense category' : 'Create a new expense category'; ?></div>
  </div>
</div>

<div class="mp-card-form" style="max-width:720px;">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Category' : 'Add Category'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="expense-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id ?? ''); ?>"/>
      <?php } ?>

      <div class="mp-form-grid" style="grid-template-columns:1fr;">
        <div class="mp-form-group">
          <label for="category"><?= $this->lang->line('category_name'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="category" name="category" value="<?= htmlspecialchars($category_name); ?>" autofocus>
          <span id="category_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="description"><?= $this->lang->line('description'); ?></label>
          <textarea class="mp-form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description); ?></textarea>
          <span id="description_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('expense/category'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/expense_category.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.expense-category-list-active-li').addClass('active');
  $('.expense-category-list-active-li').closest('.mp-nav-group').addClass('open');
</script>
