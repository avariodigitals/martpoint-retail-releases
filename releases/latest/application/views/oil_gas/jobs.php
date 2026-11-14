<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$CI =& get_instance();
$og_sites = is_array($sites ?? null) ? $sites : array();
$og_editing = $editing ?? null;
$og_open = $editing ? (int)($editing->warehouse_id ?? 0) : (int)($site ?? 0);
$og_types = is_array($job_types ?? null) ? $job_types : array();
$og_first_name = function($name){
  $name = trim((string)$name);
  if($name === '') return 'nobody yet';
  $parts = preg_split('/\s+/', $name);
  return $parts[0];
};
$og_status_words = array(
  'open'=>'Booked', 'scheduled'=>'Scheduled', 'in_progress'=>'On site',
  'awaiting_parts'=>'Waiting for a part', 'completed'=>'Finished', 'cancelled'=>'Called off',
);
?>

<div class="gas-hero">
  <div>
    <h2>Service work &mdash; every <?= htmlspecialchars(count($og_sites) > 1 ? strtolower($site_label).'s' : strtolower($site_label)); ?></h2>
    <p>Installation, maintenance and repair visits, each one tied to the <?= htmlspecialchars(strtolower($site_label)); ?> it happens at. Head office sees the whole company's work; a site's staff see only their own.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas'); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> Workspace</a>
    <a href="<?= base_url('oil_gas/report'); ?>" class="gas-btn"><i class="fa fa-bar-chart"></i> Company report</a>
  </div>
</div>

