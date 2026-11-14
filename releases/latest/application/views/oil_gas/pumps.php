<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$editing = !empty($edit);
$pump_id = $editing ? (int)$edit->id : 0;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Every dispenser a duty has to read at this <?= htmlspecialchars(strtolower($site_label)); ?>. A nozzle that is not registered here is a hole in the reconciliation.</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('oil_gas/forecourt?site='.(int)$site_id); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Forecourt</a>
    <a href="<?= base_url('oil_gas/duty?site='.(int)$site_id); ?>" class="mp-btn-secondary"><i class="fa fa-exchange"></i> Duty sheet</a>
  </div>
</div>

<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/pumps?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Pumps here (<?= count($pumps); ?>)</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($pumps)): ?>
        <p class="gas-empty">No pump is registered at this <?= htmlspecialchars(strtolower($site_label)); ?> yet. Until one is, a duty cannot be opened here.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table">
          <thead><tr><th>Pump</th><th>Draws from</th><th>Gives out</th><th>Meter stands at</th><th></th></tr></thead>
          <tbody>
            <?php foreach($pumps as $pump): ?>
            <tr>
              <td><div class="loc-name"><?= htmlspecialchars($pump->pump_name); ?></div></td>
              <td><?= htmlspecialchars((string)$pump->tank_name) ?: '<span class="text-muted">no tank</span>' ?></td>
              <td><?= $pump->item_id ? htmlspecialchars(get_item_name($pump->item_id)) : '—' ?></td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres(isset($meters[(int)$pump->id]) ? $meters[(int)$pump->id] : $pump->opening_meter); ?></span> L</td>
              <td><?php if($can_edit): ?><a href="<?= base_url('oil_gas/pumps?site='.(int)$site_id.'&edit='.(int)$pump->id); ?>" class="mp-btn-secondary" style="padding:6px 10px;font-size:12px;"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3><?= $editing ? 'Change this pump' : 'Add a pump' ?></h3></div>
    <div class="mp-card-body">
      <?php if(!$can_edit): ?>
        <p class="gas-empty">Registering a pump changes what a duty measures, so it needs the location-edit permission.</p>
      <?php elseif(empty($tanks)): ?>
        <p class="gas-empty">Add the tank this pump draws from first &mdash; a pump without a tank cannot be reconciled.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_pump')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <input type="hidden" name="pump_id" value="<?= (int)$pump_id; ?>">
      <input type="hidden" name="warehouse_id" value="<?= (int)$site_id; ?>">
      <div class="gas-form">
        <div class="mp-form-group full">
          <label for="pump_name">Pump name <span class="text-danger">*</span></label>
          <input class="mp-form-control" type="text" id="pump_name" name="pump_name" required maxlength="120"
                 value="<?= htmlspecialchars($editing ? $edit->pump_name : 'Pump 1'); ?>" placeholder="What the attendant will say out loud">
        </div>
        <div class="mp-form-group">
          <label for="tank_id">Draws from <span class="text-danger">*</span></label>
          <select class="mp-form-control" id="tank_id" name="tank_id" required>
            <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0); ?>
            <option value="<?= (int)$tank->id; ?>" <?= ($editing && (int)$edit->tank_id === (int)$tank->id) ? 'selected' : ''; ?>>
              <?= htmlspecialchars($tank->tank_name); ?> — <?= mp_format_litres($pos['book']); ?> L in it
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="item_id">Gives out</label>
          <select class="mp-form-control" id="item_id" name="item_id">
            <option value="0">Same as its tank</option>
            <?php foreach($items as $item): ?>
            <option value="<?= (int)$item->id; ?>" <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="loc-sub">It has to match the tank, or the litres leave the wrong product.</div>
        </div>
        <div class="mp-form-group">
          <label for="opening_meter">Meter face today (litres)</label>
          <input class="mp-form-control" type="number" step="0.001" min="0" id="opening_meter" name="opening_meter"
                 value="<?= $editing ? mp_format_litres($edit->opening_meter) : ''; ?>" placeholder="Read it off the pump">
          <div class="loc-sub">A meter may only be set higher than a reading this pump has already reported.</div>
        </div>
      </div>
      <div class="mp-form-actions" style="margin-top:18px;">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Save pump</button>
        <?php if($editing): ?><a href="<?= base_url('oil_gas/pumps?site='.(int)$site_id); ?>" class="mp-btn-secondary">Cancel</a><?php endif; ?>
      </div>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  $(function(){ $('.oil_gas-forecourt-active-li').addClass('active'); });
</script>
