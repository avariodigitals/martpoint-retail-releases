<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$esc = function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
$clocked = !empty($attendance_record);
$bmAttendanceName = trim(($attendance_member->first_name ?? '').' '.($attendance_member->last_name ?? '')) ?: $attendance_member->username;
?>
<style>
.bo-attendance-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:22px;margin-bottom:24px;align-items:start}.bo-clock-state{font-size:32px;font-weight:700;letter-spacing:-1px;margin:18px 0 8px}.bo-clock-detail{color:#737373;font-size:13px;line-height:1.8}.bo-clock-form{margin-top:25px;max-width:400px}.bo-clock-form label{display:block;font-size:12px;font-weight:600;margin-bottom:9px}.bo-clock-form input{width:100%;padding:13px 14px;border:1px solid #dedede;border-radius:8px;font:inherit;color:#202124;background:#fff;margin-bottom:14px}.bo-clock-form button{min-height:46px}.bo-clock-result{font-size:13px;line-height:1.7;margin:16px 0 0;overflow-wrap:anywhere}.bo-clock-shifts{list-style:none;margin:12px 0 0;padding:0}.bo-clock-shifts li{border-top:1px solid #eee;padding:13px 0;font-size:13px;line-height:1.7}.bo-clock-shifts span{display:block;color:#737373;font-size:12px}@media(max-width:800px){.bo-attendance-grid{grid-template-columns:1fr}.bo-clock-state{font-size:28px}}
.bm-ops .bo-card h2.bo-clock-state{font-size:28px!important;line-height:1.3!important;margin:16px 0 12px!important;letter-spacing:-.7px!important}
.bo-clock-form input{box-sizing:border-box;max-width:100%}
</style>
<div class="bm-ops">
  <header class="bo-head"><div><span class="bo-eyebrow">Staff workspace</span><h1>My attendance</h1><p>Clock in or out for your workday. Your attendance uses the existing team attendance system.</p></div><span class="bo-pill"><?= $esc(date('D, j M Y')); ?></span></header>
  <div class="bo-attendance-grid">
    <section class="bo-card">
      <span class="bo-eyebrow"><?= $esc($bmAttendanceName); ?></span>
      <h2 class="bo-clock-state" id="bm-attendance-state"><?= $clocked ? 'You are clocked in' : 'Ready to start work?'; ?></h2>
      <p class="bo-clock-detail"><?= $clocked ? 'Clocked in at '.$esc($attendance_record->clock_in).'. Clock out when you finish.' : 'Record your arrival before starting your workday.'; ?><br>Recorded in <?= $esc(date_default_timezone_get()); ?> time.</p>
      <div class="bo-clock-form"><button type="button" class="bo-button primary" data-bm-clock-trigger aria-haspopup="dialog"><i class="fa fa-clock-o" aria-hidden="true"></i> <span data-bm-clock-label><?= $clocked ? 'Clock Out' : 'Clock In'; ?></span></button></div>
      <p class="bo-clock-detail">Use the Clock In / Out popup from any workspace screen. This page is your attendance history; no cashier role is needed.</p>
      <p id="bm-attendance-updated" role="status" hidden>Attendance updated. <a href="<?= base_url('attendance/workspace'); ?>">Refresh history</a> to see the latest entry.</p>
    </section>
    <section class="bo-card"><h2>Your shifts</h2>
      <?php if($attendance_shifts): ?><ul class="bo-clock-shifts"><?php foreach($attendance_shifts as $shift): ?><li><strong><?= $esc($shift->shift_name); ?></strong><span><?= $esc($shift->start_time); ?> – <?= $esc($shift->end_time); ?></span></li><?php endforeach; ?></ul>
      <?php else: ?><p class="bo-clock-detail">No shift is assigned. You can still record attendance; ask your manager if a roster is needed.</p><?php endif; ?>
      <?php if($can_attendance_report || $can_attendance_edit): ?><div class="bo-form-actions"><?php if($can_attendance_report): ?><a class="bo-button" href="<?= base_url('attendance/daily'); ?>">Team attendance</a><a class="bo-button" href="<?= base_url('attendance/report'); ?>">Reports</a><?php endif; ?><?php if($can_attendance_edit): ?><a class="bo-button" href="<?= base_url('attendance/assign_shifts'); ?>">Assign shifts</a><?php endif; ?></div><?php endif; ?>
    </section>
  </div>
  <section class="bo-card"><div class="bo-section-head"><h2>Your recent attendance</h2><span class="bo-pill">Latest 14 entries</span></div>
    <?php if(!$attendance_history): ?><div class="bo-empty">Your recorded clock-in and clock-out times will appear here.</div><?php else: ?>
    <div class="bo-table-wrap"><table class="bo-table"><thead><tr><th>Date</th><th>Clock in</th><th>Clock out</th><th>Status</th></tr></thead><tbody><?php foreach($attendance_history as $entry): ?><tr><td><?= $esc($entry->attendance_date); ?></td><td><?= $esc($entry->clock_in ?: '—'); ?></td><td><?= $esc($entry->clock_out ?: '—'); ?></td><td><?= $entry->clock_out ? 'Completed' : ($entry->attendance_date === date('Y-m-d') ? 'Clocked in' : 'Missing clock-out'); ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
  </section>
</div>
<script>
document.addEventListener('bm:attendancechange',function(event){
  document.getElementById('bm-attendance-state').textContent=event.detail.clocked_in?'You are clocked in':'You are clocked out';
  document.getElementById('bm-attendance-updated').hidden=false;
});
</script>
