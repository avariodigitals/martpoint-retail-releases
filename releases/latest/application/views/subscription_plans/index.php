<?php
/* Subscription plans list page — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head"><h1 class="mp-page-title">Subscription Plans</h1></div>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list-alt"></i> All Plans</h3>
    <div class="box-tools">
      <a href="<?=base_url('subscription_plans/add');?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Plan</a>
    </div>
  </div>
  <div class="box-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Code</th>
          <th>Branches</th>
          <th>Users</th>
          <th>Products</th>
          <th>Services</th>
          <th>Media (MB)</th>
          <th>Status</th>
          <th style="width:120px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($plans as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p->plan_name); ?></strong><br><small class="text-muted"><?= htmlspecialchars($p->description); ?></small></td>
          <td><code><?= htmlspecialchars($p->plan_code); ?></code></td>
          <td><?= (int) $p->branch_limit; ?></td>
          <td><?= (int) $p->user_limit; ?></td>
          <td><?= (int) $p->product_limit; ?></td>
          <td><?= (int) $p->service_limit; ?></td>
          <td><?= number_format((int) $p->media_storage_limit_mb); ?></td>
          <td>
            <?php if($p->is_active): ?>
            <span class="label label-success">Active</span>
            <?php else: ?>
            <span class="label label-default">Inactive</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?=base_url('subscription_plans/edit/'.$p->id);?>" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
            <button class="btn btn-xs btn-danger" onclick="deletePlan(<?= $p->id; ?>)"><i class="fa fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($plans)): ?>
        <tr><td colspan="9" class="text-center text-muted">No plans found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
function withCsrf(data){
  data = data || {};
  data[csrfName] = csrfHash;
  return data;
}
function deletePlan(id){
  if(!confirm('Are you sure you want to delete this plan?')) return;
  $.post('<?=base_url("subscription_plans/delete_plan");?>', withCsrf({id: id}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){ location.reload(); } else { toastr.error(r.message); }
    } catch(e) { toastr.error('Unexpected response'); }
  }).fail(function(){ toastr.error('Request failed'); });
}
</script>
<script>
$(".subscription-plans-active-li").addClass("active");
$(".subscription-plans-active-li").closest(".mp-nav-group").addClass("open");
</script>
