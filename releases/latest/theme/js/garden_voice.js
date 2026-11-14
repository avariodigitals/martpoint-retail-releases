(function(){
'use strict';
if(window.mpGardenVoiceLoaded)return;window.mpGardenVoiceLoaded=true;
var Speech=window.SpeechRecognition||window.webkitSpeechRecognition,active=null,sequence=0;

// --- Search-aware dictation -------------------------------------------------
// Voice is great for filling a note verbatim, but a search box should behave
// like a typed search: loose, token-based matching instead of one exact phrase.
// These helpers are shared with the workspace filters (garden/scripts.php)
// through window.mpSearchMatch / window.mpSearchTokens / window.mpSearchClean.
var SEARCH_STOP={search:1,find:1,look:1,lookup:1,show:1,display:1,filter:1,list:1,open:1,go:1,everything:1,for:1,me:1,the:1,a:1,an:1,of:1,to:1,in:1,on:1,with:1,and:1,please:1,my:1,our:1,any:1,all:1};
function searchTokens(query){
 var text=String(query==null?'':query).toLowerCase().replace(/[^a-z0-9\s]+/g,' ').replace(/\s+/g,' ').trim();
 if(!text)return [];
 var out=[];
 text.split(' ').forEach(function(word){
  if(!word||word.length<2||SEARCH_STOP[word])return;
  if(word.length>3&&word.slice(-1)==='s')word=word.slice(0,-1);
  if(out.indexOf(word)===-1)out.push(word);
 });
 return out;
}
function searchMatch(text,query){
 var tokens=searchTokens(query);if(!tokens.length)return true;
 var hay=String(text==null?'':text).toLowerCase();
 for(var i=0;i<tokens.length;i++){if(hay.indexOf(tokens[i])===-1)return false;}
 return true;
}
function searchClean(value){
 return String(value==null?'':value).replace(/\s+/g,' ').trim()
  .replace(/^(?:please\s+|kindly\s+)?(?:can\s+you\s+)?(?:search(?:\s+for)?|find|look\s+up|lookup|show(?:\s+me)?|filter(?:\s+by)?|display|list|open|go\s+to)\s+/i,'')
  .trim();
}
function isSearchField(field){
 if(!field)return false;
 if(String(field.type||'').toLowerCase()==='search')return true;
 var placeholder=field.getAttribute?field.getAttribute('placeholder'):field.placeholder;
 var hay=((field.name||'')+' '+(field.id||'')+' '+(placeholder||'')).toLowerCase();
 return /(^|[^a-z])(search|find|filter|query)([^a-z]|$)/.test(hay);
}
function isVoiceEligibleField(field){
 if(!field)return false;
 if(isSearchField(field))return true;
 if(String(field.tagName||'').toLowerCase()==='textarea')return true;
 if(String(field.type||'').toLowerCase()!=='text')return false;
 var hay=((field.name||'')+' '+(field.id||'')+' '+(field.getAttribute&&field.getAttribute('placeholder')||'')).toLowerCase();
 // Never put a microphone beside money, quantities, codes, dates or contact
 // credentials. Dictation is reserved for fields where a sentence or name is
 // genuinely useful.
 if(/amount|discount|charge|tax|price|cost|total|balance|quantity|qty|rate|percent|percentage|money|payment|stock|number|code|date|phone|email|password|pin|token|secret|account|invoice|receipt|reference/.test(hay))return false;
 return /name|title|description|note|reason|comment|message|instruction|address|location|site|customer|supplier|detail|observation|outcome|summary/.test(hay);
}
window.mpSearchTokens=searchTokens;window.mpSearchMatch=searchMatch;window.mpSearchClean=searchClean;window.mpIsSearchField=isSearchField;

function attach(button,field,status){
 if(button.dataset.voiceBound)return;button.dataset.voiceBound='1';button.type='button';button.setAttribute('aria-pressed','false');
 button.title='Your browser may process speech online. Review dictated text before saving.';
 if(!Speech||!window.isSecureContext){button.disabled=true;button.title='Voice needs a supported browser over HTTPS. You can still type.';status.textContent='Voice unavailable; you can type.';return;}
 button.addEventListener('click',function(){
  if(active&&active.button===button){active.recognition.stop();return;}
  if(active)active.recognition.abort();
  if(field.disabled||field.readOnly)return;
  var recognition=new Speech(),start=field.selectionStart,end=field.selectionEnd,original=field.value;
  var session={recognition:recognition,button:button};active=session;
  recognition.lang=document.documentElement.lang==='en'?'en-NG':(document.documentElement.lang||'en-NG');recognition.interimResults=false;recognition.continuous=false;
  recognition.onstart=function(){button.setAttribute('aria-pressed','true');status.textContent='Listening…';};
  recognition.onresult=function(event){
   if(active!==session||field.disabled||field.readOnly||!field.isConnected)return;
   var text='';for(var i=event.resultIndex;i<event.results.length;i++)if(event.results[i].isFinal)text+=event.results[i][0].transcript+' ';
   text=text.trim();if(!text)return;
   // A dictated search should behave like a typed search: drop spoken command
   // words ("search for …") so the token filter can actually match records.
   if(isSearchField(field)){var cleaned=searchClean(text);if(cleaned)text=cleaned;}
   // Preserve anything typed while the speech service was working.
   if(field.value!==original||start===null){start=field.value.length;end=start;}
   var before=field.value.slice(0,start),after=field.value.slice(end),insert=(before&&!/\s$/.test(before)?' ':'')+text+(after&&!/^\s/.test(after)?' ':'');
   if(field.maxLength>=0)insert=insert.slice(0,Math.max(0,field.maxLength-(before.length+after.length)));
   field.value=before+insert+after;
   field.dispatchEvent(new Event('input',{bubbles:true}));field.dispatchEvent(new Event('change',{bubbles:true}));
   status.textContent='Text added. Review before saving.';
  };
  recognition.onerror=function(event){status.textContent=event.error==='not-allowed'?'Microphone access was not granted. You can type instead.':'Voice could not finish. Retry or type instead.';};
  recognition.onend=function(){button.setAttribute('aria-pressed','false');if(active===session)active=null;if(status.textContent==='Listening…')status.textContent='No text captured. Retry or type instead.';};
  try{recognition.start();}catch(error){status.textContent='Unable to start voice. You can type instead.';active=null;}
 });
}
function scan(){
 document.querySelectorAll('[data-garden-voice]').forEach(function(button){var field=document.getElementById(button.dataset.gardenVoice);if(!field)return;field.dataset.gardenVoiceReady='1';attach(button,field,button.nextElementSibling);});
 document.querySelectorAll('textarea,input:not([type]),input[type=text],input[type=search],input[type=email],input[type=tel],input[type=url]').forEach(function(field){
  if(!isVoiceEligibleField(field))return;
  if(field.dataset.gardenVoiceReady||field.disabled||field.readOnly||field.hidden||field.closest('[data-no-voice],.select2-container,.dataTables_length,#mp-assist-panel')||/password|passphrase|token|secret|approval_pin|otp/i.test(field.name||field.id||''))return;
  field.dataset.gardenVoiceReady='1';if(!field.id)field.id='mp-garden-voice-'+(++sequence);
  // The mic sits inside the field it fills, like a native trailing control, so
  // no page ever shows a loose "Voice" button hanging off the side of an input.
  var wrap=document.createElement('span');wrap.className='mp-voice-field'+(field.tagName==='TEXTAREA'?' mp-voice-field-area':'');
  field.parentNode.insertBefore(wrap,field);wrap.appendChild(field);
  var button=document.createElement('button');button.type='button';button.className='mp-voice-btn';button.setAttribute('aria-controls',field.id);
  button.innerHTML='<i class="fa fa-microphone" aria-hidden="true"></i><i class="fa fa-stop" aria-hidden="true"></i>';
  var label=(field.labels&&field.labels[0]?field.labels[0].textContent:field.getAttribute('aria-label')||field.placeholder||field.name||'this field').trim().slice(0,100);
  button.setAttribute('aria-label','Dictate into '+label);
  var status=document.createElement('small');status.className='mp-voice-status';status.setAttribute('role','status');status.setAttribute('aria-live','polite');
  wrap.appendChild(button);wrap.after(status);attach(button,field,status);
 });
}
function init(){scan();var pending=false;new MutationObserver(function(){if(pending)return;pending=true;requestAnimationFrame(function(){pending=false;scan();});}).observe(document.body,{childList:true,subtree:true});}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
window.addEventListener('pagehide',function(){if(active)active.recognition.abort();});
})();
