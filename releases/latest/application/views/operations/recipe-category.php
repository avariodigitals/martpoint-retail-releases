<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Configure recipe category</div>
  </div>
  <a href="<?= base_url('operations/recipe_categories'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Categories</a>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Please Enter Valid Data</h3></div>
  <div class="mp-card-body">
    <form id="category-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="name">Category Name <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="name" name="name" value="<?= isset($category_name) ? htmlspecialchars($category_name) : ''; ?>" placeholder="e.g. Cakes, Bread, Soup" autofocus>
          <span id="name_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
      <?php if(isset($q_id)){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= $q_id; ?>"/>
      <?php } ?>
      <div class="mp-form-actions">
        <?php if(isset($q_id)){ ?>
        <button type="button" id="update" class="mp-btn-primary"><i class="fa fa-check"></i> Update</button>
        <?php } else { ?>
        <button type="button" id="save" class="mp-btn-primary"><i class="fa fa-check"></i> Save</button>
        <?php } ?>
        <a href="<?= base_url('operations/recipe_categories'); ?>" class="mp-btn-secondary">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/category.js"></script>
<script>
  <?php if(isset($q_id)){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  // Override category.js endpoint for recipe categories
  var saveUrl = "<?= base_url('operations/recipe_category_save'); ?>";
  var redirectUrl = "<?= base_url('operations/recipe_categories'); ?>";

  $('#save, #update').on('click', function(e){
    var base_url = "<?= base_url(); ?>";
    e.preventDefault();
    var flag = true;
    if($('#name').val() == '') {
      $('#name_msg').html('Category Name is required.').show();
      flag = false;
    } else {
      $('#name_msg').hide();
    }
    if(flag) {
      var btn = $(this);
      btn.prop('disabled', true).text('Saving...');
      var data = new FormData(document.getElementById('category-form'));
      $.ajax({
        url: saveUrl, type: 'POST', data: data, processData: false, contentType: false,
        success: function(result){
          btn.prop('disabled', false);
          if(result.trim() == 'success') {
            toastr.success('Saved Successfully!');
            setTimeout(function(){ window.location = redirectUrl; }, 500);
          } else {
            toastr.error(result);
            btn.text(btn.attr('id') == 'save' ? 'Save' : 'Update');
          }
        },
        error: function(){ toastr.error('Server Error'); btn.prop('disabled', false); }
      });
    }
  });
</script>
