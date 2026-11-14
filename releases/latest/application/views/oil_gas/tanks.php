<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$CI =& get_instance();
$editing = !empty($edit);
$tank_id = $editing ? (int)$edit->id : 0;
$dip_tank = $this->input->get('dip') ? (int)$this->input->get('dip', TRUE) : 0;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">The ground tanks at this <?= htmlspecialchars(strtolower($site_label)); ?> &mdash; what each one can hold, what it actually holds, and the dip that proves it</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('oil_gas/forecourt?site='.(int)$site_id); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Forecourt</a>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/tanks?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(!$can_edit): ?>
<div class="alert alert-info"><strong>Read only.</strong> Registering a tank changes what the site can measure, so it needs the location-edit permission. You can still read a dip below.</div>
<?php endif; ?>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Tanks here (<?= count($tanks); ?>)</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($tanks)): ?>
        <p class="gas-empty">No tanks yet. Add the tank as it is built &mdash; name, what it holds, and how many litres it takes.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table">
          <thead><tr><th>Tank</th><th>Holds</th><th>Book litres</th><th>Last dip</th><th></th></tr></thead>
          <tbody>
            <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0,'dip'=>null,'gap'=>null); ?>
            <tr>
              <td>
                <div class="loc-name"><?= htmlspecialchars($tank->tank_name); ?></div>
                <?php if(!empty($tank->note)): ?><div class="loc-sub"><?= htmlspecialchars($tank->note); ?></div><?php endif; ?>
              </td>
              <td>
                <?= $tank->item_id ? htmlspecialchars(get_item_name($tank->item_id)) : 'Any product' ?>
                <div class="loc-sub"><?= mp_format_litres($tank->capacity_litres); ?> L capacity</div>
              </td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($pos['book']); ?></span> L
                <div class="loc-sub">safe to <?= mp_format_litres($tank->safe_fill_litres); ?> L</div></td>
              <td class="gas-qty">
                <?= $pos['dip'] === null ? '—' : mp_format_litres($pos['dip']).' L' ?>
                <?php if($pos['gap'] !== null && abs($pos['gap']) >= 0.0001): ?>
                  <div class="loc-sub <?= $pos['gap'] < 0 ? 'text-danger' : 'text-warning'; ?>"><?= ($pos['gap'] > 0 ? '+' : '') . mp_format_litres($pos['gap']); ?> L against the book</div>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;">
                <a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id.'&dip='.(int)$tank->id); ?>" class="mp-btn-secondary" style="padding:6px 10px;font-size:12px;"><i class="fa fa-tint"></i> Dip</a>
                <a href="<?= base_url('oil_gas/fill?tank='.(int)$tank->id); ?>" class="mp-btn-secondary" style="padding:6px 10px;font-size:12px;"><i class="fa fa-arrow-down"></i> Fill</a>
                <?php if($can_edit): ?><a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id.'&edit='.(int)$tank->id); ?>" class="mp-btn-secondary" style="padding:6px 10px;font-size:12px;"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3><?= $editing ? 'Change this tank' : 'Add a tank' ?></h3></div>
    <div class="mp-card-body">
      <?php if(!$can_edit): ?>
        <p class="gas-empty">You can read the tanks at this <?= htmlspecialchars(strtolower($site_label)); ?> but not change them.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_tank')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <input type="hidden" name="tank_id" value="<?= (int)$tank_id; ?>">
      <input type="hidden" name="warehouse_id" value="<?= (int)$site_id; ?>">
      <div class="gas-form">
        <div class="mp-form-group full">
          <label for="tank_name">Tank name <span class="text-danger">*</span></label>
          <input class="mp-form-control" type="text" id="tank_name" name="tank_name" required maxlength="120"
                 value="<?= htmlspecialchars($editing ? $edit->tank_name : 'Tank 1 — Petrol'); ?>"
                 placeholder="The name the staff will read out">
        </div>
        <div class="mp-form-group">
          <label for="item_id">Holds</label>
          <select class="mp-form-control" id="item_id" name="item_id">
            <option value="0">Any product</option>
            <?php foreach($items as $item): ?>
            <option value="<?= (int)$item->id; ?>" <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="capacity_litres">Capacity in litres <span class="text-danger">*</span></label>
          <input class="mp-form-control" type="number" step="0.001" min="1" id="capacity_litres" name="capacity_litres" required
                 value="<?= $editing ? mp_format_litres($edit->capacity_litres) : ''; ?>" placeholder="e.g. 20000">
        </div>
        <div class="mp-form-group">
          <label for="safe_fill_litres">Fill no higher than</label>
          <input class="mp-form-control" type="number" step="0.001" min="0" id="safe_fill_litres" name="safe_fill_litres"
                 value="<?= $editing ? mp_format_litres($edit->safe_fill_litres) : ''; ?>" placeholder="Leave blank for 95% of capacity">
          <div class="loc-sub">A tanker that is refused at the hatch is better than one that overflows.</div>
        </div>
        <div class="mp-form-group">
          <label for="note">Note</label>
          <input class="mp-form-control" type="text" id="note" name="note" maxlength="255"
                 value="<?= htmlspecialchars($editing ? (string)$edit->note : ''); ?>" placeholder="e.g. shared header with Tank 2">
        </div>
      </div>
      <div class="mp-form-actions" style="margin-top:18px;">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Save tank</button>
        <?php if($editing): ?><a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id); ?>" class="mp-btn-secondary">Cancel</a><?php endif; ?>
      </div>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if(!empty($tanks)): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Record a dip</h3></div>
  <div class="mp-card-body">
    <p class="gas-note" style="margin-top:0;">A dip is evidence, not a booking. Inside the <?= mp_format_percent(mp_forecourt_tank_variance_percent(get_current_store_id())); ?> you allow for a tank, the book is corrected on the spot. Past that, nothing moves until a manager signs for it.</p>
    <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0); ?>
    <?php $open = $can_edit && $dip_tank === (int)$tank->id; ?>
    <div class="mp-card-form" style="padding:14px;margin-bottom:12px;">
      <strong><?= htmlspecialchars($tank->tank_name); ?></strong>
      <span class="loc-sub">book holds <?= mp_format_litres($pos['book']); ?> L</span>
      <?php if($open): ?>
      <?= form_open(base_url('oil_gas/save_dip')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <input type="hidden" name="tank_id" value="<?= (int)$tank->id; ?>">
      <div class="gas-form" style="margin-top:10px;">
        <div class="mp-form-group">
          <label for="level-<?= (int)$tank->id; ?>">Dipped quantity in litres <span class="text-danger">*</span></label>
          <input class="mp-form-control" type="number" step="0.001" min="0" id="level-<?= (int)$tank->id; ?>" name="level_litres" required
                 placeholder="What the dip tape or gauge says">
        </div>
        <div class="mp-form-group">
          <label for="note-<?= (int)$tank->id; ?>">Note</label>
          <input class="mp-form-control" type="text" id="note-<?= (int)$tank->id; ?>" name="note" maxlength="255" placeholder="Water past 40mm, temperature 31°C">
        </div>
      </div>
      <div class="mp-form-actions" style="margin-top:12px;"><button type="submit" class="mp-btn-primary"><i class="fa fa-tint"></i> Record this dip</button></div>
      <?= form_close(); ?>
      <?php elseif(!$can_edit): ?>
      <div class="loc-sub">You are not allowed to record dips here.</div>
      <?php else: ?>
      <div class="loc-sub"><a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id.'&dip='.(int)$tank->id); ?>">Dip this tank</a></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
  $(function(){ $('.oil_gas-forecourt-active-li').addClass('active'); });
</script>
