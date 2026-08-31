<?php /* Modern in-app alerts, toasts and confirmations for mobile views. */ ?>
<style>
  .mp-alert-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,0.42); backdrop-filter: blur(2px); z-index: 1; opacity: 0; transition: opacity 0.2s; }
  .mp-confirm-wrap { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px 12px; z-index: 10005; pointer-events: none; }
  .mp-confirm-wrap.active { pointer-events: auto; }
  .mp-confirm-wrap.active .mp-alert-backdrop { opacity: 1; }
  .mp-confirm-card { position: relative; width: 100%; max-width: 430px; box-sizing: border-box; background: #fff; border-radius: 6px; padding: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.18); transform: scale(0.94); opacity: 0; transition: all 0.2s ease; z-index: 2; }
  .mp-confirm-wrap.active .mp-confirm-card { transform: scale(1); opacity: 1; }
  .mp-confirm-title { font-size: 18px; font-weight: 700; color: var(--mp-text, #0F172A); margin: 0 0 8px; }
  .mp-confirm-msg { font-size: 15px; line-height: 1.5; color: var(--mp-muted, #64748B); margin: 0 0 22px; }
  .mp-confirm-actions { display: flex; gap: 10px; }
  .mp-confirm-actions button { flex: 1; padding: 13px 0; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; -webkit-tap-highlight-color: transparent; touch-action: manipulation; user-select: none; }
  .mp-confirm-actions .mp-confirm-cancel { background: #F1F5F9; color: #334155; }
  .mp-confirm-actions .mp-confirm-ok { background: var(--mp-primary, #0057FF); color: #fff; }
  .mp-confirm-actions .mp-confirm-ok.danger { background: var(--mp-danger, #EF4444); }

  .mp-toast { position: fixed; top: 14px; top: calc(14px + env(safe-area-inset-top, 0px)); left: 50%; transform: translateX(-50%) translateY(-180%); width: calc(100% - 28px); max-width: 420px; background: #fff; border-radius: 6px; padding: 14px 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.16); display: flex; align-items: center; gap: 12px; border: 1px solid #E2E8F0; z-index: 10006; opacity: 0; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
  .mp-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
  .mp-toast.success { border-color: #10B981; background: #F0FDF4; }
  .mp-toast.danger { border-color: #EF4444; background: #FEF2F2; }
  .mp-toast.warning { border-color: #F59E0B; background: #FFFBEB; }
  .mp-toast.info { border-color: var(--mp-primary, #0057FF); background: #EFF6FF; }
  .mp-toast .mp-toast-text { flex: 1; font-size: 14px; font-weight: 500; color: #0F172A; line-height: 1.35; white-space: pre-line; }
  .mp-toast .mp-toast-close { background: transparent; border: none; color: #64748B; font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px 8px; text-transform: uppercase; letter-spacing: 0.2px; }
</style>

<div id="mpConfirmWrap" class="mp-confirm-wrap" style="display:none;">
  <div class="mp-alert-backdrop"></div>
  <div class="mp-confirm-card">
    <div class="mp-confirm-title" id="mpConfirmTitle">Confirm</div>
    <div class="mp-confirm-msg" id="mpConfirmMsg"></div>
    <div class="mp-confirm-actions">
      <button type="button" class="mp-confirm-cancel" id="mpConfirmCancel">Cancel</button>
      <button type="button" class="mp-confirm-ok" id="mpConfirmOk">Confirm</button>
    </div>
  </div>
</div>

<script>
(function(){
  var confirmWrap = document.getElementById('mpConfirmWrap');
  var confirmTitle = document.getElementById('mpConfirmTitle');
  var confirmMsg = document.getElementById('mpConfirmMsg');
  var confirmOk = document.getElementById('mpConfirmOk');
  var confirmCancel = document.getElementById('mpConfirmCancel');
  var onConfirmCb = null, onCancelCb = null;
  var csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
  var csrfToken = <?= json_encode($this->security->get_csrf_hash()); ?>;
  var mpUserId = <?= (int)$this->session->userdata('inv_userid'); ?>;
  var clockOutAjaxUrl = '<?= base_url('attendance/clock_out_ajax'); ?>';
  var needsClockOutAjaxUrl = '<?= base_url('attendance/needs_clock_out_ajax'); ?>';

  function showConfirm(){ confirmWrap.style.display = ''; setTimeout(function(){ confirmWrap.classList.add('active'); }, 10); }
  function hideConfirm(){ confirmWrap.classList.remove('active'); setTimeout(function(){ confirmWrap.style.display = 'none'; }, 200); }

  window.mpConfirm = function(msg, onConfirm, onCancel, options){
    options = options || {};
    confirmTitle.textContent = options.title || 'Confirm';
    confirmMsg.textContent = msg;
    confirmOk.textContent = options.okText || 'Confirm';
    confirmOk.className = 'mp-confirm-ok' + (options.danger ? ' danger' : '');
    onConfirmCb = onConfirm;
    onCancelCb = onCancel || null;
    showConfirm();
  };

  var touchHandled = false;
  function handleConfirm(e){
    e.preventDefault(); e.stopPropagation();
    if(touchHandled && e.type === 'click') { touchHandled = false; return; }
    if(e.type !== 'click') touchHandled = true;
    hideConfirm(); if(typeof onConfirmCb === 'function') onConfirmCb();
  }
  function handleCancel(e){
    e.preventDefault(); e.stopPropagation();
    if(touchHandled && e.type === 'click') { touchHandled = false; return; }
    if(e.type !== 'click') touchHandled = true;
    hideConfirm(); if(typeof onCancelCb === 'function') onCancelCb();
  }
  confirmOk.addEventListener('click', handleConfirm);
  confirmOk.addEventListener('touchend', handleConfirm);
  confirmCancel.addEventListener('click', handleCancel);
  confirmCancel.addEventListener('touchend', handleCancel);
  confirmWrap.addEventListener('click', function(e){ if(e.target === confirmWrap || e.target.classList.contains('mp-alert-backdrop')){ e.stopPropagation(); hideConfirm(); if(typeof onCancelCb === 'function') onCancelCb(); } });

  window.mpConfirmAction = function(el, msg, ev, options){
    if(ev && ev.preventDefault) ev.preventDefault();
    if(ev && ev.stopPropagation) ev.stopPropagation();
    var proceed = function(){
      var href = el && (el.getAttribute('href') || el.href);
      if(href && href !== '#' && !/^javascript:/i.test(href)) { window.location.href = href; return; }
      if(el && el.form) { el.form.submit(); return; }
      if(el && typeof el.click === 'function' && !el.href) el.click();
    };
    mpConfirm(msg, proceed, null, options);
    return false;
  };

  var toastQueue = [];
  var toastActive = false;
  function showNextToast(){
    if(toastQueue.length === 0){ toastActive = false; return; }
    toastActive = true;
    var item = toastQueue.shift();
    var toast = document.createElement('div');
    toast.className = 'mp-toast ' + (item.type || 'info');
    toast.innerHTML = '<div class="mp-toast-text">' + escapeHtml(item.msg) + '</div><button class="mp-toast-close" type="button" aria-label="Close">Cancel</button>';
    document.body.appendChild(toast);
    var btn = toast.querySelector('.mp-toast-close');
    function close(){
      toast.classList.remove('show');
      setTimeout(function(){ toast.remove(); showNextToast(); }, 260);
    }
    btn.addEventListener('click', close);
    requestAnimationFrame(function(){ toast.classList.add('show'); });
    setTimeout(close, item.duration || 4500);
  }

  window.mpAlert = function(msg, type, duration){
    type = type || 'info';
    duration = duration || (type === 'danger' ? 6000 : 4500);
    toastQueue.push({ msg: msg, type: type, duration: duration });
    if(!toastActive) showNextToast();
  };

  window.mpToast = window.mpAlert;
  window.mpSuccess = function(msg){ mpAlert(msg, 'success'); };
  window.mpError = function(msg){ mpAlert(msg, 'danger'); };

  var decimals = <?= (int)decimals(); ?>;
  window.mpFormatNumber = function(num, showComma){
    showComma = showComma !== false;
    var s = String(num || 0).replace(/[^0-9.\-]/g, '');
    var n = parseFloat(s) || 0;
    var formatted = n.toFixed(decimals);
    if(!showComma) return formatted;
    var parts = formatted.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
  };

  // Dispatch any messages queued by earlier PHP flashdata blocks
  if(typeof window.mpFlashMessages !== 'undefined' && window.mpFlashMessages.length){
    window.mpFlashMessages.forEach(function(item){ mpAlert(item.msg, item.type); });
    window.mpFlashMessages = [];
  }

  function doClockOutThenLogout(href){
    var req = new XMLHttpRequest();
    req.open('POST', clockOutAjaxUrl, true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.onreadystatechange = function(){
      if(req.readyState === 4){
        var res = {};
        try { res = JSON.parse(req.responseText); } catch(e){}
        if(res.status === 'success'){
          mpSuccess('Clocked out successfully. Signing out...');
          setTimeout(function(){ window.location.href = href; }, 800);
        } else {
          mpError(res.message || 'Failed to clock out');
        }
      }
    };
    var data = 'user_id=' + encodeURIComponent(mpUserId) + '&' + encodeURIComponent(csrfName) + '=' + encodeURIComponent(csrfToken);
    req.send(data);
  }

  window.mpLogout = function(el, ev){
    if(ev && ev.preventDefault) ev.preventDefault();
    if(ev && ev.stopPropagation) ev.stopPropagation();
    var href = el && (el.getAttribute('href') || el.href);
    if(!href) return false;

    var req = new XMLHttpRequest();
    req.open('POST', needsClockOutAjaxUrl, true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.onreadystatechange = function(){
      if(req.readyState === 4){
        var needs = false;
        try { var data = JSON.parse(req.responseText); needs = data.needs_clock_out; } catch(e){}
        if(needs){
          mpConfirm(
            "You haven't clocked out for today. Clock out before signing out?",
            function(){ doClockOutThenLogout(href); },
            null,
            {title: 'Clock Out Required', okText: 'Clock Out & Logout', danger: true}
          );
        } else {
          mpConfirm(
            'Are you sure you want to log out?',
            function(){ window.location.href = href; },
            null,
            {title: 'Confirm', okText: 'Log Out'}
          );
        }
      }
    };
    req.send(encodeURIComponent(csrfName) + '=' + encodeURIComponent(csrfToken));
    return false;
  };

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); });
  }
  window.escapeHtml = escapeHtml;
})();
</script>
