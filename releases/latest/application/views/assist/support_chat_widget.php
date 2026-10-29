<?php
$mp_support_store_id = (int)get_current_store_id();
$mp_support_external_id = $this->session->userdata('support_chat_external_id');
if(!$mp_support_external_id){
    $mp_support_external_id = 'web_' . bin2hex(random_bytes(12));
    $this->session->set_userdata('support_chat_external_id', $mp_support_external_id);
}
$mp_support_name = $this->session->userdata('inv_username') ?: 'Merchant';
?>
<style>
  #mp-support-widget.mp-support-widget{position:fixed!important;top:auto!important;right:24px!important;bottom:24px!important;left:auto!important;z-index:10001!important;box-sizing:border-box!important;width:420px!important;max-width:calc(100vw - 32px)!important;height:620px!important;max-height:calc(100vh - 48px)!important;min-height:0!important;display:none!important;flex-direction:column!important;background:#fff!important;border:1px solid #e5e7eb!important;border-radius:16px!important;box-shadow:0 18px 55px rgba(15,23,42,.24)!important;overflow:hidden!important}
  #mp-support-widget.mp-support-widget.open{display:flex!important}
  #mp-support-widget.mp-support-widget.minimized{height:auto!important}
  #mp-support-widget.mp-support-widget.minimized .mp-support-widget-body,#mp-support-widget.mp-support-widget.minimized .mp-support-widget-composer{display:none!important}
  #mp-support-widget .mp-support-widget-head{flex:0 0 auto!important;min-width:0!important}
  #mp-support-widget .mp-support-widget-head>div:first-child{min-width:0!important;overflow:hidden!important}
  #mp-support-widget .mp-support-widget-title{min-width:0!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}
  #mp-support-widget .mp-support-widget-body{box-sizing:border-box!important;min-height:0!important;flex:1 1 auto!important;overflow-y:auto!important}
  #mp-support-widget .mp-support-widget-composer{box-sizing:border-box!important;flex:0 0 auto!important;min-width:0!important}
  #mp-support-file{display:none!important;width:0!important;height:0!important;opacity:0!important;position:absolute!important;pointer-events:none!important}
  .mp-support-widget-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;background:#0f4cbd;color:#fff}
  .mp-support-widget-title{font-weight:700;font-size:15px}.mp-support-widget-sub{font-size:11px;opacity:.8;margin-top:2px}
  .mp-support-widget-actions{display:flex;gap:6px}.mp-support-widget-actions button{border:0;background:transparent;color:#fff;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:15px}.mp-support-widget-actions button:hover{background:rgba(255,255,255,.16)}
  .mp-support-widget-body{flex:1;min-height:0;overflow-y:auto;padding:14px;background:#f3f4f6;display:flex;flex-direction:column;gap:9px}
  .mp-support-widget-empty{margin:auto;text-align:center;color:#6b7280;font-size:13px}.mp-support-msg{max-width:82%;padding:9px 12px;border-radius:11px;font-size:13px;line-height:1.45;white-space:pre-wrap;word-break:break-word}.mp-support-msg.inbound{align-self:flex-start;background:#fff;border:1px solid #e5e7eb}.mp-support-msg.outbound{align-self:flex-end;background:#0f4cbd;color:#fff}
  .mp-support-time{font-size:10px;color:#9ca3af;margin-top:3px}.mp-support-widget-composer{padding:10px;border-top:1px solid #e5e7eb;background:#fff}.mp-support-compose-row{display:flex;gap:7px}.mp-support-compose-row textarea{flex:1;resize:none;min-height:40px;max-height:100px;border:1px solid #d1d5db;border-radius:8px;padding:9px;font:inherit;font-size:13px}.mp-support-compose-row button{border:0;border-radius:8px;background:#0f4cbd;color:#fff;width:42px;cursor:pointer}.mp-support-file-btn{opacity:.5;cursor:not-allowed!important}.mp-support-file-btn.approved{opacity:1;cursor:pointer!important}.mp-support-bridge-error{padding:8px 10px;border-radius:8px;background:#fff1f2;color:#b91c1c;font-size:11px;line-height:1.4}.mp-support-end{margin-top:8px;width:100%;border:0;background:#fff1f2;color:#b91c1c;border-radius:7px;padding:7px;font-size:11px;cursor:pointer}.mp-support-end-confirm{display:none;margin-top:8px;padding:10px;border:1px solid #fecdd3;border-radius:9px;background:#fff1f2;color:#881337;font-size:12px}.mp-support-end-confirm.open{display:block}.mp-support-end-confirm-actions{display:flex;gap:7px;margin-top:8px}.mp-support-end-confirm-actions button{flex:1;border:0;border-radius:7px;padding:7px;font-size:11px;font-weight:600;cursor:pointer}.mp-support-end-cancel{background:#fff;color:#6b7280}.mp-support-end-yes{background:#be123c;color:#fff}.mp-support-ended{text-align:center;color:#6b7280;font-size:12px;padding:9px}
  @media(max-width:768px){#mp-support-widget.mp-support-widget{top:auto!important;right:12px!important;bottom:calc(88px + env(safe-area-inset-bottom,0px))!important;left:12px!important;width:auto!important;max-width:none!important;height:min(560px,calc(100vh - 120px))!important;max-height:calc(100vh - 120px)!important}}
</style>

<div id="mp-support-widget" class="mp-support-widget" aria-label="Live Chat with Support">
  <div class="mp-support-widget-head">
    <div><div class="mp-support-widget-title">Live Chat with Support</div><div id="mp-support-widget-status" class="mp-support-widget-sub">Support team</div></div>
    <div class="mp-support-widget-actions">
      <button type="button" onclick="MPSupportChat.minimize()" title="Minimize">−</button>
      <button type="button" onclick="MPSupportChat.cancel()" title="Close">×</button>
    </div>
  </div>
  <div id="mp-support-widget-body" class="mp-support-widget-body"><div class="mp-support-widget-empty">Start a conversation with MartPoint Support.</div></div>
  <div id="mp-support-widget-composer" class="mp-support-widget-composer">
    <div class="mp-support-compose-row"><button id="mp-support-file-btn" class="mp-support-file-btn" type="button" onclick="MPSupportChat.chooseFile()" title="Waiting for Support to request a file" aria-disabled="true"><i class="fa fa-paperclip"></i></button><button type="button" onclick="MPSupportChat.toggleVoice()" title="Record voice"><i id="mp-support-voice-icon" class="fa fa-microphone"></i></button><textarea id="mp-support-widget-input" rows="1" placeholder="Type a message..."></textarea><button type="button" onclick="MPSupportChat.send()" title="Send"><i class="fa fa-paper-plane"></i></button></div>
    <input id="mp-support-file" type="file" hidden accept="image/*,.pdf,.txt,audio/*" onchange="MPSupportChat.upload(this.files[0])">
    <button type="button" class="mp-support-end" onclick="MPSupportChat.end()">End this chat</button>
    <div id="mp-support-end-confirm" class="mp-support-end-confirm" role="dialog" aria-label="Confirm ending chat">
      <div>End this conversation? You can start a new chat later.</div>
      <div class="mp-support-end-confirm-actions"><button type="button" class="mp-support-end-cancel" onclick="MPSupportChat.cancelEnd()">Keep chatting</button><button type="button" class="mp-support-end-yes" onclick="MPSupportChat.confirmEnd()">End chat</button></div>
    </div>
  </div>
</div>

<script>
(function(){
  var root=document.getElementById('mp-support-widget');
  if(!root || window.MPSupportChat) return;
  var body=document.getElementById('mp-support-widget-body'), input=document.getElementById('mp-support-widget-input'), status=document.getElementById('mp-support-widget-status'), fileInput=document.getElementById('mp-support-file'), fileButton=document.getElementById('mp-support-file-btn'), endConfirm=document.getElementById('mp-support-end-confirm');
  var base=<?= json_encode(base_url()); ?>, storeId=<?= $mp_support_store_id; ?>, externalId=<?= json_encode($mp_support_external_id); ?>, name=<?= json_encode($mp_support_name); ?>, timer=null, ended=false, recorder=null, recordingChunks=[], uploadApproved=false, knownMessageIds={}, chatLoaded=false;
  function esc(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML;}
  function scroll(){body.scrollTop=body.scrollHeight;}
  function setBridgeStatus(message, isError){status.textContent=message;status.style.color=isError?'#fecaca':'';}
  function setFileAvailability(approved){uploadApproved=approved;if(!fileButton)return;fileButton.classList.toggle('approved',approved);fileButton.setAttribute('aria-disabled',approved?'false':'true');fileButton.title=approved?'Upload an approved file':'Waiting for Support to request a file';}
  function render(messages){
    body.innerHTML=''; setFileAvailability(false);
    if(!messages.length){setFileAvailability(false);body.innerHTML='<div class="mp-support-widget-empty">Start a conversation with MartPoint Support.</div>';return;}
    var latestApproval=0, latestFile=0;
    messages.forEach(function(m){var wrap=document.createElement('div');var out=m.direction==='outbound';var stamp=new Date(m.created_at).getTime();knownMessageIds[m.id]=true;if(m.message_type==='file_request'&&out)latestApproval=Math.max(latestApproval,stamp);if(m.attachment_url&&m.message_type!=='voice'&&!out)latestFile=Math.max(latestFile,stamp);wrap.innerHTML='<div class="mp-support-msg '+(out?'outbound':'inbound')+'">'+esc(m.message)+'</div><div class="mp-support-time" style="text-align:'+(out?'right':'left')+'">'+(out?'Support':'You')+' · '+new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})+'</div>';if(m.attachment_url){var media=m.attachment_mime&&m.attachment_mime.indexOf('audio/')===0?document.createElement('audio'):document.createElement('a');media.className='mp-support-msg';if(media.tagName==='A'){media.href=m.attachment_url;media.target='_blank';media.rel='noreferrer';media.textContent=m.attachment_name||'Open attachment';}else{media.controls=true;media.src=m.attachment_url;}wrap.appendChild(media);}if(m.message_type==='file_request'&&out){var request=document.createElement('button');request.className='mp-support-end';request.textContent='Choose an approved file';request.onclick=function(){MPSupportChat.chooseFile();};wrap.appendChild(request);}body.appendChild(wrap);});setFileAvailability(latestApproval>latestFile);scroll();
  }
  function api(path,opts){opts=opts||{};opts.credentials='same-origin';opts.headers=Object.assign({'Content-Type':'application/json'},opts.headers||{});return fetch(base+path,opts).then(function(r){return r.text().then(function(raw){try{return JSON.parse(raw);}catch(e){return {success:false,error:/logout|login|sign in/i.test(raw)?'Your session has expired. Refresh the page and sign in again.':'We could not load support chat. Please refresh and try again shortly.'};}});});}
  function chime(){try{var C=window.AudioContext||window.webkitAudioContext;if(!C)return;var c=new C(),o=c.createOscillator(),g=c.createGain();o.type='sine';o.frequency.setValueAtTime(740,c.currentTime);o.frequency.exponentialRampToValueAtTime(980,c.currentTime+.08);g.gain.setValueAtTime(.0001,c.currentTime);g.gain.exponentialRampToValueAtTime(.07,c.currentTime+.015);g.gain.exponentialRampToValueAtTime(.0001,c.currentTime+.18);o.connect(g).connect(c.destination);o.start();o.stop(c.currentTime+.2);setTimeout(function(){c.close();},350);}catch(e){}}
  var pollBusy=false;
  function load(){if(pollBusy)return;pollBusy=true;api('support/chat/messages?store_id='+storeId+'&external_id='+encodeURIComponent(externalId)).then(function(j){var p=j.data||j;if(j.success&&Array.isArray(p.messages)){if(chatLoaded&&p.messages.some(function(m){return !knownMessageIds[m.id]&&m.direction==='outbound';}))chime();render(p.messages);chatLoaded=true;if(!input.disabled)setBridgeStatus('Support team',false);}else{setBridgeStatus(j.message||j.error||'Support chat is temporarily unavailable. Please try again shortly.',true);}}).catch(function(){setBridgeStatus('Support chat is temporarily unavailable. Please try again shortly.',true);}).finally(function(){pollBusy=false;});}
  window.MPSupportChat={
    open:function(){root.classList.add('open');root.classList.remove('minimized');ended=false;status.textContent='Support team';document.getElementById('mp-support-widget-composer').style.display='';load();if(timer)clearInterval(timer);timer=setInterval(load,5000);input.focus();},
    minimize:function(){root.classList.add('minimized');},
    cancel:function(){root.classList.remove('open');if(timer){clearInterval(timer);timer=null;}},
    chooseFile:function(){if(ended)return;if(!uploadApproved){setBridgeStatus('Support must request a file before upload is enabled',true);return;}fileInput.click();},
    upload:function(file,type){if(!file||ended)return;if(file.size>10*1024*1024){alert('Files must be 10MB or smaller.');fileInput.value='';return;}var fd=new FormData();fd.append('store_id',storeId);fd.append('external_id',externalId);fd.append('message_type',type||'file');fd.append('file',file);fetch(base+'support/chat/upload',{method:'POST',credentials:'same-origin',body:fd,headers:{'X-CSRF-TOKEN':window.csrfHash||''}}).then(function(r){return r.json();}).then(function(j){if(j.success)load();else if(j.code==='csrf')location.reload();}).catch(function(){}).finally(function(){fileInput.value='';});},
    toggleVoice:function(){if(ended)return;if(recorder&&recorder.state==='recording'){recorder.stop();return;}if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){alert('Voice recording is not supported in this browser.');return;}navigator.mediaDevices.getUserMedia({audio:true}).then(function(stream){recordingChunks=[];recorder=new MediaRecorder(stream);recorder.ondataavailable=function(e){if(e.data.size)recordingChunks.push(e.data);};recorder.onstop=function(){stream.getTracks().forEach(function(t){t.stop();});document.getElementById('mp-support-voice-icon').className='fa fa-microphone';var blob=new Blob(recordingChunks,{type:recorder.mimeType||'audio/webm'});var voice=new File([blob],'voice-message.webm',{type:blob.type});MPSupportChat.upload(voice,'voice');};recorder.start();document.getElementById('mp-support-voice-icon').className='fa fa-stop';}).catch(function(){alert('Microphone permission was not granted.');});},
    send:function(){var text=input.value.trim();if(!text||ended)return;input.disabled=true;setBridgeStatus('Sending…',false);api('support/chat/send',{method:'POST',body:JSON.stringify({store_id:storeId,external_id:externalId,customer_name:name,message:text,[window.csrfName||'csrf_test_name']:window.csrfHash||''}),headers:{'X-CSRF-TOKEN':window.csrfHash||''}}).then(function(j){if(j.success){input.value='';setBridgeStatus('Support team',false);load();}else if(j.code==='csrf'){location.reload();}else{setBridgeStatus(j.message||j.error||'We could not send your message. Please try again shortly.',true);}}).catch(function(){setBridgeStatus('We could not send your message. Please try again shortly.',true);}).finally(function(){input.disabled=false;input.focus();});},
    end:function(){if(ended)return;endConfirm.classList.add('open');},
    cancelEnd:function(){endConfirm.classList.remove('open');},
    confirmEnd:function(){if(ended)return;endConfirm.classList.remove('open');setBridgeStatus('Ending chat…',false);api('support/chat/end',{method:'POST',body:JSON.stringify({store_id:storeId,external_id:externalId,[window.csrfName||'csrf_test_name']:window.csrfHash||''}),headers:{'X-CSRF-TOKEN':window.csrfHash||''}}).then(function(j){if(j.success){ended=true;status.textContent='Chat ended';body.innerHTML='<div class="mp-support-ended">This conversation has ended.</div>';document.getElementById('mp-support-widget-composer').style.display='none';}else{setBridgeStatus(j.message||j.error||'We could not end this conversation. Please try again shortly.',true);}}).catch(function(){setBridgeStatus('We could not reach support just now. Please try again shortly.',true);});}
  };
  input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();MPSupportChat.send();}});
})();
</script>
