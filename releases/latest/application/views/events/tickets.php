<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); ?>

<style>
.tk-scan{display:flex;gap:10px;align-items:flex-end}
.tk-scan .mp-form-control{font-size:20px;letter-spacing:2px;text-transform:uppercase;font-family:ui-monospace,Menlo,monospace}
.tk-row{display:flex;justify-content:space-between;align-items:center;gap:12px;border:1px solid var(--mp-border);border-radius:10px;padding:11px 14px;margin-bottom:8px;background:var(--mp-surface)}
.tk-row.current{border-color:var(--mp-primary);box-shadow:0 0 0 2px rgba(0,87,255,.08)}
.tk-name{font-weight:700;color:var(--mp-text);font-size:14px}
.tk-meta{font-size:12px;color:var(--mp-muted);margin-top:2px}
.tk-progress{font-size:12px;font-weight:600;color:var(--mp-success)}
.tk-holder{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 10px;border-bottom:1px solid var(--mp-border)}
.tk-holder:last-child{border-bottom:0}
.tk-holder .code{font-family:ui-monospace,Menlo,monospace;font-size:12px;color:var(--mp-muted)}
.tk-holder .who{font-weight:600;color:var(--mp-text);font-size:13px}
.tk-holder form{margin:0}
.tk-mini{font-size:11px;padding:4px 10px;border:1px solid var(--mp-primary);border-radius:6px;background:var(--mp-surface);color:var(--mp-primary);cursor:pointer}
.tk-mini:hover{background:var(--mp-primary);color:#fff}
</style>

<div class="mp-page-head">
  <div>
    <h2>Ticket Check-in</h2>
    <div class="mp-page-sub">Enter the code on the guest's ticket to mark it used at the door</div>
  </div>
  <div><a class="mp-btn-secondary" href="<?= base_url('events'); ?>"><i class="fa fa-arrow-left"></i> All Events</a></div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<div class="mp-form-grid" style="grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:24px; align-items:start;">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-tag"></i> Scan a ticket</h3></div>
    <div class="mp-card-body">
      <?= form_open('events/tickets', ['id' => 'scan-form']); ?>
        <input type="hidden" name="check_code" value="1">
        <input type="hidden" name="session" value="<?= $selected ? (int)$selected->id : 0; ?>">
        <div class="tk-scan">
          <div style="flex:1 1 auto;">
            <label>Ticket code</label>
            <input type="text" name="ticket_code" id="ticket_code" class="mp-form-control" placeholder="MPXXXXXXXX"
              autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="24" required>
          </div>
          <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Check In</button>
        </div>
        <div class="mp-page-sub" style="margin-top:10px;">Codes look like <strong>MP1A2B3C4D</strong> and print on the order confirmation.</div>
      <?= form_close(); ?>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-users"></i> Who is expected</h3>
      <?php if($selected): ?>
      <span class="mp-page-sub">
        <?= (int)$selected_counts['redeemed']; ?> of <?= (int)$selected_counts['sold']; ?> checked in
      </span>
      <?php endif; ?>
    </div>
    <div class="mp-card-body">
      <?php if(!$selected): ?>
        <div class="mp-page-sub" style="padding:6px 0 12px;">Pick a date on the right to see its guest list.</div>
      <?php else: ?>
        <?php if(empty($holders)): ?>
          <div class="mp-page-sub" style="padding:6px 0 12px;">No tickets are on sale for this date yet.</div>
        <?php else: ?>
          <?php foreach($holders as $t): ?>
          <div class="tk-holder">
            <div>
              <div class="who"><?= htmlspecialchars($t->holder_name ?: 'Guest') ?></div>
              <div class="code"><?= htmlspecialchars($t->ticket_code); ?></div>
            </div>
            <?= form_open('events/tickets'); ?>
              <input type="hidden" name="check_code" value="1">
              <input type="hidden" name="ticket_code" value="<?= htmlspecialchars($t->ticket_code); ?>">
              <input type="hidden" name="session" value="<?= (int)$selected->id; ?>">
              <button type="submit" class="tk-mini">Check in</button>
            <?= form_close(); ?>
          </div>
          <?php endforeach; ?>
          <?php if((int)$selected_counts['sold'] > count($holders)): ?>
            <div class="mp-page-sub" style="margin-top:10px;">Showing the first <?= count($holders); ?> — check the rest by code.</div>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-card-form" style="margin-top:20px;">
  <div class="mp-card-head">
    <h3><i class="fa fa-calendar"></i> Upcoming dates</h3>
    <span class="mp-page-sub"><?= (int)$session_total; ?> date<?= $session_total === 1 ? '' : 's'; ?> on sale</span>
  </div>
  <div class="mp-card-body">
    <?php if(empty($sessions_rows)): ?>
      <div class="mp-empty-state">
        <div class="mp-empty-icon"><i class="fa fa-calendar"></i></div>
        <h4>Nothing coming up</h4>
        <p>Add event dates to see guests here.</p>
      </div>
    <?php else: ?>
      <?php foreach($sessions_rows as $row): $s = $row['session']; ?>
      <div class="tk-row <?= $selected && (int)$selected->id === (int)$s->id ? 'current' : ''; ?>">
        <div>
          <div class="tk-name"><?= htmlspecialchars($row['event']->event_name); ?></div>
          <div class="tk-meta">
            <?= date('D, j M Y', strtotime($s->session_date)); ?>
            <?= $s->start_time ? ' · ' . date('g:ia', strtotime($s->start_time)) : ' · All day'; ?>
            <?php if(!empty($row['event']->venue)): ?> · <?= htmlspecialchars($row['event']->venue); ?><?php endif; ?>
          </div>
        </div>
        <div style="text-align:right;flex:0 0 auto;">
          <div class="tk-progress"><?= (int)$row['counts']['redeemed']; ?> / <?= (int)$row['counts']['sold']; ?> in</div>
          <div class="tk-meta"><?= (int)$row['waiting']; ?> expected · <?= (int)$s->remaining; ?> seats free</div>
          <div style="margin-top:6px;"><a class="mp-btn-secondary" style="font-size:12px;padding:5px 10px;" href="<?= base_url('events/tickets?session=' . (int)$s->id); ?>">Guest list</a></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if($session_total > count($sessions_rows)): ?>
        <div class="mp-page-sub" style="margin-top:10px;">Showing the nearest <?= count($sessions_rows); ?> dates.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var f = document.getElementById('ticket_code');
  if (!f) return;
  f.focus();
  // Codes are issued uppercase, so normalise whatever the scanner or thumb types.
  f.addEventListener('input', function(){ this.value = this.value.toUpperCase(); });
})();
</script>
