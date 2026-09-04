<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage tills and cash-in-hand accounts</div>
  </div>
  <?php if($CI->permissions('tills_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('tills/new_form'); ?>">
    <i class="fa fa-plus"></i> Add Till
  </a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <?php $this->load->view('comman/code_flashdata.php'); ?>
    <div class="mp-dt-scroll">
      <table class="table mp-static-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Till Name</th>
            <th>Cashier</th>
            <th>Account</th>
            <th class="text-right">Balance</th>
            <th>Default</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($tills)): $i=0; foreach($tills as $t): $i++; ?>
          <tr>
            <td><?=$i;?></td>
            <td class="row-name"><?=htmlspecialchars($t->till_name ?? '');?></td>
            <td>
              <?php
                $cashier = trim(($t->first_name ?? '').' '.($t->last_name ?? ''));
                if(empty($cashier)){ $cashier = $t->username ?? ''; }
                echo htmlspecialchars($cashier) ?: '— Shared —';
              ?>
            </td>
            <td><?=htmlspecialchars($t->account_name ?? '—');?></td>
            <td class="text-right <?=floatval($t->balance ?? 0)<0?'text-danger':'text-success';?>"><?=store_number_format($t->balance ?? 0);?></td>
            <td><?=!empty($t->is_default) ? '<span class="label label-primary">Default</span>' : '—';?></td>
            <td><?=!empty($t->status) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';?></td>
            <td>
              <div class="mp-actions">
                <?php if($CI->permissions('tills_edit')): ?>
                <a href="<?=base_url('tills/edit_form/'.$t->id);?>" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                <?php endif; ?>
                <?php if($CI->permissions('tills_delete')): ?>
                <a href="<?=base_url('tills/delete/'.$t->id);?>" class="mp-delete" title="Deactivate" onclick="return confirm('Deactivate this till?');"><i class="fa fa-trash"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="8">
              <div class="mp-empty-state">
                <div class="mp-empty-icon"><i class="fa fa-inbox"></i></div>
                <h4>No tills created yet</h4>
                <p>Add a till to track cash-in-hand for each counter or cashier.</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  $('.mp-nav-item').removeClass('active');
  $('.tills-active-li').addClass('active');
  $('.tills-active-li').closest('.mp-nav-group').addClass('open');
</script>
