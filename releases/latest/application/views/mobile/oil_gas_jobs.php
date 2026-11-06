<?php
$og_sites = is_array($sites ?? null) ? $sites : array();
$og_locations = is_array($locations ?? null) ? $locations : array();
$og_editing = $editing ?? null;
$og_open = $og_editing ? (int)($og_editing->warehouse_id ?? 0) : (int)($site ?? 0);
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
$this->load->view('mobile/gas_head', [
  'gas_title' => 'Service Work',
  'gas_sub' => 'Installation, maintenance and repair visits, each one tied to the location it happens at',
  'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
  'theme_link' => $theme_link,
]);
if(count($og_locations) > 1){
  $this->load->view('mobile/gas_sites', ['sites'=>$og_locations, 'site_id'=>$site, 'gas_route'=>'jobs']);
}
?>

<div class='stats'>
  <div class='stat <?= $open_count ? '' : ''; ?>'><strong><?= (int)$open_count; ?></strong><span>Jobs still open</span></div>
  <div class='stat'><strong><?= (int)$total; ?></strong><span>In this view</span></div>
</div>

<?php if(!empty($scoped)): ?>
  <div class='callout info'>You are shown the work booked at <?= count($og_locations) === 1 ? 'your location' : 'your ' . count($og_locations) . ' locations'; ?>. Head office sees the whole company.</div>
<?php endif; ?>

<?php if(!$og_sites): ?>
  <div class='callout bad'>No <?= htmlspecialchars(strtolower($site_label)); ?> is open to you yet, so there is nowhere to send a visit. Ask head office to add you to a location under Branches.</div>
<?php endif; ?>

