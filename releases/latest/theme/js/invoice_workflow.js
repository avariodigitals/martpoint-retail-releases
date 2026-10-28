(function(root,factory){
  var api=factory();if(typeof module==='object'&&module.exports)module.exports=api;
  if(root){root.MPInvoiceWorkflow=api;if(root.document){if(root.document.readyState==='loading')root.document.addEventListener('DOMContentLoaded',function(){api.bind(root.document);});else api.bind(root.document);}}
})(typeof window!=='undefined'?window:null,function(){
  function validate(v){
    if(!Number.isFinite(v.amount)||v.amount<0)return 'Enter a valid non-negative payment amount.';
    if(v.state==='draft'&&v.amount>0)return 'Choose Issued to apply this payment, or clear the received amount to save a draft.';
    return null;
  }
  function bind(doc){
    if(!doc.getElementById('standard_invoice_editor'))return;
    var select=doc.getElementById('invoice_status'),amount=doc.getElementById('amount');
    if(select&&select.dataset.iwBound)return;
    var trigger,list,options=[];
    function state(){return select?select.value:(doc.querySelector('[name="invoice_status"]:checked')||{}).value||'issued';}
    function close(){if(list){list.hidden=true;trigger.setAttribute('aria-expanded','false');}}
    function sync(){
      var draft=state()==='draft',status=doc.getElementById('sales_status'),timing=doc.getElementById('invoice_payment_timing');
      if(status)status.value=draft?'Draft':'Final';
      if(timing)timing.value=amount&&Number(String(amount.value||'0').replace(/,/g,''))>0?'now':'later';
      if(trigger)trigger.textContent=draft?'Draft':'Issued';
      options.forEach(function(o){o.setAttribute('aria-selected',o.dataset.value===state()?'true':'false');});
      var help=doc.getElementById('invoice-workflow-help');
      if(help)help.textContent=draft?'Draft: saved for later, with no payment or stock movement. Choose Issued before applying a payment.':'Issued can remain unpaid. Enter only money actually received in Payment, or record it against this invoice later.';
      var save=doc.getElementById('save'),update=doc.getElementById('update');
      if(save)save.textContent=draft?'Save draft':'Save invoice';if(update)update.textContent=draft?'Update draft':'Save invoice changes';
    }
    if(select){
      select.dataset.iwBound='1';
      var wrap=doc.createElement('div');wrap.className='iw-status-select-wrap';select.parentNode.insertBefore(wrap,select);wrap.appendChild(select);select.style.display='none';
      trigger=doc.createElement('button');trigger.type='button';trigger.className='iw-status-trigger';trigger.setAttribute('role','combobox');trigger.setAttribute('aria-label','Invoice status');trigger.setAttribute('aria-haspopup','listbox');trigger.setAttribute('aria-expanded','false');trigger.setAttribute('aria-controls','invoice_status_options');
      list=doc.createElement('div');list.id='invoice_status_options';list.className='iw-status-options';list.hidden=true;list.setAttribute('role','listbox');list.setAttribute('aria-label','Invoice status options');
      Array.prototype.forEach.call(select.options,function(option){var b=doc.createElement('button');b.type='button';b.dataset.value=option.value;b.textContent=option.textContent;b.setAttribute('role','option');b.addEventListener('click',function(){select.value=option.value;select.dispatchEvent(new Event('change',{bubbles:true}));close();trigger.focus();});options.push(b);list.appendChild(b);});
      trigger.addEventListener('click',function(){list.hidden=!list.hidden;trigger.setAttribute('aria-expanded',list.hidden?'false':'true');});
      trigger.addEventListener('keydown',function(e){if(e.key==='ArrowDown'||e.key==='ArrowUp'){e.preventDefault();list.hidden=false;trigger.setAttribute('aria-expanded','true');(e.key==='ArrowUp'?options[options.length-1]:options[0]).focus();}});
      list.addEventListener('keydown',function(e){if(e.key==='Escape'){close();trigger.focus();}else if(e.key==='ArrowDown'||e.key==='ArrowUp'){e.preventDefault();var index=options.indexOf(e.target);options[(index+(e.key==='ArrowDown'?1:options.length-1))%options.length].focus();}});
      doc.addEventListener('click',function(e){if(!wrap.contains(e.target))close();});doc.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
      wrap.appendChild(trigger);wrap.appendChild(list);select.addEventListener('change',sync);
    }
    if(amount)amount.addEventListener('input',sync);
    sync();
  }
  return {validate:validate,bind:bind};
});