<?php if(count($og_sites) > 1): ?>
<div class="gas-sites">
  <a href="<?= base_url('oil_gas/jobs'); ?>" class="gas-site <?= $site ? '' : 'active'; ?>">All my locations</a>
  <?php foreach($og_sites as $id => $name): ?>
    <a href="<?= base_url('oil_gas/jobs?site='.(int)$id); ?>" class="gas-site <?= (int)$site === (int)$id ? 'active' : ''; ?>"><?= htmlspecialchars($name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi"><strong><?= (int)$open_count; ?></strong><span>Jobs still open</span><small>Anything not finished or called off</small></div>
  <div class="gas-kpi"><strong><?= (int)$total; ?></strong><span>Jobs in this view</span><small><?= $site && isset($og_sites[$site]) ? htmlspecialchars($og_sites[$site]) : 'Every location you are allowed to see'; ?></small></div>
  <div class="gas-kpi"><strong><?= count($og_sites); ?></strong><span><?= htmlspecialchars($site_label); ?> you work across</span><small>Site staff only see their own</small></div>
</div>

<?php if(!$og_sites): ?>
  <div class="mp-card-form gas-card"><div class="mp-card-body">
    <p class="gas-empty">No <?= htmlspecialchars(strtolower($site_label)); ?> is open to you yet, so there is nowhere to send a visit. Ask head office to add you to a location under Branches.</p>
  </div></div>
<?php endif; ?>

<?php if($can_add || $can_edit): ?>
<div class="mp-card-form gas-card" style="margin-bottom:20px">
  <div class="mp-card-head">
    <h3><?= $og_editing ? 'Change this booking' : 'Book a visit'; ?></h3>
    <?php if($og_editing): ?><a class="mp-qa-btn" href="<?= base_url('oil_gas/jobs'); ?>">Leave it alone</a><?php endif; ?>
  </div>
  <div class="mp-card-body">
    <?php if(!$og_sites): ?>
      <p class="gas-empty">Choose a location to yourself first &mdash; work is always booked against one.</p>
    <?php else: ?>
    <?= form_open(base_url('oil_gas/save_job')); ?>
    <input type="hidden" name="job_id" value="<?= $og_editing ? (int)$og_editing->id : 0; ?>">
    <input type="hidden" name="status" value="<?= $og_editing ? htmlspecialchars((string)$og_editing->status) : 'open'; ?>">
    <div class="gas-form">
      <div class="mp-form-group">
        <label for="og_job_customer">Who is this work for <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="og_job_customer" name="customer_id" required>
          <option value="">Choose the <?= htmlspecialchars(strtolower($customer_label)); ?></option>
          <?php foreach($customers as $customer): ?>
          <option value="<?= (int)$customer->id; ?>" <?= $og_editing && (int)$og_editing->customer_id === (int)$customer->id ? 'selected' : ''; ?>><?= htmlspecialchars($customer->customer_name); ?></option>
          <?php endforeach; ?>
        </select>
        <?php if(count($customers) >= 200): ?>
        <p class="gas-note">Only the first 200 names show here. Use the search box above to find someone further down.</p>
        <?php endif; ?>
      </div>
      <div class="mp-form-group">
        <label for="og_job_site">Which <?= htmlspecialchars($site_label); ?> does the work <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="og_job_site" name="warehouse_id" required>
          <?php if(!$og_editing || !isset($og_sites[$og_open])): ?><option value="">Choose the location</option><?php endif; ?>
          <?php foreach($og_sites as $id => $name): ?>
          <option value="<?= (int)$id; ?>" <?= $og_open === (int)$id ? 'selected' : ''; ?>><?= htmlspecialchars($name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="og_job_type">What kind of work</label>
        <select class="mp-form-control" id="og_job_type" name="job_type">
          <?php foreach($og_types as $type): ?>
          <option value="<?= htmlspecialchars($type); ?>" <?= $og_editing && $og_editing->job_type === $type ? 'selected' : ''; ?>><?= htmlspecialchars(ucfirst($type)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="og_job_priority">How urgent</label>
        <select class="mp-form-control" id="og_job_priority" name="priority">
          <?php foreach(array('normal','high','urgent') as $level): ?>
          <option value="<?= $level; ?>" <?= (($og_editing ? $og_editing->priority : 'normal') === $level) ? 'selected' : ''; ?>><?= ucfirst($level); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="og_job_date">Date of the visit</label>
        <input class="mp-form-control" type="date" id="og_job_date" name="scheduled_date" value="<?= $og_editing && !empty($og_editing->scheduled_date) ? htmlspecialchars((string)$og_editing->scheduled_date) : ''; ?>">
      </div>
      <div class="mp-form-group">
        <label for="og_job_time">Time, if it is fixed</label>
        <input class="mp-form-control" type="text" id="og_job_time" name="scheduled_time" maxlength="10" placeholder="e.g. 09:30" value="<?= $og_editing ? htmlspecialchars((string)$og_editing->scheduled_time) : ''; ?>">
      </div>
      <div class="mp-form-group">
        <label for="og_job_staff">Who is going</label>
        <select class="mp-form-control" id="og_job_staff" name="assigned_user_id">
          <option value="">Not given to anybody yet</option>
          <?php foreach($staff as $person): ?>
          <option value="<?= (int)$person->id; ?>" <?= $og_editing && (int)$og_editing->assigned_user_id === (int)$person->id ? 'selected' : ''; ?>><?= htmlspecialchars($person->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="og_job_charge">What the visit costs</label>
        <input class="mp-form-control" type="number" id="og_job_charge" name="labour_charge" step="0.01" min="0" placeholder="0.00"
               value="<?= $og_editing ? htmlspecialchars((string)$og_editing->labour_charge) : ''; ?>">
      </div>
      <div class="mp-form-group full">
        <label for="og_job_title">Short name for the job <span class="text-danger">*</span></label>
        <input class="mp-form-control" type="text" id="og_job_title" name="title" maxlength="200" required
               placeholder="e.g. Replace the LPG filling scale head" value="<?= $og_editing ? htmlspecialchars((string)$og_editing->title) : ''; ?>">
      </div>
      <div class="mp-form-group full">
        <label for="og_job_notes">What is wrong, or what was asked for</label>
        <textarea class="mp-form-control" id="og_job_notes" name="description" rows="3" placeholder="Access notes, the machine involved, what the customer said"><?= $og_editing ? htmlspecialchars((string)$og_editing->description) : ''; ?></textarea>
      </div>
      <div class="mp-form-actions">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $og_editing ? 'Save the changes' : 'Book the visit'; ?></button>
      </div>
      <p class="gas-note">The location matters as much as the date: it decides whose worklist the visit lands on and which site's parts are used.</p>
      <?= form_close(); ?>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form gas-card">
  <div class="mp-card-head"><h3>Show me</h3></div>
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('oil_gas/jobs'); ?>">
    <?php if($site): ?><input type="hidden" name="site" value="<?= (int)$site; ?>"><?php endif; ?>
    <div class="gas-form" style="grid-template-columns:minmax(0,1fr) minmax(0,1fr) auto;align-items:end">
      <div class="mp-form-group" style="margin:0">
        <label for="og_job_status">Where it stands</label>
        <select class="mp-form-control" id="og_job_status" name="status">
          <option value="">Every job</option>
          <option value="open_list" <?= ($status ?? '') === 'open_list' ? 'selected' : ''; ?>>Still open</option>
          <?php foreach($og_status_words as $key => $word): ?>
          <option value="<?= $key; ?>" <?= ($status ?? '') === $key ? 'selected' : ''; ?>><?= htmlspecialchars($word); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group" style="margin:0">
        <label for="og_job_search">Find the <?= htmlspecialchars(strtolower($customer_label)); ?> by name</label>
        <input class="mp-form-control" type="search" id="og_job_search" name="q" maxlength="60" placeholder="Start a name…" value="<?= htmlspecialchars((string)($search ?? '')); ?>">
      </div>
      <div class="mp-form-actions" style="margin:0"><button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Filter</button></div>
    </div>
    </form>
  </div>
</div>

<div class="mp-card-form gas-card" style="margin-top:16px">
  <div class="mp-card-head"><h3>Jobs<?= $shown < $total ? ' — showing the newest '.$shown.' of '.$total : ''; ?></h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($jobs)): ?>
      <p class="gas-empty">No work booked<?= $search !== '' ? ' for the name “'.htmlspecialchars($search).'”' : '' ?><?= $site ? ' at this location' : ''; ?>. Book a visit above, or clear the filters.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr>
          <th>Job</th><th><?= htmlspecialchars($site_label); ?></th><th>When</th><th>Who is going</th><th>Cost</th><th>Where it stands</th><th></th>
        </tr></thead>
        <tbody>
          <?php foreach($jobs as $job):
            $site_key = (int)($job->warehouse_id ?? 0);
            $site_word = $site_key && isset($og_sites[$site_key]) ? $og_sites[$site_key] : ($job->location_name ?? '');
            $overdue = !empty($job->scheduled_date) && $job->scheduled_date < date('Y-m-d') && !in_array($job->status, array('completed','cancelled'), true);
            $charge = (float)($job->labour_charge ?? 0) + (float)($job->parts_total ?? 0);
          ?>
          <tr>
            <td>
              <div class="loc-name"><?= htmlspecialchars((string)($job->title !== '' ? $job->title : 'Service visit')); ?></div>
              <div class="loc-sub"><?= htmlspecialchars(trim((string)($job->customer_name ?? '')) ?: 'a customer'); ?>
               <?= $job->equipment_serial ? ' · unit ' . htmlspecialchars((string)$job->equipment_serial) : ''; ?></div>
            </td>
            <td><?= $site_word !== '' ? htmlspecialchars($site_word) : '<span class="loc-off">no location yet</span>'; ?></td>
            <td>
              <?= !empty($job->scheduled_date) ? show_date((string)$job->scheduled_date) : 'not dated'; ?>
              <?= !empty($job->scheduled_time) ? ' · ' . htmlspecialchars((string)$job->scheduled_time) : ''; ?>
              <?= $overdue ? '<div class="loc-sub">past its date</div>' : ''; ?>
            </td>
            <td><?= htmlspecialchars($og_first_name($job->engineer ?? '')); ?></td>
            <td class="gas-qty"><?= mp_format_money($charge); ?></td>
            <td><span class="gas-status <?= in_array($job->status, array('completed'), true) ? 'ok' : ($job->status === 'cancelled' ? 'held' : 'open'); ?>">
              <?= htmlspecialchars($og_status_words[$job->status] ?? ucwords(str_replace('_',' ',(string)$job->status))); ?></span></td>
            <td style="white-space:nowrap">
              <?php if($can_edit && !empty($job->next)): ?>
              <?= form_open(base_url('oil_gas/set_job_status')); ?>
              <input type="hidden" name="job_id" value="<?= (int)$job->id; ?>">
              <input type="hidden" name="warehouse_id" value="<?= $site_key; ?>">
              <select class="mp-form-control" name="status" style="width:170px;display:inline-block">
                <option value="">Move to…</option>
                <?php foreach($job->next as $next): ?>
                <option value="<?= htmlspecialchars($next); ?>"><?= htmlspecialchars($og_status_words[$next] ?? ucfirst($next)); ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="mp-qa-btn teal" style="padding:6px 10px"><i class="fa fa-check"></i></button>
              <?= form_close(); ?>
              <?php endif; ?>
              <?php if($can_edit): ?><a class="mp-qa-btn" href="<?= base_url('oil_gas/jobs?edit='.(int)$job->id); ?>" style="margin-top:6px;display:inline-block"><i class="fa fa-pencil"></i> Change</a><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
