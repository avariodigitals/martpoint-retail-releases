<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php $this->load->view('comman/code_css.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>

<div class="content-wrapper">

  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Menu Items</li>
    </ol>
  </section>

  <section class="content">
    <?php $this->load->view('comman/code_flashdata.php'); ?>

    <div class="row">
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-plus-circle"></i> Add New Menu Item</h3>
          </div>
          <div class="box-body">
            <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <?= form_open('operations/menu_items', ['id'=>'menu-item-form']); ?>
              <input type="hidden" name="save_menu_item" value="1">
              <input type="hidden" name="command" value="save">
              <input type="hidden" id="hidden_price" name="price" value="">

              <div class="form-group">
                <label>Item Name <span class="text-danger">*</span></label>
                <input type="text" name="item_name" class="form-control" placeholder="e.g. Jollof Rice, Grilled Chicken" required>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Menu Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-money"></i></span>
                      <input type="number" name="sales_price" id="sales_price" class="form-control" placeholder="2500" step="0.01" min="0" required oninput="$('#hidden_price').val(this.value)">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-control select2" required>
                      <option value="">-- Select Category --</option>
                      <?php foreach($categories as $cat): ?>
                      <option value="<?= $cat->id; ?>"><?= htmlspecialchars($cat->category_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Kitchen Station</label>
                    <select name="kitchen_station" class="form-control">
                      <option value="">-- Select Station --</option>
                      <option value="grill">Grill</option>
                      <option value="fryer">Fryer</option>
                      <option value="cold">Cold Prep / Salad</option>
                      <option value="barista">Barista / Drinks</option>
                      <option value="bakery">Bakery</option>
                      <option value="main">Main Kitchen</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Prep Time (minutes)</label>
                    <input type="number" name="prep_time_min" class="form-control" placeholder="15" min="0">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Unit</label>
                    <select name="unit_id" class="form-control select2">
                      <option value="">-- Select Unit --</option>
                      <?php foreach($units as $u): ?>
                      <option value="<?= $u->id; ?>" <?= $u->id==1 ? 'selected' : ''; ?>><?= htmlspecialchars($u->unit_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tax</label>
                    <select name="tax_id" class="form-control select2">
                      <option value="">-- Select Tax --</option>
                      <?php foreach($taxes as $t): ?>
                      <option value="<?= $t->id; ?>" <?= $t->id==1 ? 'selected' : ''; ?>><?= htmlspecialchars($t->tax_name); ?> (<?= $t->tax; ?>%)</option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Item Code (optional)</label>
                <input type="text" name="item_code" class="form-control" placeholder="Auto-generated if left blank">
              </div>

              <div class="form-group text-right">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Menu Item</button>
                <a href="<?= base_url('operations/kitchen'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Kitchen</a>
              </div>

            <?= form_close(); ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Tips</h3>
          </div>
          <div class="box-body">
            <ul>
              <li><strong>Item Name:</strong> Keep it simple — what customers see on the menu.</li>
              <li><strong>Menu Price:</strong> The price charged to customers.</li>
              <li><strong>Kitchen Station:</strong> Helps route orders to the right prep area on the KDS.</li>
              <li><strong>Prep Time:</strong> Estimates how long the kitchen needs. Shown on KDS cards.</li>
              <li>Items saved here appear in POS search immediately.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
</div>

<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(function(){
  $('.select2').select2();
});
</script>
</body>
</html>
