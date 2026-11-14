<?php $this->load->helper('form'); ?>
<style>
  .vd-wrap .mp-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; overflow: hidden; margin-bottom: 24px; }
  .vd-wrap .mp-card-head { padding: 16px 20px; border-bottom: 1px solid #E5E7EB; background: #F9FAFB; }
  .vd-wrap .mp-card-head h3 { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
  .vd-wrap .mp-card-body { padding: 20px; }
  .vd-wrap .mp-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end; }
  .vd-wrap .form-group { margin-bottom: 0; }
  .vd-wrap .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .vd-wrap .form-control { border-radius: 10px; border: 1px solid #D1D5DB; padding: 10px 12px; font-size: 14px; }
  .vd-wrap table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  .vd-wrap th, .vd-wrap td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; text-align: left; font-size: 13px; }
  .vd-wrap th { background: #F9FAFB; font-weight: 600; color: #111827; }
  .vd-wrap .vd-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; background: #2563EB; color: #fff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; }
  .vd-wrap .vd-btn:hover { background: #1D4ED8; }
  .vd-wrap .vd-btn.danger { background: #DC2626; }
  .vd-wrap .vd-btn.danger:hover { background: #B91C1C; }
  .vd-wrap .tag { display: inline-block; padding: 2px 8px; border-radius: 6px; background: #F3F4F6; color: #4B5563; font-size: 11px; font-weight: 600; text-transform: capitalize; }
</style>

<div class="mp-page-head">
  <div>
    <h2>Vehicle Master Data</h2>
    <div class="mp-page-sub">Manage vehicle makes, models, and all dropdown options.</div>
  </div>
</div>

<div class="vd-wrap">

  <!-- Makes -->
  <div class="mp-card">
    <div class="mp-card-head"><h3><i class="fa fa-industry"></i> Vehicle Makes</h3></div>
    <div class="mp-card-body">
      <?= form_open('vehicle_data/save/make', ['class' => 'mp-form-grid']); ?>
        <input type="hidden" name="id" value="<?= (!empty($edit) && $edit->type === 'make') ? (int) $edit->id : ''; ?>">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label><?= (!empty($edit) && $edit->type === 'make') ? 'Edit' : 'New'; ?> Make</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Toyota" value="<?= (!empty($edit) && $edit->type === 'make') ? htmlspecialchars($edit->name) : ''; ?>" required>
        </div>
        <div class="form-group" style="display:flex; gap:8px; align-items:center;">
          <button type="submit" class="vd-btn"><i class="fa fa-<?= (!empty($edit) && $edit->type === 'make') ? 'save' : 'plus'; ?>"></i> <?= (!empty($edit) && $edit->type === 'make') ? 'Update Make' : 'Add Make'; ?></button>
          <?php if (!empty($edit) && $edit->type === 'make'): ?>
          <a href="<?= base_url('vehicle_data'); ?>" class="vd-btn" style="background:#6B7280;">Cancel</a>
          <?php endif; ?>
        </div>
      <?= form_close(); ?>

      <?php if (!empty($makes)): ?>
      <table>
        <thead><tr><th>Name</th><th width="120">Action</th></tr></thead>
        <tbody>
          <?php foreach ($makes as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m->name); ?></td>
            <td>
              <a href="<?= base_url('vehicle_data?edit=make&id=' . $m->id); ?>" class="vd-btn" style="background:#3B82F6;"><i class="fa fa-pencil"></i></a>
              <a href="<?= base_url('vehicle_data/delete/make/' . $m->id); ?>" class="vd-btn danger" onclick="return confirm('Delete this make and all its models?')"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Models -->
  <div class="mp-card">
    <div class="mp-card-head"><h3><i class="fa fa-car"></i> Vehicle Models</h3></div>
    <div class="mp-card-body">
      <?= form_open('vehicle_data/save/model', ['class' => 'mp-form-grid']); ?>
        <input type="hidden" name="id" value="<?= (!empty($edit) && $edit->type === 'model') ? (int) $edit->id : ''; ?>">
        <div class="form-group">
          <label>Make</label>
          <select name="make_id" class="form-control" required>
            <option value="">Select Make</option>
            <?php foreach ($makes as $m): ?>
            <option value="<?= $m->id; ?>" <?= (!empty($edit) && $edit->type === 'model' && $edit->make_id == $m->id) ? 'selected' : ''; ?>><?= htmlspecialchars($m->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column: span 2;">
          <label><?= (!empty($edit) && $edit->type === 'model') ? 'Edit' : 'New'; ?> Model</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Corolla" value="<?= (!empty($edit) && $edit->type === 'model') ? htmlspecialchars($edit->name) : ''; ?>" required>
        </div>
        <div class="form-group" style="display:flex; gap:8px; align-items:center;">
          <button type="submit" class="vd-btn"><i class="fa fa-<?= (!empty($edit) && $edit->type === 'model') ? 'save' : 'plus'; ?>"></i> <?= (!empty($edit) && $edit->type === 'model') ? 'Update Model' : 'Add Model'; ?></button>
          <?php if (!empty($edit) && $edit->type === 'model'): ?>
          <a href="<?= base_url('vehicle_data'); ?>" class="vd-btn" style="background:#6B7280;">Cancel</a>
          <?php endif; ?>
        </div>
      <?= form_close(); ?>

      <?php if (!empty($models)): ?>
      <table>
        <thead><tr><th>Make</th><th>Model</th><th width="120">Action</th></tr></thead>
        <tbody>
          <?php foreach ($models as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m->make_name); ?></td>
            <td><?= htmlspecialchars($m->name); ?></td>
            <td>
              <a href="<?= base_url('vehicle_data?edit=model&id=' . $m->id); ?>" class="vd-btn" style="background:#3B82F6;"><i class="fa fa-pencil"></i></a>
              <a href="<?= base_url('vehicle_data/delete/model/' . $m->id); ?>" class="vd-btn danger" onclick="return confirm('Delete this model?')"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Attributes -->
  <div class="mp-card">
    <div class="mp-card-head"><h3><i class="fa fa-list"></i> Dropdown Options</h3></div>
    <div class="mp-card-body">
      <?= form_open('vehicle_data/save/attribute', ['class' => 'mp-form-grid']); ?>
        <input type="hidden" name="id" value="<?= (!empty($edit) && $edit->type === 'attribute') ? (int) $edit->id : ''; ?>">
        <div class="form-group">
          <label>Field</label>
          <select name="attribute_type" class="form-control" required>
            <?php foreach ($attribute_types as $t): ?>
            <option value="<?= $t; ?>" <?= (!empty($edit) && $edit->type === 'attribute' && $edit->attribute_type === $t) ? 'selected' : ''; ?>><?= ucwords(str_replace('_', ' ', $t)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Option Value</label>
          <input type="text" name="attribute_value" class="form-control" placeholder="e.g. SUV" value="<?= (!empty($edit) && $edit->type === 'attribute') ? htmlspecialchars($edit->attribute_value) : ''; ?>" required>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (!empty($edit) && $edit->type === 'attribute') ? (int) $edit->sort_order : '0'; ?>" min="0">
        </div>
        <div class="form-group" style="display:flex; gap:8px; align-items:center;">
          <button type="submit" class="vd-btn"><i class="fa fa-<?= (!empty($edit) && $edit->type === 'attribute') ? 'save' : 'plus'; ?>"></i> <?= (!empty($edit) && $edit->type === 'attribute') ? 'Update Option' : 'Add Option'; ?></button>
          <?php if (!empty($edit) && $edit->type === 'attribute'): ?>
          <a href="<?= base_url('vehicle_data'); ?>" class="vd-btn" style="background:#6B7280;">Cancel</a>
          <?php endif; ?>
        </div>
      <?= form_close(); ?>

      <?php if (!empty($attributes)): ?>
      <table>
        <thead><tr><th>Field</th><th>Value</th><th>Sort</th><th width="120">Action</th></tr></thead>
        <tbody>
          <?php foreach ($attributes as $a): ?>
          <tr>
            <td><span class="tag"><?= ucwords(str_replace('_', ' ', $a->attribute_type)); ?></span></td>
            <td><?= htmlspecialchars($a->attribute_value); ?></td>
            <td><?= (int) $a->sort_order; ?></td>
            <td>
              <a href="<?= base_url('vehicle_data?edit=attribute&id=' . $a->id); ?>" class="vd-btn" style="background:#3B82F6;"><i class="fa fa-pencil"></i></a>
              <a href="<?= base_url('vehicle_data/delete/attribute/' . $a->id); ?>" class="vd-btn danger" onclick="return confirm('Delete this option?')"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

</div>
