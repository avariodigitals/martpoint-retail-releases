<?php /* Shared "Book a session" dialog — a day picker and a time picker, like an
         appointment but simpler. Replaces the old free-text "YYYY-MM-DD HH:MM"
         prompt so nobody has to type a datetime.
         Exposes window.mpBookSession(opts) -> Promise<string|null> resolving
         "YYYY-MM-DD HH:MM:00", or null when cancelled.
         Plain JS only (same contract as components/dialog.php). Emit once. */
   if(!empty($GLOBALS['__mp_book_session_loaded'])){ return; }
   $GLOBALS['__mp_book_session_loaded'] = true;
?>
<style>
  .mpbk-wrap{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:20px 14px;z-index:100050;}
  .mpbk-wrap.open{display:flex;}
  .mpbk-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);}
  .mpbk-card{position:relative;width:100%;max-width:440px;background:#fff;border-radius:18px;padding:22px;box-shadow:0 28px 70px rgba(15,23,42,.28);font-family:inherit;}
  .mpbk-card h4{margin:0 0 6px;font-size:17px;font-weight:700;color:#0F172A;}
  .mpbk-card p{margin:0 0 16px;font-size:13px;line-height:1.5;color:#64748B;}
  .mpbk-lbl{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748B;margin:14px 0 6px;}
  .mpbk-date{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #E2E8F0;border-radius:12px;font-size:15px;font-family:inherit;color:#0F172A;background:#fff;outline:none;}
  .mpbk-date:focus{border-color:#0D9488;box-shadow:0 0 0 3px rgba(13,148,136,.15);}
  .mpbk-quick{display:flex;gap:8px;margin-top:8px;}
  .mpbk-quick button{padding:7px 12px;border-radius:999px;border:1px solid #E2E8F0;background:#F8FAFC;color:#334155;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
  .mpbk-quick button:hover{background:#F0FDFA;border-color:#0D9488;color:#0F766E;}
  .mpbk-times{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;max-height:190px;overflow-y:auto;padding:2px;}
  .mpbk-time{padding:10px 0;border:1px solid #E2E8F0;border-radius:10px;background:#fff;color:#0F172A;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-align:center;}
  .mpbk-time:hover{background:#F8FAFC;}
  .mpbk-time.sel{border-color:#0D9488;background:#F0FDFA;color:#0F766E;}
  .mpbk-err{display:none;margin:12px 0 0;font-size:12px;font-weight:600;color:#DC2626;}
  .mpbk-actions{display:flex;gap:10px;margin-top:20px;}
  .mpbk-actions button{flex:1;padding:13px 0;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;}
  .mpbk-cancel{background:#F1F5F9;color:#334155;}
  .mpbk-ok{background:#0D9488;color:#fff;}
</style>

<div class="mpbk-wrap" id="mpBkWrap">
  <div class="mpbk-backdrop"></div>
  <div class="mpbk-card" role="dialog" aria-modal="true">
    <h4 id="mpBkTitle">Book a session</h4>
    <p id="mpBkMsg">Pick the day and the time the patient will come in.</p>
    <div class="mpbk-lbl">Day</div>
    <input type="date" class="mpbk-date" id="mpBkDate">
    <div class="mpbk-quick">
      <button type="button" id="mpBkToday">Today</button>
      <button type="button" id="mpBkTomorrow">Tomorrow</button>
    </div>
    <div class="mpbk-lbl">Time</div>
    <div class="mpbk-times" id="mpBkTimes"></div>
    <div class="mpbk-err" id="mpBkErr"></div>
    <div class="mpbk-actions">
      <button type="button" class="mpbk-cancel" id="mpBkCancel">Cancel</button>
      <button type="button" class="mpbk-ok" id="mpBkOk">Book it</button>
    </div>
  </div>
</div>

<script>
(function(){
  if(window.mpBookSessionReady) return;
  window.mpBookSessionReady = true;

  var wrap = document.getElementById('mpBkWrap');
  var elDate = document.getElementById('mpBkDate');
  var elTimes = document.getElementById('mpBkTimes');
  var elErr = document.getElementById('mpBkErr');
  var pickedTime = '';

  function pad(n){ return (n < 10 ? '0' : '') + n; }
  function isoDay(d){ return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

  /* Time slots every 30 minutes, 08:00–17:30 — the clinic day. */
  function renderTimes(defaultTime){
    elTimes.innerHTML = '';
    pickedTime = defaultTime || '';
    for(var h = 8; h <= 17; h++){
      for(var m = 0; m < 60; m += 30){
        if(h === 17 && m > 30) break;
        var t = pad(h) + ':' + pad(m);
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'mpbk-time' + (t === pickedTime ? ' sel' : '');
        b.textContent = t;
        b.addEventListener('click', function(){
          elTimes.querySelectorAll('.mpbk-time').forEach(function(x){ x.classList.remove('sel'); });
          this.classList.add('sel');
          pickedTime = this.textContent;
          elErr.style.display = 'none';
        });
        elTimes.appendChild(b);
      }
    }
    if(!pickedTime){
      var first = elTimes.querySelector('.mpbk-time');
      if(first){ first.classList.add('sel'); pickedTime = first.textContent; }
    }
  }

  function nextSlot(){
    var now = new Date();
    var mins = now.getHours() * 60 + now.getMinutes();
    var start = 8 * 60, end = 17 * 60 + 30;
    if(mins < start) return '08:00';
    if(mins >= end) return '08:00';
    var rounded = Math.ceil(mins / 30) * 30;
    return pad(Math.floor(rounded / 60)) + ':' + pad(rounded % 60);
  }

  var resolveFn = null;
  function close(val){
    wrap.classList.remove('open');
    var r = resolveFn; resolveFn = null;
    if(r) r(val);
  }
  function fail(msg){ elErr.textContent = msg; elErr.style.display = 'block'; }
  function submit(){
    if(!elDate.value){ fail('Choose the day the patient will come in'); return; }
    if(!pickedTime){ fail('Choose a time for the session'); return; }
    close(elDate.value + ' ' + pickedTime + ':00');
  }

  document.getElementById('mpBkCancel').addEventListener('click', function(){ close(null); });
  wrap.querySelector('.mpbk-backdrop').addEventListener('click', function(){ close(null); });
  document.getElementById('mpBkOk').addEventListener('click', submit);
  document.getElementById('mpBkToday').addEventListener('click', function(){ elDate.value = isoDay(new Date()); elErr.style.display = 'none'; });
  document.getElementById('mpBkTomorrow').addEventListener('click', function(){
    var d = new Date(); d.setDate(d.getDate() + 1); elDate.value = isoDay(d); elErr.style.display = 'none';
  });
  elDate.addEventListener('change', function(){ elErr.style.display = 'none'; });
  document.addEventListener('keydown', function(e){
    if(!wrap.classList.contains('open')) return;
    if(e.key === 'Escape'){ e.preventDefault(); close(null); }
    if(e.key === 'Enter' && e.target.tagName !== 'BUTTON'){ e.preventDefault(); submit(); }
  });

  /* opts: title, message, date (YYYY-MM-DD, default today) -> Promise<string|null> */
  window.mpBookSession = function(o){
    o = o || {};
    document.getElementById('mpBkTitle').textContent = o.title || 'Book a session';
    document.getElementById('mpBkMsg').textContent = o.message || 'Pick the day and the time the patient will come in.';
    elErr.style.display = 'none';
    elDate.value = o.date || isoDay(new Date());
    renderTimes(nextSlot());
    wrap.classList.add('open');
    setTimeout(function(){ elDate.focus(); }, 60);
    return new Promise(function(res){ resolveFn = res; });
  };
})();
</script>
