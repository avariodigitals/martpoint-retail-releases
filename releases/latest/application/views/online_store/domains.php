<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-domains-grid{display:grid!important;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)!important;gap:20px!important;align-items:start!important}
@media(max-width:1024px){.os-domains-grid{grid-template-columns:1fr!important}}
.os-url-box{background:var(--mp-bg)!important;padding:14px 18px!important;border-radius:12px!important;font-family:'SFMono-Regular',Consolas,Menlo,monospace!important;font-size:13px!important;word-break:break-all!important;border:1px solid var(--mp-border)!important;color:var(--mp-ink)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Connect custom domains or MartPoint subdomains to your storefront</div>
  </div>
</div>

<div class="os-domains-grid">
  <div class="mp-table-wrap">
    <div class="mp-card-head"><h3>Domains</h3></div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table id="os-domains-table" class="table mp-dt-table" width="100%">
          <thead><tr><th>Domain</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach($domains as $d): ?>
            <tr>
              <td class="row-name"><?= htmlspecialchars($d->domain_value); ?></td>
              <td><?= ucfirst(htmlspecialchars($d->domain_type)); ?></td>
              <td><span class="label label-<?= $d->connection_status === 'connected' ? 'success' : 'warning'; ?>"><?= ucfirst(htmlspecialchars($d->connection_status)); ?></span></td>
              <td>
                <div class="mp-actions">
                  <?php if($d->connection_status !== 'connected'): ?>
                  <button class="mp-edit" title="Connect" onclick="updateStatus(<?= (int)$d->id; ?>, 'connected')"><i class="fa fa-plug"></i></button>
                  <?php else: ?>
                  <button class="mp-edit" title="Disconnect" onclick="updateStatus(<?= (int)$d->id; ?>, 'disconnected')"><i class="fa fa-power-off"></i></button>
                  <?php endif; ?>
                  <button class="mp-delete" title="Delete" onclick="deleteDomain(<?= (int)$d->id; ?>)"><i class="fa fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($domains)): ?><tr><td colspan="4" class="mp-empty-state">No domains configured.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Add Domain</h3></div>
      <div class="mp-card-body">
        <form id="domain-form" onsubmit="return false;">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="mp-form-group"><label for="domain_type">Domain Type</label>
            <select class="mp-form-control" name="domain_type" id="domain_type">
              <option value="subdomain">MartPoint Subdomain</option>
              <option value="custom">Custom Domain</option>
            </select>
          </div>
          <div class="mp-form-group"><label for="domain_value">Domain Value</label><input type="text" class="mp-form-control" name="domain_value" id="domain_value" placeholder="yourstore.martpoint.store or shop.yourstore.com"></div>
          <div class="mp-form-group"><label for="dns_instructions">DNS Instructions</label><textarea class="mp-form-control" name="dns_instructions" id="dns_instructions" rows="3" placeholder="CNAME yourstore.martpoint.store -> martpoint.store"></textarea></div>
          <div class="mp-form-actions"><button type="button" class="mp-btn-primary" onclick="saveDomain()"><i class="fa fa-plus"></i> Add Domain</button></div>
        </form>
      </div>
    </div>

    <div class="mp-card-form" style="margin-top:20px!important;">
      <div class="mp-card-head"><h3>Free MartPoint URL</h3></div>
      <div class="mp-card-body">
        <div class="os-url-box"><?= base_url('store/' . ($settings->store_slug ?? '')); ?></div>
        <div class="mp-form-hint" style="margin-top:8px;">Your store is always accessible at this URL.</div>
      </div>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function saveDomain(){
  const fd = new FormData(document.getElementById('domain-form'));
  fetch('<?= base_url('online_store/save_domain'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } });
}
function updateStatus(id, status){
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('domain_id', id);
  fd.append('connection_status', status);
  fetch('<?= base_url('online_store/update_domain_status'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed'); } });
}
function deleteDomain(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this domain?')) return;
    doDeleteDomain(id);
    return;
  }
  swal({ title: "Delete Domain?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteDomain(id); });
}
function doDeleteDomain(id){
  var fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fetch('<?= base_url('online_store/delete_domain'); ?>/'+id, {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed'); } });
}
$(document).ready(function(){
  $('#os-domains-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[0,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [3], "orderable": false }]
  });
});
</script>
<script>$(".online_store-domains-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
