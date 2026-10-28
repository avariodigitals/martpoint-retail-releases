(function(root,factory){
  var api=factory(); if(typeof module==='object' && module.exports)module.exports=api;
  if(root){root.MPInvoiceWorkflow=api;if(root.document){if(root.document.readyState==='loading')root.document.addEventListener('DOMContentLoaded',function(){api.bind(root.document);});else api.bind(root.document);}}
})(typeof window!=='undefined'?window:null,function(){
  function validate(v){
    if(!Number.isFinite(v.amount)||v.amount<0)return 'Enter a valid non-negative payment amount.';
    if(v.state==='draft')return v.amount>0?'Issue the invoice before recording a payment.':null;
    if(v.timing==='later' && v.amount>0)return 'Clear the payment amount or choose Record a payment now.';
    if(v.timing==='now' && v.amount<=0)return 'Enter the payment received, or choose Pay later.';
    if(v.customerIsWalkIn && v.amount+(v.paidPreviously||0)+0.005<v.total)return 'For Pay later, select or create a named customer. You can also save a draft. Walk-in checkout still requires full payment.';
    return null;
  }
  function bind(doc){
    if(!doc.getElementById('standard_invoice_editor'))return;
    var state=function(){return (doc.querySelector('[name="invoice_status"]:checked')||{}).value||'issued';};
    function sync(){
      var draft=state()==='draft', later=doc.querySelector('[name="payment_timing"][value="later"]'),now=doc.querySelector('[name="payment_timing"][value="now"]');
      if(draft && later)later.checked=true;
      if(now)now.disabled=draft;
      var collect=!draft&&!!(now&&now.checked);
      ['amount','payment_type','payment_note','account_id','allow_tot_advance','payment_reference','cheque_number','cheque_period'].forEach(function(id){var el=doc.getElementById(id);if(el){el.disabled=!collect;if(!collect && id==='amount')el.value='';if(!collect && id==='allow_tot_advance')el.checked=false;}});
      doc.querySelectorAll('.payment-fields,.payments_div').forEach(function(el){el.hidden=!collect;el.style.display=collect?'':'none';});
      var status=doc.getElementById('sales_status');if(status)status.value=draft?'Draft':'Final';
      var help=doc.getElementById('invoice-workflow-help');if(help)help.textContent=draft?'Draft only: no payment, stock consumption or invoice email. Issue it when ready.':'Save an unpaid invoice for a named customer. Record partial/full payments later; document status and payment status are separate.';
      var save=doc.getElementById('save'),update=doc.getElementById('update');if(save)save.textContent=draft?'Save draft':'Save invoice';if(update)update.textContent=draft?'Update draft':'Save invoice changes';
    }
    doc.querySelectorAll('[name="invoice_status"],[name="payment_timing"]').forEach(function(el){el.addEventListener('change',sync);});sync();
  }
  return {validate:validate,bind:bind};
});
