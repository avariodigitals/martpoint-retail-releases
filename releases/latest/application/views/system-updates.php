<!DOCTYPE html>
<html>
<head>
  <?php include "comman/code_css.php"; ?>
  <style>
    .update-card { border: 1px solid #d2d6de; border-radius: 6px; padding: 24px; background: #fff; }
    .step-list { list-style: none; padding: 0; margin: 20px 0; }
    .step-list li { display: flex; align-items: center; padding: 10px 12px; border-radius: 4px; margin-bottom: 6px; background: #f5f5f5; }
    .step-list li.active { background: #e3f2fd; font-weight: bold; }
    .step-list li.success { background: #e8f5e9; }
    .step-list li.error { background: #ffebee; }
    .step-icon { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 12px; }
    .step-icon.pending { background: #ddd; color: #666; }
    .step-icon.running { background: #2196f3; color: #fff; animation: pulse 1.2s infinite; }
    .step-icon.done { background: #4caf50; color: #fff; }
    .step-icon.fail { background: #f44336; color: #fff; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: .5; } 100% { opacity: 1; } }
    .progress-wrap { margin: 20px 0; }
    .log-box { background: #263238; color: #aed581; padding: 14px; border-radius: 4px; font-family: monospace; font-size: 12px; max-height: 260px; overflow-y: auto; white-space: pre-wrap; }
    .version-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    .badge-current { background: #e3f2fd; color: #1565c0; }
    .badge-remote { background: #e8f5e9; color: #2e7d32; }
    .preview-table { font-size: 13px; }
    .preview-table th { background: #f5f5f5; }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <?php include "sidebar.php"; ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>System Update <small>One-Click Auto Updater</small></h1>
        <ol class="breadcrumb">
          <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">System Update</li>
        </ol>
      </section>

      <section class="content">
        <?php include "comman/code_flashdata.php"; ?>
        <div class="row">
          <div class="col-md-8">
            <div class="update-card">
              <div class="clearfix">
                <h3 class="pull-left" style="margin-top:0">Update Status</h3>
                <div class="pull-right">
                  <span class="version-badge badge-current">Current: <span id="currentVersion">checking...</span></span>
                  <span class="version-badge badge-remote" style="margin-left:6px">Remote: <span id="remoteVersion">checking...</span></span>
                </div>
              </div>

              <div id="statusMessage" class="alert alert-info" style="margin-top:12px">Checking for updates...</div>

              <div class="progress-wrap" style="display:none" id="progressWrap">
                <div class="progress">
                  <div id="progressBar" class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width: 0%">0%</div>
                </div>
              </div>

              <ol class="step-list" id="stepList" style="display:none">
                <li data-step="1"><span class="step-icon pending">1</span> Backup Database</li>
                <li data-step="2"><span class="step-icon pending">2</span> Backup Files</li>
                <li data-step="3"><span class="step-icon pending">3</span> Download Changed Files</li>
                <li data-step="4"><span class="step-icon pending">4</span> Verify File Integrity</li>
                <li data-step="5"><span class="step-icon pending">5</span> Apply File Changes</li>
                <li data-step="6"><span class="step-icon pending">6</span> Run Database Migrations</li>
                <li data-step="7"><span class="step-icon pending">7</span> Finalize Update</li>
                <li data-step="8"><span class="step-icon pending">8</span> Cleanup</li>
              </ol>

              <div id="actionArea" style="margin-top:16px">
                <button id="btnCheck" class="btn btn-primary"><i class="fa fa-refresh"></i> Check for Updates</button>
                <button id="btnPreview" class="btn btn-info" style="display:none;margin-left:6px"><i class="fa fa-eye"></i> Preview Changes</button>
                <button id="btnUpdate" class="btn btn-success" style="display:none;margin-left:6px"><i class="fa fa-cloud-download"></i> Start Update</button>
                <button id="btnRestore" class="btn btn-danger" style="display:none;margin-left:6px"><i class="fa fa-undo"></i> Restore Previous</button>
              </div>

              <div id="previewArea" style="margin-top:20px; display:none">
                <h4>What will change</h4>
                <div id="previewContent"></div>
              </div>

              <div style="margin-top:20px">
                <h4>Live Log</h4>
                <div class="log-box" id="liveLog">Ready.</div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="update-card">
              <h4 style="margin-top:0"><i class="fa fa-cog"></i> Update Channel</h4>
              <p class="text-muted" style="font-size:12px">Where updates are pulled from (GitHub raw URL)</p>
              <div class="input-group">
                <input type="text" id="channelUrl" class="form-control" placeholder="https://raw.githubusercontent.com/..." />
                <span class="input-group-btn">
                  <button id="btnSaveChannel" class="btn btn-default" type="button">Save</button>
                </span>
              </div>
              <hr style="margin: 16px 0">
              <h5><i class="fa fa-shield"></i> Protected Files</h5>
              <ul class="text-muted" style="font-size:12px; padding-left:18px">
                <li>application/config/database.php</li>
                <li>application/config/config.php</li>
                <li>uploads/ folder</li>
                <li>backups/ folder</li>
              </ul>
              <hr style="margin: 16px 0">
              <h5><i class="fa fa-history"></i> Recent Jobs</h5>
              <div id="recentJobs" class="text-muted" style="font-size:12px">Loading...</div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include "footer.php"; ?>
  </div>

  <?php include "comman/code_js_language.php"; ?>
  <?php include "comman/code_js_sound.php"; ?>
  <?php include "comman/code_js.php"; ?>

  <script>
  (function(){
    var isUpdating = false;
    var currentStep = 0;
    var totalSteps = 8;
    var pollInterval = null;
    var manifestData = null;
    var previewData = null;

    function log(msg) {
      var box = document.getElementById('liveLog');
      var time = new Date().toLocaleTimeString();
      box.textContent += '\n[' + time + '] ' + msg;
      box.scrollTop = box.scrollHeight;
    }

    function setStatus(type, msg) {
      var el = document.getElementById('statusMessage');
      el.className = 'alert alert-' + type;
      el.textContent = msg;
    }

    function setStepIcon(step, state) {
      var li = document.querySelector('.step-list li[data-step="' + step + '"]');
      if (!li) return;
      var icon = li.querySelector('.step-icon');
      li.classList.remove('active', 'success', 'error');
      icon.classList.remove('pending', 'running', 'done', 'fail');
      if (state === 'running') { li.classList.add('active'); icon.classList.add('running'); icon.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; }
      else if (state === 'done') { li.classList.add('success'); icon.classList.add('done'); icon.innerHTML = '<i class="fa fa-check"></i>'; }
      else if (state === 'fail') { li.classList.add('error'); icon.classList.add('fail'); icon.innerHTML = '<i class="fa fa-times"></i>'; }
      else { icon.classList.add('pending'); icon.textContent = step; }
    }

    function updateProgress(step) {
      var pct = Math.round((step / totalSteps) * 100);
      var bar = document.getElementById('progressBar');
      bar.style.width = pct + '%';
      bar.textContent = pct + '%';
    }

    function showActions(state) {
      document.getElementById('btnCheck').style.display = (state === 'idle' || state === 'checked') ? '' : 'none';
      document.getElementById('btnPreview').style.display = (state === 'checked') ? '' : 'none';
      document.getElementById('btnUpdate').style.display = (state === 'checked') ? '' : 'none';
      document.getElementById('btnRestore').style.display = (state === 'failed') ? '' : 'none';
    }

    function checkUpdates() {
      setStatus('info', 'Checking for updates...');
      log('Contacting update channel...');
      $.post('<?= base_url('system_updates/check'); ?>', function(res) {
        if (res.available) {
          setStatus('success', 'Update available: ' + res.installed_version + ' → ' + res.remote_version);
          document.getElementById('currentVersion').textContent = res.installed_version;
          document.getElementById('remoteVersion').textContent = res.remote_version;
          manifestData = res.manifest;
          log('Update found: ' + res.remote_version + ' (' + (res.release_date || 'unknown date') + ')');
          showActions('checked');
        } else if (res.error) {
          setStatus('danger', res.error);
          log('Error: ' + res.error);
          showActions('idle');
        } else {
          setStatus('success', 'You are on the latest version (' + res.installed_version + ').');
          document.getElementById('currentVersion').textContent = res.installed_version;
          document.getElementById('remoteVersion').textContent = res.remote_version || res.installed_version;
          log('No update available.');
          showActions('idle');
        }
      }, 'json').fail(function(xhr) {
        var msg = 'Server error. Check console / Network tab.';
        if (xhr.status === 500) msg = 'Server error (500). Is update_channel_url column missing? Run migration first.';
        setStatus('danger', msg);
        log(msg);
        showActions('idle');
      });
    }

    function previewChanges() {
      if (!manifestData) return;
      setStatus('info', 'Calculating changes...');
      log('Calculating diff...');
      $.post('<?= base_url('system_updates/preview'); ?>', function(res) {
        if (res.status !== 'ok') {
          setStatus('danger', res.message || 'Preview failed');
          return;
        }
        previewData = res.preview;
        var html = '<table class="table table-bordered preview-table">';
        html += '<tr><th>Files to Update</th><td>' + (res.preview.files_to_update.length || 0) + '</td></tr>';
        html += '<tr><th>Files to Add</th><td>' + (res.preview.files_to_add.length || 0) + '</td></tr>';
        html += '<tr><th>Migrations</th><td>' + (res.preview.migrations.length || 0) + '</td></tr>';
        html += '</table>';
        if (res.preview.files_to_update.length > 0) {
          html += '<p><strong>Modified files:</strong></p><ul style="font-size:12px;max-height:120px;overflow:auto">';
          res.preview.files_to_update.forEach(function(f){ html += '<li>' + f + '</li>'; });
          html += '</ul>';
        }
        if (res.preview.files_to_add.length > 0) {
          html += '<p><strong>New files:</strong></p><ul style="font-size:12px;max-height:120px;overflow:auto">';
          res.preview.files_to_add.forEach(function(f){ html += '<li>' + f + '</li>'; });
          html += '</ul>';
        }
        document.getElementById('previewContent').innerHTML = html;
        document.getElementById('previewArea').style.display = 'block';
        setStatus('info', 'Ready to update. Review the changes above, then click Start Update.');
        log('Diff calculated. ' + res.preview.total_operations + ' operations pending.');
      }, 'json').fail(function(xhr) {
        setStatus('danger', 'Preview failed: ' + (xhr.status === 500 ? 'Server error (500)' : 'Network error'));
        log('Preview failed.');
      });
    }

    function runStep(step) {
      if (step > totalSteps) {
        isUpdating = false;
        setStatus('success', 'Update completed successfully!');
        log('Update finished.');
        clearInterval(pollInterval);
        showActions('idle');
        return;
      }

      setStepIcon(step, 'running');
      log('Starting step ' + step + ' / ' + totalSteps + '...');

      $.post('<?= base_url('system_updates/run_step'); ?>', { step: step }, function(res) {
        if (res.status === 'error') {
          setStepIcon(step, 'fail');
          setStatus('danger', 'Step ' + step + ' failed: ' + res.message);
          log('FAILED at step ' + step + ': ' + res.message);
          isUpdating = false;
          clearInterval(pollInterval);
          showActions('failed');
          // Auto-restore prompt
          if (confirm('Update failed. Would you like to restore from backup automatically?')) {
            doRestore();
          }
          return;
        }

        setStepIcon(step, 'done');
        updateProgress(step);
        log('Step ' + step + ' completed.');

        if (res.done) {
          isUpdating = false;
          setStatus('success', 'Update completed successfully!');
          clearInterval(pollInterval);
          showActions('idle');
        } else {
          // Proceed to next step after short delay
          setTimeout(function() { runStep(step + 1); }, 800);
        }
      }, 'json').fail(function(xhr) {
        setStepIcon(step, 'fail');
        setStatus('danger', 'Network/server error at step ' + step);
        log('Network error at step ' + step);
        isUpdating = false;
        clearInterval(pollInterval);
        showActions('failed');
      });
    }

    function startUpdate() {
      if (isUpdating) return;
      if (!confirm('This will update your system. A backup will be created first. Continue?')) return;

      isUpdating = true;
      document.getElementById('stepList').style.display = 'block';
      document.getElementById('progressWrap').style.display = 'block';
      document.getElementById('previewArea').style.display = 'none';
      showActions('updating');

      // Reset all steps
      for (var i = 1; i <= totalSteps; i++) setStepIcon(i, 'pending');
      updateProgress(0);

      log('Update initiated.');
      runStep(1);

      // Fallback polling in case browser tab loses focus / AJAX drops
      pollInterval = setInterval(function() {
        $.post('<?= base_url('system_updates/progress'); ?>', function(res) {
          if (res.status === 'running' && res.current_step > 0) {
            for (var i = 1; i < res.current_step; i++) setStepIcon(i, 'done');
            setStepIcon(res.current_step, 'running');
            updateProgress(res.current_step);
          }
        }, 'json').fail(function(xhr) {
          setStatus('danger', 'Progress poll failed: ' + xhr.status);
          clearInterval(pollInterval);
        });
      }, 3000);
    }

    function doRestore() {
      log('Restoring from backup...');
      setStatus('warning', 'Restoring...');
      $.post('<?= base_url('system_updates/restore'); ?>', function(res) {
        if (res.status === 'success') {
          setStatus('success', 'Restored successfully. System is back to pre-update state.');
          log('Restore complete.');
        } else {
          setStatus('danger', 'Restore failed: ' + res.message);
          log('Restore failed: ' + res.message);
        }
        showActions('idle');
      }, 'json').fail(function(xhr) {
        setStatus('danger', 'Restore failed: ' + (xhr.status === 500 ? 'Server error (500)' : 'Network error'));
        log('Restore failed.');
      });
    }

    function saveChannel() {
      var url = document.getElementById('channelUrl').value.trim();
      if (!url) { alert('Enter a URL'); return; }
      $.post('<?= base_url('system_updates/save_channel'); ?>', { url: url }, function(res) {
        if (res.status === 'ok') {
          toastr.success('Channel URL saved');
          log('Update channel saved.');
        } else {
          toastr.error(res.message || 'Failed to save');
        }
      }, 'json').fail(function() { toastr.error('Failed to save channel.'); });
    }

    function loadChannel() {
      $.post('<?= base_url('system_updates/get_channel'); ?>', function(res) {
        if (res.status === 'ok' && res.url) {
          document.getElementById('channelUrl').value = res.url;
        }
      }, 'json').fail(function() { /* silent */ });
    }

    function loadRecentJobs() {
      // Just show last 3 from the same progress endpoint
      $.post('<?= base_url('system_updates/progress'); ?>', function(res) {
        var html = '';
        if (res.status && res.status !== 'idle') {
          html += '<div style="margin-bottom:6px">';
          html += '<strong>#' + (res.to_version || '—') + '</strong> ';
          html += '<span class="label label-' + (res.status === 'success' ? 'success' : res.status === 'failed' ? 'danger' : 'info') + '">' + res.status + '</span><br>';
          html += '<small>' + (res.step_label || '') + '</small>';
          html += '</div>';
        } else {
          html = '<em>No recent update jobs.</em>';
        }
        document.getElementById('recentJobs').innerHTML = html;
      }, 'json').fail(function() { /* silent */ });
    }

    // Event bindings
    document.getElementById('btnCheck').addEventListener('click', checkUpdates);
    document.getElementById('btnPreview').addEventListener('click', previewChanges);
    document.getElementById('btnUpdate').addEventListener('click', startUpdate);
    document.getElementById('btnRestore').addEventListener('click', doRestore);
    document.getElementById('btnSaveChannel').addEventListener('click', saveChannel);

    // Init
    loadChannel();
    loadRecentJobs();
    checkUpdates();
  })();
  </script>
  <script>$(".system-updates-active-li").addClass("active");</script>
</body>
</html>
