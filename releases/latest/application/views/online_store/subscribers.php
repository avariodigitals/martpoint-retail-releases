<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-sub-count{background:var(--mp-bg)!important;border:1px dashed var(--mp-border)!important;border-radius:14px!important;padding:18px 22px!important;margin-bottom:20px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;flex-wrap:wrap!important}
.os-sub-count h4{font-size:14px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0 0 4px!important}
.os-sub-count p{font-size:13px!important;color:var(--mp-muted)!important;margin:0!important;line-height:1.5!important}
.os-sub-num{font-size:28px!important;font-weight:800!important;color:var(--mp-primary)!important}
.os-sub-search{display:flex!important;gap:10px!important;margin-bottom:16px!important;flex-wrap:wrap!important}
.os-sub-search input{flex:1!important;min-width:220px!important;padding:10px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;font-size:14px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Emails collected from your storefront newsletter forms</div>
  </div>
  <a href="<?= base_url('online_store/export_subscribers'); ?>" class="mp-qa-btn green"><i class="fa fa-download"></i> Export CSV</a>
</div>

<div class="os-sub-count">
  <div>
    <h4><i class="fa fa-envelope-o" style="color:var(--mp-primary);"></i> Subscriber List</h4>
    <p>These customers opted in on your online store. Export the list to use with any email or WhatsApp marketing tool.</p>
  </div>
  <div class="os-sub-num"><?= (int)$total_subscribers; ?> total</div>
</div>

<form method="get" action="<?= base_url('online_store/subscribers'); ?>" class="os-sub-search">
  <input type="text" name="search" placeholder="Search by email…" value="<?= htmlspecialchars($search ?? ''); ?>">
  <button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Search</button>
  <?php if(!empty($search)): ?><a href="<?= base_url('online_store/subscribers'); ?>" class="mp-qa-btn"><i class="fa fa-times"></i> Clear</a><?php endif; ?>
</form>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Subscribers</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-subscribers-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Email</th><th>Source</th><th>Subscribed</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($subscribers as $s): ?>
          <tr data-id="<?= (int)$s->id; ?>">
            <td class="row-name"><?= htmlspecialchars($s->email); ?></td>
            <td><span class="label label-default"><?= htmlspecialchars($s->source ?: 'newsletter'); ?></span></td>
            <td><?= !empty($s->created_at) ? date('M j, Y g:ia', strtotime($s->created_at)) : '—'; ?></td>
            <td>
              <div class="mp-actions">
                <button class="mp-delete" title="Delete" onclick="deleteSubscriber(<?= (int)$s->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($subscribers)): ?><tr><td colspan="4" class="mp-empty-state">No subscribers yet. Enable the Newsletter section on your storefront homepage to start collecting emails.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function deleteSubscriber(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Remove this subscriber?')) return;
    doDeleteSubscriber(id);
    return;
  }
  swal({ title: "Remove Subscriber?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteSubscriber(id); });
}
function doDeleteSubscriber(id){
  $.post('<?= base_url('online_store/delete_subscriber/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
$(document).ready(function(){
  $('#os-subscribers-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[2,"desc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [3], "orderable": false }]
  });
});
</script>
<script>$(".online_store-subscribers-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
