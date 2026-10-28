(function(root,factory){
  var api=factory();if(typeof module==='object'&&module.exports)module.exports=api;
  if(root&&root.document){function start(){api.bind(root.document,root);}if(root.document.readyState==='loading')root.document.addEventListener('DOMContentLoaded',start);else start();}
})(typeof window!=='undefined'?window:null,function(){
  async function run(step,onProgress,maxSteps){
    for(var i=0;i<(maxSteps||100);i++){
      var result=await step();
      if(!result||!result.success)throw new Error(result&&result.message||'Setup could not confirm the last step. Check setup again before resuming.');
      onProgress(result);
      if(result.ready)return result;
    }
    return {ready:false,message:'Checkpoint saved. More history remains; press Resume to continue.'};
  }
  function bind(doc,win){
    var form=doc.getElementById('messaging-setup-form');if(!form||form.dataset.bound)return;form.dataset.bound='1';
    var button=doc.getElementById('messaging-setup-button'),progress=doc.getElementById('messaging-setup-progress'),busy=false;
    form.addEventListener('submit',async function(event){
      event.preventDefault();if(busy)return;busy=true;button.disabled=true;button.textContent='Setting up…';progress.textContent='Starting the next safe checkpoint…';
      try{
        var result=await run(async function(){
          var data=new win.FormData(form);data.set('format','json');var abort=new win.AbortController();
          var timer=win.setTimeout(function(){abort.abort();},12000);
          try{
            var response=await win.fetch(form.action,{method:'POST',credentials:'same-origin',cache:'no-store',headers:{'Accept':'application/json'},body:data,signal:abort.signal});
            var item=await response.json();
            if(item.csrf_name&&item.csrf_hash){var field=form.elements[item.csrf_name];if(field)field.value=item.csrf_hash;win.csrfName=item.csrf_name;win.csrfHash=item.csrf_hash;}
            return item;
          }finally{win.clearTimeout(timer);}
        },function(item){progress.textContent=item.message+' ('+item.tables_ready+'/'+item.tables_total+' tables ready)';});
        if(result.ready&&result.redirect){progress.textContent='Setup complete. Opening messaging…';win.location.assign(result.redirect);return;}
        progress.textContent=result.message;
      }catch(error){progress.textContent=error.name==='AbortError'?'The server did not confirm this checkpoint in time. Use “Check setup again”, then Resume. Completed work is kept; do not repeatedly submit.':(error.message||'Setup paused. Check setup again, then Resume.');}
      finally{busy=false;button.disabled=false;button.textContent='Resume messaging setup';}
    });
  }
  return {run:run,bind:bind};
});
