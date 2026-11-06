<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<style>
.tm-card {
    background: var(--mp-surface);
    border: 1px solid var(--mp-border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    border-left: 4px solid var(--mp-border);
    box-shadow: var(--mp-shadow-sm);
    transition: all 0.2s ease;
    position: relative;
}
.tm-card:hover { transform: translateY(-2px); box-shadow: var(--mp-shadow); }
.tm-card.available { border-left-color: var(--mp-success); }
.tm-card.occupied { border-left-color: var(--mp-danger); }
.tm-card.reserved { border-left-color: var(--mp-warning); }
.tm-card.cleaning { border-left-color: var(--mp-muted); }
.tm-table-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--mp-text);
    margin-bottom: 4px;
}
.tm-table-meta {
    font-size: 12px;
    color: var(--mp-muted);
    margin-bottom: 10px;
}
.tm-status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tm-status-badge.available { background: rgba(5,150,105,.1); color: var(--mp-success); }
.tm-status-badge.occupied { background: rgba(220,38,38,.1); color: var(--mp-danger); }
.tm-status-badge.reserved { background: rgba(245,158,11,.1); color: var(--mp-warning); }
.tm-status-badge.cleaning { background: rgba(120,113,108,.1); color: var(--mp-muted); }
.tm-actions { margin-top: 10px; display: flex; gap: 8px; justify-content: flex-end; }
.tm-actions a { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-ink); cursor: pointer; transition: all .15s ease; text-decoration: none; }
.tm-actions a:hover { background: var(--mp-bg); text-decoration: none; }
.tm-actions .tm-edit:hover { border-color: var(--mp-primary); color: var(--mp-primary); background: rgba(0,87,255,.06); }
.tm-actions .tm-delete:hover { border-color: var(--mp-danger); color: var(--mp-danger); background: rgba(220,38,38,.06); }
.tm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
.tm-section { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; padding: 4px 10px; border-radius: 20px; background: rgba(0,87,255,.07); color: var(--mp-primary); font-size: 12px; font-weight: 600; }
.tm-status-badge + .tm-section { margin-left: 8px; }
.tm-section.none { background: rgba(120,113,108,.1); color: var(--mp-muted); font-weight: 500; }
.tm-sect-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; margin-bottom: 16px; }
.tm-sect-card { border: 1px solid var(--mp-border); border-radius: 10px; padding: 12px 14px; background: var(--mp-bg); }
.tm-sect-card .tm-sect-name { font-weight: 700; font-size: 14px; color: var(--mp-text); }
.tm-sect-card .tm-sect-count { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
.tm-sect-card .tm-sect-zones { font-size: 12px; color: var(--mp-ink); margin-top: 6px; word-break: break-word; }
.tm-assign { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; padding-top: 4px; border-top: 1px dashed var(--mp-border); }
.tm-assign .tm-assign-field { display: flex; flex-direction: column; gap: 4px; min-width: 170px; }
.tm-assign label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: var(--mp-muted); }
.tm-assign select { padding: 8px 10px; border: 1px solid var(--mp-border); border-radius: 8px; background: var(--mp-surface); font-size: 14px; min-height: 38px; }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage tables, zones, and seating status</div>
  </div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<!-- Status Counts -->
<div class="mp-kpi-grid">
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Available</div>
    <div class="mp-kpi-value"><?= $status_counts['available']; ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-times-circle"></i></div>
    <div class="mp-kpi-label">Occupied</div>
    <div class="mp-kpi-value"><?= $status_counts['occupied']; ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Reserved</div>
    <div class="mp-kpi-value"><?= $status_counts['reserved']; ?></div>
  </div>
  <div class="mp-kpi-card expense">
    <div class="mp-kpi-icon"><i class="fa fa-refresh"></i></div>
    <div class="mp-kpi-label">Cleaning</div>
    <div class="mp-kpi-value"><?= $status_counts['cleaning']; ?></div>
  </div>
</div>

