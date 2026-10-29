<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); ?>

<style>
.se-row{display:flex;justify-content:space-between;align-items:center;gap:12px;border:1px solid var(--mp-border);border-radius:10px;padding:12px 14px;margin-bottom:8px;background:var(--mp-surface)}
.se-row.past{opacity:.55}
.se-date{font-weight:700;color:var(--mp-text);font-size:14px}
.se-meta{font-size:12px;color:var(--mp-muted);margin-top:2px}
.se-right{text-align:right;flex:0 0 auto}
.se-seats{font-size:12px;font-weight:600;color:var(--mp-success)}
.se-seats.low{color:var(--mp-warning)}
.se-seats.full{color:var(--mp-danger)}
.se-btns{display:flex;gap:6px;margin-top:6px;justify-content:flex-end}
.se-btns a{font-size:11px;padding:3px 8px;border:1px solid var(--mp-border);border-radius:6px;color:var(--mp-ink);text-decoration:none;background:var(--mp-surface)}
.se-btns a:hover{border-color:var(--mp-primary);color:var(--mp-primary)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($edit_event->event_name); ?></h2>
    <div class="mp-page-sub">
      Event dates and details
      <?php if(!empty($storefront_url)): ?>
        · <a href="<?= htmlspecialchars($storefront_url); ?>" target="_blank" rel="noopener">view in online store <i class="fa fa-external-link"></i></a>
      <?php endif; ?>
    </div>
  </div>
  <div><a class="mp-btn-secondary" href="<?= base_url('events'); ?>"><i class="fa fa-arrow-left"></i> All Events</a></div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<div class="mp-form-grid" style="grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:24px; align-items:start;">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-calendar"></i> Event Details</h3></div>
    <div class="mp-card-body">
      <?= form_open_multipart('events', ['enctype' => 'multipart/form-data']); ?>
        <input type="hidden" name="save_event" value="1">
        <input type="hidden" name="edit_id" value="<?= (int)$edit_event->id; ?>">
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Event Name <span class="text-danger">*</span></label>
          <input type="text" name="event_name" class="mp-form-control" value="<?= htmlspecialchars($edit_event->event_name); ?>" required maxlength="200">
        </div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label>Ticket Price <span class="text-danger">*</span></label>
          <input type="number" name="ticket_price" class="mp-form-control" step="0.01" min="0" required
            value="<?= htmlspecialchars((string)$ticket_price); ?>">
          <div class="mp-page-sub">Applies to every date. Changing it does not change tickets already sold.</div>
        </div>
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Venue</label>
            <input type="text" name="venue" class="mp-form-control" value="<?= htmlspecialchars((string)$edit_event->venue); ?>" maxlength="200">
          </div>
          <div class="mp-form-group">
            <label>Poster (optional)</label>
            <input type="file" name="poster" accept="image/*" class="mp-form-control">
          </div>
        </div>
        <div class="mp-form-group" style="margin-top:16px;margin-bottom:16px;">
          <label>About</label>
          <textarea name="description" class="mp-form-control" rows="3"><?= htmlspecialchars((string)$edit_event->description); ?></textarea>
        </div>
        <div class="mp-form-actions" style="justify-content:flex-end;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> Save Details</button>
        </div>
      <?= form_close(); ?>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-clock-o"></i> Dates</h3>
      <span class="mp-page-sub">Tickets sell per date, up to each date's seats</span>
    </div>
    <div class="mp-card-body">
      <?= form_open('events/edit/' . (int)$edit_event->id); ?>
        <input type="hidden" name="add_session" value="1">
        <div class="mp-form-grid" style="margin-bottom:12px;">
          <div class="mp-form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="date" name="session_date" class="mp-form-control" min="<?= date('Y-m-d'); ?>" required>
          </div>
          <div class="mp-form-group">
            <label>Start Time</label>
            <input type="time" name="start_time" class="mp-form-control" value="19:00">
          </div>
          <div class="mp-form-group">
            <label>Seats <span class="text-danger">*</span></label>
            <input type="number" name="capacity" class="mp-form-control" min="1" value="50" required>
          </div>
        </div>
        <div class="mp-form-actions" style="justify-content:flex-end;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-plus"></i> Add Date</button>
        </div>
      <?= form_close(); ?>

      <hr style="margin:16px 0;border:0;border-top:1px solid var(--mp-border);">

      <?php if(empty($sessions)): ?>
        <div class="mp-page-sub" style="text-align:center;padding:12px;">No dates yet — the ticket is not on sale until you add one.</div>
      <?php else: ?>
        <?php $today = date('Y-m-d'); ?>
        <?php foreach($sessions as $s): $past = $s->session_date < $today; ?>
        <div class="se-row <?= $past ? 'past' : ''; ?>">
          <div>
            <div class="se-date"><?= date('D, j M Y', strtotime($s->session_date)); ?></div>
            <div class="se-meta">
              <?= $s->start_time ? date('g:ia', strtotime($s->start_time)) : 'All day'; ?>
              · <?= (int)$s->capacity; ?> seats
              <?php if((int)$s->status === 0): ?> · <strong>closed</strong><?php endif; ?>
              <?php if($past): ?> · passed<?php endif; ?>
            </div>
          </div>
          <div class="se-right">
            <?php $left = (int)$s->capacity - (int)$s->sold; $cs = $s->ticket_counts ?? ['sold'=>0,'redeemed'=>0]; ?>
            <div class="se-seats <?= $left <= 0 ? 'full' : ($left <= 5 ? 'low' : ''); ?>">
              <?= $past ? '' : ($left . ' left'); ?>
            </div>
            <div class="se-meta"><?= (int)$cs['sold']; ?> sold · <?= (int)$cs['redeemed']; ?> checked in</div>
            <div class="se-btns">
              <a href="?close=<?= (int)$s->id; ?>"><?= (int)$s->status === 1 ? 'Close' : 'Reopen'; ?></a>
              <?php if((int)$s->sold === 0): ?>
              <a href="?delete_session=<?= (int)$s->id; ?>" data-mp-confirm="Remove <?= date('j M', strtotime($s->session_date)); ?>?">Remove</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('click', function(e){
  var a = e.target.closest('[data-mp-confirm]');
  if(!a) return;
  if (typeof mpConfirmAsync === 'function') {
    e.preventDefault(); e.stopPropagation();
    mpConfirmAsync(a.getAttribute('data-mp-confirm')).then(function(ok){ if(ok) window.location.href = a.getAttribute('href'); });
  }
}, true);
</script>
