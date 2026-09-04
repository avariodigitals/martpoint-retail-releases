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
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="mp-card" id="resultBox" style="display:none">
      <div class="mp-card-body" id="resultBody"></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="mp-card">
      <div class="mp-card-body">
        <h3>How to Upload to GitHub</h3>
        <ol>
          <li>Click <strong>Build Release Package</strong> above.</li>
          <li>Open your file manager (Finder) and navigate to:<br>
            <code>/Users/ralphmore/Sites/localhost/martpoint retail/release_upload/</code></li>
          <li>Open your <strong>other IDE window</strong> (the GitHub repo one).</li>
          <li>Drag the <code>releases/</code> folder from Finder into that IDE.</li>
          <li>Commit and push to GitHub — done!</li>
        </ol>
        <p class="text-muted">Alternatively, zip the <code>releases/</code> folder and use GitHub's web UI upload.</p>
      </div>
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
</script>
<script>$('.release-active-li').addClass("active");$('.release-active-li').closest(".mp-nav-group").addClass("open");</script>
