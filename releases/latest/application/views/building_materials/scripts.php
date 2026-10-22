<script>
(function(){
  var mobile=<?= !empty($building_mobile)?'true':'false'; ?>;
  function syncScreen(){if(document.activeElement&&document.activeElement.matches('input,textarea,[contenteditable=true]'))return;var wanted=matchMedia('(max-width:768px), (max-width:1024px) and (orientation:portrait)').matches;if(wanted!==mobile){var url=new URL(location.href);if(wanted)url.searchParams.set('screen','mobile');else url.searchParams.delete('screen');location.replace(url.toString());}}
  syncScreen();window.addEventListener('resize',syncScreen);
  if(mobile)document.querySelectorAll('.bm-ops a,.bm-projects a,.bm-project-detail a,.bo-mobile-nav a').forEach(function(a){var u=new URL(a.href,location.href);if(/\/building_(workspace|projects)(\/|$)/.test(u.pathname)||/\/online_store(\/(enquiry|settings|content_settings)(\/|$)|\/?$)/.test(u.pathname)){u.searchParams.set('screen','mobile');a.href=u.toString();}});
  var search=document.getElementById('bm-tool-search');
  if(search)search.addEventListener('input',function(){var count=0;var words=search.value.toLowerCase().trim().split(/\s+/).filter(Boolean);document.querySelectorAll('[data-tool-group]').forEach(function(group){var n=0;group.querySelectorAll('[data-tool]').forEach(function(a){var text=a.dataset.search.toLowerCase();var show=typeof window.mpSearchMatch==='function'?window.mpSearchMatch(text,search.value):words.every(function(w){return text.indexOf(w)!==-1;});a.hidden=!show;if(show){n++;count++;}});group.hidden=!n;});document.getElementById('bm-tools-empty').hidden=count>0;});
  function picker(select){
    var required=select.required;select.hidden=true;select.required=false;select.tabIndex=-1;
    var wrap=document.createElement('div'),trigger=document.createElement('button'),options=document.createElement('div'),error=document.createElement('small');
    wrap.className='bo-select';trigger.type='button';trigger.className='bo-select-trigger';trigger.setAttribute('aria-expanded','false');trigger.setAttribute('aria-label',(select.closest('label')?.childNodes[0]?.textContent||'Choose option').trim());options.className='bo-select-options';options.hidden=true;error.className='bo-error';error.hidden=true;error.setAttribute('role','alert');
    function refresh(){trigger.textContent=select.options[select.selectedIndex]?.textContent||'Choose';trigger.disabled=select.disabled;options.textContent='';Array.from(select.options).forEach(function(o){if(o.hidden)return;var b=document.createElement('button');b.type='button';b.textContent=o.textContent;b.disabled=o.disabled;b.addEventListener('click',function(){select.value=o.value;select.dispatchEvent(new Event('change',{bubbles:true}));options.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();});options.appendChild(b);});}
    trigger.addEventListener('click',function(){options.hidden=!options.hidden;trigger.setAttribute('aria-expanded',String(!options.hidden));});
    wrap.addEventListener('keydown',function(e){if(e.key==='Escape'){options.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();}});
    select.addEventListener('change',function(){error.hidden=true;refresh();});select.bmRefresh=refresh;
    if(select.form)select.form.addEventListener('submit',function(e){if(required&&!select.disabled&&!select.value){e.preventDefault();error.textContent='Choose an option before saving.';error.hidden=false;trigger.focus();}});
    select.after(wrap);wrap.append(trigger,options,error);refresh();
  }
  document.querySelectorAll('[data-bm-select]').forEach(picker);
  var customer=document.querySelector('[data-bm-customer]'),site=document.querySelector('[data-bm-site]');
  function sites(){if(!customer||!site)return;Array.from(site.options).forEach(function(o){o.hidden=!!o.value&&o.dataset.customer!==customer.value;});if(site.selectedOptions[0]?.hidden)site.value='';if(site.bmRefresh)site.bmRefresh();}
  if(customer){customer.addEventListener('change',sites);sites();}
  var project=document.querySelector('[data-bm-project]'),standalone=document.querySelector('[data-bm-standalone]');
  function projectChanged(){if(!project||!standalone)return;standalone.hidden=!!project.value;standalone.querySelectorAll('select,input').forEach(function(el){el.disabled=!!project.value;if(el.bmRefresh)el.bmRefresh();});}
  if(project){project.addEventListener('change',projectChanged);projectChanged();}
  var route=document.querySelector('select[name="route"]');
  function routeChanged(){if(!route)return;var visit=document.querySelector('[data-website-visit]'),service=document.querySelector('[data-website-service]');if(visit){visit.hidden=route.value==='project';visit.querySelector('input').required=route.value==='survey';}if(service)service.hidden=route.value!=='service';}
  if(route){route.addEventListener('change',routeChanged);routeChanged();}
})();
</script>
