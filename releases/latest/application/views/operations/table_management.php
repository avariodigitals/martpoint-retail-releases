<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php'); ?>
<style>
.tm-card {
    background: #fff;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 15px;
    border-left: 4px solid #ccc;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    position: relative;
}
.tm-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.tm-card.available { border-left-color: #28a745; }
.tm-card.occupied { border-left-color: #dc3545; }
.tm-card.reserved { border-left-color: #ffc107; }
.tm-card.cleaning { border-left-color: #6c757d; }
.tm-table-name {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}
.tm-table-meta {
    font-size: 12px;
    color: #777;
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
.tm-status-badge.available { background: #d4edda; color: #155724; }
.tm-status-badge.occupied { background: #f8d7da; color: #721c24; }
.tm-status-badge.reserved { background: #fff3cd; color: #856404; }
.tm-status-badge.cleaning { background: #e2e3e5; color: #383d41; }
.tm-actions { margin-top: 10px; }
.tm-actions .btn { padding: 4px 10px; font-size: 12px; }
.tm-count-box {
    text-align: center;
    padding: 15px;
    border-radius: 6px;
    color: #fff;
    margin-bottom: 15px;
}
.tm-count-box.available { background: #28a745; }
.tm-count-box.occupied { background: #dc3545; }
.tm-count-box.reserved { background: #ffc107; color: #333; }
.tm-count-box.cleaning { background: #6c757d; }
.tm-count-number { font-size: 24px; font-weight: 700; display: block; }
.tm-count-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">

  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Table Management</li>
    </ol>
  </section>

  <section class="content">
    <?php $this->load->view('comman/code_flashdata.php'); ?>

    <!-- Status Counts -->
    <div class="row">
      <div class="col-md-3 col-sm-6">
        <div class="tm-count-box available">
          <span class="tm-count-number"><?= $status_counts['available']; ?></span>
          <span class="tm-count-label">Available</span>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="tm-count-box occupied">
          <span class="tm-count-number"><?= $status_counts['occupied']; ?></span>
          <span class="tm-count-label">Occupied</span>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="tm-count-box reserved">
          <span class="tm-count-number"><?= $status_counts['reserved']; ?></span>
          <span class="tm-count-label">Reserved</span>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="tm-count-box cleaning">
          <span class="tm-count-number"><?= $status_counts['cleaning']; ?></span>
          <span class="tm-count-label">Cleaning</span>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Table List -->
      <div class="col-md-8">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> Tables</h3>
            <div class="box-tools pull-right">
              <span class="text-muted">Total: <?= $status_counts['total']; ?></span>
            </div>
          </div>
          <div class="box-body">
            <?php if(empty($tables)): ?>
            <div class="text-center" style="padding:40px 0;">
              <i class="fa fa-table" style="font-size:48px;color:#ddd;"></i>
              <p style="margin-top:12px;color:#999;">No tables yet. Add your first table using the form on the right.</p>
            </div>
            <?php else: ?>
              <?php foreach($tables as $t): ?>
              <div class="tm-card <?= $t->status; ?>">
                <div class="tm-table-name"><?= htmlspecialchars($t->table_name); ?></div>
                <div class="tm-table-meta">
                  <?php if(!empty($t->table_code)): ?><span class="label label-default"><?= htmlspecialchars($t->table_code); ?></span><?php endif; ?>
                  <?php if(!empty($t->zone)): ?><i class="fa fa-map-marker"></i> <?= htmlspecialchars($t->zone); ?><?php endif; ?>
                  <i class="fa fa-users"></i> Capacity: <?= (int)$t->capacity; ?>
                </div>
                <span class="tm-status-badge <?= $t->status; ?>"><?= ucfirst($t->status); ?></span>
                <div class="tm-actions pull-right">
                  <a href="?edit=<?= $t->id; ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i></a>
                  <a href="?delete=<?= $t->id; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this table?');"><i class="fa fa-trash"></i></a>
                </div>
                <div style="clear:both;"></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Add / Edit Form -->
      <div class="col-md-4">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-plus-circle"></i> <?= !empty($edit_table) ? 'Edit Table' : 'Add Table'; ?></h3>
          </div>
          <div class="box-body">
            <?= form_open('operations/table_management', ['id'=>'table-form']); ?>
              <input type="hidden" name="save_table" value="1">
              <?php if(!empty($edit_table)): ?>
              <input type="hidden" name="edit_id" value="<?= $edit_table->id; ?>">
              <?php endif; ?>

              <div class="form-group">
                <label>Table Name <span class="text-danger">*</span></label>
                <input type="text" name="table_name" class="form-control" placeholder="e.g. Table 1, VIP Booth A" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->table_name) : ''; ?>" required>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Table Code</label>
                    <input type="text" name="table_code" class="form-control" placeholder="T01" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->table_code) : ''; ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" placeholder="4" min="1" value="<?= !empty($edit_table) ? (int)$edit_table->capacity : '4'; ?>">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Zone / Area</label>
                    <input type="text" name="zone" class="form-control" placeholder="e.g. Indoor, Garden, VIP" list="zone-list" value="<?= !empty($edit_table) ? htmlspecialchars($edit_table->zone) : ''; ?>">
                    <datalist id="zone-list">
                      <?php foreach($zones as $z): ?>
                      <option value="<?= htmlspecialchars($z->zone); ?>">
                      <?php endforeach; ?>
                    </datalist>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                      <option value="available" <?= (!empty($edit_table) && $edit_table->status=='available') ? 'selected' : ''; ?>>Available</option>
                      <option value="occupied" <?= (!empty($edit_table) && $edit_table->status=='occupied') ? 'selected' : ''; ?>>Occupied</option>
                      <option value="reserved" <?= (!empty($edit_table) && $edit_table->status=='reserved') ? 'selected' : ''; ?>>Reserved</option>
                      <option value="cleaning" <?= (!empty($edit_table) && $edit_table->status=='cleaning') ? 'selected' : ''; ?>>Cleaning</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" class="form-control" placeholder="0" value="<?= !empty($edit_table) ? (int)$edit_table->sort_order : ''; ?>">
              </div>

              <div class="form-group text-right">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?= !empty($edit_table) ? 'Update' : 'Save'; ?></button>
                <?php if(!empty($edit_table)): ?>
                <a href="<?= base_url('operations/table_management'); ?>" class="btn btn-default">Cancel</a>
                <?php endif; ?>
              </div>
            <?= form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
</div>

<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(".table-management-active-li").addClass("active");
</script>
</body>
</html>
