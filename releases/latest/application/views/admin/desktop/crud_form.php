<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
$crud = $crud ?? [];
$page_title = $crud['page_title'] ?? ($page_title ?? 'Form');
$page_sub = $crud['page_sub'] ?? '';
$fields = $crud['fields'] ?? [];
$form_id = $crud['form_id'] ?? 'crud-form';
$save_url = $crud['save_url'] ?? '';
$update_url = $crud['update_url'] ?? '';
$list_url = $crud['list_url'] ?? '';
$module = $crud['module'] ?? 'crud';
$use_ajax = $crud['use_ajax'] ?? true;
$q_id = $q_id ?? ($id ?? '');
$btn_label = $q_id ? 'Update' : 'Save';
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($page_sub); ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($page_title); ?></h3>
    <?php if ($list_url): ?>
    <a href="<?= $list_url; ?>" class="mp-card-link"><i class="fa fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
  </div>
  <div class="mp-card-body">
    <?= form_open($use_ajax ? '#' : ($base_url . $save_url), ['id' => $form_id, 'method' => 'POST', 'enctype' => 'multipart/form-data']); ?>
    <input type="hidden" id="base_url" value="<?= $base_url; ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

    <?php foreach ($fields as $field): if (($field['type'] ?? 'text') === 'hidden'): ?>
      <?php $name = $field['name']; $val = isset(${$name}) ? ${$name} : ($field['value'] ?? ''); ?>
      <input type="hidden" name="<?= $name; ?>" id="<?= $name; ?>" value="<?= htmlspecialchars($val); ?>">
    <?php endif; endforeach; ?>
    <?php if (!in_array('store_id', array_column($fields, 'name'))): ?>
      <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
    <?php endif; ?>
    <?php if (!in_array('q_id', array_column($fields, 'name'))): ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id ?? ''); ?>">
    <?php endif; ?>

    <div class="mp-form-grid">
      <?php foreach ($fields as $field):
        $type = $field['type'] ?? 'text';
        if ($type === 'hidden') continue;
        $name = $field['name'];
        $val = isset(${$name}) ? ${$name} : ($field['value'] ?? '');
        $required = !empty($field['required']);
        $req_html = $required ? ' <span class="text-danger">*</span>' : '';
        $placeholder = $field['placeholder'] ?? '';
        $help = $field['help'] ?? '';
        $readonly = !empty($field['readonly']) ? 'readonly' : '';
      ?>
      <div class="mp-form-group <?= !empty($field['full']) ? 'full' : ''; ?>">
        <?php if ($type !== 'checkbox'): ?>
        <label for="<?= $name; ?>"><?= htmlspecialchars($field['label']); ?><?= $req_html; ?></label>
        <?php endif; ?>

        <?php if (in_array($type, ['text', 'number', 'email'])): ?>
          <input type="<?= $type; ?>" class="mp-form-control" id="<?= $name; ?>" name="<?= $name; ?>" value="<?= htmlspecialchars($val); ?>" placeholder="<?= htmlspecialchars($placeholder); ?>" <?= $readonly; ?> <?= $required ? 'required' : ''; ?>>
          <span id="<?= $name; ?>_msg" style="display:none" class="text-danger"></span>
        <?php elseif ($type === 'textarea'): ?>
          <textarea class="mp-form-control" id="<?= $name; ?>" name="<?= $name; ?>" rows="3" placeholder="<?= htmlspecialchars($placeholder); ?>" <?= $readonly; ?> <?= $required ? 'required' : ''; ?>><?= htmlspecialchars($val); ?></textarea>
        <?php elseif ($type === 'select'): ?>
          <select class="form-control select2" id="<?= $name; ?>" name="<?= $name; ?>" style="width:100%;" <?= $required ? 'required' : ''; ?>>
            <?php foreach (($field['options'] ?? []) as $optval => $optlabel): ?>
              <option value="<?= htmlspecialchars($optval); ?>" <?= ((string)$val === (string)$optval) ? 'selected' : ''; ?>><?= htmlspecialchars($optlabel); ?></option>
            <?php endforeach; ?>
          </select>
          <span id="<?= $name; ?>_msg" style="display:none" class="text-danger"></span>
        <?php elseif ($type === 'checkbox'): ?>
          <input type="hidden" name="<?= $name; ?>" value="0">
          <label class="mp-form-control" style="display:flex;align-items:center;gap:10px;padding:10px 14px;min-height:44px;">
            <input type="checkbox" name="<?= $name; ?>" value="1" <?= ($val == 1 || $val == '1') ? 'checked' : ''; ?> style="width:18px;height:18px;cursor:pointer;">
            <span><?= htmlspecialchars($field['label']); ?><?= $req_html; ?></span>
          </label>
          <?php if ($help): ?><div class="mp-form-hint"><?= htmlspecialchars($help); ?></div><?php endif; ?>
        <?php endif; ?>

        <?php if ($help && $type !== 'checkbox'): ?>
        <div class="mp-form-hint"><?= htmlspecialchars($help); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="mp-form-actions" style="margin-top:24px;">
      <?php if ($use_ajax): ?>
      <button type="button" id="save" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $btn_label; ?></button>
      <?php else: ?>
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $btn_label; ?></button>
      <?php endif; ?>
      <?php if ($list_url): ?>
      <a href="<?= $list_url; ?>" class="mp-btn-secondary close_btn"><i class="fa fa-times"></i> Close</a>
      <?php endif; ?>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<?php if ($use_ajax): ?>
