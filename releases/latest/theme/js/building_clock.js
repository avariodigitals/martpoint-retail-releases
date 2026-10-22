(function () {
  'use strict';
  var dialog = document.getElementById('bm-clock-dialog');
  if (!dialog || dialog.dataset.bound) return;
  dialog.dataset.bound = '1';
  var video = document.getElementById('bm-clock-video');
  var form = document.getElementById('bm-clock-form');
  var password = document.getElementById('bm-clock-password');
  var confirm = document.getElementById('bm-clock-confirm');
  var result = document.getElementById('bm-clock-result');
  var device = document.getElementById('bm-clock-device-status');
  var retry = document.getElementById('bm-clock-device-retry');
  var stream = null, generation = 0, busy = false, ready = false, clocked = false, toastTimer;
  function request(url, config) {
    var controller = new AbortController();
    config.signal = controller.signal;
    var timer = setTimeout(function () { controller.abort(); }, 15000);
    return fetch(url, config).finally(function () { clearTimeout(timer); });
  }
  function stop() {
    if (stream) stream.getTracks().forEach(function (track) { track.stop(); });
    stream = null; video.srcObject = null;
  }
  function enabled() { confirm.disabled = busy || !ready || !stream; }
  function labels(value) {
    clocked = !!value;
    var text = clocked ? 'Clock Out' : 'Clock In';
    document.querySelectorAll('[data-bm-clock-label]').forEach(function (label) { label.textContent = text; });
    document.getElementById('bm-clock-title').textContent = text;
    confirm.textContent = 'Confirm ' + text;
  }
  function status(id) {
    ready = false; enabled();
    return request(dialog.dataset.base + 'status_ajax', {credentials: 'same-origin', cache: 'no-store'})
      .then(function (response) { if (!response.ok) throw new Error(); return response.json(); })
      .then(function (reply) {
        if (id !== generation || !dialog.open) return;
        if (typeof reply.clocked_in !== 'boolean') throw new Error();
        labels(reply.clocked_in); ready = true; enabled();
      }).catch(function () {
        if (id !== generation || !dialog.open) return;
        ready = false; enabled(); result.textContent = 'Unable to check attendance. Close and reopen this dialog to try again.';
      });
  }
  function devices(id) {
    stop(); enabled(); retry.hidden = true;
    device.textContent = 'Please allow camera and microphone access in your browser.';
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      device.textContent = 'Camera and microphone access requires a supported browser and a secure HTTPS connection.';
      retry.hidden = false; return;
    }
    navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}, audio: true}).then(function (media) {
      if (id !== generation || !dialog.open) { media.getTracks().forEach(function (track) { track.stop(); }); return; }
      stream = media; video.srcObject = media;
      var play = video.play(); if (play && play.catch) play.catch(function () {});
      media.getTracks().forEach(function (track) {
        track.addEventListener('ended', function () {
          if (stream !== media) return;
          stop(); enabled(); retry.hidden = false; device.textContent = 'Device access ended. Enable camera and microphone again to continue.';
        });
      });
      device.textContent = 'Camera and microphone enabled. Preview only; nothing is recorded.';
      enabled();
    }).catch(function () {
      if (id !== generation || !dialog.open) return;
      device.textContent = 'Camera or microphone is blocked or unavailable. Allow both in browser settings, then try again.';
      retry.hidden = false; enabled();
    });
  }
  function close() { if (busy) return; dialog.close(); }
  dialog.addEventListener('close', function () { generation++; stop(); password.value = ''; ready = false; enabled(); });
  dialog.addEventListener('cancel', function (event) { if (busy) event.preventDefault(); });
  dialog.querySelector('[data-bm-clock-close]').addEventListener('click', close);
  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-bm-clock-trigger]');
    if (!trigger) return;
    event.preventDefault();
    if (busy || dialog.open) return;
    if (!dialog.showModal) { window.alert('Please use an up-to-date browser to clock in.'); return; }
    result.textContent = ''; password.value = ''; ready = false;
    dialog.showModal(); var id = ++generation;
    status(id); devices(id);
  });
  retry.addEventListener('click', function () { devices(++generation); status(generation); });
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (busy || !ready || !stream || !form.reportValidity()) return;
    var payload = new FormData(form), action = clocked ? 'clock_out' : 'clock_in';
    password.value = ''; busy = true; enabled();
    dialog.querySelector('[data-bm-clock-close]').disabled = true;
    result.textContent = 'Recording attendance…';
    request(dialog.dataset.base + action, {method: 'POST', credentials: 'same-origin', body: payload})
      .then(function (response) { if (!response.ok) throw new Error(); return response.json(); })
      .then(function (reply) {
        if (reply.csrf_hash) form.querySelector('[data-bm-clock-csrf]').value = reply.csrf_hash;
        if (reply.status !== 'success') { result.textContent = reply.message || 'Attendance could not be recorded.'; return; }
        labels(reply.clocked_in);
        var toast = document.getElementById('bm-clock-toast');
        toast.textContent = reply.message || 'Attendance recorded.'; toast.hidden = false;
        clearTimeout(toastTimer); toastTimer = setTimeout(function () { toast.hidden = true; }, 6000);
        document.dispatchEvent(new CustomEvent('bm:attendancechange', {detail: {clocked_in: clocked}}));
        dialog.close();
      }).catch(function () {
        result.textContent = 'Unable to confirm attendance. Checking your current status before you try again…';
        return status(generation);
      }).finally(function () {
        busy = false; dialog.querySelector('[data-bm-clock-close]').disabled = false; enabled();
      });
  });
  window.addEventListener('pagehide', function () { generation++; stop(); password.value = ''; });
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden || !dialog.open) return;
    generation++; stop(); password.value = ''; enabled(); retry.hidden = false;
    device.textContent = 'Preview paused while this tab is hidden. Enable camera and microphone again to continue.';
  });
})();