<?php if(($can_add || $can_edit) && $og_sites): ?>
<div class='card'>
  <div class='section-title'><?= $og_editing ? 'Change this booking' : 'Book a visit'; ?></div>
  <?= form_open(base_url('oil_gas/save_job')); ?>
  <input type='hidden' name='job_id' value='<?= $og_editing ? (int)$og_editing->id : 0; ?>'>
  <input type='hidden' name='status' value="<?= $og_editing ? htmlspecialchars((string)$og_editing->status) : 'open'; ?>">
  <div class='form-group'>
    <label>Short name for the job <span class='req'>*</span></label>
    <input type='text' name='title' maxlength='200' required placeholder='Replace the LPG filling scale head'
           value="<?= $og_editing ? htmlspecialchars((string)$og_editing->title) : ''; ?>">
  </div>
  <div class='form-group'>
    <label>Who is this work for <span class='req'>*</span></label>
    <select class='mp-select' name='customer_id' required>
      <option value=''>Choose the <?= htmlspecialchars(strtolower($customer_label)); ?></option>
      <?php foreach($customers as $customer): ?>
      <option value='<?= (int)$customer->id; ?>' <?= $og_editing && (int)$og_editing->customer_id === (int)$customer->id ? 'selected' : ''; ?>><?= htmlspecialchars($customer->customer_name); ?></option>
      <?php endforeach; ?>
    </select>
    <?php if(count($customers) >= 200): ?><span class='hint'>Not listed? Search the name above — the list is capped at 200.</span><?php endif; ?>
  </div>
  <div class='form-group'>
    <label>Which <?= htmlspecialchars($site_label); ?> does the work <span class='req'>*</span></label>
    <select class='mp-select' name='warehouse_id' required>
      <?php if(!$og_editing || !isset($og_sites[$og_open])): ?><option value=''>Choose the location</option><?php endif; ?>
      <?php foreach($og_sites as $id => $name): ?>
      <option value='<?= (int)$id; ?>' <?= $og_open === (int)$id ? 'selected' : ''; ?>><?= htmlspecialchars($name); ?></option>
      <?php endforeach; ?>
    </select>
    <span class='hint'>This decides whose worklist the visit lands on.</span>
  </div>
  <div class='form-row'>
    <div class='form-group'>
      <label>Kind of work</label>
      <select class='mp-select' name='job_type'>
        <?php foreach($og_types as $type): ?>
        <option value='<?= htmlspecialchars($type); ?>' <?= $og_editing && $og_editing->job_type === $type ? 'selected' : ''; ?>><?= htmlspecialchars(ucfirst($type)); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class='form-group'>
      <label>How urgent</label>
      <select class='mp-select' name='priority'>
        <?php foreach(array('normal','high','urgent') as $level): ?>
        <option value='<?= $level; ?>' <?= (($og_editing ? $og_editing->priority : 'normal') === $level) ? 'selected' : ''; ?>><?= ucfirst($level); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class='form-row'>
    <div class='form-group'>
      <label>Date of the visit</label>
      <input type='date' name='scheduled_date' value="<?= $og_editing && !empty($og_editing->scheduled_date) ? htmlspecialchars((string)$og_editing->scheduled_date) : ''; ?>">
    </div>
    <div class='form-group'>
      <label>Time, if fixed</label>
      <input type='text' name='scheduled_time' maxlength='10' placeholder='09:30' value="<?= $og_editing ? htmlspecialchars((string)$og_editing->scheduled_time) : ''; ?>">
    </div>
  </div>
  <div class='form-group'>
    <label>Who is going</label>
    <select class='mp-select' name='assigned_user_id'>
      <option value=''>Not given to anybody yet</option>
      <?php foreach($staff as $person): ?>
      <option value='<?= (int)$person->id; ?>' <?= $og_editing && (int)$og_editing->assigned_user_id === (int)$person->id ? 'selected' : ''; ?>><?= htmlspecialchars($person->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>What the visit costs</label>
    <input type='number' step='0.01' min='0' name='labour_charge' placeholder='0.00' inputmode='decimal'
           value="<?= $og_editing ? htmlspecialchars((string)$og_editing->labour_charge) : ''; ?>">
  </div>
  <div class='form-group'>
    <label>What is wrong, or what was asked for</label>
    <textarea name='description' rows='3' placeholder='Access notes, the machine involved, what the customer said'><?= $og_editing ? htmlspecialchars((string)$og_editing->description) : ''; ?></textarea>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-check'></i> <?= $og_editing ? 'Save the changes' : 'Book the visit'; ?></button>
  <?= form_close(); ?>
</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Show me</div>
  <form method='get' action='<?= base_url('oil_gas/jobs'); ?>'>
    <?php if($site): ?><input type='hidden' name='site' value='<?= (int)$site; ?>'><?php endif; ?>
    <div class='form-group'>
      <label>Where it stands</label>
      <select class='mp-select' name='status'>
        <option value=''>Every job</option>
        <option value='open_list' <?= ($status ?? '') === 'open_list' ? 'selected' : ''; ?>>Still open</option>
        <?php foreach($og_status_words as $key => $word): ?>
        <option value='<?= $key; ?>' <?= ($status ?? '') === $key ? 'selected' : ''; ?>><?= htmlspecialchars($word); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class='form-group'>
      <label>Find the <?= htmlspecialchars(strtolower($customer_label)); ?> by name</label>
      <input type='search' name='q' maxlength='60' placeholder='Start a name…' value="<?= htmlspecialchars((string)($search ?? '')); ?>">
    </div>
    <button type='submit' class='btn btn-ghost'><i class='fa fa-search'></i> Filter</button>
  </form>
</div>

<div class='card'>
  <div class='section-title'>Jobs<?= $shown < $total ? ' — newest ' . (int)$shown . ' of ' . (int)$total : ''; ?></div>
  <?php if(empty($jobs)): ?>
    <div class='empty'><i class='fa fa-wrench'></i>No work booked<?= $search !== '' ? ' for “'.htmlspecialchars($search).'”' : '' ?><?= $site ? ' here' : ''; ?>. Book a visit above, or clear the filters.</div>
  <?php else: foreach($jobs as $job):
    $site_key = (int)($job->warehouse_id ?? 0);
    $site_word = $site_key && isset($og_sites[$site_key]) ? $og_sites[$site_key] : ($job->location_name ?? '');
    $overdue = !empty($job->scheduled_date) && $job->scheduled_date < date('Y-m-d') && !in_array($job->status, array('completed','cancelled'), true);
    $charge = (float)($job->labour_charge ?? 0) + (float)($job->parts_total ?? 0);
  ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)($job->title !== '' ? $job->title : 'Service visit')); ?>
        <span class='flag <?= $job->status === 'completed' ? 'ok' : ($job->status === 'cancelled' ? 'held' : 'open'); ?>'><?= htmlspecialchars($og_status_words[$job->status] ?? ucwords(str_replace('_',' ',(string)$job->status))); ?></span></div>
      <div class='meta'><?= htmlspecialchars(trim((string)($job->customer_name ?? '')) ?: 'a customer'); ?>
        · <?= $site_word !== '' ? htmlspecialchars($site_word) : 'no location yet'; ?></div>
      <div class='meta'><?= !empty($job->scheduled_date) ? show_date((string)$job->scheduled_date) : 'not dated'; ?>
        <?= !empty($job->scheduled_time) ? ' · ' . htmlspecialchars((string)$job->scheduled_time) : ''; ?>
        · <?= htmlspecialchars($og_first_name($job->engineer ?? '')); ?>
        · <span class='litre'><?= mp_format_money($charge); ?></span>
        <?= $overdue ? ' · <strong>past its date</strong>' : ''; ?></div>
      <?php if($job->description): ?><div class='meta'><?= htmlspecialchars(mb_strimwidth(strip_tags((string)$job->description), 0, 140, '…')); ?></div><?php endif; ?>
      <?php if($can_edit && !empty($job->next)): ?>
      <?= form_open(base_url('oil_gas/set_job_status')); ?>
      <input type='hidden' name='job_id' value='<?= (int)$job->id; ?>'>
      <input type='hidden' name='warehouse_id' value='<?= $site_key; ?>'>
      <div class='form-group' style='margin:10px 0 8px'>
        <label>Move this job to</label>
        <select class='mp-select' name='status'>
          <option value=''>Choose the next step</option>
          <?php foreach($job->next as $next): ?>
          <option value='<?= htmlspecialchars($next); ?>'><?= htmlspecialchars($og_status_words[$next] ?? ucfirst($next)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type='submit' class='btn btn-ghost'><i class='fa fa-arrow-right'></i> Record it</button>
      <?= form_close(); ?>
      <?php endif; ?>
      <?php if($can_edit): ?><p class='note'><a href='<?= base_url('oil_gas/jobs?edit='.(int)$job->id); ?>'>Change the booking</a></p><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
