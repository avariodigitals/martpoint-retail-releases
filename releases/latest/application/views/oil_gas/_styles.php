<?php
/**
 * Shared styling for the desktop Oil & Gas screens. One copy of the gas look for
 * the workspace, the forecourt, the tanks, the pumps and the duty sheet.
 */
?>
<style>
.gas-hero{background:linear-gradient(135deg,#1b2a4a,#2f6db5);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;display:flex;justify-content:space-between;gap:24px;align-items:center}
.gas-hero h2{margin:0 0 6px;font-size:25px;font-weight:800}.gas-hero p{margin:0;opacity:.85;max-width:720px}
.gas-hero-actions{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.gas-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid rgba(255,255,255,.6);border-radius:10px;color:#fff!important;font-size:13px;font-weight:800;text-decoration:none!important;white-space:nowrap}
.gas-btn:hover{background:rgba(255,255,255,.12)}
.gas-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
.gas-kpi{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;padding:17px;text-decoration:none!important;min-width:0}
.gas-kpi strong{display:block;font-size:26px;color:var(--mp-ink);line-height:1.2;overflow:hidden;text-overflow:ellipsis}
.gas-kpi span{font-size:12px;color:var(--mp-muted);display:block;margin-top:5px}
.gas-kpi small{display:block;color:var(--mp-muted);font-size:11px;margin-top:8px}
.gas-kpi.alert strong{color:#b42318}.gas-kpi.watch strong{color:#b54708}.gas-kpi.money strong{font-size:21px}
.gas-two{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(0,1fr);gap:16px;align-items:start;margin-bottom:20px}
.gas-card{margin:0;min-width:0}
.gas-status{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700}
.gas-status.transit{background:#fffaeb;color:#9a3412}.gas-status.held{background:#fef2f2;color:#991b1b}
.gas-status.open{background:#eff6ff;color:#1e40af}.gas-status.ok{background:#ecfdf5;color:#047857}
.gas-qty{font-variant-numeric:tabular-nums}
.gas-empty{padding:18px;margin:0;color:var(--mp-muted)}
.gas-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.gas-action{display:flex;gap:13px;align-items:flex-start;padding:17px;border:1px solid var(--mp-border);border-radius:13px;background:var(--mp-surface);text-decoration:none!important;color:var(--mp-ink)}
.gas-action i{width:38px;height:38px;border-radius:10px;background:#eaf2fb;color:#1f4e79;display:flex;align-items:center;justify-content:center;font-size:17px}
.gas-action b{display:block;margin:1px 0 4px}.gas-action small{color:var(--mp-muted);line-height:1.35;display:block}
.gas-sites{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:18px}
.gas-site{padding:9px 15px;border:1px solid var(--mp-border);border-radius:999px;background:var(--mp-surface);color:var(--mp-ink)!important;font-size:13px;font-weight:700;text-decoration:none!important}
.gas-site.active{background:#1b2a4a;border-color:#1b2a4a;color:#fff!important}
.gas-litre{font-weight:800;font-variant-numeric:tabular-nums}
.gas-bar{height:7px;border-radius:5px;background:#eef2f7;overflow:hidden;margin-top:7px;min-width:70px}
.gas-bar i{display:block;height:100%;background:#2f6db5}
.gas-bar.over i{background:#b42318}
.gas-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.gas-form .full{grid-column:1 / -1}
.gas-form label{display:block;font-size:12px;font-weight:700;color:var(--mp-muted);margin-bottom:5px}
.gas-note{margin:14px 0 0;color:var(--mp-muted);font-size:12px;line-height:1.5}
.gas-meter-row td{vertical-align:middle}
.gas-reading{width:150px;max-width:100%}
.loc-table{width:100%;border-collapse:collapse}
.loc-table th{text-align:left;font-size:11px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.04em;padding:12px 14px;border-bottom:1px solid var(--mp-border);background:var(--mp-bg)}
.loc-table td{padding:14px;border-bottom:1px solid var(--mp-border);font-size:13px;color:var(--mp-ink);vertical-align:top}
.loc-table tr:last-child td{border-bottom:none}
.loc-name{font-weight:700;font-size:14px}
.loc-sub{font-size:11px;color:var(--mp-muted);margin-top:3px}
.loc-chip{display:inline-block;padding:2px 8px;border-radius:999px;background:#eef2f7;color:#475467;font-size:11px;font-weight:700;margin-left:6px}
.loc-switch{display:flex;align-items:flex-start;gap:8px;margin-bottom:8px}
.loc-switch:last-child{margin-bottom:0}
.loc-switch input{margin-top:3px}
.loc-switch span{font-size:12px;line-height:1.35}
.loc-off{color:var(--mp-muted);font-size:11px}
@media(max-width:1100px){.gas-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.gas-two{grid-template-columns:1fr}.gas-actions{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.gas-hero{align-items:flex-start;flex-direction:column}.gas-actions{grid-template-columns:1fr}.gas-form{grid-template-columns:1fr}}
</style>
