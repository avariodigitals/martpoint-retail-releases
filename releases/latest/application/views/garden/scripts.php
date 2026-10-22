<script>
document.addEventListener('click',function(event){var link=event.target.closest('[data-open-assessment]');if(!link)return;var form=document.getElementById('garden-site-assessment');if(form){form.open=true;form.scrollIntoView({behavior:'smooth',block:'start'});var field=form.querySelector('input:not([type=hidden]),textarea');if(field)field.focus({preventScroll:true});}});

(function(){
  var renderedMobile=<?= !empty($mobile) ? 'true' : 'false'; ?>;
  // Token-based matching so a dictated (or typed) phrase finds partial records
  // instead of requiring one exact substring. Shares garden_voice.js helpers.
  function gardenSearchMatch(text,query){
    if(typeof window.mpSearchMatch==='function')return window.mpSearchMatch(text,query);
    var stop={search:1,find:1,look:1,lookup:1,show:1,filter:1,for:1,me:1,the:1,a:1,an:1,of:1,to:1,in:1,on:1,please:1,list:1,all:1};
    var raw=String(query==null?'':query).toLowerCase().replace(/[^a-z0-9\s]+/g,' ').trim();if(!raw)return true;
    var tokens=[];raw.split(/\s+/).forEach(function(word){if(word.length<2||stop[word])return;if(word.length>3&&word.slice(-1)==='s')word=word.slice(0,-1);if(tokens.indexOf(word)===-1)tokens.push(word);});
    if(!tokens.length)return true;var hay=String(text==null?'':text).toLowerCase();
    return tokens.every(function(token){return hay.indexOf(token)!==-1;});
  }
  function syncScreen(){if(document.activeElement && document.activeElement.matches('input,textarea,[contenteditable=true]'))return;var mobile=window.matchMedia('(max-width:768px), (max-width:1024px) and (orientation:portrait)').matches;
    if(mobile!==renderedMobile){var url=new URL(location.href);if(mobile)url.searchParams.set('screen','mobile');else url.searchParams.delete('screen');location.replace(url.toString());}}
  function openLinkedForm(){var id=location.hash.slice(1);if(!id)return;var node=document.getElementById(id);if(node && node.tagName==='DETAILS')node.open=true;}
  openLinkedForm();window.addEventListener('hashchange',openLinkedForm);
  syncScreen();window.addEventListener('resize',syncScreen);
  if(renderedMobile)document.querySelectorAll('.garden-workspace a').forEach(function(a){var u=new URL(a.href,location.href);if(u.pathname.indexOf('/garden')!==-1){u.searchParams.set('screen','mobile');a.href=u.toString();}});
  document.querySelectorAll('.garden-mode-card input').forEach(function(input){input.addEventListener('change',function(){if(!input.checked)return;document.querySelectorAll('.garden-mode-card input').forEach(function(other){if(other!==input && (input.value==='combined' || other.value==='combined'))other.checked=false;});});});
  // The app bar menu button owns the workspace menu, so the header anchors
  // navigation instead of a loose menu box under it. The menu opens as a sheet
  // pinned under the bar (--gw-bar-h keeps it flush at every viewport), over a
  // dimmed page, so it can never open above the fold.
  var gwBar=document.getElementById('gw-appbar'),gwMenuButton=document.getElementById('gw-menu-toggle'),gwMenu=document.getElementById('gw-workspace-menu'),gwBackdrop=document.getElementById('gw-workspace-backdrop');
  if(gwBar&&gwMenuButton&&gwMenu){
    var gwSyncBar=function(){document.documentElement.style.setProperty('--gw-bar-h',gwBar.offsetHeight+'px');};
    var gwSetMenu=function(open){gwMenu.hidden=!open;gwMenu.setAttribute('aria-hidden',open?'false':'true');if(gwBackdrop)gwBackdrop.hidden=!open;gwMenuButton.setAttribute('aria-expanded',open?'true':'false');document.documentElement.classList.toggle('gw-menu-open',open);if(open){gwSyncBar();var first=gwMenu.querySelector('a,summary');if(first)first.focus();}};
    gwSyncBar();window.addEventListener('resize',gwSyncBar);
    gwMenuButton.addEventListener('click',function(){gwSetMenu(gwMenu.hidden);});
    if(gwBackdrop)gwBackdrop.addEventListener('click',function(){gwSetMenu(false);gwMenuButton.focus();});
    gwMenu.addEventListener('click',function(event){if(event.target.closest('a')){gwSetMenu(false);gwMenuButton.focus();}});
    document.addEventListener('keydown',function(event){if(event.key==='Escape'&&!gwMenu.hidden){gwSetMenu(false);gwMenuButton.focus();}});
  }
  var gwAccountTrigger=document.getElementById('gw-account-trigger'),gwAccountMenu=document.getElementById('gw-account-menu');
  if(gwAccountTrigger&&gwAccountMenu){
    var gwSetAccount=function(open){gwAccountMenu.hidden=!open;gwAccountTrigger.setAttribute('aria-expanded',open?'true':'false');};
    gwAccountTrigger.addEventListener('click',function(event){event.stopPropagation();gwSetAccount(gwAccountMenu.hidden);});
    gwAccountMenu.addEventListener('click',function(event){if(event.target.closest('a'))gwSetAccount(false);});
    document.addEventListener('click',function(event){if(!event.target.closest('.gw-appbar-account'))gwSetAccount(false);});
    document.addEventListener('keydown',function(event){if(event.key==='Escape'&&!gwAccountMenu.hidden){gwSetAccount(false);gwAccountTrigger.focus();}});
  }
  // Project board: stages are foldable sections. Searching filters the cards and
  // opens the stages that still match, so nothing is ever swiped sideways.
  var projectSearch=document.getElementById('garden-project-search');
  if(projectSearch)projectSearch.addEventListener('input',function(){var query=projectSearch.value;document.querySelectorAll('[data-project-lane]').forEach(function(lane){var count=0;lane.querySelectorAll('[data-project-card]').forEach(function(card){var show=gardenSearchMatch(card.textContent,query);card.hidden=!show;if(show)count++;});lane.querySelector('[data-lane-count]').textContent=count;lane.querySelector('[data-lane-empty]').hidden=count>0;if(query)lane.open=count>0;});});
  // --- Paging instead of sideways scrolling ---------------------------------
  // A record table is shown one page at a time, so a phone never swipes a table
  // sideways and no column is clipped at the screen edge. Filtering re-pages the
  // matching set; hidden rows stay in the DOM so the CSV export keeps every row.
  var LIST_PAGE_SIZE=20;
  function buildPager(wrap){
    if(wrap.hasAttribute('data-server-paged'))return;
    var tbody=wrap.querySelector('tbody');if(!tbody)return;
    var rows=Array.prototype.filter.call(tbody.rows,function(row){return !row.querySelector('[colspan]');});
    if(rows.length<=LIST_PAGE_SIZE)return;
    var nav=document.createElement('nav'),prev=document.createElement('button'),next=document.createElement('button'),status=document.createElement('span');
    nav.className='garden-pager';nav.setAttribute('aria-label','Records pagination');
    prev.type='button';next.type='button';prev.className='garden-pager-step';next.className='garden-pager-step';
    prev.textContent='Previous';next.textContent='Next';
    status.className='garden-pager-status';status.setAttribute('role','status');status.setAttribute('aria-live','polite');
    nav.appendChild(prev);nav.appendChild(status);nav.appendChild(next);wrap.after(nav);
    var page=1;
    wrap.mpRenderPager=function(reset){
      if(reset)page=1;
      var live=rows.filter(function(row){return row.dataset.filtered!=='1';}),pages=Math.max(1,Math.ceil(live.length/LIST_PAGE_SIZE));
      if(page>pages)page=pages;if(page<1)page=1;
      rows.forEach(function(row){row.hidden=true;});
      live.slice((page-1)*LIST_PAGE_SIZE,page*LIST_PAGE_SIZE).forEach(function(row){row.hidden=false;});
      status.textContent=live.length?'Page '+page+' of '+pages+' · '+live.length+' records':'No records';
      prev.disabled=page<=1;next.disabled=page>=pages;
    };
    prev.addEventListener('click',function(){page--;wrap.mpRenderPager();wrap.scrollIntoView({block:'nearest'});});
    next.addEventListener('click',function(){page++;wrap.mpRenderPager();wrap.scrollIntoView({block:'nearest'});});
    wrap.mpRenderPager(true);
  }
  document.querySelectorAll('.garden-table-wrap').forEach(buildPager);
  var draggedTask=null;
  document.querySelectorAll('[data-task-card][draggable=true]').forEach(function(card){card.addEventListener('dragstart',function(event){draggedTask=card;event.dataTransfer.effectAllowed='move';event.dataTransfer.setData('text/plain',card.dataset.taskCard);card.classList.add('is-dragging');});card.addEventListener('dragend',function(){card.classList.remove('is-dragging');draggedTask=null;document.querySelectorAll('[data-task-lane]').forEach(function(lane){lane.classList.remove('is-drop-target');});});});
  document.querySelectorAll('[data-task-lane]').forEach(function(lane){lane.addEventListener('dragover',function(event){if(draggedTask){event.preventDefault();lane.classList.add('is-drop-target');}});lane.addEventListener('dragleave',function(event){if(!lane.contains(event.relatedTarget))lane.classList.remove('is-drop-target');});lane.addEventListener('drop',function(event){if(!draggedTask)return;event.preventDefault();lane.classList.remove('is-drop-target');var form=draggedTask.querySelector('form');if(!form)return;var status=document.createElement('input');status.type='hidden';status.name='status';status.value=lane.dataset.taskLane;form.appendChild(status);if(form.requestSubmit)form.requestSubmit();else form.submit();});});
  var catalogueSearch=document.getElementById('garden-catalogue-search'),catalogueKind='all';
  function filterCatalogue(){var term=catalogueSearch ? catalogueSearch.value : '',shown=0;
    document.querySelectorAll('[data-garden-offering]').forEach(function(row){var show=(catalogueKind==='all' || row.dataset.kind===catalogueKind) && gardenSearchMatch(row.textContent,term);row.dataset.filtered=show?'0':'1';row.hidden=!show;if(show)shown++;});
    document.querySelectorAll('.garden-table-wrap').forEach(function(wrap){if(wrap.mpRenderPager)wrap.mpRenderPager(true);});
    var empty=document.getElementById('garden-catalogue-empty'),count=document.getElementById('garden-catalogue-count');if(empty)empty.hidden=shown>0;if(count)count.textContent=shown+' offerings shown';}
  if(catalogueSearch){catalogueSearch.addEventListener('input',filterCatalogue);if(catalogueSearch.form)catalogueSearch.addEventListener('change',function(){catalogueSearch.form.submit();});}
  document.querySelectorAll('.garden-type-filter').forEach(function(button){button.addEventListener('click',function(){catalogueKind=button.dataset.kind;document.querySelectorAll('.garden-type-filter').forEach(function(other){other.classList.toggle('is-selected',other===button);other.setAttribute('aria-pressed',other===button ? 'true' : 'false');});filterCatalogue();});});
  if(renderedMobile)document.querySelectorAll('.garden-workspace a').forEach(function(a){var u=new URL(a.href,location.href),path=u.pathname;
    if(/\/quotation\/add$/.test(path))u.pathname=path.replace(/\/quotation\/add$/,'/mobile/quotation_form');
    else if(/\/quotation\/invoice\/\d+$/.test(path))u.pathname=path.replace('/quotation/invoice/','/mobile/quotation_view/');
    else if(/\/sales\/invoice\/\d+$/.test(path))u.pathname=path.replace('/sales/invoice/','/mobile/sales_invoice/');
    else if(/\/sales\/quotation\/\d+$/.test(path))u.searchParams.set('mobile','1');
    else if(/\/expense\/add$/.test(path))u.pathname=path.replace(/\/expense\/add$/,'/mobile/finance/expenses/form');
    else if(/\/expense\/update\/\d+$/.test(path))u.pathname=path.replace('/expense/update/','/mobile/finance/expenses/form/');
    a.href=u.toString();
  });
  function initGardenSelect(select){
    var required=select.required;select.hidden=true;select.tabIndex=-1;select.removeAttribute('required');
    var wrapper=document.createElement('div'),trigger=document.createElement('button'),options=document.createElement('div');
    wrapper.className='garden-select';trigger.type='button';trigger.className='garden-button';trigger.setAttribute('aria-expanded','false');
    options.className='garden-select-options';options.hidden=true;
    var error=document.createElement('span');error.className='garden-select-error';error.hidden=true;error.setAttribute('role','alert');
    trigger.disabled=select.disabled;if(required)trigger.setAttribute('aria-required','true');
    if(select.form)select.form.addEventListener('submit',function(event){if(event.defaultPrevented)return;if(required&&!select.disabled&&!select.value){event.preventDefault();trigger.setAttribute('aria-invalid','true');error.textContent='Choose an option before saving.';error.hidden=false;trigger.focus();}});
    select.addEventListener('change',function(){trigger.removeAttribute('aria-invalid');error.hidden=true;refresh();});
    function refresh(){trigger.textContent=select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : 'Choose';}
    Array.from(select.options).forEach(function(option){var button=document.createElement('button');button.type='button';button.textContent=option.text;button.disabled=option.disabled;
      button.addEventListener('click',function(){select.value=option.value;select.dispatchEvent(new Event('change',{bubbles:true}));refresh();options.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();});options.appendChild(button);});
    trigger.addEventListener('click',function(){options.hidden=!options.hidden;trigger.setAttribute('aria-expanded',String(!options.hidden));if(!options.hidden){var first=options.querySelector('button:not(:disabled)');if(first)first.focus();}});
    wrapper.addEventListener('keydown',function(e){if(e.key==='Escape'){options.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.focus();}});
    select.after(wrapper);wrapper.append(trigger,options,error);refresh();
  }
  document.querySelectorAll('[data-garden-select]').forEach(initGardenSelect);
  var addMaterial=document.querySelector('[data-add-material-line]');if(addMaterial)addMaterial.addEventListener('click',function(){var host=document.querySelector('[data-material-lines]');if(!host||host.children.length>=60)return;var row=host.children[0].cloneNode(true);row.querySelectorAll('.garden-select').forEach(function(node){node.remove();});row.querySelectorAll('input').forEach(function(input){input.value='';});row.querySelectorAll('select').forEach(function(select){select.value='';});host.appendChild(row);row.querySelectorAll('[data-garden-select]').forEach(initGardenSelect);});
})();
</script>
