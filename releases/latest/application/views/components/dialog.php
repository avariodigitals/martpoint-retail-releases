<?php /* Shared modern in-app dialog + toast skin. Replaces window.prompt()/alert()/confirm().
         Plain JS only: this partial is loaded by desktop pages (jQuery present) and by
         mobile pages (no jQuery), so it must stand on its own.
         Exposes window.cqPrompt(opts) -> Promise<string|string[]|boolean|null>,
                 window.cqConfirm(message, opts) -> Promise<true|null>,
                 window.mpPrompt / window.mpConfirmAsync (aliases).
         Included more than once per page (bottom_nav + mp_alert) — emit only once. */
   if(!empty($GLOBALS['__mp_dialog_loaded'])){ return; }
   $GLOBALS['__mp_dialog_loaded'] = true;
?>
<style>
  .cq-prompt-wrap{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:20px 14px;z-index:100050;}
  .cq-prompt-wrap.open{display:flex;}
  .cq-prompt-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);opacity:0;transition:opacity .18s ease;}
  .cq-prompt-wrap.open .cq-prompt-backdrop{opacity:1;}
  .cq-prompt-card{position:relative;width:100%;max-width:420px;background:#fff;border-radius:18px;padding:22px;box-shadow:0 28px 70px rgba(15,23,42,.28);transform:translateY(10px) scale(.97);opacity:0;transition:all .2s cubic-bezier(.16,1,.3,1);font-family:inherit;}
  .cq-prompt-wrap.open .cq-prompt-card{transform:translateY(0) scale(1);opacity:1;}
  .cq-prompt-card h4{margin:0 0 6px;font-size:17px;font-weight:700;color:#0F172A;}
  .cq-prompt-card p{margin:0 0 16px;font-size:13px;line-height:1.5;color:#64748B;white-space:pre-line;}
  .cq-prompt-body textarea,.cq-prompt-body input[type=text]{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #E2E8F0;border-radius:12px;font-size:15px;font-family:inherit;color:#0F172A;background:#fff;outline:none;transition:border-color .15s, box-shadow .15s;}
  .cq-prompt-body textarea:focus,.cq-prompt-body input[type=text]:focus{border-color:#0D9488;box-shadow:0 0 0 3px rgba(13,148,136,.15);}
  .cq-prompt-opts{display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;}
  .cq-prompt-opt{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid #E2E8F0;border-radius:12px;font-size:14px;color:#0F172A;cursor:pointer;transition:all .14s;}
  .cq-prompt-opt:hover{background:#F8FAFC;}
  .cq-prompt-opt.sel{border-color:#0D9488;background:#F0FDFA;font-weight:600;}
  .cq-prompt-opt input{accent-color:#0D9488;width:17px;height:17px;margin:0;}
  .cq-prompt-err{display:none;margin:10px 0 0;font-size:12px;font-weight:600;color:#DC2626;}
  .cq-prompt-actions{display:flex;gap:10px;margin-top:20px;}
  .cq-prompt-actions button{flex:1;padding:13px 0;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;transition:filter .15s;}
  .cq-prompt-actions button:hover{filter:brightness(.96);}
  .cq-prompt-cancel{background:#F1F5F9;color:#334155;}
  .cq-prompt-ok{background:#0D9488;color:#fff;}
  .cq-prompt-ok.danger{background:#EF4444;}

  /* Modern toasts — toastr keeps its markup, this replaces its skin. */
  #toast-container > div{border-radius:12px!important;box-shadow:0 14px 38px rgba(15,23,42,.16)!important;padding:13px 17px!important;font-size:13px!important;font-weight:600!important;line-height:1.45;background-image:none!important;opacity:1!important;border:1px solid transparent;}
  #toast-container > .toast-success{background-color:#F0FDF4!important;color:#065F46!important;border-color:#10B981!important;}
  #toast-container > .toast-error{background-color:#FEF2F2!important;color:#991B1B!important;border-color:#EF4444!important;}
  #toast-container > .toast-warning{background-color:#FFFBEB!important;color:#92400E!important;border-color:#F59E0B!important;}
  #toast-container > .toast-info{background-color:#EFF6FF!important;color:#1E40AF!important;border-color:#3B82F6!important;}
  #toast-container > div:hover{filter:brightness(.98);}
  .toast-close-button{opacity:.55!important;color:inherit!important;text-shadow:none!important;font-size:15px!important;}

  /* Self-contained toasts for pages without toastr / mp_alert. */
  .cq-toast-rail{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100060;display:flex;flex-direction:column;gap:10px;width:min(92vw,420px);pointer-events:none;}
  .cq-toast{display:flex;align-items:flex-start;gap:11px;padding:13px 15px;border-radius:14px;background:#fff;border:1px solid #E2E8F0;box-shadow:0 16px 40px rgba(15,23,42,.18);font-size:13.5px;font-weight:600;line-height:1.45;color:#0F172A;opacity:0;transform:translateY(-10px);transition:all .22s cubic-bezier(.16,1,.3,1);pointer-events:auto;}
  .cq-toast.show{opacity:1;transform:translateY(0);}
  .cq-toast i{font-size:15px;line-height:1.4;}
  .cq-toast.success{background:#F0FDF4;border-color:#10B981;color:#065F46;}
  .cq-toast.success i{color:#10B981;}
  .cq-toast.danger,.cq-toast.error{background:#FEF2F2;border-color:#EF4444;color:#991B1B;}
  .cq-toast.danger i,.cq-toast.error i{color:#EF4444;}
  .cq-toast.warning{background:#FFFBEB;border-color:#F59E0B;color:#92400E;}
  .cq-toast.warning i{color:#F59E0B;}
  .cq-toast.info{background:#EFF6FF;border-color:#3B82F6;color:#1E40AF;}
  .cq-toast.info i{color:#3B82F6;}
</style>

<div class="cq-prompt-wrap" id="cqPromptWrap">
  <div class="cq-prompt-backdrop"></div>
  <div class="cq-prompt-card" role="dialog" aria-modal="true">
    <h4 id="cqPromptTitle"></h4>
    <p id="cqPromptMsg"></p>
    <div class="cq-prompt-body" id="cqPromptBody"></div>
    <div class="cq-prompt-err" id="cqPromptErr"></div>
    <div class="cq-prompt-actions">
      <button type="button" class="cq-prompt-cancel" id="cqPromptCancel">Cancel</button>
      <button type="button" class="cq-prompt-ok" id="cqPromptOk">Confirm</button>
    </div>
  </div>
</div>

<script>
(function(){
  if(window.cqPromptReady) return;
  window.cqPromptReady = true;

  var wrap = document.getElementById('cqPromptWrap');
  var elTitle = document.getElementById('cqPromptTitle');
  var elMsg = document.getElementById('cqPromptMsg');
  var elBody = document.getElementById('cqPromptBody');
  var elErr = document.getElementById('cqPromptErr');
  var elOk = document.getElementById('cqPromptOk');
  var elCancel = document.getElementById('cqPromptCancel');
  var resolveFn = null, opts = null;

  function open(){
    wrap.classList.add('open');
    setTimeout(function(){
      var f = elBody.querySelector('textarea, input');
      if(f) f.focus();
    }, 60);
  }
  function close(val){
    wrap.classList.remove('open');
    var r = resolveFn; resolveFn = null;
    if(r) r(val);
  }
  function fail(msg){
    elErr.textContent = msg;
    elErr.style.display = 'block';
  }

  elCancel.addEventListener('click', function(){ close(null); });
  wrap.querySelector('.cq-prompt-backdrop').addEventListener('click', function(){ close(null); });
  document.addEventListener('keydown', function(e){
    if(!wrap.classList.contains('open')) return;
    if(e.key === 'Escape'){ e.preventDefault(); close(null); }
    if(e.key === 'Enter' && opts && !opts.multiline && !opts.options && e.target.tagName !== 'BUTTON'){
      e.preventDefault(); submit();
    }
  });

  function submit(){
    elErr.style.display = 'none';
    if(opts.options){
      var picked = [];
      elBody.querySelectorAll('input:checked').forEach(function(i){ picked.push(i.value); });
      if(!picked.length){ fail('Choose at least one option'); return; }
      close(opts.multi ? picked : picked[0]);
      return;
    }
    if(opts.confirm){ close(true); return; }
    var field = elBody.querySelector('textarea, input[type=text]');
    var val = field ? field.value.trim() : '';
    if(opts.required && !val){ fail('This is required'); return; }
    close(val);
  }
  elOk.addEventListener('click', submit);

  /* opts: title, message, placeholder, value, multiline, required, okText, danger,
            options:[{value,label}], multi  ->  Promise<string|string[]|null> */
  window.cqPrompt = function(o){
    opts = o || {};
    elTitle.textContent = opts.title || 'Please confirm';
    elMsg.textContent = opts.message || '';
    elMsg.style.display = opts.message ? '' : 'none';
    elErr.style.display = 'none';
    elOk.textContent = opts.okText || 'Confirm';
    elOk.className = 'cq-prompt-ok' + (opts.danger ? ' danger' : '');
    elBody.innerHTML = '';

    if(opts.options){
      var list = document.createElement('div');
      list.className = 'cq-prompt-opts';
      opts.options.forEach(function(op){
        var lbl = document.createElement('label');
        lbl.className = 'cq-prompt-opt';
        var input = document.createElement('input');
        input.type = opts.multi ? 'checkbox' : 'radio';
        input.name = 'cqPromptOpt';
        input.value = op.value;
        var span = document.createElement('span');
        span.textContent = op.label;
        lbl.appendChild(input); lbl.appendChild(span);
        lbl.addEventListener('click', function(){
          setTimeout(function(){
            if(!opts.multi){
              list.querySelectorAll('.cq-prompt-opt').forEach(function(x){ x.classList.remove('sel'); });
            }
            lbl.classList.toggle('sel', input.checked);
          }, 0);
        });
        list.appendChild(lbl);
      });
      elBody.appendChild(list);
    } else if(!opts.confirm){
      var f = document.createElement(opts.multiline ? 'textarea' : 'input');
      if(!opts.multiline) f.type = 'text';
      f.placeholder = opts.placeholder || '';
      f.value = opts.value || '';
      if(opts.multiline) f.rows = 4;
      elBody.appendChild(f);
    }

    return new Promise(function(res){ resolveFn = res; open(); });
  };
  /* Convenience wrappers — same shape as mpConfirm/mpAlert on mobile. */
  window.cqConfirm = function(message, o){
    return window.cqPrompt(Object.assign({ confirm: true, title: 'Are you sure?', message: message, okText: 'Confirm' }, o || {}));
  };
  /* Aliases so pages can speak the same language as the mobile toast helpers. */
  window.mpPrompt = window.cqPrompt;
  window.mpConfirmAsync = window.cqConfirm;

  /* Toasts. cqToast falls back to toastr / mpAlert when the page already has
     one of those, so pages can call it unconditionally. */
  var toastRail = null;
  function ownToast(msg, type){
    if(!toastRail){
      toastRail = document.createElement('div');
      toastRail.className = 'cq-toast-rail';
      document.body.appendChild(toastRail);
    }
    var glyph = { success:'&#10003;', danger:'&#10007;', error:'&#10007;', warning:'!', info:'i' }[type] || 'i';
    var t = document.createElement('div');
    t.className = 'cq-toast ' + (type || 'info');
    t.innerHTML = '<i>' + glyph + '</i><div></div>';
    t.lastChild.textContent = String(msg == null ? '' : msg);
    toastRail.appendChild(t);
    requestAnimationFrame(function(){ t.classList.add('show'); });
    setTimeout(function(){
      t.classList.remove('show');
      setTimeout(function(){ if(t.parentNode) t.remove(); }, 260);
    }, type === 'danger' || type === 'error' ? 6000 : 4200);
  }
  window.cqToast = function(msg, type){
    type = type || 'info';
    if(window.mpAlert && window.cqToastReady !== 'self'){ window.mpAlert(msg, type === 'error' ? 'danger' : type); return; }
    if(window.toastr){
      if(type === 'success') window.toastr.success(msg);
      else if(type === 'danger' || type === 'error') window.toastr.error(msg);
      else if(type === 'warning') window.toastr.warning(msg);
      else window.toastr.info(msg);
      return;
    }
    ownToast(msg, type);
  };
  window.cqSuccess = function(m){ window.cqToast(m, 'success'); };
  window.cqError = function(m){ window.cqToast(m, 'danger'); };
})();
</script>
