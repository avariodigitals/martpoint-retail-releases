<?php
/* Release Builder — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php include "comman/code_flashdata.php"; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title">Build Release Package</h1>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mp-card">
      <div class="mp-card-body">
        <h3>Build Package</h3>
        <p class="text-muted">This reads the manifest you already generated and copies all referenced files into a <code>release_upload/</code> folder with the correct GitHub structure.</p>
        <button id="btnBuild" class="btn btn-primary"><i class="fa fa-folder-open"></i> Build Release Package</button>
        <button id="btnBuildFull" class="btn btn-default" style="margin-left:6px"><i class="fa fa-archive"></i> Build Full Package (deployments)</button>
        <p class="text-muted" style="font-size:11px;margin-top:10px">Full Package builds <code>martpoint-full.zip</code> — the complete install the <code>deploy.php</code> launcher streams via <code>fleet/package</code>. Rebuild it each release.</p>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="mp-card">
      <div class="mp-card-body">
        <h3>Release flow</h3>
        <ol>
          <li><strong>Manifest Generator</strong> — version + Fleet URL/Key → generate.</li>
          <li><strong>Build Release Package</strong> — copies files to <code>release_upload/releases/latest/</code>.</li>
          <li><strong>Push Release to GitHub</strong> — publishes to the update channel. Installs see the update on their next check-in.</li>
          <li><strong>Build Full Package</strong> — refreshes <code>martpoint-full.zip</code> for new deployments.</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mp-card">
      <div class="mp-card-body">
        <h3>Publish to Update Channel</h3>
        <div class="form-group">
          <label>GitHub repo</label>
          <input type="text" id="ghRepo" class="form-control" value="<?= htmlspecialchars($gh_repo ?: 'avariodigitals/martpoint-retail-releases') ?>" placeholder="owner/repo">
        </div>
        <div class="row">
          <div class="col-xs-4"><div class="form-group"><label>Branch</label><input type="text" id="ghBranch" class="form-control" value="<?= htmlspecialchars($gh_branch ?: 'main') ?>"></div></div>
          <div class="col-xs-8"><div class="form-group"><label>Access token <?= $gh_token_set ? '<span class="fleet-badge ok">saved</span>' : '' ?></label><input type="password" id="ghToken" class="form-control" autocomplete="new-password" placeholder="<?= $gh_token_set ? '(unchanged — saved)' : 'ghp_… / github_pat_…' ?>"></div></div>
        </div>
        <button id="btnSaveGh" class="btn btn-default btn-sm"><i class="fa fa-save"></i> Save Settings</button>
        <button id="btnPublish" class="btn btn-success" style="margin-left:6px"><i class="fa fa-cloud-upload"></i> Push Release to GitHub</button>
        <div id="ghProgress" class="text-muted" style="font-size:12px;margin-top:10px"></div>
        <p class="text-muted" style="font-size:11px;margin-top:8px">Pushes <code>release_upload/releases/latest/</code> to the repo via the GitHub API — only changed files upload, committed atomically. Token needs <strong>contents: read &amp; write</strong> on the repo.</p>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="mp-card" id="resultBox" style="display:none">
      <div class="mp-card-body" id="resultBody"></div>
    </div>
  </div>
</div>

<script>
    $('#btnBuild').on('click', function() {
      var $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Building...');
      $.post('<?= base_url('release/build'); ?>', function(res) {
        $btn.prop('disabled', false).html('<i class="fa fa-folder-open"></i> Build Release Package');
        if (res.status === 'ok') {
          $('#resultBox').show();
          $('#resultBody').html(
            '<div class="alert alert-success">' +
            '<strong>Package built!</strong><br>' +
            'Version: <b>' + res.version + '</b><br>' +
            'Files: <b>' + res.files_count + '</b><br>' +
            'Migrations: <b>' + res.migrations_count + '</b><br>' +
            'Protected skipped: <b>' + res.skipped_count + '</b><br>' +
            'Output: <code>' + res.output_path + '</code>' +
            '</div>'
          );
          toastr.success(res.message);
        } else {
          toastr.error(res.message || 'Build failed');
        }
      }, 'json').fail(function() {
        $btn.prop('disabled', false).html('<i class="fa fa-folder-open"></i> Build Release Package');
        toastr.error('Server error.');
      });
    });

    $('#btnSaveGh').on('click', function() {
      $.post('<?= base_url('release/save_github'); ?>', {
        github_repo: $('#ghRepo').val(),
        github_branch: $('#ghBranch').val(),
        github_token: $('#ghToken').val()
      }, function(res) {
        if (res.status === 'ok') { toastr.success(res.message); $('#ghToken').val('').attr('placeholder', '(unchanged — saved)'); }
        else toastr.error(res.message || 'Failed');
      }, 'json').fail(function() { toastr.error('Server error'); });
    });

    $('#btnPublish').on('click', function() {
      var $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Publishing...');
      var publishStep = function() {
        $.post('<?= base_url('release/publish'); ?>', function(res) {
          if (res.status === 'partial') {
            $('#ghProgress').text('Uploading files… ' + res.done + '/' + res.total + ' (' + res.remaining + ' left)');
            publishStep();
            return;
          }
          $btn.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Push Release to GitHub');
          if (res.status === 'ok') {
            $('#ghProgress').html('<span class="text-success">' + res.message + ' — commit <code>' + (res.commit || '').substr(0, 8) + '</code></span>');
            $('#resultBox').show();
            $('#resultBody').html('<div class="alert alert-success"><strong>Published to the update channel.</strong><br>' + res.message + '<br>Commit: <code>' + res.commit + '</code><br>Files pushed: <b>' + res.files + '</b><br><small>Installs pick this up on their next update check.</small></div>');
            toastr.success(res.message);
          } else {
            $('#ghProgress').text('');
            toastr.error(res.message || 'Publish failed');
          }
        }, 'json').fail(function() {
          $btn.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Push Release to GitHub');
          $('#ghProgress').text('');
          toastr.error('Server error — large pushes resume; click Publish again to continue.');
        });
      };
      publishStep();
    });

    $('#btnBuildFull').on('click', function() {
      var $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Building full package...');
      $.post('<?= base_url('release/build_full'); ?>', function(res) {
        $btn.prop('disabled', false).html('<i class="fa fa-archive"></i> Build Full Package (deployments)');
        if (res.status === 'ok') {
          $('#resultBox').show();
          $('#resultBody').html(
            '<div class="alert alert-success">' +
            '<strong>Full package built!</strong><br>' +
            'Files: <b>' + res.files_count + '</b><br>' +
            'Size: <b>' + res.size_mb + ' MB</b><br>' +
            'Served at: <code>release_upload/releases/latest/martpoint-full.zip</code>' +
            '</div>'
          );
          toastr.success(res.message);
        } else {
          toastr.error(res.message || 'Build failed');
        }
      }, 'json').fail(function() {
        $btn.prop('disabled', false).html('<i class="fa fa-archive"></i> Build Full Package (deployments)');
        toastr.error('Server error.');
      });
    });
</script>
<script>$('.release-active-li').addClass("active");$('.release-active-li').closest(".mp-nav-group").addClass("open");</script>
