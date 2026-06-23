<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('reports');?>">Reports</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <?php $this->load->view('comman/code_flashdata'); ?>
        </div>
      </div>
      <!-- Filters -->
      <div class="box box-default">
        <div class="box-body">
          <form method="get" class="form-inline">
            <div class="form-group">
              <select name="type" class="form-control">
                <option value="">All Types</option>
                <?php foreach($approval_types as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($filters['approval_type']==$k)?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="pending" <?= ($filters['status']=='pending')?'selected':'' ?>>Pending</option>
                <option value="approved" <?= ($filters['status']=='approved')?'selected':'' ?>>Approved</option>
                <option value="rejected" <?= ($filters['status']=='rejected')?'selected':'' ?>>Rejected</option>
              </select>
            </div>
            <div class="form-group">
              <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?>" placeholder="From">
            </div>
            <div class="form-group">
              <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?>" placeholder="To">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
            <a href="<?= base_url('approvals/logs') ?>" class="btn btn-default">Reset</a>
          </form>
        </div>
      </div>

      <div class="box box-primary">
        <div class="box-body table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Requester</th>
                <th>Approver</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($logs as $log): ?>
              <tr>
                <td><?= date('Y-m-d H:i', strtotime($log->created_at)) ?></td>
                <td><?= $approval_types[$log->approval_type] ?? $log->approval_type ?></td>
                <td><?= htmlspecialchars($log->requesting_user_name) ?></td>
                <td><?= htmlspecialchars($log->approving_user_name ?: '-') ?></td>
                <td>
                  <?php
                  $badgeClass = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'default'][$log->status] ?? 'default';
                  ?>
                  <span class="label label-<?= $badgeClass ?>"><?= ucfirst($log->status) ?></span>
                </td>
                <td><?= htmlspecialchars($log->reason ?: '-') ?></td>
                <td><?= $log->amount ? number_format($log->amount, 2) : '-' ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($logs)): ?>
              <tr><td colspan="7" class="text-center text-muted">No approval logs found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if($pages > 1): ?>
        <div class="box-footer text-center">
          <?php for($i=1; $i<=$pages; $i++): ?>
          <a href="<?= base_url('approvals/logs') ?>?page=<?= $i ?>&type=<?= $filters['approval_type'] ?>&status=<?= $filters['status'] ?>&date_from=<?= $filters['date_from'] ?>&date_to=<?= $filters['date_to'] ?>" class="btn btn-sm <?= $i==$page?'btn-primary':'btn-default' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js'); ?>
</body>
</html>
