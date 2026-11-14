<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $is_edit = !empty($till); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update till settings' : 'Add a new till or cash-in-hand account'; ?></div>
  </div>
  <a href="<?= base_url('tills'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Tills</a>
</div>

<div class="mp-card-form" style="max-width:720px;">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Edit Till' : 'Add Till'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form role="form" class="form-horizontal" method="post" action="<?=base_url('tills/save');?>" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
      <input type="hidden" name="id" value="<?=!empty($till)?$till->id:'';?>">

      <div class="mp-form-grid" style="grid-template-columns:1fr;">
        <div class="mp-form-group">
          <label for="till_name">Till Name</label>
          <input type="text" class="mp-form-control" id="till_name" name="till_name" required value="<?=!empty($till)?htmlspecialchars($till->till_name):'';?>" placeholder="e.g. Till 1 - Front Counter">
        </div>

        <div class="mp-form-group">
          <label for="cashier_user_id">Assigned Cashier</label>
          <select class="mp-form-control" id="cashier_user_id" name="cashier_user_id">
            <option value="">— Shared / Any Cashier —</option>
            <?php foreach($users as $u): ?>
            <option value="<?=$u->id;?>" <?=!empty($till) && $till->cashier_user_id == $u->id ? 'selected' : '';?>>
              <?=htmlspecialchars(($u->first_name?$u->first_name.' '.$u->last_name:$u->username));?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="account_id">Cash Account <small class="text-muted">(optional)</small></label>
          <select class="form-control select2 mp-form-control" id="account_id" name="account_id" style="width:100%;">
            <option value="">— Auto-create account —</option>
            <?=$accounts;?>
          </select>
          <p class="mp-form-hint">If left blank, a new cash-in-hand account will be created automatically.</p>
        </div>

        <div class="mp-form-group">
          <label class="mp-form-hint" style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
            <input type="checkbox" name="is_default" value="1" <?=!empty($till) && $till->is_default ? 'checked' : '';?>> Default till for this cashier
          </label>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="submit" class="mp-btn-primary">Save Till</button>
        <a href="<?=base_url('tills');?>" class="mp-btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
  $('.mp-nav-item').removeClass('active');
  $('.tills-active-li').addClass('active');
  $('.tills-active-li').closest('.mp-nav-group').addClass('open');
</script>
