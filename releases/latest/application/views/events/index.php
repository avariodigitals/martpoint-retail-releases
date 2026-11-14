<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>

<style>
.ev-card{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:12px;padding:16px;margin-bottom:12px;box-shadow:var(--mp-shadow-sm)}
.ev-name{font-size:16px;font-weight:700;color:var(--mp-text)}
.ev-meta{font-size:12px;color:var(--mp-muted);margin-top:4px}
.ev-badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;background:rgba(5,150,105,.1);color:var(--mp-success);margin-top:8px}
.ev-badge.soldout{background:rgba(220,38,38,.1);color:var(--mp-danger)}
.ev-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px}
.ev-actions{margin-top:10px;display:flex;gap:8px;justify-content:flex-end}
</style>

<div class="mp-page-head">
  <div>
    <h2>Events</h2>
    <div class="mp-page-sub">Sell tickets to dated sessions — online store and counter</div>
  </div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-calendar"></i></div>
    <div class="mp-kpi-label">Active Events</div>
    <div class="mp-kpi-value"><?= (int)$counts['events']; ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Upcoming Dates</div>
    <div class="mp-kpi-value"><?= (int)$counts['sessions']; ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-icon"><i class="fa fa-tag"></i></div>
    <div class="mp-kpi-label">Tickets Sold</div>
    <div class="mp-kpi-value"><?= (int)$counts['tickets_sold']; ?></div>
  </div>
  <div class="mp-kpi-card expense">
    <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
    <div class="mp-kpi-label">Seats Still Free</div>
    <div class="mp-kpi-value"><?= (int)$counts['seats_left']; ?></div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: minmax(0,2fr) minmax(0,1fr); gap:24px; align-items:start;">
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-calendar"></i> Events</h3>
      <a class="mp-btn-secondary" href="<?= base_url('events/tickets'); ?>"><i class="fa fa-check-square-o"></i> Check-in</a>
    </div>
    <div class="mp-card-body">
      <?php if(empty($event_rows)): ?>
      <div class="mp-empty-state">
        <div class="mp-empty-icon"><i class="fa fa-calendar"></i></div>
        <h4>No events yet</h4>
        <p>Create one on the right, then add the dates you are running it.</p>
      </div>
      <?php else: ?>
        <div class="ev-grid">
          <?php foreach($event_rows as $row): $ev = $row['event']; ?>
          <div class="ev-card">
            <div class="ev-name"><?= htmlspecialchars($ev->event_name); ?></div>
            <div class="ev-meta">
              <?php if(!empty($ev->venue)): ?><i class="fa fa-map-marker"></i> <?= htmlspecialchars($ev->venue); ?> · <?php endif; ?>
              <i class="fa fa-tag"></i> <?= htmlspecialchars(store_number_format((float)$row['price'])); ?> per ticket
            </div>
            <div class="ev-meta" style="margin-top:4px;"><?= count($row['open']); ?> date<?= count($row['open']) === 1 ? '' : 's'; ?> on sale<?= $row['open'] ? ', first ' . date('j M', strtotime($row['open'][0]->session_date)) : ''; ?></div>
            <span class="ev-badge <?= $row['seats'] > 0 ? '' : 'soldout'; ?>"><?= $row['seats'] > 0 ? $row['seats'] . ' seats free' : 'Sold out / no dates'; ?></span>
            <div class="ev-actions">
              <a href="<?= base_url('events/edit/' . (int)$ev->id); ?>" class="mp-btn-secondary"><i class="fa fa-pencil"></i> Dates &amp; details</a>
              <a href="<?= base_url('events/archive/' . (int)$ev->id); ?>" class="mp-btn-secondary" data-mp-confirm="Archive <?= htmlspecialchars($ev->event_name, ENT_QUOTES); ?>? Tickets stop selling."><i class="fa fa-archive"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> New Event</h3></div>
    <div class="mp-card-body">
      <?= form_open_multipart('events', ['id' => 'event-create-form', 'enctype' => 'multipart/form-data']); ?>
        <input type="hidden" name="save_event" value="1">
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Event Name <span class="text-danger">*</span></label>
          <input type="text" name="event_name" class="mp-form-control" placeholder="e.g. Live Jazz Night" required maxlength="200">
        </div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Ticket Price <span class="text-danger">*</span></label>
          <input type="number" name="ticket_price" class="mp-form-control" step="0.01" min="0" placeholder="5000" required>
          <div class="mp-page-sub">One price per ticket; seats are limited by each date's capacity.</div>
        </div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Venue</label>
          <input type="text" name="venue" class="mp-form-control" placeholder="e.g. Garden Hall" maxlength="200">
        </div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Poster (optional)</label>
          <input type="file" name="poster" accept="image/*" class="mp-form-control">
        </div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>About</label>
          <textarea name="description" class="mp-form-control" rows="3" placeholder="What guests can expect"></textarea>
        </div>
        <div class="mp-form-actions" style="justify-content:flex-end;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> Create Event</button>
        </div>
      <?= form_close(); ?>
    </div>
  </div>
</div>

<script>
// Destructive links confirm in-app, never via window.confirm (project rule).
document.addEventListener('click', function(e){
  var a = e.target.closest('[data-mp-confirm]');
  if(!a) return;
  if (typeof mpConfirmAsync === 'function') {
    e.preventDefault(); e.stopPropagation();
    mpConfirmAsync(a.getAttribute('data-mp-confirm')).then(function(ok){ if(ok) window.location.href = a.getAttribute('href'); });
  }
}, true);
</script>
