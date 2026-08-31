<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php $this->load->view('sidebar.php'); ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?=$page_title;?><small></small></h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?= base_url('tills'); ?>">Tills</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?= empty($till) ? 'Add' : 'Edit'; ?> Till</h3>
            </div>
            <form role="form" class="form-horizontal" method="post" action="<?=base_url('tills/save');?>">
              <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
              <input type="hidden" name="id" value="<?=!empty($till)?$till->id:'';?>">
              <div class="box-body">
                <div class="form-group">
                  <label for="till_name" class="col-sm-3 control-label">Till Name</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="till_name" name="till_name" required value="<?=!empty($till)?htmlspecialchars($till->till_name):'';?>" placeholder="e.g. Till 1 - Front Counter">
                  </div>
                </div>
                <div class="form-group">
                  <label for="cashier_user_id" class="col-sm-3 control-label">Assigned Cashier</label>
                  <div class="col-sm-9">
                    <select class="form-control" id="cashier_user_id" name="cashier_user_id">
                      <option value="">— Shared / Any Cashier —</option>
                      <?php foreach($users as $u): ?>
                      <option value="<?=$u->id;?>" <?=!empty($till) && $till->cashier_user_id == $u->id ? 'selected' : '';?>>
                        <?=htmlspecialchars(($u->first_name?$u->first_name.' '.$u->last_name:$u->username));?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="account_id" class="col-sm-3 control-label">Cash Account <small class="text-muted">(optional)</small></label>
                  <div class="col-sm-9">
                    <select class="form-control select2" id="account_id" name="account_id">
                      <option value="">— Auto-create account —</option>
                      <?=$accounts;?>
                    </select>
                    <small class="text-muted">If left blank, a new cash-in-hand account will be created automatically.</small>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="is_default" value="1" <?=!empty($till) && $till->is_default ? 'checked' : '';?>> Default till for this cashier
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right">Save Till</button>
                <a href="<?=base_url('tills');?>" class="btn btn-default">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer.php'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".tills-active-li").addClass("active");</script>
</body>
</html>
