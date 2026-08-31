<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <?= $this->lang->line('city'); ?>
        <small>Add City</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>city"><?= $this->lang->line('cities_list'); ?></a></li>
        <li class="active"><?= $this->lang->line('city'); ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Please Enter Valid Data</h3>
            </div>
            <form class="form-horizontal" method="post" action="<?php echo $base_url; ?>city/newcity">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
              <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">

              <div class="box-body">
                <div class="form-group">
                  <label for="state_id" class="col-sm-2 control-label">State<label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <select class="form-control select2" id="state_id" name="state_id" style="width: 100%;">
                      <option value="">-Select-</option>
                      <?php
                        $q = $this->db->where('status', 1)->order_by('state', 'asc')->get('db_states');
                        if($q->num_rows() > 0){
                          foreach($q->result() as $res1){
                            $selected = (isset($state_id) && $state_id == $res1->id) ? 'selected' : '';
                            echo "<option $selected value='".$res1->id."'>".$res1->state." (".$res1->country.")</option>";
                          }
                        } else {
                          echo '<option value="">No Records Found</option>';
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="city" class="col-sm-2 control-label">City Name<label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="city" name="city" placeholder="Enter city name" value="<?= isset($city) ? $city : ''; ?>">
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-sm-8 col-sm-offset-2 text-center">
                  <div class="col-md-3 col-md-offset-3">
                    <button type="button" class="btn btn-block btn-danger" title="Go Back" onclick="window.history.go(-1); return false;">Back</button>
                  </div>
                  <div class="col-md-3">
                    <button type="submit" class="btn btn-block btn-success" title="Save Data">Save</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>
<?php include"comman/code_js.php"; ?>
<script>$(".city-active-li").addClass("active");</script>
</body>
</html>
