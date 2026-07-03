
// WhatsApp share flag
var pos_sale_just_saved = false;
var pos_saved_customer_id = '';
var pos_saved_warehouse_id = '';
var pos_saved_advance = '0';

// Populate staff assignment when payment modal opens
$('#multiple-payments-modal').on('shown.bs.modal', function(){
	if(typeof populateStaffAssignment === 'function'){
		populateStaffAssignment();
	}
});

// Modal cleanup after successful payment + WhatsApp share
$('#multiple-payments-modal').on('hidden.bs.modal', function(){
	if(pos_sale_just_saved){
		pos_sale_just_saved = false;
		$(".items_table > tbody").empty();
		$(".discount_input").val(0);
		uncheck_allow_tot_advance();
		var rc=$("#payment_row_count").val();
		while(rc>1){
			remove_row(rc);
			rc--;
		}
		$("#pos-form")[0].reset();
		$("#customer_id").val(pos_saved_customer_id).select2();
		$("#customer_id").find(':selected').attr('data-tot_advance',to_Fixed(pos_saved_advance)).trigger('change');
		$("#search_it").val('');
		if(warehouse_module){
			$("#warehouse_id").val(pos_saved_warehouse_id).select2();	
		}
		final_total();
		get_details(null,true);
		hold_invoice_list();
		get_coupon_details();
		// Hide WhatsApp button for next use
		$("#btn-share-whatsapp").addClass('hide').attr('href','#');
	}
});

//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent,target){

    if(kevent.keyCode==13){
		$("#"+target).focus();
    }
	
}
/*Email validation code*/
function validateEmail(sEmail) {
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,9}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
}

function uncheck_allow_tot_advance(){
  //verify is checked ?
  if($("#allow_tot_advance").is(':checked')){
    $("#click_to_uncheck").trigger("click");
  }
}

$("#pay_all").on("click",function(){
	save(print=true,pay_all=true);
});

/* ─── Offline Sales Queue Helpers ─── */
function serializeFormData(formData){
  var obj = {};
  formData.forEach(function(value, key){
    if (obj[key] !== undefined) {
      if (!Array.isArray(obj[key])) obj[key] = [obj[key]];
      obj[key].push(value);
    } else {
      obj[key] = value;
    }
  });
  return obj;
}
function objectToFormData(obj){
  var fd = new FormData();
  for (var key in obj) {
    if (Array.isArray(obj[key])) {
      obj[key].forEach(function(v){ fd.append(key, v); });
    } else {
      fd.append(key, obj[key]);
    }
  }
  return fd;
}
function processSaveSuccess(result, print, command, customer_id){
  var base_url = $("#base_url").val();
  result = result.split("<<<###>>>");
  if(result[0]=="success"){
    toastr['success']("Record Saved Successfully!!");
    success.currentTime = 0;
    success.play();
    var warehouse_id = $("#warehouse_id").val();
    var print_done = true;
    if(print){
      print_done = window.open(base_url+"pos/print_invoice_pos/"+result[1], "_blank", "scrollbars=1,resizable=1,height=300,width=450");
    }
    if(print_done){
      if(command=='update'){
        window.location = base_url+"sales";
      } else {
        var grandTotal = $(".sales_div_tot_payble").text();
        var pdfToken = result[5] || '';
        var pdfUrl = base_url + "publicpdf/sales/" + result[1] + "?t=" + pdfToken;
        var salesCode = result[6] || result[1];
        var storeName = $("#store_name").val() || 'MartPoint';
        var waText = "You just made a purchase\n*" + storeName + "*\nReceipt: " + salesCode + "\nAmount: " + grandTotal + "\nView/Download: " + pdfUrl + "\nThank you for your business!";
        var custMobile = window.pos_customer_mobile || '';
        custMobile = custMobile.replace(/\D/g, '');
        if(custMobile.indexOf('00') === 0) { custMobile = custMobile.substring(2); }
        var waUrl = custMobile ? "https://wa.me/" + custMobile + "?text=" + encodeURIComponent(waText) : "https://wa.me/?text=" + encodeURIComponent(waText);
        $("#btn-share-whatsapp").removeClass('hide').attr('href', waUrl);
        pos_sale_just_saved = true;
        pos_saved_customer_id = customer_id;
        pos_saved_warehouse_id = warehouse_id;
        pos_saved_advance = result[4] || '0';
        // Create PayPlan plan if active
        if(typeof bnplActive !== 'undefined' && bnplActive && typeof createInstallmentPlan === 'function'){
          createInstallmentPlan(result[1], customer_id);
        }
      }
    }
    $("#init_code").val(result[2]);
    $("#count_id").val(result[3]);
  }
  else if(result[0]=="failed"){
    toastr['error']("Sorry! Failed to save Record.Try again");
  }
  else {
    toastr.error(result);
  }
  $("#make_sale").attr('disabled',false);
  $(".overlay").remove();
  updatePendingSalesBadge();
}

