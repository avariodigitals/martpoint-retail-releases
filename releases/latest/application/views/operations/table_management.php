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
