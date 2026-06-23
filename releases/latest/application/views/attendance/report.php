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
    <h1>Attendance Report</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <form method="get" class="form-inline">
              <div class="form-group">
                <input type="date" name="start" class="form-control" value="<?= $start ?>">
              </div>
              <div class="form-group">
                <input type="date" name="end" class="form-control" value="<?= $end ?>">
              </div>
              <div class="form-group">
                <select name="user_id" class="form-control select2">
                  <option value="">All Users</option>
                  <?php foreach($users as $u): ?>
                  <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>><?= htmlspecialchars($u->username . ' - ' . ($u->first_name ?: '') . ' ' . ($u->last_name ?: '')) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
            </form>
          </div>
          <div class="box-body">
            <table class="table table-bordered table-striped datatable">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Staff</th>
                  <th>Shift</th>
                  <th>Clock In</th>
                  <th>Clock Out</th>
                  <th>Status</th>
                  <th>Location OK</th>
                  <th>Face</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($records as $r): ?>
                <tr>
                  <td><?= show_date($r->attendance_date) ?></td>
                  <td><?= htmlspecialchars(($r->first_name ?: '') . ' ' . ($r->last_name ?: '') . ' (' . $r->username . ')') ?></td>
                  <td><?= $r->shift_name ?: '-' ?></td>
                  <td><?= $r->clock_in ?: '-' ?></td>
                  <td><?= $r->clock_out ?: '-' ?></td>
                  <td><span class="label label-<?= $r->status === 'present' ? 'success' : 'danger' ?>"><?= ucfirst($r->status) ?></span></td>
                  <td>
                    <?php
                    $locOk = false;
                    if($r->clock_in_lat && $r->location_lat){
                      $dist = $this->attendance_model->haversine($r->location_lat, $r->location_lng, $r->clock_in_lat, $r->clock_in_lng);
                      $locOk = $dist <= ($r->location_radius_meters ?: 100);
                    }
                    ?>
                    <?= $locOk ? '<span class="label label-success"><i class="fa fa-check"></i></span>' : ($r->clock_in_lat ? '<span class="label label-warning"><i class="fa fa-times"></i></span>' : '-') ?>
                  </td>
                  <td>
                    <?php if($r->face_image): ?>
                      <a href="<?= base_url($r->face_image) ?>" target="_blank"><img src="<?= base_url($r->face_image) ?>" style="width:40px;height:40px;border-radius:4px;object-fit:cover;"></a>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($records)): ?>
                <tr><td colspan="8" class="text-center text-muted">No records found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$('.attendance-report-active-li').addClass('active');</script>
</body>
</html>