/* ─── Retry Queued Sales ─── */
function retryQueuedSales(){
  if (!navigator.onLine || typeof MPOfflineDB === 'undefined') return;
  MPOfflineDB.getPendingSales().then(function(sales){
    if (!sales || !sales.length) return;
    toastr.info('Syncing ' + sales.length + ' queued sale(s)...', 'Offline Sync');
    sales.forEach(function(sale){
      var fd = objectToFormData(sale.formData);
      $.ajax({
        type: 'POST',
        url: sale.url,
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result){
          MPOfflineDB.removeQueuedSale(sale.queueId).then(function(){
            toastr.success('Queued sale synced successfully.');
            updatePendingSalesBadge();
          }).catch(function(){});
        },
        error: function(){
          // Will retry next time connection is restored
        }
      });
    });
  }).catch(function(){});
}

function updatePendingSalesBadge(){
  if (typeof MPOfflineDB === 'undefined') return;
  MPOfflineDB.countPendingSales().then(function(count){
    var badge = document.getElementById('pendingSalesBadge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
    // Also toggle Retry button
    var retryLi = document.getElementById('retrySalesLi');
    if (retryLi) {
      retryLi.style.display = (count > 0 && navigator.onLine) ? '' : 'none';
    }
  }).catch(function(){});
}

// Retry when coming back online
window.addEventListener('online', function(){
  setTimeout(retryQueuedSales, 2000);
});

/* ─── Local Receipt Printer (Offline) ─── */
function printLocalReceipt(){
  var storeName = $("#store_name").val() || 'MartPoint';
  var custName = $("#select2-customer_id-container").text() || 'Walk-in Customer';
  var now = new Date();
  var receiptNo = 'OFF-' + now.getFullYear() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0') + '-' + String(Math.floor(Math.random()*9999)).padStart(4,'0');
  var rowsHtml = '';
  $(".items_table > tbody > tr").each(function(){
    var $row = $(this);
    var itemCell = $row.find('td').eq(0).text().trim();
    var qty = $row.find('input[name^="item_qty_"]').val() || '1';
    var price = $row.find('input[name^="sales_price_"]').val() || '0';
    var tax = $row.find('input[name^="td_data_"][name$="_11"]').val() || '0';
    var subtotal = $row.find('input[name^="td_data_"][name$="_4"]').val() || '0';
    if(itemCell){
      rowsHtml += '<tr><td style="text-align:left;padding:3px 0;border-bottom:1px dashed #ccc;">' + itemCell + '</td>' +
                  '<td style="text-align:center;padding:3px 0;border-bottom:1px dashed #ccc;">' + qty + '</td>' +
                  '<td style="text-align:right;padding:3px 0;border-bottom:1px dashed #ccc;">' + price + '</td>' +
                  '<td style="text-align:right;padding:3px 0;border-bottom:1px dashed #ccc;">' + subtotal + '</td></tr>';
    }
  });
  var totQty = $(".tot_qty").text() || '0';
  var totAmt = $(".tot_amt").text() || '0';
  var totDisc = $(".tot_disc").text() || '0';
  var totGrand = $(".tot_grand").text() || '0';

  var receipt = '<!DOCTYPE html><html><head><title>Receipt</title>' +
    '<style>body{font-family:monospace;font-size:13px;max-width:300px;margin:0 auto;padding:10px;} ' +
    'h2{text-align:center;margin:5px 0;} .center{text-align:center;} .line{border-top:1px dashed #000;margin:8px 0;} ' +
    'table{width:100%;border-collapse:collapse;} th{text-align:left;border-bottom:1px solid #000;padding:3px 0;} ' +
    '.totals{text-align:right;font-weight:bold;} .footer{text-align:center;font-size:11px;margin-top:15px;color:#666;}</style></head><body>' +
    '<div class="center"><h2>' + storeName + '</h2><p>OFFLINE RECEIPT</p></div>' +
    '<div class="line"></div>' +
    '<p>Receipt #: ' + receiptNo + '<br>Date: ' + now.toLocaleString() + '<br>Customer: ' + custName + '</p>' +
    '<div class="line"></div>' +
    '<table><thead><tr><th>Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>' +
    '<div class="line"></div>' +
    '<div class="totals">Qty: ' + totQty + '<br>Subtotal: ' + totAmt + '<br>Discount: ' + totDisc + '<br>GRAND TOTAL: ' + totGrand + '</div>' +
    '<div class="line"></div>' +
    '<div class="footer">Thank you!<br>This receipt was generated offline.<br>Sale will sync when connection is restored.</div>' +
    '</body></html>';

  var w = window.open('', '_blank', 'width=350,height=600');
  w.document.write(receipt);
  w.document.close();
  setTimeout(function(){ w.print(); }, 300);
}

function save(print=false,pay_all=false){
	/* PayPlan Customer Eligibility Check */
	if(typeof bnplActive !== 'undefined' && bnplActive){
		var cid = $("#customer_id").val();
		// Walk-in check
		if(isWalkInCustomer()){
			toastr.error('Walk-in customers are not eligible for PayPlan. Please create a registered customer.');
			return;
		}
		// Check customer verification via AJAX
		$.ajax({
			type: 'POST',
			url: $("#base_url").val() + 'ninverify/check_bnpl_eligible',
			data: { customer_id: cid },
			dataType: 'json',
			async: false,
			success: function(res){
				if(!res.eligible){
					toastr.error(res.reason || 'Customer is not eligible for PayPlan.');
					window.bnplEligible = false;
				} else {
					window.bnplEligible = true;
				}
			},
			error: function(){
				window.bnplEligible = false;
			}
		});
		if(!window.bnplEligible){
			return;
		}
	}
	/* PayPlan Approval Check */
	if(typeof bnplActive !== 'undefined' && bnplActive && typeof MPApproval !== 'undefined'){
		MPApproval.request('bnpl', {
			total: $(".tot_grand").text(),
			down_payment: $("#bnpl_down_amt").val(),
			installments: $("#bnpl_count").val()
		}, function(approved, ctx){
			if(approved){
				doSave(print, pay_all);
			} else {
				toastr.warning('PayPlan sale requires manager/owner approval.');
			}
		});
		return;
	}
	doSave(print, pay_all);
}

function doSave(print=false,pay_all=false){
	var base_url=$("#base_url").val();
    
    if($(".items_table tr").length==1){
    	toastr["warning"]("Empty Sales List!!");
		return;
    }

    var tot_qty=$(".tot_qty").text();
    var tot_amt=$(".tot_amt").text();
    var tot_disc=$(".tot_disc").text();
    var tot_grand=$(".tot_grand").text();

    var paid_amt=(pay_all) ? tot_grand : $(".sales_div_tot_paid").text();
    var balance=(pay_all) ? 0 : parseFloat($(".sales_div_tot_balance").text());

    var customer_id=$("#customer_id").val();

    if(isWalkInCustomer() && balance > 0){
    	toastr["error"]("Walk-in Customer must pay in full. Credit is not allowed. Please collect full payment or create a registered customer profile.");
		failed.currentTime = 0;
		failed.play();
		return;
    }
    if(document.getElementById("sales_id")){ var command = 'update'; }
    else{ var command = 'save'; }
    var this_btn='make_sale';

	$("#"+this_btn).attr('disabled',true);
	var data = new FormData($('#pos-form')[0]);
	if(!xss_validation(data)){ return false; }

	var saveUrl = base_url+'pos/pos_save_update?command='+command+'&tot_qty='+tot_qty+'&tot_amt='+tot_amt+'&tot_disc='+tot_disc+'&tot_grand='+tot_grand+"&paid_amt="+paid_amt+'&balance='+balance+"&pay_all="+pay_all;

	// OFFLINE: queue the sale instead of sending
	if (!navigator.onLine && typeof MPOfflineDB !== 'undefined') {
		var saleData = {
			formData: serializeFormData(data),
			url: saveUrl,
			print: print,
			command: command,
			customer_id: customer_id,
			tot_grand: tot_grand
		};
		MPOfflineDB.queueSale(saleData).then(function(queueId){
			toastr.warning('Sale queued (' + queueId + '). Will sync when online.', 'Offline Mode');
			success.currentTime = 0;
			success.play();
			// Print local receipt if requested
			if(print){ printLocalReceipt(); }
			// Simulate successful save UI (clear form, etc.)
			pos_sale_just_saved = true;
			pos_saved_customer_id = customer_id;
			pos_saved_warehouse_id = $("#warehouse_id").val();
			pos_saved_advance = '0';
			$("#make_sale").attr('disabled',false);
			$(".overlay").remove();
			updatePendingSalesBadge();
		}).catch(function(err){
			toastr.error('Failed to queue sale locally.');
			$("#make_sale").attr('disabled',false);
			$(".overlay").remove();
		});
		return;
	}
	
	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$.ajax({
		type: 'POST',
		url: saveUrl,
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		success: function(result){
			processSaveSuccess(result, print, command, customer_id);
		},
		error: function(xhr){
			// Network failed mid-request — offer to queue
			$(".overlay").remove();
			$("#make_sale").attr('disabled',false);
			if (typeof MPOfflineDB !== 'undefined') {
				swal({
					title: "Network Error",
					text: "Failed to save sale to server. Queue it for later sync?",
					icon: "warning",
					buttons: ["Cancel", "Queue Sale"],
					dangerMode: false
				}).then(function(willQueue){
					if (willQueue) {
						var saleData = {
							formData: serializeFormData(data),
							url: saveUrl,
							print: print,
							command: command,
							customer_id: customer_id,
							tot_grand: tot_grand
						};
						MPOfflineDB.queueSale(saleData).then(function(queueId){
							toastr.success('Sale queued for sync. (' + queueId + ')');
							pos_sale_just_saved = true;
							pos_saved_customer_id = customer_id;
							updatePendingSalesBadge();
						}).catch(function(){
							toastr.error('Failed to queue sale.');
						});
					}
				});
			} else {
				toastr['error']("Network error. Please check your connection and try again.");
			}
		}
	});
}



/* *********************** HOLD INVOICE START****************************/
$('#hold_invoice').on("click",function (e) {

	//table should not be empty
	if($(".items_table tr").length==1){
    	toastr["error"]("Please Select Items from List!!");
    	failed.currentTime = 0;
		failed.play();
		return;
    }

	swal({
		title: "Hold Invoice ? Same Reference will replace the old list if exist!!",icon: "warning",buttons: true,dangerMode: true,
		content: {
			element: "input",attributes: 
			{
				placeholder: "Please Enter Reference Number!",
				type: "text",
				
				inputAttributes: {
				    maxlength: '5'
				  }
			},},
		}).then(name => {
			//If input box blank Throw Error
			if (!name){ throw null; return false; }
			var reference_id = name;
			/* ********************************************************** */
			var base_url=$("#base_url").val();
    
			//RETRIVE ALL DYNAMIC HTML VALUES
		    var tot_qty=$(".tot_qty").text();
		    var tot_amt=$(".tot_amt").text();
		    var tot_disc=$(".tot_disc").text();
		    var tot_grand=$(".tot_grand").text();
		    var hidden_rowcount=$("#hidden_rowcount").val();

		    var this_id=this.id;//id=save or id=update

				e.preventDefault();
				data = new FormData($('#pos-form')[0]);//form name
				/*Check XSS Code*/
				if(!xss_validation(data)){ return false; }

				// OFFLINE: save hold invoice locally
				if (!navigator.onLine && typeof MPOfflineDB !== 'undefined') {
					var holdData = {
						reference_id: reference_id,
						formData: serializeFormData(data),
						cartHtml: $('#pos-form-tbody').html(),
						customer_id: $('#customer_id').val(),
						tot_qty: tot_qty,
						tot_amt: tot_amt,
						tot_disc: tot_disc,
						tot_grand: tot_grand,
						hidden_rowcount: hidden_rowcount,
						discount_input: $('#discount_input').val() || '',
						discount_type: $('#discount_type').val() || ''
					};
					MPOfflineDB.saveHoldInvoice(holdData).then(function(holdId){
						toastr.success('Invoice held locally (Ref: ' + reference_id + ')', 'Offline Hold');
						$('#pos-form-tbody').html('');
						final_total();
						hold_invoice_list();
						success.currentTime = 0;
						success.play();
					}).catch(function(err){
						toastr.error('Failed to save hold invoice locally.');
					});
					return;
				}

				$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
				$("#"+this_id).attr('disabled',true);  //Enable Save or Update button				
				$.ajax({
					type: 'POST',
					url: base_url+'pos/hold_invoice?command='+this_id+'&tot_qty='+tot_qty+'&tot_amt='+tot_amt+'&tot_disc='+tot_disc+'&tot_grand='+tot_grand+"&reference_id="+reference_id,
					data: data,
					cache: false,
					contentType: false,
					processData: false,
					success: function(result){
						//alert(result);return;
						$("#hidden_invoice_id").val('');
						result=result.split("<<<###>>>");
						
							if(result[0]=="success")
							{
								$('#pos-form-tbody').html('');
								//CALCULATE FINAL TOTAL AND OTHER OPERATIONS
			    					final_total();

								hold_invoice_list();
								success.currentTime = 0;
								success.play();
							}
							else if(result[0]=="failed")
							{
							   toastr['error']("Sorry! Failed to save Record.Try again");
							}
							else
							{
								toastr.error(result);
							}
						
						$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
						$(".overlay").remove();
				   }
			   });
			/* ********************************************************** */

		}) //name end
	.catch(err => {
	    toastr['error']("Failed!! Invoice Not Saved! <br/>Please Enter Reference Number");
	    failed.currentTime = 0;
		failed.play();
	});//swal end

}); //hold_invoice end

function hold_invoice_list(){
	var base_url=$("#base_url").val();
  // If offline, render local holds from IndexedDB
  if (!navigator.onLine && typeof MPOfflineDB !== 'undefined') {
    MPOfflineDB.getHoldInvoices().then(function(holds){
      var html = '';
      var count = holds.length;
      holds.forEach(function(h){
        var dateStr = new Date(h.timestamp).toLocaleString();
        html += '<tr>' +
          '<td>' + h.holdId + '</td>' +
          '<td>' + dateStr + '</td>' +
          '<td>' + (h.reference_id || 'N/A') + '</td>' +
          '<td><a onclick="hold_invoice_edit(' + h.holdId + ')" class="fa fa-fw fa-edit text-success" style="cursor:pointer;" title="Edit Invoice?"></a> ' +
          '<a onclick="hold_invoice_delete(' + h.holdId + ')" class="fa fa-fw fa-trash text-danger" style="cursor:pointer;" title="Delete Invoice?"></a></td>' +
          '</tr>';
      });
      $("#hold_invoice_list").html(html || '<tr><td colspan="4" class="text-center">No offline hold invoices</td></tr>');
      $(".hold_invoice_list_count").html(count);
    }).catch(function(){
      $("#hold_invoice_list").html('<tr><td colspan="4" class="text-center">No offline hold invoices</td></tr>');
      $(".hold_invoice_list_count").html('0');
    });
    return;
  }
  $.post(base_url+"pos/hold_invoice_list",{},function(result){
  	//alert(result);
  	var data = jQuery.parseJSON(result)
    $("#hold_invoice_list").html('').html(data['result']);
    $(".hold_invoice_list_count").html('').html(data['tot_count']);
  });
}
function hold_invoice_delete(invoice_id){
  swal({ title: "Are you sure?",icon: "warning",buttons: true,dangerMode: true,}).then((sure) => {
    if(!sure) return;
    // Require approval for deleting a hold invoice only if manager_approvals is enabled
    if(typeof MPApproval !== 'undefined' && window.managerApprovalsEnabled){
      MPApproval.request('hold_delete', {}, function(approved, ctx){
        if(!approved){
          toastr['error']('Delete hold invoice was not approved.');
          failed.currentTime = 0;
          failed.play();
          return;
        }
        _doHoldInvoiceDelete(invoice_id);
      });
    } else {
      _doHoldInvoiceDelete(invoice_id);
    }
  });
}

function _doHoldInvoiceDelete(invoice_id){
  // If offline, delete from IndexedDB
  if (!navigator.onLine && typeof MPOfflineDB !== 'undefined') {
    MPOfflineDB.deleteHoldInvoice(invoice_id).then(function(){
      toastr["success"]("Success! Offline Invoice Deleted!!");
      success.currentTime = 0;
      success.play();
      hold_invoice_list();
    }).catch(function(){
      toastr['error']("Failed to Delete Offline Invoice!");
      failed.currentTime = 0;
      failed.play();
    });
    return;
  }
  var base_url=$("#base_url").val();
  $.post(base_url+"pos/hold_invoice_delete/"+invoice_id,{},function(result){
    if(result=='success'){
      toastr["success"]("Success! Invoice Deleted!!");
      success.currentTime = 0;
      success.play();
      hold_invoice_list();
    }
    else{
      toastr['error']("Failed to Delete Invoice! Try again!!");
      failed.currentTime = 0;
      failed.play();
    }
  });
}

function hold_invoice_edit(id){

	swal({ title: "Are you sure?",icon: "warning",buttons: true,dangerMode: true,}).then((sure) => {
	if(sure) {//confirmation start

	// OFFLINE: restore from IndexedDB
	if (!navigator.onLine && typeof MPOfflineDB !== 'undefined') {
		MPOfflineDB.getHoldInvoices().then(function(holds){
			var hold = holds.find(function(h){ return h.holdId == id; });
			if (!hold) {
				toastr['error']("Offline hold invoice not found.");
				return;
			}
			$('#pos-form-tbody').html(hold.cartHtml || '');
			$('#discount_input').val(hold.discount_input || '');
			$('#discount_type').val(hold.discount_type || '');
			$("#hidden_rowcount").val(parseInt($(".items_table tr").length)-1);
			// Restore customer if cached
			if (hold.customer_id) {
				MPOfflineDB.getCustomerById(parseInt(hold.customer_id)).then(function(cust){
					if (cust) {
						var option = new Option(cust.text, cust.id, true, true);
						$('#customer_id').append(option).trigger('change');
						set_the_previous_due(cust.previous_due, cust.tot_advance);
					}
				}).catch(function(){});
			}
			final_total();
			success.currentTime = 0;
			success.play();
		}).catch(function(){
			toastr['error']("Failed to restore offline hold invoice.");
		});
		return;
	}

	var base_url=$("#base_url").val();

	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$.post(base_url+"pos/hold_invoice_edit",{hold_id:id},function(result){

    		console.log(result);

      result=result.split("<<<###>>>");
      $('#pos-form-tbody').html('').append(result[0]);
      $('#discount_input').val(result[1]);
      $('#discount_type').val(result[2]);
      /*if(store_module){
        $('#store_id').val(result[4]).select2();
      }
      else{*/
        $('#store_id').val(result[4]);
      /*}*/
      console.log("warehouse = "+result[5]);
      if(warehouse_module){
        $('#warehouse_id').val(result[5]).select2();
      }
      else{
        $('#warehouse_id').val(result[5]);
      }

      autoLoadFirstCustomer(result[3]);
      $("#hidden_invoice_id").val(result[7]);
      $("#hidden_rowcount").val(parseInt($(".items_table tr").length)-1);
      final_total();
      get_details(null,true);
      $(".overlay").remove();
      
      if(result[5]==1){
        $( "#binvoice" ).prop( "checked", true );
        $('#binvoice').parent('div').addClass('checked');
      }
    	});

				
		} //confirmation sure
	}); //confirmation end
}
/* *********************** HOLD INVOICE END****************************/
/* *********************** ORDER INVOICE START****************************/
function get_id_value(id){
	return $("#"+id).val();
}
$('#collect_customer_info').on("click",function (e) {
	
	//table should not be empty
	if($(".items_table tr").length==1){
    	toastr["error"]("Please Select Items from List!!");
    	failed.currentTime = 0;
		failed.play();
		return;
    }
    if(get_id_value('customer_id')==1){
    	//$('#customer-modal').modal('toggle');
    	toastr["error"]("Please Select Customer!!");
    	failed.currentTime = 0;
		failed.play();
    	return false;
    }
    else{
    	$('#delivery-info').modal('toggle');
    }
}); //hold_invoice end
$('.show_payments_modal').on("click",function (e) {
	
	//table should not be empty
	if($(".items_table tr").length==1){
    	toastr["error"]("Please Select Items from List!!");
    	failed.currentTime = 0;
		failed.play();
		return;
    }
    adjust_payments();
    $("#add_payment_row,#payment_type_1").parent().show();
    $("#amount_1").parent().parent().removeClass('col-md-12').addClass('col-md-6');
    $('#multiple-payments-modal').modal('toggle');
}); //hold_invoice end
$('#show_cash_modal').on("click",function (e) {
	//table should not be empty
	if($(".items_table tr").length==1){
    	toastr["error"]("Please Select Items from List!!");
    	failed.currentTime = 0;
		failed.play();
		return;
    }
    else{
    	adjust_payments();
    	$("#add_payment_row,#payment_type_1").parent().hide();
    	$("#amount_1").focus();
    	$("#amount_1").parent().parent().removeClass('col-md-6').addClass('col-md-12');
    	$('#multiple-payments-modal').modal('toggle');
    }
}); //hold_invoice end

$('#add_payment_row').on("click",function (e) {
	
	var base_url=$("#base_url").val();
	//table should not be empty
	if($(".items_table tr").length==1){
    	toastr["error"]("Please Select Items from List!!");
    	failed.currentTime = 0;
		failed.play();
		return;
    }
    /*if(get_id_value('customer_id')==1){
    	//$('#customer-modal').modal('toggle');
    	toastr["error"]("Please Select Customer!!");
    	failed.currentTime = 0;
failed.play();
    	return false;
    }*/
    else{
    	/*BUTTON LOAD AND DISABLE START*/
    	var this_id=this.id;
    	var this_val=$(this).html();
    	$("#"+this_id).html('<i class="fa fa-spinner fa-spin"></i> Please Wait..');
    	$("#"+this_id).attr('disabled',true);  
    	/*BUTTON LOAD AND DISABLE END*/

    	var payment_row_count=get_id_value("payment_row_count");
    	$.post(base_url+"pos/add_payment_row",{payment_row_count:payment_row_count},function(result){
    		$('.payments_div').parent().append(result);
    		$("#payment_row_count").val(parseInt(payment_row_count)+1);

    		/*BUTTON LOAD AND DISABLE START*/
    		$("#"+this_id).html(this_val);
    		$("#"+this_id).attr('disabled',false); 
    		/*BUTTON LOAD AND DISABLE END*/    	
    		failed.currentTime = 0;
			failed.play();
    		adjust_payments();
    	});
    }
}); //hold_invoice end
function remove_row(id){
	$(".payments_div_"+id).html('');
	failed.currentTime = 0;
	failed.play();
	adjust_payments();
}
function calculate_payments(){
	adjust_payments();
}

function get_item_details(item_id, barcodeData){
  var base_url=$("#base_url").val();
  var warehouse_id=$("#warehouse_id").val();
  var price_type=$("#price_type").val() || 'wholesale';
  var postData = {item_id:item_id,warehouse_id:warehouse_id,price_type:price_type};
  if(barcodeData && barcodeData.barcode){
    postData.barcode = barcodeData.barcode;
    postData.batch_lot = barcodeData.batch_lot || '';
  }
  if(barcodeData && barcodeData.barcode_id){
    postData.barcode_id = barcodeData.barcode_id;
  }
  $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');

  var processItemResult = function(item){
    // Check for expiry error
    if(item.error){
      toastr['error'](item.error);
      $(".overlay").remove();
      return;
    }
    // Custom order intercept
    if(item.accept_custom_order == 1){
      $(".overlay").remove();
      openCustomOrderModal(item);
      return;
    }
    var obj = {};
    obj['item_id']        = item['id'];
    obj['item_name']      = item['item_name'];
    obj['stock']          = item['stock'];
    obj['sales_price']    = item['sales_price'];
    obj['purchase_price'] = item['purchase_price'];
    obj['tax_id']         = item['tax_id'];
    obj['tax_type']       = item['tax_type'];
    obj['tax']            = item['tax'];
    obj['tax_name']       = item['tax_name'];
    obj['item_tax_amt']   = item['item_tax_amt'];
    obj['discount_type']  = item['discount_type'];
    obj['discount']       = item['discount'];
    obj['service_bit']    = item['service_bit'];
    obj['package_bit']    = item['package_bit'] || 0;
    obj['price_type']     = $('#price_type').val() || 'retail';
    obj['batch_lot']      = item['batch_lot'] || '';
    obj['barcode']        = item['barcode'] || '';
    obj['barcode_id']     = item['barcode_id'] || 0;
    obj['serial_number']  = item['serial_number'] || '';
    obj['imei_number']    = item['imei_number'] || '';
    obj['warranty_months']= item['warranty_months'] || 0;
    addrow(null,obj);
    $(".overlay").remove();
  };

  $.post(base_url+"pos/get_item_details",postData,function(result){
    console.log(result);
    var item = jQuery.parseJSON(result);
    processItemResult(item);
    // Cache full item details for offline use
    if (typeof MPOfflineDB !== 'undefined' && item && item.id) {
      MPOfflineDB.saveItemDetails(item).catch(function(e){ /* silent */ });
    }
  }).fail(function(){
    // Network failed — try offline cache
    if (typeof MPOfflineDB !== 'undefined') {
      MPOfflineDB.getItemDetails(item_id).then(function(cachedItem){
        if (cachedItem) {
          processItemResult(cachedItem);
        } else {
          $(".overlay").remove();
          toastr['warning']('Item details not available offline. Please connect to network.');
        }
      }).catch(function(){
        $(".overlay").remove();
        toastr['warning']('Item details not available offline. Please connect to network.');
      });
    } else {
      $(".overlay").remove();
      toastr['warning']('Item details not available offline. Please connect to network.');
    }
  });
}

/* *********************** ORDER INVOICE END****************************/


$("#item_search").bind("paste", function(e){
    $("#item_search").autocomplete('search');
} );



$("#item_search").autocomplete({
	minLength: 0,
    source: function(data, cb){
        var doOfflineSearch = function() {
            if (typeof MPOfflineDB !== 'undefined') {
                MPOfflineDB.searchItems(data.term, 20).then(function(items) {
                    var result = [{ label: 'No Records Found ', value: '' }];
                    if (items && items.length) {
                        result = $.map(items, function(el){
                            return {
                                label: el.item_code +'--['+(el.package_bit ? 'Package' : 'Qty:'+el.stock)+'] --'+ el.label,
                                value: '',
                                id: el.id,
                                item_name: el.value,
                                stock: el.stock,
                                service_bit: el.service_bit,
                                package_bit: el.package_bit || 0,
                                barcode: el.barcode || '',
                                batch_lot: el.batch_lot || '',
                                barcode_price: el.barcode_price || '',
                                barcode_mrp: el.barcode_mrp || '',
                                barcode_pprice: el.barcode_pprice || '',
                                barcode_id: el.barcode_id || 0,
                                serial_number: el.serial_number || '',
                                imei_number: el.imei_number || '',
                                warranty_months: el.warranty_months || 0,
                            };
                        });
                    }
                    $("#item_search").removeClass('ui-autocomplete-loading');
                    cb(result);
                }).catch(function(){
                    $("#item_search").removeClass('ui-autocomplete-loading');
                    cb([{ label: 'No Records Found ', value: '' }]);
                });
            } else {
                $("#item_search").removeClass('ui-autocomplete-loading');
                cb([{ label: 'No Records Found ', value: '' }]);
            }
        };

        $.ajax({
        	autoFocus:true,
            url: $("#base_url").val()+'items/get_json_items_details',
            method: 'GET',
            dataType: 'json',
            data: {
                name: data.term,
                store_id:$("#store_id").val(),
                warehouse_id:$("#warehouse_id").val(),
                search_for:"sales",
            },
            beforeSend: function() {
                if($("#warehouse_id").val()==''){
                  toastr['warning']("Please Select Branch!");
                  $("#warehouse_id").select2('open');
                  $("#item_search").removeClass('ui-autocomplete-loading');
                  return;
                }
                $("#item_search").addClass('ui-autocomplete-loading');
            },
            success: function(res){
                var result;
                result = [
                    {
                        label: 'No Records Found ',
                        value: ''
                    }
                ];

                if (res.length) {
                    result = $.map(res, function(el){
                        return {
                            label: el.item_code +'--['+(el.package_bit ? 'Package' : 'Qty:'+el.stock)+'] --'+ el.label,
                            value: '',
                            id: el.id,
                            item_name: el.value,
                            stock: el.stock,
                            service_bit: el.service_bit,
                            package_bit: el.package_bit || 0,
                            barcode: el.barcode || '',
                            batch_lot: el.batch_lot || '',
                            barcode_price: el.barcode_price || '',
                            barcode_mrp: el.barcode_mrp || '',
                            barcode_pprice: el.barcode_pprice || '',
                            barcode_id: el.barcode_id || 0,
                            serial_number: el.serial_number || '',
                            imei_number: el.imei_number || '',
                            warranty_months: el.warranty_months || 0,
                        };
                    });
                    // Cache results in IndexedDB for offline use
                    if (typeof MPOfflineDB !== 'undefined') {
                        MPOfflineDB.saveItems(res).catch(function(e){ /* silent */ });
                    }
                }

                cb(result);
            },
            error: function() {
                // Network failed — try offline cache
                doOfflineSearch();
            }
        });
    },
     response:function(e,ui){
          if(ui.content.length==1){
            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
            $(this).autocomplete("close");
          }
          //console.log(ui.content[0].id);
        },
        //loader start
        search: function (e, ui) {
        },
        select: function (e, ui) {
            var barcodeData = {};
            if(typeof ui.content!='undefined'){
              console.log("Autoselected first");
              if(isNaN(ui.content[0].id)){
                return;
              }
              var stock=ui.content[0].stock;
              var item_id=ui.content[0].id;
              var service_bit=ui.content[0].service_bit;
              var package_bit=ui.content[0].package_bit || 0;
              barcodeData = ui.content[0];
            }
            else{
              console.log("manual Selected");
              var stock=ui.item.stock;
              var item_id=ui.item.id;
              var service_bit=ui.item.service_bit;
              var package_bit=ui.item.package_bit || 0;
              barcodeData = ui.item;
            }
            if(service_bit==0 && package_bit==0 && parseFloat(stock)<=0){
              if(typeof MPApproval !== 'undefined'){
                MPApproval.request('negative_stock_sale', {item_id: item_id, stock: stock}, function(approved, ctx){
                  if(!approved){
                    toastr["warning"](stock+" Items in Stock!! Approval denied.");
                    failed.currentTime = 0;
                    failed.play();
                    return;
                  }
                  get_item_details(item_id, barcodeData);
                  $("#item_search").val('');
                });
                return false;
              } else {
                toastr["warning"](stock+" Items in Stock!!");
                failed.currentTime = 0;
                failed.play();
                return false;
              }
            }

            get_item_details(item_id, barcodeData);
            $("#item_search").val('');
            
            
        },   
        //loader end
});


function set_previous_due(previous_due,tot_advance){
  $(".customer_previous_due").html(previous_due);
  $(".customer_tot_advance").html(tot_advance);
}



function get_coupon_details(){
  var input_box = $("#coupon_code");
  var coupon_code = $.trim(input_box.val());
  var customer_id = $("#customer_id").val();
  var base_url=$("#base_url").val();

  var coupon_type='';
  var coupon_value=0;
  if(coupon_code!=''){
    input_box.addClass('ui-autocomplete-loading');
    $.post(base_url+'customer_coupon/get_coupon_details', {invoice_type:'sales',coupon_code: coupon_code,customer_id:customer_id}, function(data, textStatus, xhr) {
      var json = $.parseJSON(data);
      coupon_value=json.coupon_value;
      coupon_type=json.coupon_type;

      

      $(".coupon_value").html(to_Fixed(coupon_value));
      $(".coupon_type").html(coupon_type);  

      $(".div1,.div2").removeClass('hide');
      
      if(json.expire_status=='Valid'){
        $(".msg_color").removeClass('alert-warning').addClass('alert-success');
      }
      else{
       $(".msg_color").removeClass('alert-success').addClass('alert-warning');
       $(".div2").addClass('hide');
      }
      $("#coupon_code_msg").text(json.message);

      input_box.removeClass('ui-autocomplete-loading');

      final_total();
      adjust_payments();
    });
  }else{
    $(".div1, .div2").addClass('hide');
    $(".coupon_value").html(to_Fixed(coupon_value));
    $(".coupon_type").html(coupon_type);  
    final_total();
    adjust_payments();
  }  
}


$("#coupon_code, #customer_id").on("change",function() {
  get_coupon_details();
});
$('#coupon_code').keypress(function (e) {
 var key = e.which;
 // the enter key code
 if(key == 13){
    get_coupon_details();  
  }
});  

/*Calculate Coupon Discount Amount*/
 const discount_coupon_tot = function(subtotal) {
     var coupon_value=parseFloat($(".coupon_value").html());
         coupon_value = isNaN(coupon_value) ? 0 : coupon_value;

     var coupon_type=$(".coupon_type").html();

     var discount_amt =0;
     if(coupon_type!='' && coupon_value>0){

         if(coupon_type=='Percentage'){
             discount_amt=(subtotal*coupon_value)/100;
         }
         else{//Fixed
             discount_amt=coupon_value;
         }
     }
     return discount_amt;
 }
 
 $('#item_search').keypress(function (e) {
 var key = e.which;
 // the enter key code
 if(key == 13){
    $("#item_search").autocomplete('search');
  }
}); 


