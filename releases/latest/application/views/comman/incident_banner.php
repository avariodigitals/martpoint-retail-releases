<?php
/**
 * Incident / service-status banner — renders nothing unless an incident is
 * active (Site Settings → Service Status, or the martpoint.com.ng status API
 * mirrored by Updater::syncStatusFeed).
 * Usage: $this->load->view('comman/incident_banner', ['context' => 'desktop']);
 *        $this->load->view('comman/incident_banner', ['context' => 'mobile']);
 * 'mobile' emits a <template> + script that slots the strip right after the
 * page's .topbar so it scrolls away naturally under the sticky header.
 */
$__mp_inc = function_exists('mp_get_incident') ? mp_get_incident() : ['active' => false];
if (empty($__mp_inc['active'])) {
    return;
}
// Hard rule: on client installs the banner only reflects a real incident from
// the martpoint.com.ng status feed (source 'central'). Only Central may show
// a locally-written notice.
if (($__mp_inc['source'] ?? 'local') !== 'central'
    && !(function_exists('mp_is_central') && mp_is_central())) {
    return;
}
$__mp_ctx = ($context ?? 'desktop') === 'mobile' ? 'mobile' : 'desktop';
$__mp_sev = in_array($__mp_inc['severity'], ['investigating', 'identified', 'monitoring', 'maintenance'], true)
    ? $__mp_inc['severity'] : 'investigating';
$__mp_icons = [
    'investigating' => 'fa-exclamation-circle',
    'identified'    => 'fa-exclamation-triangle',
    'monitoring'    => 'fa-eye',
    'maintenance'   => 'fa-wrench',
];
$__mp_icon   = $__mp_icons[$__mp_sev];
$__mp_msg    = trim($__mp_inc['message']) !== '' ? $__mp_inc['message'] : 'We are investigating a technical issue.';
$__mp_url    = trim($__mp_inc['url']) !== '' ? $__mp_inc['url'] : 'https://www.martpoint.com.ng/status';
$__mp_banner = '<div class="mp-incident mp-incident--' . $__mp_sev . '" role="status">'
    . '<i class="fa ' . $__mp_icon . ' mp-incident-ico"></i>'
    . '<span class="mp-incident-msg">' . htmlspecialchars($__mp_msg) . '</span>'
    . '<a class="mp-incident-link" href="' . htmlspecialchars($__mp_url) . '" target="_blank" rel="noopener noreferrer">'
    . 'Status page <i class="fa fa-external-link"></i></a>'
    . '</div>';

if (empty($GLOBALS['__mp_incident_css'])):
    $GLOBALS['__mp_incident_css'] = true;
?>
<style>
.mp-incident{display:flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;font-size:13px;font-weight:500;line-height:1.4;flex-shrink:0;flex-wrap:wrap;text-align:center}
.mp-incident-ico{font-size:14px}
.mp-incident-msg{min-width:0}
.mp-incident-link{display:inline-flex;align-items:center;gap:5px;font-weight:700;white-space:nowrap;text-decoration:underline;text-underline-offset:2px}
.mp-incident-link:hover{opacity:.85}
.mp-incident--investigating{background:#FEF3C7;color:#92400E}.mp-incident--investigating .mp-incident-link{color:#92400E}
.mp-incident--identified{background:#FFEDD5;color:#9A3412}.mp-incident--identified .mp-incident-link{color:#9A3412}
.mp-incident--monitoring{background:#DBEAFE;color:#1E40AF}.mp-incident--monitoring .mp-incident-link{color:#1E40AF}
.mp-incident--maintenance{background:#E0E7FF;color:#3730A3}.mp-incident--maintenance .mp-incident-link{color:#3730A3}
</style>
<?php endif; ?>

<?php if ($__mp_ctx === 'mobile'): ?>
<template id="mpIncidentTpl"><?= $__mp_banner; ?></template>
<script>
(function(){
  var tpl = document.getElementById('mpIncidentTpl');
  if(!tpl) return;
  var node = tpl.content ? tpl.content.firstElementChild : null;
  if(!node) return;
  var topbar = document.querySelector('.topbar');
  if(topbar && topbar.parentNode){
    topbar.parentNode.insertBefore(node, topbar.nextSibling);
  } else {
    var host = document.querySelector('.screen') || document.getElementById('app') || document.body;
    host.insertBefore(node, host.firstChild);
  }
})();
</script>
<?php else: ?>
<?= $__mp_banner; ?>
<?php endif; ?>
