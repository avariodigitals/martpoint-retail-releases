(function($) {
  'use strict';
  $(function(){
    var form = document.querySelector('#sales-form, #quotation-form');
    if(!form) return;
    var customer = $('#customer_id'), body = form.querySelector('#sales_table tbody, #quotation_table tbody');
    if(!body) return;
    var base = $('#base_url').val() || window.base_url, sequence = 0, timer, ready = true;
    var previousCustomer = String(customer.val() || ''), lastSource = 'normal';
    var status = document.createElement('div');status.setAttribute('role','status');status.style.cssText='font-size:13px;color:var(--mp-primary,#155eef);margin:8px 0';
    customer.closest('.form-group, .mp-form-group').append(status);
    var refresh = document.createElement('button');refresh.type='button';refresh.textContent='Refresh customer prices';refresh.className='btn btn-default btn-sm';status.after(refresh);
    function readLines(){
      return Array.from(body.querySelectorAll('[name^="tr_item_id_"]')).map(function(input){
        var index=input.name.replace('tr_item_id_','');
        return {index:index,id:input.value,price_type:form.id==='quotation-form'?'wholesale':($('#price_type_'+index).val() || $('#price_type').val() || 'wholesale'),unit_id:$('#unit_id_'+index).val() || 0,barcode_id:$('#barcode_id_'+index).val() || $('#barcode_'+index).val() || 0};
      });
    }
    function refreshPrices(force){
      var request=++sequence, lines=readLines(), cid=String(customer.val() || ''), customerChanged=cid!==previousCustomer;
      var existingDocument=!!form.querySelector('[name="sales_id"], [name="quotation_id"]')
        || /\/sales\/(?:update|quotation)\/\d+\/?$/.test(window.location.pathname);
      var keepSavedPrices=existingDocument && !customerChanged && !force;
      // An unchanged saved invoice already has an accepted price snapshot. Do
      // not make edits depend on a secondary pricing lookup completing.
      ready=keepSavedPrices;
      var guardTimeout=setTimeout(function(){
        if(request!==sequence)return;
        ready=true;
        status.textContent='Customer price check timed out. The server will validate invoice prices when you save.';
      },8000);
      $.getJSON(base.replace(/\/$/,'')+'/customer_groups/pricing',{customer_id:cid,lines:JSON.stringify(lines)}).done(function(result){
        if(request!==sequence)return;
        clearTimeout(guardTimeout);
        if(result.error){status.textContent=result.error+' The server will validate invoice prices when you save.';ready=true;return;}
        status.textContent=MpCustomerPricing.label(result.policy);
        lines.forEach(function(line,key){
          var input=document.getElementById('pricing_snapshot_'+line.index), q=result.quotes[key];
          if(!q)return;
          // Initial render of saved invoices/quotations preserves their historical quote.
          var old=null;try{old=input && input.value ? JSON.parse(input.value) : null;}catch(ignore){}
          var isSaved=keepSavedPrices;
          if(isSaved)return;
          if(q.source!=='normal' || customerChanged || force || (old && old.source!=='normal') || lastSource!=='normal'){
            $('#td_data_'+line.index+'_10').val(q.price);
            $('#td_data_'+line.index+'_4').val(q.price);
            if(q.source==='group'){
              $('#td_data_'+line.index+'_8').val(0);
              $('#item_discount_input_'+line.index).val(0);
              $('#price_type_'+line.index).val('wholesale');
            }
            if(input)input.value=q.source!=='normal'?JSON.stringify(q):'';
            if(typeof window.calculate_tax==='function')window.calculate_tax(line.index);
          }
        });
        previousCustomer=cid;lastSource=result.policy.source;ready=true;
      }).fail(function(){
        if(request!==sequence)return;
        clearTimeout(guardTimeout);
        // The save endpoint revalidates pricing rules. Keep the form usable if
        // this advisory quote request fails; the server remains authoritative.
        ready=true;
        status.textContent='Could not refresh customer prices. The server will validate the invoice prices when you save.';
      });
    }
    function schedule(force){clearTimeout(timer);timer=setTimeout(function(){refreshPrices(force);},80);}
    customer.on('change',function(){schedule(false);});
    $(body).on('change','[name^="barcode_id_"]',function(){schedule(true);});
    new MutationObserver(function(changes){
      if(changes.some(function(c){return Array.from(c.addedNodes).some(function(n){return n.nodeType===1 && n.tagName==='TR';});}))schedule(false);
    }).observe(body,{childList:true});
    refresh.addEventListener('click',function(){schedule(true);});
    // Pricing lookup is advisory. Never intercept invoice saves while it is
    // pending; the save endpoint performs the authoritative validation.
    schedule(false);
  });
})(jQuery);
