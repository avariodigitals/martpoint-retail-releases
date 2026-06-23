<!DOCTYPE html>
<html>
<head>
  <?php include "comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <?php include "sidebar.php"; ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>Release Manifest Generator <small>Build update packages without CLI</small></h1>
        <ol class="breadcrumb">
          <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Manifest Generator</li>
        </ol>
      </section>

      <section class="content">
        <?php include "comman/code_flashdata.php"; ?>
        <div class="row">
          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Generate Manifest</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label>New Version</label>
                  <input type="text" id="version" class="form-control" placeholder="e.g. 4.0.0" value="4.0.0">
                  <p class="help-block">Format: x.x.x (e.g. 4.0.0)</p>
                </div>
                <div class="form-group">
                  <label>Previous Version</label>
                  <input type="text" id="previous" class="form-control" placeholder="e.g. 3.0" value="3.0">
                  <p class="help-block">The version clients are currently running</p>
                </div>
                <button id="btnGenerate" class="btn btn-primary"><i class="fa fa-cogs"></i> Generate Manifest</button>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-success" id="resultBox" style="display:none">
              <div class="box-header with-border">
                <h3 class="box-title">Result</h3>
                <div class="box-tools pull-right">
                  <button class="btn btn-box-tool" onclick="$('#resultBox').hide()"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body">
                <div id="resultStats"></div>
                <hr>
                <h5>Manifest JSON — Copy this into your GitHub repo at <code>releases/latest/release-manifest.json</code></h5>
                <textarea id="manifestJson" class="form-control" rows="12" style="font-family:monospace;font-size:11px"></textarea>
                <hr>
                <h5>Files to Upload</h5>
                <div id="filesToUpload" style="max-height:200px;overflow:auto;font-family:monospace;font-size:11px;background:#f5f5f5;padding:10px;border-radius:4px"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">Instructions</h3>
              </div>
              <div class="box-body">
                <ol>
                  <li>Enter the new version and the previous version above.</li>
                  <li>Click <strong>Generate Manifest</strong> — this scans all files and creates <code>release_build/release-manifest.json</code> locally.</li>
                  <li>Copy the generated JSON into your GitHub <code>martpoint-retail-releases</code> repo at <code>releases/latest/release-manifest.json</code>.</li>
                  <li>Only upload the files that actually changed between versions into the same <code>releases/latest/</code> folder (the manifest paths must match the folder tree).</li>
                  <li>If you have SQL migrations, place them in <code>updates/migrations/</code> locally first, then they will be included.</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include "footer.php"; ?>
  </div>
  <?php include "comman/code_js.php"; ?>
  <script>
    $('#btnGenerate').on('click', function() {
      var $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
      $.post('<?= base_url('manifest/generate'); ?>', {
        version: $('#version').val(),
        previous_version: $('#previous').val()
      }, function(res) {
        $btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Generate Manifest');
        if (res.status === 'ok') {
          $('#resultBox').show();
          $('#resultStats').html(
            '<div class="alert alert-success">' +
            '<strong>Manifest generated!</strong><br>' +
            'Files scanned: <b>' + res.files_count + '</b><br>' +
            'Migrations: <b>' + res.migrations_count + '</b><br>' +
            'Saved to: <code>release_build/release-manifest.json</code>' +
            '</div>'
          );
          $('#manifestJson').val(JSON.stringify(res.manifest, null, 2));
          var filesHtml = '';
          res.manifest.files.forEach(function(f) {
            filesHtml += f.path + '\n';
          });
          $('#filesToUpload').text(filesHtml);
          toastr.success('Manifest generated successfully');
        } else {
          toastr.error(res.message || 'Generation failed');
        }
      }, 'json').fail(function() {
        $btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Generate Manifest');
        toastr.error('Server error. Check PHP error logs.');
      });
    });
  </script>
  <script>$('.manifest-active-li').addClass('active');</script>
</body>
</html>