<script type="text/javascript">
  $(document).ready(function(){
    $('.select2').select2();

    $('#save').on('click', function(e){
      e.preventDefault();
      var base_url = $('#base_url').val();
      var $btn = $(this);
      var flag = true;

      // Clear previous messages
      $('#<?= $form_id; ?> .text-danger').hide();

      // Simple required check
      $('#<?= $form_id; ?> input[required], #<?= $form_id; ?> select[required], #<?= $form_id; ?> textarea[required]').each(function(){
        if ($.trim($(this).val()) === '') {
          $('#' + $(this).attr('id') + '_msg').show().html('Required field');
          flag = false;
        }
      });

      if (!flag) {
        toastr['warning']('Please fill all required fields.');
        return;
      }

      var q_id = $('#q_id').val();
      var post_url = q_id ? '<?= $update_url; ?>' : '<?= $save_url; ?>';
      if (!post_url) {
        toastr['error']('Save URL is not configured.');
        return;
      }

      var data = new FormData($('#<?= $form_id; ?>')[0]);
      if (!xss_validation(data)) { return false; }

      $('.mp-card-form').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $btn.attr('disabled', true);

      $.ajax({
        type: 'POST',
        url: base_url + post_url,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result){
          if (result === 'success') {
            toastr['success']('Record saved successfully.');
            setTimeout(function(){ window.location.href = '<?= $list_url; ?>'; }, 600);
          } else if (result === 'failed') {
            toastr['error']('Failed to save record. Please try again.');
            $btn.attr('disabled', false);
            $('.overlay').remove();
          } else {
            toastr['error'](result);
            $btn.attr('disabled', false);
            $('.overlay').remove();
          }
        },
        error: function(){
          toastr['error']('Network error. Please try again.');
          $btn.attr('disabled', false);
          $('.overlay').remove();
        }
      });
    });

    $('.close_btn').on('click', function(e){
      e.preventDefault();
      window.location.href = $(this).attr('href');
    });
  });
</script>
<?php else: ?>
<script type="text/javascript">
  $(document).ready(function(){
    $('.select2').select2();
  });
</script>
<?php endif; ?>

<script>
  $(".<?= $module; ?>-add-active-li").addClass("active");
  $(".<?= $module; ?>-add-active-li").closest(".mp-nav-group").addClass("open");
  // Fallback: if no add-specific class, use the generic module class
  if (!$(".<?= $module; ?>-add-active-li").length) {
    $(".<?= $module; ?>-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
  }
</script>
