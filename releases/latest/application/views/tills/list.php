<?php $CI =& get_instance(); ?>
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
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Tills / Cash-in-Hand Accounts</h3>
              <?php if($CI->permissions('tills_add')): ?>
              <a href="<?= base_url('tills/new_form'); ?>" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add Till</a>
              <?php endif; ?>
            </div>
            <div class="box-body table-responsive no-padding">
              <?php $tills = (isset($tills) && is_array($tills)) ? $tills : array(); ?>
              <table class="table table-bordered table-hover">
                <thead>
                  <tr class="bg-blue">
                    <th>#</th>
                    <th>Till Name</th>
                    <th>Cashier</th>
                    <th>Account</th>
                    <th class="text-right">Balance</th>
                    <th>Default</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(!empty($tills)): $i=0; foreach($tills as $t): $i++; ?>
                  <tr>
                    <td><?=$i;?></td>
                    <td><?=htmlspecialchars($t->till_name ?? '');?></td>
                    <td>
                      <?php
                        $cashier = trim(($t->first_name ?? '').' '.($t->last_name ?? ''));
                        if(empty($cashier)){ $cashier = $t->username ?? ''; }
                        echo htmlspecialchars($cashier) ?: '— Shared —';
                      ?>
                    </td>
                    <td><?=htmlspecialchars($t->account_name ?? '—');?></td>
                    <td class="text-right <?=floatval($t->balance ?? 0)<0?'text-danger':'text-success';?>"><?=store_number_format($t->balance ?? 0);?></td>
                    <td><?=!empty($t->is_default) ? '<span class="label label-primary">Default</span>' : '—';?></td>
                    <td><?=!empty($t->status) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';?></td>
                    <td>
                      <?php if($CI->permissions('tills_edit')): ?>
                      <a href="<?=base_url('tills/edit_form/'.$t->id);?>" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                      <?php endif; ?>
                      <?php if($CI->permissions('tills_delete')): ?>
                      <a href="<?=base_url('tills/delete/'.$t->id);?>" class="btn btn-xs btn-danger" onclick="return confirm('Deactivate this till?');"><i class="fa fa-trash"></i></a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; else: ?>
                  <tr><td colspan="8" class="text-center text-muted">No tills created yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
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
