(function(){
  if(window.mpQuickCatalogueBound)return;window.mpQuickCatalogueBound=true;
  document.addEventListener('keydown',function(event){if(event.key==='Enter'&&event.target.matches('.mp-lookup-name')){event.preventDefault();event.target.closest('.mp-quick-lookup').querySelector('.mp-lookup-save').click();}});
  document.addEventListener('click',function(event){
    var button=event.target.closest('.mp-lookup-save');if(!button)return;
    var box=button.closest('.mp-quick-lookup'),input=box.querySelector('.mp-lookup-name'),feedback=box.querySelector('.mp-lookup-feedback');
    var name=input.value.trim();if(!name){feedback.textContent='Enter a name first.';return;}
    button.disabled=true;feedback.textContent='Saving…';
    var data=new URLSearchParams({kind:box.dataset.kind,name:name});
    if(window.csrfName&&window.csrfHash)data.set(window.csrfName,window.csrfHash);
    else if(box.dataset.csrfName)data.set(box.dataset.csrfName,box.dataset.csrfHash);
    fetch(box.dataset.url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:data.toString()})
      .then(function(response){return response.json();}).then(function(result){
        if(result.csrf_name&&result.csrf_hash){window.csrfName=result.csrf_name;window.csrfHash=result.csrf_hash;document.querySelectorAll('input[name="'+result.csrf_name+'"]') .forEach(function(el){el.value=result.csrf_hash;});}
        if(!result.success)throw new Error(result.message||'Could not save.');
        var select=document.getElementById(box.dataset.target);
        if(select){var found=Array.prototype.find.call(select.options,function(o){return String(o.value)===String(result.id);});if(!found)select.add(new Option(result.name,String(result.id)));select.value=String(result.id);select.dispatchEvent(new Event('change',{bubbles:true}));if(window.jQuery)window.jQuery(select).trigger('change');}
        input.value='';feedback.textContent=(result.existing?'Selected existing: ':'Created: ')+result.name;
      }).catch(function(error){feedback.textContent=error.message||'Network error. Please try again.';}).finally(function(){button.disabled=false;});
  });
})();
