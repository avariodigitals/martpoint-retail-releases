<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';

if(!isset($category_name)){
  $category_code=$category_name=$description=$store_id="";
}
// Save vs Update button
if(isset($q_id)){
  $btn_name = 'Update';
  $btn_id = 'update';
} else {
  $btn_name = 'Save';
  $btn_id = 'save';
}
?>

<style>
.mp-card-form { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card-form .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card-form .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card-form .mp-card-body { padding: 20px; }
.mp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 24px; }
.mp-form-grid .mp-form-group.full { grid-column: 1 / -1; }
.mp-form-group { display: flex; flex-direction: column; gap: 6px; }
.mp-form-group > label { font-size: 13px; font-weight: 600; color: var(--mp-ink); }
.mp-form-group > label .text-danger { color: var(--mp-danger); }
.mp-form-hint { font-size: 12px; color: var(--mp-muted); margin: 0; }
.mp-form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 500; font-family: inherit; transition: all .15s ease; }
.mp-form-control:focus { outline: none; border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.1); }
.mp-form-control[readonly] { background: var(--mp-bg); color: var(--mp-muted); }
textarea.mp-form-control { min-height: 90px; resize: vertical; }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); }
.mp-file-drop { border: 1px dashed var(--mp-border); border-radius: 10px; padding: 18px; text-align: center; color: var(--mp-muted); font-size: 13px; cursor: pointer; transition: all .15s ease; background: var(--mp-bg); }
.mp-file-drop:hover { border-color: var(--mp-primary); color: var(--mp-primary); }
.mp-file-drop input[type=file] { display: none; }
.mp-file-preview { margin-top: 12px; }
.mp-file-preview img { max-height: 72px; border-radius: 8px; border: 1px solid var(--mp-border); object-fit: cover; }
@media (max-width:767px){ .mp-form-grid { grid-template-columns: 1fr; } }
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; <?= isset($q_id) ? 'Update category' : 'Add a new category'; ?></div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?php echo $base_url; ?>category/view" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Categories
      </a>
    </div>
  </div>
</div>

<!-- Form Card -->
<div class="mp-section">
  <div class="mp-card-form box">
    <div class="mp-card-head">
      <h3>Category Details</h3>
    </div>
    <form class="mp-card-body" id="category-form" onkeypress="return event.keyCode != 13;" enctype="multipart/form-data">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
      <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?php echo get_current_store_id(); ?>">
      <?php if(isset($q_id)): ?>
      <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id; ?>"/>
      <?php endif; ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="category"><?= $this->lang->line('category_name'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="category" name="category" placeholder="e.g. Beverages" onkeyup="shift_cursor(event,'description')" value="<?php print $category_name; ?>" autofocus>
          <span id="category_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="description"><?= $this->lang->line('description'); ?></label>
          <textarea class="mp-form-control" id="description" name="description" placeholder="Short description (optional)"><?php print $description; ?></textarea>
          <span id="description_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="category_image">Category Image</label>
          <label class="mp-file-drop" for="category_image">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Click to upload an image
          </label>
          <input type="file" class="mp-form-control" id="category_image" name="category_image" accept="image/*" style="display:none;">
          <?php if(!empty($category_image)): ?>
          <div class="mp-file-preview"><img src="<?= base_url($category_image); ?>"></div>
          <?php endif; ?>
          <span id="category_image_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
    </form>

    <div class="mp-form-actions">
      <button type="button" id="<?php echo $btn_id; ?>" class="mp-qa-btn green" title="Save Data">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <?php echo $btn_name; ?>
      </button>
      <a href="<?php echo $base_url; ?>category/view" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Cancel</a>
    </div>
  </div>
</div>

<script type="text/javascript">
  <?php if(isset($q_id)): ?>
  $("#store_id").attr('readonly', true);
  <?php endif; ?>
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
