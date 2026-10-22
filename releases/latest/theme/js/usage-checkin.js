(function () {
    'use strict';
    var script = document.currentScript;
    if (!script || !window.fetch) return;
    var endpoint = script.getAttribute('data-endpoint');
    var lastAttempt = 0;
    function checkin() {
        if (document.visibilityState === 'hidden') return;
        var now = Date.now();
        if (now - lastAttempt < 900000) return;
        lastAttempt = now;
        var body = encodeURIComponent(script.getAttribute('data-csrf-name')) + '=' +
            encodeURIComponent(script.getAttribute('data-csrf-value'));
        window.fetch(endpoint, {
            method: 'POST', credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body
        }).catch(function () {});
    }
    // No recurring timer: an idle tab must not keep a dormant client active.
    if (document.readyState === 'complete') window.setTimeout(checkin, 3000);
    else window.addEventListener('load', function () { window.setTimeout(checkin, 3000); });
    document.addEventListener('pointerdown', checkin, {passive: true});
    document.addEventListener('keydown', checkin, {passive: true});
}());
