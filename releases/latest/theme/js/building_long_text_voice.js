/* Building Materials staff workspace only. No audio is stored by MartPoint. */
(function(){
  'use strict';
  if(window.mpBuildingLongTextVoiceLoaded || !document.querySelector('script[data-bm-long-text-voice]')) return;
  window.mpBuildingLongTextVoiceLoaded=true;
  var Speech=window.SpeechRecognition||window.webkitSpeechRecognition;
  var active=null,sequence=0,controls=new Map();
  var privacy='Voice may be processed online by your browser. Review before saving.';
  function eligible(field){
    if(field.tagName!=='TEXTAREA' || field.hidden || field.disabled || field.readOnly || (field.matches && field.matches(':disabled'))) return false;
    if(/^mp-(assist|support)/i.test(field.id||''))return false;
    if(field.closest('[data-no-voice],#mp-assist-panel,.CodeMirror,.ace_editor,.note-editor,.tox') || field.getAttribute('aria-hidden')==='true') return false;
    var key=(field.name||'')+' '+(field.id||'');
    if(/(?:^|[_\[\s-])(pin|otp)(?:[_\]\s-]|$)/i.test(key))return false;
    if(/password|passphrase|secret|token|api.?key|approval.?pin|\botp\b|csrf|html|javascript|css|json|source.?code/i.test(key) || /(?:^|[_\[\s-])scripts?(?:[_\]\s-]|$)/i.test(key)) return false;
    if(field.maxLength>=0 && field.maxLength<=255 && field.getAttribute('data-bm-long-text')===null) return false;
    // Theme headings may use a textarea for line breaks, but are not long prose.
    if(/(?:^|[_\[\s-])(title|heading|eyebrow|label|button)(?:[_\]\s-]|$)/i.test(key)) return false;
    return true;
  }
  function labelFor(field){
    var label=field.getAttribute('aria-label');
    if(!label && field.labels && field.labels[0]){
      var copy=field.labels[0].cloneNode(true);
      copy.querySelectorAll('input,textarea,select,button,small').forEach(function(node){node.remove();});
      label=copy.textContent;
    }
    return String(label||field.placeholder||field.name||'long text').replace(/\s+/g,' ').trim().slice(0,100);
  }
  function resetControl(session,message){
    session.button.setAttribute('aria-pressed','false');
    session.button.setAttribute('aria-label','Dictate '+session.label);
    session.button.title='Start dictation. '+privacy;
    if(message) session.status.textContent=message;
  }
  function cancel(message){
    if(!active) return;
    var previous=active;active=null;
    resetControl(previous,message||'Dictation stopped. Review any text already added.');
    try{previous.recognition.abort();}catch(ignore){}
  }
  function start(control){
    if(active && active.button===control.button){
      control.status.textContent='Finishing dictation…';
      try{active.recognition.stop();}catch(ignore){cancel();}
      return;
    }
    cancel();
    var field=control.field;
    if(!eligible(field) || !field.isConnected) return;
    var recognition=new Speech();
    var session={recognition:recognition,field:field,button:control.button,status:control.status,label:control.label,value:field.value,start:field.selectionStart,end:field.selectionEnd,seen:{}};
    active=session;
    recognition.lang=document.documentElement.lang==='en'?'en-NG':(document.documentElement.lang||'en-NG');
    recognition.interimResults=false;recognition.continuous=false;
    control.status.textContent='Starting microphone… '+privacy;
    recognition.onstart=function(){
      if(active!==session) return;
      session.button.setAttribute('aria-pressed','true');
      session.button.setAttribute('aria-label','Stop dictation for '+session.label);
      session.button.title='Stop dictation';
      session.status.textContent='Listening… Click the microphone again to stop.';
    };
    recognition.onresult=function(event){
      if(active!==session || !field.isConnected || !eligible(field) || !field.getClientRects().length) return;
      var text=[];
      for(var i=event.resultIndex;i<event.results.length;i++){
        if(event.results[i].isFinal && !session.seen[i]){session.seen[i]=true;text.push(event.results[i][0].transcript);}
      }
      text=text.join(' ').trim();if(!text) return;
      // Preserve edits made while the browser was processing speech.
      var from=session.start,to=session.end;
      if(field.value!==session.value || from===null){from=field.value.length;to=from;}
      var before=field.value.slice(0,from),after=field.value.slice(to);
      var insert=(before&&!/\s$/.test(before)?' ':'')+text+(after&&!/^\s/.test(after)?' ':'');
      if(field.maxLength>=0) insert=insert.slice(0,Math.max(0,field.maxLength-before.length-after.length));
      if(!insert){session.status.textContent='This field has reached its text limit. Review or shorten it before dictating more.';return;}
      field.value=before+insert+after;
      session.value=field.value;session.start=before.length+insert.length;session.end=session.start;
      field.setSelectionRange(session.start,session.end);
      field.dispatchEvent(new Event('input',{bubbles:true}));
      field.dispatchEvent(new Event('change',{bubbles:true}));
      session.status.textContent='Text added. Review and save when ready.';
    };
    recognition.onerror=function(event){
      if(active!==session) return;
      session.status.textContent=event.error==='not-allowed'||event.error==='service-not-allowed'?'Microphone access was not granted. You can type instead.':'Voice could not finish. Retry or type instead.';
    };
    recognition.onend=function(){
      if(active!==session) return;
      active=null;resetControl(session);
      if(/^(Listening|Starting|Finishing)/.test(session.status.textContent))session.status.textContent='No text captured. Retry or type instead.';
    };
    try{recognition.start();}catch(error){active=null;resetControl(session,'Unable to start voice. You can type instead.');}
  }
  function scan(){
    controls.forEach(function(control,field){
      if(!field.isConnected){if(active && active.field===field)cancel();controls.delete(field);return;}
      var enabled=eligible(field);
      control.wrap.hidden=!enabled;control.status.hidden=!enabled;
      if(!enabled && active && active.field===field)cancel();
      if(!enabled){
        // Preserve the original editable control if its state changes later.
        field.setAttribute('aria-describedby',(field.getAttribute('aria-describedby')||'').split(/\s+/).filter(function(id){return id && id!==control.status.id;}).join(' '));
        control.wrap.before(field);control.wrap.remove();control.status.remove();
        controls.delete(field);delete field.dataset.bmVoiceReady;
      }
    });
    document.querySelectorAll('textarea').forEach(function(field){
      if(!eligible(field) || field.dataset.bmVoiceReady) return;
      var label=labelFor(field);
      // Keep an implicit label's new button text out of the textarea's name.
      if(!field.getAttribute('aria-label'))field.setAttribute('aria-label',label);
      field.dataset.bmVoiceReady='1';
      if(!field.id){do{field.id='bm-long-text-'+(++sequence);}while(document.getElementById(field.id)&&document.getElementById(field.id)!==field);}
      var wrap=document.createElement('span');wrap.className='bm-voice-field';
      field.parentNode.insertBefore(wrap,field);wrap.appendChild(field);
      var button=document.createElement('button');button.type='button';button.className='bm-voice-button';
      button.setAttribute('aria-controls',field.id);button.setAttribute('aria-pressed','false');button.setAttribute('aria-label','Dictate '+label);button.title='Start dictation. '+privacy;
      button.innerHTML='<svg class="bm-voice-mic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v2a7 7 0 0014 0v-2M12 19v3M8 22h8"/></svg><svg class="bm-voice-stop" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="5" y="5" width="14" height="14" rx="2"/></svg>';
      var status=document.createElement('small');status.className='bm-voice-status';status.id=field.id+'-voice-status';
      status.setAttribute('role','status');status.setAttribute('aria-live','polite');status.textContent=privacy;
      field.setAttribute('aria-describedby',((field.getAttribute('aria-describedby')||'')+' '+status.id).trim());
      wrap.appendChild(button);wrap.after(status);
      var control={field:field,wrap:wrap,button:button,status:status,label:label};controls.set(field,control);
      if(!Speech || !window.isSecureContext){button.disabled=true;button.title='Voice needs a supported browser over HTTPS. You can still type.';status.textContent='Voice unavailable in this browser. You can still type.';}
      else button.addEventListener('click',function(event){event.preventDefault();start(control);});
    });
  }
  function init(){
    scan();var pending=false;
    new MutationObserver(function(){if(pending)return;pending=true;requestAnimationFrame(function(){pending=false;scan();});}).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['disabled','readonly','hidden','data-no-voice']});
    document.addEventListener('submit',function(){cancel('Dictation stopped before saving.');},true);
    document.addEventListener('reset',function(){cancel();},true);
    document.addEventListener('visibilitychange',function(){if(document.hidden)cancel();});
  }
  window.addEventListener('pagehide',function(){cancel();});
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
