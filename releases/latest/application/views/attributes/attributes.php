<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$is_update = isset($q_id);
$btn_name = $is_update ? 'Update' : 'Save';
?>

<style>
.mp-card-form { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card-form .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card-form .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card-form .mp-card-body { padding: 20px; }
.mp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 24px; }
.mp-form-grid .mp-form-group.full { grid-column: 1 / -1; }
.mp-form-group { display: flex; flex-direction: column; gap: 6px; }
.mp-form-group > label { font-size: 13px; font-weight: 600; color: var(--mp-ink); }
.mp-form-group > label .text-danger { color: var(--mp-danger); }
.mp-form-hint { font-size: 12px; color: var(--mp-muted); margin: 0; }
.mp-form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 500; font-family: inherit; transition: all .15s ease; }
.mp-form-control:focus { outline: none; border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.1); }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); }
@media (max-width:767px){ .mp-form-grid { grid-template-columns: 1fr; } }
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; <?= $is_update ? 'Update attribute' : 'Add a new attribute'; ?></div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?= base_url('attributes'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Attributes
      </a>
    </div>
  </div>
</div>

<!-- Form Card -->
<div class="mp-section">
  <div class="mp-card-form box">
    <div class="mp-card-head">
      <h3>Attribute Details</h3>
    </div>
    <form class="mp-card-body" id="attribute-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="command" value="<?= $is_update ? 'update' : 'save'; ?>">
      <input type="hidden" name="q_id" value="<?= htmlspecialchars($q_id ?? ''); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="attribute_type">Attribute Type <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="attribute_type" name="attribute_type" value="<?= htmlspecialchars($attribute_type ?? ''); ?>" placeholder="e.g. size, colour, length" autofocus>
          <p class="mp-form-hint">Use lowercase English names: size, colour, length, material, storage, shade</p>
          <span id="attribute_type_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="attribute_value">Attribute Value <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="attribute_value" name="attribute_value" value="<?= htmlspecialchars($attribute_value ?? ''); ?>" placeholder="e.g. S, Red, Short">
          <span id="attribute_value_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="sort_order">Sort Order</label>
          <input type="number" class="mp-form-control" id="sort_order" name="sort_order" value="<?= htmlspecialchars($sort_order ?? '0'); ?>" placeholder="0">
          <p class="mp-form-hint">Lower numbers appear first in dropdowns.</p>
        </div>
      </div>
    </form>

    <div class="mp-form-actions">
      <button type="button" id="save" class="mp-qa-btn green" title="Save Data">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <?= $btn_name; ?>
      </button>
      <a href="<?= base_url('attributes'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Cancel</a>
    </div>
  </div>
</div>


<script type="text/javascript">
var base_url = "<?= $base_url; ?>";
$("#save").on("click", function(){
    var flag = true;
    function check_field(id){
        if(!$("#" + id).val()){
            $("#" + id + "_msg").fadeIn(200).show().html('Required Field');
            flag = false;
        } else {
            $("#" + id + "_msg").fadeOut(200).hide();
        }
    }
    check_field("attribute_type");
    check_field("attribute_value");
    if(!flag){ toastr["warning"]("You have missed something to fill up!"); return; }

    var $btn = $(this);
    $btn.attr('disabled', true);
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    var data = new FormData($("#attribute-form")[0]);
    $.ajax({
        type: 'POST', url: base_url + 'attributes/save', data: data,
        cache: false, contentType: false, processData: false, dataType: 'html',
        success: function(res){
            if(res.indexOf("success") !== -1){ window.location.href = base_url + 'attributes'; }
            else { toastr["error"](res); $btn.attr('disabled', false); }
            $(".overlay").remove();
        },
        error: function(){
            toastr["error"]("Failed to save attribute. Try again.");
            $btn.attr('disabled', false);
            $(".overlay").remove();
        }
    });
});
</script>
<script>$('.attributes-active-li').addClass('active');</script>