<?php if(!empty($sections_on)): ?>
<!-- Who is standing behind what, and the one control that moves a whole area.
     The summary and the handover live in the same card so the answer to "is the
     garden covered tonight?" and the fix for it are one glance apart. -->
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head">
    <h3><i class="fa fa-user-circle-o"></i> Sections tonight</h3>
    <span class="mp-page-sub">A tab opened on a table takes that table's waiter, so every round on it is attributed.</span>
  </div>
  <div class="mp-card-body">
    <?php if(empty($sections)): ?>
      <p class="mp-page-sub" style="margin:0 0 12px;">No table has a section yet. Use the form on the right for one table, or hand over a whole area below.</p>
    <?php else: ?>
      <div class="tm-sect-grid">
        <?php foreach($sections as $s): ?>
        <div class="tm-sect-card">
          <div class="tm-sect-name"><?= htmlspecialchars($s->waiter_name); ?></div>
          <div class="tm-sect-count"><?= (int)$s->table_count; ?> table<?= (int)$s->table_count == 1 ? '' : 's'; ?></div>
          <?php if($s->zones !== ''): ?>
          <div class="tm-sect-zones"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($s->zones); ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?= form_open('operations/table_management', ['id'=>'section-form']); ?>
      <input type="hidden" name="assign_section" value="1">
      <div class="tm-assign">
        <div class="tm-assign-field">
          <label for="zone_name">Area</label>
          <select name="zone_name" id="zone_name" required>
            <option value="">Choose an area</option>
            <?php foreach($zones as $z): if(trim((string)$z->zone) === '') continue; ?>
            <option value="<?= htmlspecialchars($z->zone); ?>"><?= htmlspecialchars($z->zone); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="tm-assign-field">
          <label for="zone_waiter_id">Give it to</label>
          <select name="zone_waiter_id" id="zone_waiter_id">
            <option value="0">Back to the floor</option>
            <?php foreach($section_staff as $w): ?>
            <option value="<?= (int)$w['id']; ?>"><?= htmlspecialchars($w['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="tm-assign-field" style="min-width:0;">
          <label>&nbsp;</label>
          <button type="submit" class="mp-btn-primary"><i class="fa fa-exchange"></i> Hand over the area</button>
        </div>
        <?php if(empty($zones)): ?>
        <p class="mp-page-sub" style="flex:1 1 100%; margin:4px 0 0;">Name an area on a table first (e.g. Garden), then the whole area can be handed over in one go.</p>
        <?php endif; ?>
      </div>
    <?= form_close(); ?>
  </div>
</div>
<?php endif; ?>

<div class="mp-form-grid" style="grid-template-columns: minmax(0,2fr) minmax(0,1fr); gap:24px; align-items:start;">
  <!-- Table List -->
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-table"></i> Tables</h3>
      <span class="mp-page-sub">Total: <?= $status_counts['total']; ?></span>
    </div>
    <div class="mp-card-body">
      <?php if(empty($tables)): ?>
      <div class="mp-empty-state">
        <div class="mp-empty-icon"><i class="fa fa-table"></i></div>
        <h4>No tables yet</h4>
        <p>Add your first table using the form on the right.</p>
      </div>
      <?php else: ?>
        <div class="tm-grid">
          <?php foreach($tables as $t): ?>
          <div class="tm-card <?= $t->status; ?>">
            <div class="tm-table-name"><?= htmlspecialchars($t->table_name); ?></div>
            <div class="tm-table-meta">
              <?php if(!empty($t->table_code)): ?><span class="label label-default"><?= htmlspecialchars($t->table_code); ?></span><?php endif; ?>
              <?php if(!empty($t->zone)): ?><i class="fa fa-map-marker"></i> <?= htmlspecialchars($t->zone); ?><?php endif; ?>
              <i class="fa fa-users"></i> Capacity: <?= (int)$t->capacity; ?>
            </div>
            <span class="tm-status-badge <?= $t->status; ?>"><?= ucfirst($t->status); ?></span>
            <?php if(!empty($sections_on)): $w = (int)($t->waiter_user_id ?? 0); ?>
            <span class="tm-section<?= $w ? '' : ' none'; ?>">
              <i class="fa fa-user-circle-o"></i>
              <?= $w ? htmlspecialchars($waiter_names[$w] ?? 'no longer on the floor') : 'No section'; ?>
            </span>
            <?php endif; ?>
            <div class="tm-actions">
              <a href="?edit=<?= $t->id; ?>" class="tm-edit" title="Edit"><i class="fa fa-pencil"></i></a>
              <a href="?delete=<?= $t->id; ?>" class="tm-delete" title="Delete" onclick="return confirm('Delete this table?');"><i class="fa fa-trash"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add / Edit Form -->
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-plus-circle"></i> <?= !empty($edit_table) ? 'Edit Table' : 'Add Table'; ?></h3>
    </div>
    <div class="mp-card-body">
      <?= form_open('operations/table_management', ['id'=>'table-form']); ?>
        <input type="hidden" name="save_table" value="1">
        <?php if(!empty($edit_table)): ?>
        <input type="hidden" name="edit_id" value="<?= $edit_table->id; ?>">
        <?php endif; ?>

        <div class="mp-form-group" style="margin-bottom:20px;">
          <label>Table Name <span class="text-danger">*</span></label>
          <input type="text" name="table_name" class="mp-form-control" placeholder="e.g. Table 1, VIP Booth A" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->table_name) : ''; ?>" required>
        </div>

        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Table Code</label>
            <input type="text" name="table_code" class="mp-form-control" placeholder="T01" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->table_code) : ''; ?>">
          </div>
          <div class="mp-form-group">
            <label>Capacity</label>
            <input type="number" name="capacity" class="mp-form-control" placeholder="4" min="1" value="<?= !empty($edit_table) ? (int)$edit_table->capacity : '4'; ?>">
          </div>
        </div>

        <div class="mp-form-grid" style="margin-top:20px;">
          <div class="mp-form-group">
            <label>Zone / Area</label>
            <input type="text" name="zone" class="mp-form-control" placeholder="e.g. Indoor, Garden, VIP" list="zone-list" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->zone) : ''; ?>">
            <datalist id="zone-list">
              <?php foreach($zones as $z): ?>
              <option value="<?= htmlspecialchars($z->zone); ?>">
              <?php endforeach; ?>
            </datalist>
          </div>
          <div class="mp-form-group">
            <label>Status</label>
            <select name="status" class="mp-form-control">
              <option value="available" <?= (!empty($edit_table) && $edit_table->status=='available') ? 'selected' : ''; ?>>Available</option>
              <option value="occupied" <?= (!empty($edit_table) && $edit_table->status=='occupied') ? 'selected' : ''; ?>>Occupied</option>
              <option value="reserved" <?= (!empty($edit_table) && $edit_table->status=='reserved') ? 'selected' : ''; ?>>Reserved</option>
              <option value="cleaning" <?= (!empty($edit_table) && $edit_table->status=='cleaning') ? 'selected' : ''; ?>>Cleaning</option>
            </select>
          </div>
        </div>

        <div class="mp-form-group" style="margin-top:20px;">
          <label>Sort Order</label>
          <input type="number" name="sort_order" class="mp-form-control" placeholder="0" value="<?= !empty($edit_table) ? (int)$edit_table->sort_order : ''; ?>">
        </div>

        <?php if(!empty($sections_on)): $held = (int)($edit_table->waiter_user_id ?? 0); ?>
        <div class="mp-form-group" style="margin-top:20px;">
          <label for="waiter_user_id">Waiter's section</label>
          <select name="waiter_user_id" id="waiter_user_id" class="mp-form-control">
            <option value="0">No section — anyone can sit it</option>
            <?php foreach($section_staff as $w): ?>
            <option value="<?= (int)$w['id']; ?>" <?= $held === (int)$w['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($w['name']); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="mp-page-sub">A tab opened here takes this waiter, and every round on the tab is recorded for them.</span>
        </div>
        <?php endif; ?>

        <div class="mp-form-actions" style="margin-top:20px; justify-content:flex-end;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> <?= !empty($edit_table) ? 'Update' : 'Save'; ?></button>
          <?php if(!empty($edit_table)): ?>
          <a href="<?= base_url('operations/table_management'); ?>" class="mp-btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      <?= form_close(); ?>
    </div>
  </div>
</div>
