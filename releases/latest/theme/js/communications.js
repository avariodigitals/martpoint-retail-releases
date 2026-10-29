(function(){
'use strict';
var root=document.querySelector('.comm');if(!root)return;
if(root.dataset.workspace)document.querySelectorAll('a.mp-nav-item').forEach(function(a){var target=new URL(a.href,location.href);a.classList.toggle('active',target.origin===location.origin&&target.pathname===location.pathname);});
var oldSections={compose:root.dataset.workspace==='automation'?'create':'compose',contacts:'audience',usage:'history',recharge:'recharge',subscription:'subscription'},pageUrl=new URL(location.href),oldSection=oldSections[location.hash.slice(1)];
if(['sms','email','automation'].includes(root.dataset.workspace)&&oldSection&&!pageUrl.searchParams.has('section')){pageUrl.searchParams.set('section',oldSection);pageUrl.hash='';location.replace(pageUrl.href);return;}
// Mobile options expand inside the form. The hidden select retains name/value.
function mobileSelect(s){
 if(s.dataset.commSelect==='1')return;s.dataset.commSelect='1';
 var wrap=document.createElement('div'),trigger=document.createElement('button'),list=document.createElement('div');wrap.className='comm-select';list.className='comm-select-options';trigger.type='button';trigger.setAttribute('aria-expanded','false');
 function draw(){trigger.textContent=s.options[s.selectedIndex]?s.options[s.selectedIndex].textContent:'Choose';list.replaceChildren();var group='';Array.from(s.options).forEach(function(o){var label=o.parentNode.tagName==='OPTGROUP'?o.parentNode.label:'';if(label&&label!==group){var heading=document.createElement('div');heading.className='comm-select-group';heading.textContent=label;list.appendChild(heading);group=label;}var b=document.createElement('button');b.type='button';b.textContent=o.textContent;b.disabled=o.disabled;b.onclick=function(){s.value=o.value;s.dispatchEvent(new Event('change',{bubbles:true}));wrap.classList.remove('open');trigger.setAttribute('aria-expanded','false');};list.appendChild(b);});}

 trigger.onclick=function(){var open=wrap.classList.toggle('open');trigger.setAttribute('aria-expanded',String(open));};wrap.append(trigger,list);s.after(wrap);s.addEventListener('change',draw);draw();
}
if(root.dataset.commMobile==='1')root.querySelectorAll('select').forEach(mobileSelect);
root.querySelectorAll('[data-checkout]').forEach(function(b){b.addEventListener('click',function(){b.form.querySelector('[name=action]').value='purchase';});});
root.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(e){if(e.submitter && !e.submitter.hasAttribute('data-checkout')){var action=f.querySelector('[name=action]');if(action && action.value==='purchase')action.value='communication_request';}});});
root.querySelectorAll('[data-amount]').forEach(function(b){b.onclick=function(){var f=b.form,i=f.querySelector('[name=amount]'),pkg=f.querySelector('[name=package_id]');if(pkg)pkg.value=b.dataset.bundle||'0';i.dataset.bundleUnits=b.dataset.units||'';i.dataset.settingAmount='1';i.min=b.dataset.bundle?b.dataset.amount:i.dataset.min;i.value=b.dataset.amount;i.dispatchEvent(new Event('input'));delete i.dataset.settingAmount;};});
root.querySelectorAll('[data-rate]').forEach(function(i){i.addEventListener('input',function(){if(!i.dataset.settingAmount){var pkg=i.form.querySelector('[name=package_id]');if(pkg)pkg.value='0';i.dataset.bundleUnits='';i.min=i.dataset.min;}var credits=i.dataset.bundleUnits?Number(i.dataset.bundleUnits):Math.floor(Math.round(Number(i.value)*100)/Number(i.dataset.rate));i.form.querySelector('[data-credit-quote]').textContent=Number.isFinite(credits)&&credits>=0?credits.toLocaleString()+' credits':'';});});
root.querySelectorAll('[data-send-mode]').forEach(function(s){var f=s.form,box=f.querySelector('[data-schedule-fields]'),submit=f.querySelector('[data-send-submit]');function delivery(){var later=s.value==='later';box.hidden=!later;box.querySelectorAll('input').forEach(function(i){i.disabled=!later;i.required=later;});f.querySelector('[data-send-note]').textContent=s.value==='now'?'Next: review the exact audience and credits, then confirm sending.':later?'Schedule once. Use Automation for recurring birthday checks.':'Saving a draft sends nothing.';if(submit)submit.textContent=later?'Schedule campaign':s.value==='now'?'Review & send now':'Save draft';}s.addEventListener('change',delivery);delivery();});
var pick=document.getElementById('comm-pick-contacts'),importForm=document.getElementById('comm-contact-import');
if(pick && importForm && window.isSecureContext && navigator.contacts && typeof navigator.contacts.select==='function'){
 pick.hidden=false;pick.onclick=async function(){var status=document.getElementById('comm-phone-import-status');try{var props=['name',importForm.dataset.channel==='sms'?'tel':'email'];if(navigator.contacts.getProperties){var supported=await navigator.contacts.getProperties();props=props.filter(function(p){return supported.includes(p);});if(!props.includes(importForm.dataset.channel==='sms'?'tel':'email'))throw new Error('This browser cannot share the needed contact field. Upload a vCard instead.');}var contacts=await navigator.contacts.select(props,{multiple:true}),rows=['name,email,phone'];function csv(v){return '"'+String(v||'').replace(/"/g,'""')+'"';}contacts.forEach(function(c){var name=(c.name||[])[0]||'',values=importForm.dataset.channel==='sms'?(c.tel||[]):(c.email||[]);values.forEach(function(value){rows.push([name,importForm.dataset.channel==='email'?value:'',importForm.dataset.channel==='sms'?value:''].map(csv).join(','));});});if(rows.length===1){status.textContent='No contact details selected.';return;}var text=rows.join('\r\n');if(new Blob([text]).size>1048576||rows.length>10001)throw new Error('Select fewer contacts (up to 1 MB / 10,000 details).');importForm.querySelector('[name=contacts]').value=text;importForm.querySelector('[name=contacts_file]').value='';status.textContent=(rows.length-1)+' contact details selected. Review them and confirm permission before importing.';}catch(e){status.textContent=e.name==='AbortError'?'Contact selection cancelled.':(e.message||'Use CSV or vCard import on this browser.');}};
}
// An import may land in one named audience list; the new name is asked for only when chosen.
var destination=importForm&&importForm.querySelector('[data-list-destination]'),newList=importForm&&importForm.querySelector('[data-list-new]');
if(destination&&newList){var toggleListName=function(){newList.hidden=destination.value!=='-1';if(newList.hidden)newList.querySelector('input').value='';};destination.addEventListener('change',toggleListName);toggleListName();}
root.querySelectorAll('[data-list-remove]').forEach(function(b){b.onclick=function(){if(!window.cqConfirm)return;window.cqConfirm('Remove the list “'+b.dataset.listRemove+'”? Your imported contacts stay in your account.',{title:'Remove audience list',okText:'Remove list',danger:true}).then(function(ok){if(ok)b.form.submit();});};});
root.querySelectorAll('[data-campaign]').forEach(function(b){b.onclick=function(){b.disabled=true;window.mpCampaignConfirm(b.dataset.campaign,b.dataset.test==='1',function(r){var result=document.getElementById('comm-result');result.textContent=r.message||'Request completed.';if(r.status==='success')location.reload();}).finally(function(){b.disabled=false;});};});
var search=document.getElementById('comm-search'),campaignFilter='all';
function filterCampaigns(){root.querySelectorAll('#comm-campaign-table tbody tr').forEach(function(r){var status=r.dataset.status,match=campaignFilter==='all'||status===campaignFilter||(campaignFilter==='queued'&&['sending','partial','scheduled','paused'].includes(status));r.hidden=!match||!!(search&&!r.textContent.toLowerCase().includes(search.value.toLowerCase()));});}
if(search)search.oninput=filterCampaigns;
root.querySelectorAll('[data-campaign-filter]').forEach(function(b){b.onclick=function(){campaignFilter=b.dataset.campaignFilter;root.querySelectorAll('[data-campaign-filter]').forEach(function(x){x.classList.toggle('active',x===b);});filterCampaigns();};});
var workflowSearch=document.getElementById('comm-workflow-search'),workflowFilter='all';
function filterWorkflows(){root.querySelectorAll('#comm-workflow-table tbody tr').forEach(function(r){r.hidden=!(workflowFilter==='all'||r.dataset.enabled===workflowFilter)||!!(workflowSearch&&!r.textContent.toLowerCase().includes(workflowSearch.value.toLowerCase()));});}
if(workflowSearch)workflowSearch.oninput=filterWorkflows;
root.querySelectorAll('[data-workflow-filter]').forEach(function(b){b.onclick=function(){workflowFilter=b.dataset.workflowFilter;root.querySelectorAll('[data-workflow-filter]').forEach(function(x){x.classList.toggle('active',x===b);});filterWorkflows();};});
function commPreview(fields,form){
 if(!window.COMM_PREVIEW_URL)return Promise.reject(new Error('Preview unavailable'));
 var body=new URLSearchParams();Object.keys(fields).forEach(function(k){body.append(k,fields[k]);});
 var token=Array.from(form.querySelectorAll('input')).filter(function(i){return /^csrf_/i.test(i.name);})[0];
 if(token)body.append(token.name,token.value);else if(window.csrfName)body.append(window.csrfName,window.csrfHash||'');
 return fetch(window.COMM_PREVIEW_URL,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()}).then(function(r){return r.json();});
}
function debouncePreview(form,box,collect,delay){
 var timer,seq=0;
 return function(){
  if(!box)return;clearTimeout(timer);
  timer=setTimeout(function(){
   var id=++seq;
   commPreview(collect(),form).then(function(result){if(id===seq&&result&&result.success&&typeof result.html==='string')box.srcdoc=result.html;}).catch(function(){});
  },delay||320);
 };
}
var form=document.getElementById('comm-composer');if(form){
 var design=form.querySelector('[name=design]'),message=form.querySelector('[name=message]'),subject=form.querySelector('[name=subject]'),preview=document.getElementById('comm-preview');
 var useTemplate=document.getElementById('comm-use-template');
 var refreshPreview=preview&&design?debouncePreview(form,preview,function(){return{
  design:design.value,subject:subject.value,message:message.value,
  button_label:(form.querySelector('[name=button_label]')||{}).value||'',
  button_url:(form.querySelector('[name=button_url]')||{}).value||''}}):null;
 function update(){
  if(!refreshPreview){var smsPreview=document.getElementById('comm-sms-preview');if(smsPreview)smsPreview.textContent=message.value.replace(/\{name\}/g,'Ada').replace(/\{store\}/g,window.COMM_STORE_NAME||'Our store');var count=document.getElementById('comm-sms-count');if(count){var stats=window.MartPointSms(message.value+(window.COMM_SMS_OPTOUT||''));count.textContent=Array.from(message.value).length.toLocaleString()+' typed characters · '+stats.encoding+' · '+stats.segments+' page'+(stats.segments===1?'':'s')+'/recipient (sample)';}return;}
  var choice=(window.COMM_DESIGN_DATA||{})[design.value];
  if(useTemplate)useTemplate.hidden=!(choice&&choice.kind==='built');
  refreshPreview();
 }
 if(useTemplate)useTemplate.onclick=function(){var t=window.COMM_TEMPLATE_DATA[design.value.replace(/^k:/,'')];if(!t)return;message.value=t.message;subject.value=t.subject;update();};
 form.addEventListener('input',update);if(design)design.addEventListener('change',update);update();
}
var builder=document.getElementById('comm-builder');if(builder){
 var blockList=document.getElementById('comm-block-list'),blockAdd=document.getElementById('comm-block-add'),blockCount=document.getElementById('comm-block-count'),blockTemplate=document.getElementById('comm-block-row-template');
 var builderPreview=document.getElementById('comm-builder-preview');
 var limit=Number(blockList.dataset.blockLimit)||12;
 var rows=function(){return Array.from(blockList.querySelectorAll('[data-block-row]'));};
 var fields=function(row){return{type:row.querySelector('.comm-block-type'),text:row.querySelector('.comm-block-text'),label:row.querySelector('.comm-block-label'),url:row.querySelector('.comm-block-url')};};
 var modeOf=function(row){var f=fields(row),option=f.type.querySelector('option[value="'+f.type.value+'"]');return option&&option.dataset.field?option.dataset.field:'text';};
 function syncBlock(row){var mode=modeOf(row);row.querySelector('.comm-block-text-field').hidden=mode==='none'||mode==='both';row.querySelector('.comm-block-cta').hidden=mode!=='both';}
 function blocks(){return rows().map(function(row){var f=fields(row),item={type:f.type.value,text:f.text.value};if(modeOf(row)==='both'){item.label=f.label.value;item.url=f.url.value;}return item;});}
 function refreshBlocks(){var list=rows();if(blockCount)blockCount.textContent=list.length+' of '+limit+' blocks';if(blockAdd)blockAdd.disabled=list.length>=limit;list.forEach(function(row,i){row.querySelector('[data-block-up]').disabled=i===0;row.querySelector('[data-block-down]').disabled=i===list.length-1;});}
 var refreshBuilder=debouncePreview(builder,builderPreview,function(){
  var value=function(name){var el=builder.querySelector('[name="'+name+'"]');return el?el.value:'';};
  var draft={name:value('name'),format:value('format'),category:value('category'),industry:value('industry'),occasion:value('occasion'),subject:value('subject'),preheader:value('preheader'),accent:value('accent'),brand_display:value('brand_display'),cta_label:value('cta_label'),cta_url:value('cta_url'),blocks:JSON.stringify(blocks())};
  draft.show_unsubscribe=Array.from(builder.querySelectorAll('[name=show_unsubscribe]')).some(function(i){return i.type==='checkbox'&&i.checked;})?'1':'0';
  draft.draft='1';return draft;},420);
 if(blockAdd)blockAdd.onclick=function(){
  if(rows().length>=limit)return;
  var holder=document.createElement('div');holder.innerHTML=blockTemplate.innerHTML.trim();
  var row=holder.firstElementChild;blockList.appendChild(row);
  row.querySelector('.comm-block-type').value='paragraph';syncBlock(row);
  if(root.dataset.commMobile==='1')row.querySelectorAll('select').forEach(mobileSelect);
  refreshBlocks();refreshBuilder();
 };
 blockList.addEventListener('click',function(e){var button=e.target.closest?e.target.closest('[data-block-up],[data-block-down],[data-block-remove]'):null;if(!button)return;var row=button.closest('[data-block-row]');
  if(button.hasAttribute('data-block-remove')){if(rows().length<2)return;row.remove();}
  else if(button.hasAttribute('data-block-up')&&row.previousElementSibling)row.previousElementSibling.before(row);
  else if(button.hasAttribute('data-block-down')&&row.nextElementSibling)row.nextElementSibling.after(row);
  refreshBlocks();refreshBuilder();});
 blockList.addEventListener('change',function(e){if(e.target.classList.contains('comm-block-type')){syncBlock(e.target.closest('[data-block-row]'));refreshBuilder();}});
 builder.addEventListener('input',refreshBuilder);builder.addEventListener('change',refreshBuilder);
 builder.addEventListener('submit',function(){builder.querySelector('[name=blocks]').value=JSON.stringify(blocks());});
 rows().forEach(function(row){syncBlock(row);});refreshBlocks();refreshBuilder();
}
var designShelf=root.querySelector('.comm-designs');if(designShelf){
 var designUse='all',designFormat='all',designSearch=document.getElementById('comm-design-search'),family=designShelf.dataset.designFamily||'any';
 function filterDesigns(){designShelf.querySelectorAll('[data-design-card]').forEach(function(card){
  var useOk=designUse==='all'||(designUse==='family'?card.dataset.industry===family:card.dataset.category===designUse);
  var query=designSearch?designSearch.value.trim().toLowerCase():'';
  card.hidden=!(useOk&&(designFormat==='all'||card.dataset.format===designFormat))||(!!query&&(card.dataset.name+' '+card.textContent).toLowerCase().indexOf(query)<0);});}
 if(designSearch)designSearch.oninput=filterDesigns;
 designShelf.querySelectorAll('[data-design-filter]').forEach(function(b){b.onclick=function(){designUse=b.dataset.designFilter;designShelf.querySelectorAll('[data-design-filter]').forEach(function(x){x.classList.toggle('active',x===b);});filterDesigns();};});
 designShelf.querySelectorAll('[data-design-format]').forEach(function(b){b.onclick=function(){designFormat=b.dataset.designFormat;designShelf.querySelectorAll('[data-design-format]').forEach(function(x){x.classList.toggle('active',x===b);});filterDesigns();};});
}
root.querySelectorAll('[data-design-remove]').forEach(function(b){b.onclick=function(){if(!window.cqConfirm){b.form.submit();return;}window.cqConfirm('Remove the design “'+b.dataset.designRemove+'”? Emails you already sent keep their own copy.',{title:'Remove email design',okText:'Remove design',danger:true}).then(function(ok){if(ok)b.form.submit();});};});
var workflowForm=document.getElementById('comm-automation-builder');
if(workflowForm){
 var sourcePicker=workflowForm.querySelector('[name=segment_key]'),channelPicker=workflowForm.querySelector('[name=channel]'),templatePicker=document.getElementById('comm-automation-template');
 function sources(channel,preferred){var options=(window.COMM_AUDIENCE_OPTIONS||{})[channel];if(!options)return;sourcePicker.replaceChildren();var groups={};Object.keys(options).forEach(function(key){if(templatePicker && ((templatePicker.value==='abandoned_cart')!==(key==='abandoned_carts')))return;var item=options[key],group=groups[item.group];if(!group){group=document.createElement('optgroup');group.label=item.group;groups[item.group]=group;sourcePicker.appendChild(group);}var o=document.createElement('option');o.value=key;o.textContent=item.name+' · '+Number(item.eligible).toLocaleString()+' eligible';group.appendChild(o);});if(options[preferred])sourcePicker.value=preferred;sourcePicker.dispatchEvent(new Event('change',{bubbles:true}));}
 function updateFlow(){var cartNote=document.getElementById('comm-cart-workflow-note');if(cartNote)cartNote.hidden=!templatePicker || templatePicker.value!=='abandoned_cart';root.querySelector('[data-flow-audience]').textContent=sourcePicker.options[sourcePicker.selectedIndex]?sourcePicker.options[sourcePicker.selectedIndex].textContent:'No eligible source';root.querySelector('[data-flow-channel]').textContent=channelPicker.value;root.querySelector('[data-flow-interval]').textContent=workflowForm.querySelector('[name=interval_days]').value||'—';root.querySelector('[data-flow-budget]').textContent=workflowForm.querySelector('[name=budget_units]').value||'—';var subject=document.getElementById('comm-automation-subject');subject.hidden=channelPicker.value==='sms';subject.querySelector('input').required=channelPicker.value==='email';}
 channelPicker.addEventListener('change',function(){sources(channelPicker.value,sourcePicker.value);updateFlow();});
 if(templatePicker)templatePicker.addEventListener('change',function(){var t=(window.COMM_AUTOMATION_TEMPLATES||{})[templatePicker.value];if(!t)return;workflowForm.querySelector('[name=name]').value=t.name;workflowForm.querySelector('[name=subject]').value=t.subject;workflowForm.querySelector('[name=message]').value=t.message;workflowForm.querySelector('[name=interval_days]').value=t.interval_days;sources(channelPicker.value,t.segment_key);updateFlow();});
 workflowForm.addEventListener('input',updateFlow);workflowForm.addEventListener('change',updateFlow);updateFlow();
}

var conversationBox=document.getElementById('comm-message-list');if(conversationBox && !pageUrl.searchParams.has('before'))conversationBox.scrollTop=conversationBox.scrollHeight;
function openHash(){var target=document.getElementById(location.hash.slice(1));if(target && target.tagName==='DETAILS')target.open=true;}
window.addEventListener('hashchange',openHash);if(location.hash)openHash();
var reviewId=pageUrl.searchParams.get('review_campaign');if(/^[1-9][0-9]*$/.test(reviewId||'')){var reviewButton=root.querySelector('[data-campaign="'+reviewId+'"]:not([data-test])');pageUrl.searchParams.delete('review_campaign');history.replaceState(null,'',pageUrl.href);if(reviewButton)reviewButton.click();}
// Use the dedicated shell on phone/tablet portrait and desktop shell on landscape.
var mobile=root.dataset.commMobile==='1',query=window.matchMedia('(max-width: 768px), (max-width: 1024px) and (orientation: portrait)');
if(query.matches!==mobile){var u=new URL(location.href);u.searchParams.set('screen',query.matches?'mobile':'desktop');location.replace(u.href);}query.addEventListener('change',function(e){var u=new URL(location.href);u.searchParams.set('screen',e.matches?'mobile':'desktop');location.replace(u.href);});
})();
