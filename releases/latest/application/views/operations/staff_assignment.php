<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Assign staff to services — only assigned staff show at checkout</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <!-- Staff Roster Summary -->
        <div class="row">
          <?php foreach ($staff_list as $s) { $assigned_count = 0; foreach ($assignments as $sid => $staffs) { if (isset($staffs[$s->id])) $assigned_count++; } ?>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="small-box bg-purple" style="margin-bottom:15px;">
              <div class="inner" style="padding:10px 15px;">
                <p style="margin:0;font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($s->username); ?></p>
                <p style="margin:0;font-size:12px;opacity:.8;"><?= $assigned_count; ?> service<?= $assigned_count != 1 ? 's' : ''; ?></p>
              </div>
              <div class="icon" style="top:5px;right:10px;font-size:30px;"><i class="fa fa-user"></i></div>
            </div>
          </div>
          <?php } ?>
        </div>

        <!-- Service Assignment Grid -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-user-plus"></i> Service-to-Staff Mapping</h3>
            <div class="box-tools pull-right">
              <input type="text" id="service-search" class="form-control input-sm" placeholder="Search services..." style="width:200px;display:inline-block;">
            </div>
          </div>
          <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped" id="services-table">
              <thead>
                <tr>
                  <th style="width:30%;">Service</th>
                  <th style="width:15%;">Price</th>
                  <th style="width:10%;">Duration</th>
                  <th>Assigned Staff</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($services)) { ?>
                <tr><td colspan="4" class="text-center text-muted">No services found. Create services in Online Store &rarr; Services.</td></tr>
                <?php } else { foreach ($services as $svc) { ?>
                <tr data-service-name="<?= strtolower(htmlspecialchars($svc->service_name)); ?>">
                  <td>
                    <strong><?= htmlspecialchars($svc->service_name); ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($svc->description ?? ''); ?></small>
                  </td>
                  <td><?= store_number_format($svc->price); ?></td>
                  <td><?= $svc->service_duration ? $svc->service_duration . ' min' : '-'; ?></td>
                  <td>
                    <div class="staff-chips" id="chips-<?= $svc->id; ?>">
                      <?php
                      $has_staff = false;
                      foreach ($staff_list as $s) {
                        $is_assigned = !empty($assignments[$svc->id][$s->id]);
                        if ($is_assigned) {
                          $has_staff = true;
                          echo '<span class="label label-primary" style="margin-right:4px;margin-bottom:4px;display:inline-block;font-size:12px;padding:6px 10px;cursor:pointer;" data-staff-id="'.$s->id.'" onclick="toggleStaff('.$svc->id.','.$s->id.',this)">'.htmlspecialchars($s->username).' <i class="fa fa-times"></i></span>';
                        }
                      }
                      if (!$has_staff) {
                        echo '<span class="text-muted" id="no-staff-'.$svc->id.'"><i class="fa fa-exclamation-circle text-orange"></i> No staff assigned</span>';
                      }
                      ?>
                    </div>
                    <div class="btn-group" style="margin-top:6px;">
                      <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-plus"></i> Assign Staff
                      </button>
                      <ul class="dropdown-menu">
                        <?php foreach ($staff_list as $s) { $already = !empty($assignments[$svc->id][$s->id]); ?>
                        <li <?= $already ? 'class="disabled"' : ''; ?>>
                          <a href="javascript:void(0)" <?= $already ? '' : 'onclick="assignStaff('.$svc->id.','.$s->id.',\''.htmlspecialchars($s->username).'\')"'; ?> style="padding:8px 15px;">
                            <?= $already ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-user-plus text-muted"></i>'; ?>
                            <?= htmlspecialchars($s->username); ?>
                          </a>
                        </li>
                        <?php } ?>
                      </ul>
                    </div>
                  </td>
                </tr>
                <?php } } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
function assignStaff(serviceId, staffId, staffName) {
  $.ajax({
    url: '<?= base_url("operations/staff_assignment"); ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'assign',
      service_id: serviceId,
      staff_id: staffId,
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    },
    success: function(res) {
      if (res && res.success) {
        success_show('Staff assigned successfully');
        location.reload();
      } else {
        error_show(res && res.message ? res.message : 'Failed to assign staff');
      }
    },
    error: function() {
      error_show('Network error. Please try again.');
    }
  });
}

function toggleStaff(serviceId, staffId, el) {
  if (!confirm('Remove ' + $(el).text().trim() + ' from this service?')) return;
  $.ajax({
    url: '<?= base_url("operations/staff_assignment"); ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'unassign',
      service_id: serviceId,
      staff_id: staffId,
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    },
    success: function(res) {
      if (res && res.success) {
        success_show('Staff removed successfully');
        location.reload();
      } else {
        error_show(res && res.message ? res.message : 'Failed to remove staff');
      }
    },
    error: function() {
      error_show('Network error. Please try again.');
    }
  });
}

// Search filter
$('#service-search').on('keyup', function() {
  var term = $(this).val().toLowerCase().trim();
  $('#services-table tbody tr').each(function() {
    var name = $(this).data('service-name');
    if (!term || name.indexOf(term) !== -1) {
      $(this).show();
    } else {
      $(this).hide();
    }
  });
});

$(".staff-active-li").addClass("active");
</script>
</body>
</html>
