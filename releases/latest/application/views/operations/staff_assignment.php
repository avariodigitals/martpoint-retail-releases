<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<style>
.mp-staff-chip { display:inline-block; background:rgba(0,87,255,.1); color:var(--mp-primary); border-radius:8px; padding:6px 10px; font-size:12px; margin-right:4px; margin-bottom:4px; cursor:pointer; }
.mp-staff-chip:hover { opacity:0.85; }
.mp-staff-chip i { margin-left:4px; }
.mp-assign-dropdown { position:relative; display:inline-block; }
.mp-assign-dropdown-menu { position:absolute; left:0; top:100%; z-index:1000; background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:10px; box-shadow:var(--mp-shadow); min-width:180px; padding:6px 0; margin-top:4px; }
.mp-assign-dropdown-menu a { display:block; padding:8px 15px; color:var(--mp-ink); font-size:13px; text-decoration:none; }
.mp-assign-dropdown-menu a:hover { background:var(--mp-bg); }
.mp-assign-dropdown-menu a.disabled { color:var(--mp-muted); pointer-events:none; }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Assign staff to services — only assigned staff show at checkout</div>
  </div>
</div>

<div class="mp-kpi-grid">
  <?php foreach ($staff_list as $s) { $assigned_count = 0; foreach ($assignments as $sid => $staffs) { if (isset($staffs[$s->id])) $assigned_count++; } ?>
  <div class="mp-kpi-card purple"><div class="mp-kpi-icon"><i class="fa fa-user"></i></div><div class="mp-kpi-label" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($s->username); ?></div><div class="mp-kpi-value"><?= $assigned_count; ?> service<?= $assigned_count != 1 ? 's' : ''; ?></div></div>
  <?php } ?>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-user-plus"></i> Service-to-Staff Mapping</h3>
    <input type="text" id="service-search" class="mp-form-control" placeholder="Search services..." style="width:200px;">
  </div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" id="services-table">
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
          <tr><td colspan="4" class="mp-empty-state">No services found. Create services in Online Store &rarr; Services.</td></tr>
          <?php } else { foreach ($services as $svc) { ?>
          <tr data-service-name="<?= strtolower(htmlspecialchars($svc->service_name)); ?>">
            <td>
              <strong><?= htmlspecialchars($svc->service_name); ?></strong><br>
              <small class="mp-form-hint"><?= htmlspecialchars($svc->description ?? ''); ?></small>
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
                    echo '<span class="mp-staff-chip" data-staff-id="'.$s->id.'" onclick="toggleStaff('.$svc->id.','.$s->id.',this)">'.htmlspecialchars($s->username).' <i class="fa fa-times"></i></span>';
                  }
                }
                if (!$has_staff) {
                  echo '<span class="mp-form-hint" id="no-staff-'.$svc->id.'"><i class="fa fa-exclamation-circle" style="color:var(--mp-warning);"></i> No staff assigned</span>';
                }
                ?>
              </div>
              <div class="mp-assign-dropdown" style="margin-top:6px;">
                <button type="button" class="mp-qa-btn green" style="padding:6px 12px;font-size:13px;" onclick="$(this).next('.mp-assign-dropdown-menu').toggle()">
                  <i class="fa fa-plus"></i> Assign Staff
                </button>
                <div class="mp-assign-dropdown-menu" style="display:none;">
                  <?php foreach ($staff_list as $s) { $already = !empty($assignments[$svc->id][$s->id]); ?>
                  <a href="javascript:void(0)" class="<?= $already ? 'disabled' : ''; ?>" <?= $already ? '' : 'onclick="assignStaff('.$svc->id.','.$s->id.',\''.htmlspecialchars($s->username).'\')"'; ?>>
                    <?= $already ? '<i class="fa fa-check" style="color:var(--mp-success);"></i>' : '<i class="fa fa-user-plus mp-form-hint"></i>'; ?>
                    <?= htmlspecialchars($s->username); ?>
                  </a>
                  <?php } ?>
                </div>
              </div>
            </td>
          </tr>
          <?php } } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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

// Close dropdowns when clicking outside
$(document).on('click', function(e) {
  if (!$(e.target).closest('.mp-assign-dropdown').length) {
    $('.mp-assign-dropdown-menu').hide();
  }
});
</script>
