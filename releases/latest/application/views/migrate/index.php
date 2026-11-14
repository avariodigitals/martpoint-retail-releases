<?php
/* Data Migration Wizard — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.migrate-step { display: none; }
.migrate-step.active { display: block; }
</style>

<div class="mp-page-head"><h1 class="mp-page-title"><?= $page_title; ?></h1></div>

<?php if($this->session->flashdata('failed')): ?>
<div class="alert alert-danger"><?= $this->session->flashdata('failed'); ?></div>
<?php endif; ?>
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
<?php endif; ?>
<?php if($this->session->flashdata('warning')): ?>
<div class="alert alert-warning"><?= $this->session->flashdata('warning'); ?></div>
<?php endif; ?>

<!-- STEP 1: UPLOAD -->
<div id="step-upload" class="migrate-step <?= $step === 'upload' ? 'active' : ''; ?>">
  <div class="mp-card">
    <div class="mp-card-body">
      <h3>1. Upload the old backup</h3>
      <p>This will create a staging copy of the old database so the import can be previewed before anything is written to the live database.</p>
      <form action="<?= base_url('migrate/upload'); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label>Old SQL dump (.sql) <span class="text-danger">*</span></label>
          <input type="file" name="sql_file" class="form-control" accept=".sql" required>
        </div>
        <div class="form-group">
          <label>Uploads ZIP (optional)</label>
          <input type="file" name="uploads_zip" class="form-control" accept=".zip">
          <small class="text-muted">Upload the zipped old <code>uploads/</code> folder if you have it.</small>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload & Analyze</button>
      </form>
    </div>
  </div>
</div>

<!-- STEP 2: ANALYZE -->
<div id="step-analyze" class="migrate-step <?= $step === 'analyze' ? 'active' : ''; ?>">
  <div class="mp-card">
    <div class="mp-card-body">
      <h3>2. Review what will be imported</h3>
      <?php if(!empty($analysis)): ?>
      <form action="<?= base_url('migrate/import'); ?>" method="post">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Target Store <span class="text-danger">*</span></label>
              <select name="target_store_id" class="form-control" required>
                <?php foreach($stores as $s): ?>
                <option value="<?= $s->id; ?>" <?= get_current_store_id() == $s->id ? 'selected' : ''; ?>><?= htmlspecialchars($s->store_name); ?> (#<?= $s->id; ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>User Passwords</label>
              <select name="password_option" class="form-control" id="password_option">
                <option value="preserve">Keep old passwords (MD5 will be upgraded on login)</option>
                <option value="reset">Reset all imported users to a default password</option>
              </select>
            </div>
          </div>
          <div class="col-md-4" id="default_password_wrap" style="display:none;">
            <div class="form-group">
              <label>Default Password</label>
              <input type="text" name="default_password" class="form-control" placeholder="e.g. ChangeMe123!">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="skip_admin" value="1" checked>
                <strong>Skip master admin (ID 1)</strong> — avoid being locked out of the new install
              </label>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <div class="mp-dt-scroll">
          <table class="mp-static-table">
            <thead class="bg-blue">
              <tr>
                <th>Old Table</th>
                <th>New Table</th>
                <th class="text-right">Old Rows</th>
                <th class="text-right">Existing Rows</th>
                <th class="text-center">Matching Columns</th>
                <th class="text-center">New Columns Not in Old</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($analysis as $a): ?>
              <tr>
                <td><?= htmlspecialchars($a['old_table']); ?></td>
                <td><?= htmlspecialchars($a['new_table']); ?></td>
                <td class="text-right"><?= $a['old_exists'] ? number_format($a['old_count']) : '<span class="text-muted">not found</span>'; ?></td>
                <td class="text-right"><?= number_format($a['new_count']); ?></td>
                <td class="text-center"><?= $a['common']; ?></td>
                <td class="text-center"><?= $a['missing']; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>

        <div class="callout callout-warning">
          <h4>Before you proceed</h4>
          <p>Make sure the live <code>uploads/</code> folder is backed up and that you are happy with the row counts. The import will <strong>replace</strong> matching IDs and keep any new defaults that were not in the old database.</p>
        </div>

        <button type="submit" class="btn btn-primary" onclick="return confirm('Run the import? This changes live data. A staging copy is still available for rollback.');"><i class="fa fa-database"></i> Run Data Import</button>
      </form>
      <?php else: ?>
      <p class="text-muted">No analysis available. Start again with an SQL dump.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- STEP 3: IMPORT RESULTS -->
<div id="step-import" class="migrate-step <?= $step === 'import' ? 'active' : ''; ?>">
  <div class="mp-card">
    <div class="mp-card-body">
      <h3>3. Import results</h3>
      <?php if(!empty($import_log)): ?>
      <div class="table-responsive">
        <div class="mp-dt-scroll">
        <table class="mp-static-table">
          <thead class="bg-green">
            <tr>
              <th>Old Table</th>
              <th>New Table</th>
              <th>Status</th>
              <th class="text-right">Affected Rows</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($import_log as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['old_table']); ?></td>
              <td><?= htmlspecialchars($row['new_table']); ?></td>
              <td><?= mp_migrate_status_badge($row['status']); ?></td>
              <td class="text-right"><?= number_format($row['rows']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
      <?php else: ?>
      <p class="text-muted">No import has been run yet.</p>
      <?php endif; ?>

      <hr>
      <h4>Restore uploads</h4>
      <?php if($uploads_dir || $sql_file): ?>
      <form action="<?= base_url('migrate/restore_uploads'); ?>" method="post">
        <div class="form-group">
          <label>Uploads source path</label>
          <input type="text" name="uploads_source" class="form-control" value="<?= $uploads_dir ? htmlspecialchars($uploads_dir) : ''; ?>" placeholder="/full/path/to/old/uploads">
          <small class="text-muted">If you already uploaded a ZIP, the extracted path is pre-filled. Otherwise paste the full path to the old uploads folder.</small>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-copy"></i> Copy Uploads</button>
      </form>
      <?php else: ?>
      <p class="text-muted">No uploads source available. Manually copy the old uploads folder into <code>uploads/</code> if needed.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- STEP 4: FINISH -->
<div id="step-finish" class="migrate-step <?= $step === 'finish' ? 'active' : ''; ?>">
  <div class="mp-card">
    <div class="mp-card-body">
      <h3>4. Finish</h3>
      <p>Migration is complete. Please do the following before you start using the app:</p>
      <ul>
        <li>Review <strong>Store Settings</strong>, tax, units, and payment modes.</li>
        <li>Check one sample sale and one item to confirm the data looks correct.</li>
        <li>Update the store logo and receipt header if needed.</li>
        <li>Make sure all users can log in. If you kept old MD5 passwords, they will be upgraded automatically on first login.</li>
      </ul>
      <a href="<?= base_url('migrate/cleanup'); ?>" class="btn btn-success" onclick="return confirm('Drop the staging database? This cannot be undone, but your live data is safe.');"><i class="fa fa-check"></i> Cleanup Staging Database</a>
      <a href="<?= base_url('dashboard'); ?>" class="btn btn-default">Go to Dashboard</a>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#password_option').on('change', function(){
    $('#default_password_wrap').toggle(this.value === 'reset');
  });
});
</script>
