<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Bonus Rules'); ?></h2>
    <div class="mp-page-sub">Manage Bonus Rules</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Bonus Rules</h3>
    <button type="button" class="mp-btn-primary" onclick="open_rule_modal()"><i class="fa fa-plus"></i> Add Rule</button>
  </div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead>
          <tr>
            <th>Name</th><th>Type</th><th>Multiplier</th><th>Bonus Points</th><th>Start</th><th>End</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rules as $rule) : ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($rule->rule_name); ?></td>
            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $rule->rule_type))); ?></td>
            <td class="amt"><?= htmlspecialchars($rule->multiplier); ?>x</td>
            <td class="amt"><?= htmlspecialchars($rule->bonus_points); ?></td>
            <td><?= show_date($rule->start_date) ?: '-'; ?></td>
            <td><?= show_date($rule->end_date) ?: '-'; ?></td>
            <td><?= $rule->status ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="edit_rule(<?= (int)$rule->id; ?>,'<?= htmlspecialchars($rule->rule_name, ENT_QUOTES, 'UTF-8'); ?>','<?= htmlspecialchars($rule->rule_type, ENT_QUOTES, 'UTF-8'); ?>',<?= (float)$rule->multiplier; ?>,<?= (float)$rule->bonus_points; ?>,'<?= htmlspecialchars($rule->start_date, ENT_QUOTES, 'UTF-8'); ?>','<?= htmlspecialchars($rule->end_date, ENT_QUOTES, 'UTF-8'); ?>','<?= htmlspecialchars($rule->days_of_week, ENT_QUOTES, 'UTF-8'); ?>')"><i class="fa fa-edit"></i></button>
                <button class="mp-delete" title="Delete" onclick="delete_rule(<?= (int)$rule->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($rules)) : ?>
          <tr><td colspan="8" class="mp-empty-state">
            <div class="mp-empty-icon"><i class="fa fa-bolt"></i></div>
            <h4>No bonus rules found</h4>
            <p>Add a bonus rule to get started.</p>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="rule-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Bonus Rule</h4>
      </div>
      <div class="modal-body">
        <form id="rule-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
          <input type="hidden" name="rule_id" id="rule_id">
          <div class="mp-form-grid">
            <div class="mp-form-group full">
              <label for="rule_name">Rule Name</label>
              <input type="text" class="mp-form-control" name="rule_name" id="rule_name" required>
            </div>
            <div class="mp-form-group full">
              <label for="rule_type">Rule Type</label>
              <select class="mp-form-control" name="rule_type" id="rule_type">
                <option value="double_points_day">Double Points Day</option>
                <option value="weekend_bonus">Weekend Bonus</option>
                <option value="holiday_bonus">Holiday Bonus</option>
                <option value="campaign_bonus">Campaign Bonus</option>
                <option value="birthday_bonus">Birthday Bonus</option>
                <option value="referral_bonus">Referral Bonus</option>
                <option value="vip_bonus">VIP Bonus</option>
              </select>
            </div>
            <div class="mp-form-group">
              <label for="multiplier">Multiplier</label>
              <input type="number" step="0.01" class="mp-form-control" name="multiplier" id="multiplier" value="2">
            </div>
            <div class="mp-form-group">
              <label for="bonus_points">Bonus Points (fixed)</label>
              <input type="number" class="mp-form-control" name="bonus_points" id="bonus_points" value="0">
            </div>
            <div class="mp-form-group">
              <label for="start_date">Start Date</label>
              <input type="date" class="mp-form-control" name="start_date" id="start_date">
            </div>
            <div class="mp-form-group">
              <label for="end_date">End Date</label>
              <input type="date" class="mp-form-control" name="end_date" id="end_date">
            </div>
            <div class="mp-form-group full">
              <label for="days_of_week">Days of Week (0=Sun, 6=Sat, comma separated)</label>
              <input type="text" class="mp-form-control" name="days_of_week" id="days_of_week" placeholder="e.g. 0,6">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="save_rule()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
function open_rule_modal(){
    $('#rule-form')[0].reset(); $('#rule_id').val(''); $('#rule-modal').modal('show');
}
function edit_rule(id,name,type,multiplier,bonus_points,start_date,end_date,days_of_week){
    $('#rule_id').val(id); $('#rule_name').val(name); $('#rule_type').val(type); $('#multiplier').val(multiplier);
    $('#bonus_points').val(bonus_points); $('#start_date').val(start_date); $('#end_date').val(end_date); $('#days_of_week').val(days_of_week);
    $('#rule-modal').modal('show');
}
function save_rule(){
    var form = $('#rule-form').serialize();
    $.post(base_url + 'loyalty/save_bonus_rule', form, function(res){
        if(res=='success'){ success_show('Rule saved'); $('#rule-modal').modal('hide'); location.reload(); }
        else{ error_show('Failed'); }
    });
}
function delete_rule(id){
    swal({
        title: "Are you sure?",
        text: "Delete this rule?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'loyalty/delete_bonus_rule/'+id, function(res){
                if(res=='success'){ success_show('Deleted'); location.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
$(".loyalty-bonus-rules-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
