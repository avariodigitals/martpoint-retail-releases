<?php
/* Attendance Report — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head"><h1 class="mp-page-title">Attendance Report</h1></div>
<div class="mp-card">
  <div class="mp-card-head">
    <h3 class="mp-card-title">Attendance Records</h3>
    <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <input type="date" name="start" class="form-control" value="<?= $start ?>" style="width:auto;">
      <input type="date" name="end" class="form-control" value="<?= $end ?>" style="width:auto;">
      <select name="user_id" class="form-control" style="width:auto;">
        <option value="">All Users</option>
        <?php foreach($users as $u): ?>
        <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>><?= htmlspecialchars($u->username . ' - ' . ($u->first_name ?: '') . ' ' . ($u->last_name ?: '')) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="mp-qa-btn green"><i class="fa fa-search"></i> Filter</button>
    </form>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
    <table class="mp-dt-table" id="reportTable" width="100%">
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
          <td class="row-name"><?= htmlspecialchars(($r->first_name ?: '') . ' ' . ($r->last_name ?: '') . ' (' . $r->username . ')') ?></td>
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
        <tr><td colspan="8" class="text-center text-muted" style="padding:40px">No records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<script>$('.attendance-report-active-li').addClass('active');$('.attendance-report-active-li').closest(".mp-nav-group").addClass("open");</script>
