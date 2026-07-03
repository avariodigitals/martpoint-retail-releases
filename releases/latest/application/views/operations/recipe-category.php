<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Configure recipe category</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/recipe_categories'); ?>">Recipe Categories</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row"><div class="col-md-12">
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Please Enter Valid Data</h3></div>
        <form class="form-horizontal" id="category-form" onkeypress="return event.keyCode != 13;">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <div class="box-body">
            <div class="form-group">
              <label for="name" class="col-sm-2 control-label">Category Name <span class="text-danger">*</span></label>
              <div class="col-sm-4">
                <input type="text" class="form-control input-sm" id="name" name="name" value="<?= isset($category_name) ? htmlspecialchars($category_name) : ''; ?>" placeholder="e.g. Cakes, Bread, Soup" autofocus>
                <span id="name_msg" style="display:none" class="text-danger"></span>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-sm-8 col-sm-offset-2 text-center">
              <?php if(isset($q_id)){ ?>
              <input type="hidden" name="q_id" id="q_id" value="<?= $q_id; ?>"/>
              <div class="col-md-3 col-md-offset-3"><button type="button" id="update" class="btn btn-block btn-success">Update</button></div>
              <?php } else { ?>
              <div class="col-md-3 col-md-offset-3"><button type="button" id="save" class="btn btn-block btn-success">Save</button></div>
              <?php } ?>
              <div class="col-sm-3"><a href="<?= base_url('operations/recipe_categories'); ?>"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a></div>
            </div>
          </div>
        </form>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
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
</body>
</html>
