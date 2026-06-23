<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-7">
          <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Domains</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Domain</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($domains as $d): ?>
                  <tr>
                    <td><?= htmlspecialchars($d->domain_value); ?></td>
                    <td><?= ucfirst($d->domain_type); ?></td>
                    <td><span class="label label-<?= $d->connection_status === 'connected' ? 'success' : 'warning'; ?>"><?= ucfirst($d->connection_status); ?></span></td>
                    <td>
                      <?php if($d->connection_status !== 'connected'): ?>
                      <button class="btn btn-xs btn-success" onclick="updateStatus(<?= $d->id; ?>, 'connected')">Connect</button>
                      <?php else: ?>
                      <button class="btn btn-xs btn-warning" onclick="updateStatus(<?= $d->id; ?>, 'disconnected')">Disconnect</button>
                      <?php endif; ?>
                      <button class="btn btn-xs btn-danger" onclick="deleteDomain(<?= $d->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($domains)): ?>
                  <tr><td colspan="4" class="text-center text-muted">No domains configured.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Add Domain</h3></div>
            <div class="box-body">
              <form id="domain-form" onsubmit="return false;">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="form-group">
                  <label>Domain Type</label>
                  <select class="form-control input-sm" name="domain_type" id="domain_type">
                    <option value="subdomain">MartPoint Subdomain</option>
                    <option value="custom">Custom Domain</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Domain Value</label>
                  <input type="text" class="form-control input-sm" name="domain_value" placeholder="yourstore.martpoint.store or shop.yourstore.com">
                </div>
                <div class="form-group">
                  <label>DNS Instructions</label>
                  <textarea class="form-control input-sm" name="dns_instructions" rows="3" placeholder="CNAME yourstore.martpoint.store -> martpoint.store"></textarea>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveDomain()">Add Domain</button>
              </form>
            </div>
          </div>
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Free MartPoint URL</h3></div>
            <div class="box-body">
              <div style="background:#f4f4f4; padding:12px; border-radius:4px; font-family:monospace; word-break:break-all;">
                <?= base_url('store/' . ($settings->store_slug ?? '')); ?>
              </div>
              <p class="text-muted" style="margin-top:8px; font-size:13px;">Your store is always accessible at this URL.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function saveDomain(){
  const fd = new FormData(document.getElementById('domain-form'));
  fetch('<?= base_url('online_store/save_domain'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to save'); } });
}
function updateStatus(id, status){
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('domain_id', id);
  fd.append('connection_status', status);
  fetch('<?= base_url('online_store/update_domain_status'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed'); } });
}
function deleteDomain(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this domain?')) return;
    doDeleteDomain(id);
    return;
  }
  swal({
    title: "Delete Domain?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteDomain(id);
  });
}
function doDeleteDomain(id){
  fetch('<?= base_url('online_store/delete_domain'); ?>/'+id, {method:'POST'})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed'); } });
}
</script>
</body>
</html>
