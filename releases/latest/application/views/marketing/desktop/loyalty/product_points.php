<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Product Points'); ?></h2>
    <div class="mp-page-sub">Bonus points assigned to specific products</div>
  </div>
  <button type="button" class="mp-qa-btn green" onclick="open_pp_modal()"><i class="fa fa-plus"></i> Add Product Points</button>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <table id="example2" class="table mp-dt-table custom_hover" width="100%">
      <thead>
        <tr>
          <th>#</th><th>Product</th><th>Bonus Points</th><th>Bonus Type</th><th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="pp-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Product Points</h4>
      </div>
      <div class="modal-body">
        <form id="pp-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
          <input type="hidden" name="id" id="pp_id">
          <div class="mp-form-grid">
            <div class="mp-form-group full">
              <label for="pp_item_id">Product <span class="text-danger">*</span></label>
              <select class="form-control select2" id="pp_item_id" name="item_id" style="width:100%" required>
                <option value="">-- Select Product --</option>
                <?php
                $CI =& get_instance();
                $items = $CI->db->where('store_id', get_current_store_id())->where('status', 1)->order_by('item_name', 'ASC')->get('db_items')->result();
                foreach($items as $it){
                  echo '<option value="'.(int)$it->id.'">'.htmlspecialchars($it->item_name).'</option>';
                }
                ?>
              </select>
            </div>
            <div class="mp-form-group">
              <label for="pp_bonus_points">Bonus Points</label>
              <input type="number" min="0" class="mp-form-control" id="pp_bonus_points" name="bonus_points" value="0">
            </div>
            <div class="mp-form-group">
              <label for="pp_bonus_type">Bonus Type</label>
              <select class="mp-form-control" id="pp_bonus_type" name="bonus_type">
                <option value="fixed">Fixed</option>
                <option value="multiplier">Multiplier</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="save_pp()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
var csrfToken = '<?= $this->security->get_csrf_hash(); ?>';
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';

function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10"B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat' },
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?= site_url('loyalty/ajax_product_points_list'); ?>",
            "type": "POST",
            "data": function(d){
                d[csrfName] = csrfToken;
            }
        },
        "columnDefs": [{ "targets": [0,4], "orderable": false }]
    });
}
$(document).ready(function() { load_datatable(); });

function open_pp_modal(){
    $('#pp-form')[0].reset();
    $('#pp_id').val('');
    $('#pp-modal').modal('show');
}

function edit_product_points(id){
    $.post(base_url + 'loyalty/get_product_point/' + id, { [csrfName]: csrfToken }, function(res){
        if(res.success && res.data){
            var row = res.data;
            $('#pp_id').val(row.id);
            $('#pp_item_id').val(row.item_id).trigger('change');
            $('#pp_bonus_points').val(row.bonus_points);
            $('#pp_bonus_type').val(row.bonus_type);
            $('#pp-modal').modal('show');
        } else {
            error_show(res.message || 'Record not found');
        }
    }, 'json');
}

function save_pp(){
    var form = $('#pp-form').serialize();
    $.post(base_url + 'loyalty/save_product_points', form, function(res){
        if(res=='success'){ success_show('Product points saved'); $('#pp-modal').modal('hide'); $('#example2').DataTable().ajax.reload(); }
        else{ error_show('Failed: ' + res); }
    });
}
$(".loyalty-product-points-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
