// Custom Order Modal Logic for POS
function openCustomOrderModal(item){
  var fields = [];
  try { fields = JSON.parse(item.custom_order_fields_json || '[]'); } catch(e){}
  var $body = $('#customOrderModalBody');
  $body.empty();
  if(fields.length === 0){
    $body.html('<p class="text-muted">No custom fields configured for this item.</p>');
  } else {
    fields.forEach(function(f){
      var req = f.required ? ' required' : '';
      var html = '<div class="form-group">';
      html += '<label>' + f.label + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
      if(f.type === 'textarea'){
        html += '<textarea class="form-control co-spec-input" data-label="' + f.label + '" rows="2" placeholder="Enter ' + f.label + '"' + req + '></textarea>';
      } else if(f.type === 'select'){
        html += '<select class="form-control co-spec-input" data-label="' + f.label + '"' + req + '><option value="">-- Select --</option>';
        (f.options || '').split(',').forEach(function(opt){ opt=opt.trim(); html += '<option value="' + opt + '">' + opt + '</option>'; });
        html += '</select>';
      } else if(f.type === 'date'){
        html += '<input type="date" class="form-control co-spec-input" data-label="' + f.label + '"' + req + '>';
      } else if(f.type === 'color'){
        html += '<input type="color" class="form-control co-spec-input" data-label="' + f.label + '" value="#000000" style="height:34px;">';
      } else {
        html += '<input type="' + f.type + '" class="form-control co-spec-input" data-label="' + f.label + '" placeholder="Enter ' + f.label + '"' + req + '>';
      }
      html += '</div>';
      $body.append(html);
    });
  }
  // Pre-fill price
  $('#co_price').val(item.sales_price || '0.00');
  $('#co_item_id').val(item.id);
  $('#co_item_name').val(item.item_name);
  $('#co_tax_id').val(item.tax_id);
  $('#co_tax_type').val(item.tax_type);
  $('#co_tax').val(item.tax);
  $('#co_tax_name').val(item.tax_name);
  $('#co_item_tax_amt').val(item.item_tax_amt || 0);
  $('#co_discount_type').val(item.discount_type);
  $('#co_discount').val(item.discount);
  $('#co_batch_lot').val(item.batch_lot || '');
  $('#co_barcode').val(item.barcode || '');
  $('#co_barcode_id').val(item.barcode_id || 0);
  $('#co_serial').val(item.serial_number || '');
  $('#co_imei').val(item.imei_number || '');
  $('#co_warranty').val(item.warranty_months || 0);
  $('#co_service_bit').val(item.service_bit);
  $('#co_purchase_price').val(item.purchase_price || 0);
  $('#customOrderModal').modal('show');
}

function submitCustomOrderToCart(){
  var item = {
    id: $('#co_item_id').val(),
    item_name: $('#co_item_name').val(),
    sales_price: $('#co_price').val(),
    purchase_price: $('#co_purchase_price').val(),
    tax_id: $('#co_tax_id').val(),
    tax_type: $('#co_tax_type').val(),
    tax: $('#co_tax').val(),
    tax_name: $('#co_tax_name').val(),
    item_tax_amt: $('#co_item_tax_amt').val(),
    discount_type: $('#co_discount_type').val(),
    discount: $('#co_discount').val(),
    service_bit: $('#co_service_bit').val(),
    package_bit: 0,
    batch_lot: $('#co_batch_lot').val(),
    barcode: $('#co_barcode').val(),
    barcode_id: $('#co_barcode_id').val(),
    serial_number: $('#co_serial').val(),
    imei_number: $('#co_imei').val(),
    warranty_months: $('#co_warranty').val(),
    stock: 1
  };
  var specs = {};
  $('.co-spec-input').each(function(){
    var label = $(this).data('label');
    if(label) specs[label] = $(this).val();
  });
  item['item_name'] = item['item_name'] + ' [Custom: ' + JSON.stringify(specs).substring(0,40) + '...]';
  var obj = {};
  obj['item_id'] = item['id'];
  obj['item_name'] = item['item_name'];
  obj['stock'] = item['stock'];
  obj['sales_price'] = item['sales_price'];
  obj['purchase_price'] = item['purchase_price'];
  obj['tax_id'] = item['tax_id'];
  obj['tax_type'] = item['tax_type'];
  obj['tax'] = item['tax'];
  obj['tax_name'] = item['tax_name'];
  obj['item_tax_amt'] = item['item_tax_amt'];
  obj['discount_type'] = item['discount_type'];
  obj['discount'] = item['discount'];
  obj['service_bit'] = item['service_bit'];
  obj['package_bit'] = 0;
  obj['price_type'] = $('#price_type').val() || 'retail';
  obj['batch_lot'] = item['batch_lot'];
  obj['barcode'] = item['barcode'];
  obj['barcode_id'] = item['barcode_id'];
  obj['serial_number'] = item['serial_number'];
  obj['imei_number'] = item['imei_number'];
  obj['warranty_months'] = item['warranty_months'];
  addrow(null, obj);
  $('#customOrderModal').modal('hide');
  toastr.success('Custom order item added to cart');
}
