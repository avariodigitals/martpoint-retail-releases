<?php
/**
 * Shared styles for the Creator Workspace (desktop shell).
 * Builds on the admin/finance desktop foundation.
 */
$this->load->view('admin/desktop/_styles');
?>
<style>
/* ===== CREATOR WORKSPACE ===== */
.cr-hero{background:linear-gradient(120deg,#0F172A 0%,#1E1B4B 55%,#4C1D95 100%)!important;border-radius:20px!important;padding:28px 32px!important;color:#fff!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:24px!important;margin-bottom:24px!important;box-shadow:0 10px 30px rgba(30,27,75,.25)!important}
.cr-hero h2{font-size:24px!important;font-weight:800!important;margin:0 0 6px!important;color:#fff!important;letter-spacing:-.3px!important}
.cr-hero p{margin:0!important;font-size:14px!important;color:rgba(255,255,255,.75)!important}
.cr-hero-actions{display:flex!important;gap:10px!important;flex-wrap:wrap!important;justify-content:flex-end!important}
.cr-hero-actions a{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:11px 18px!important;border-radius:12px!important;font-size:13px!important;font-weight:700!important;text-decoration:none!important;transition:all .15s ease!important;white-space:nowrap!important}
.cr-hero-actions a.solid{background:#fff!important;color:#1E1B4B!important}
.cr-hero-actions a.solid:hover{background:#EDE9FE!important}
.cr-hero-actions a.ghost{background:rgba(255,255,255,.12)!important;color:#fff!important;border:1px solid rgba(255,255,255,.25)!important}
.cr-hero-actions a.ghost:hover{background:rgba(255,255,255,.2)!important}

.cr-kpis{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.cr-kpi{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;box-shadow:var(--mp-shadow-sm)!important;min-width:0!important}
.cr-kpi-top{display:flex!important;align-items:center!important;justify-content:space-between!important;margin-bottom:12px!important}
.cr-kpi-label{font-size:12px!important;font-weight:600!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.05em!important}
.cr-kpi-icon{width:36px!important;height:36px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:15px!important;flex-shrink:0!important}
.cr-kpi.purple .cr-kpi-icon{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}
.cr-kpi.green .cr-kpi-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.cr-kpi.blue .cr-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.cr-kpi.orange .cr-kpi-icon{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.cr-kpi.pink .cr-kpi-icon{background:rgba(236,72,153,.1)!important;color:#EC4899!important}
.cr-kpi.teal .cr-kpi-icon{background:rgba(13,148,136,.1)!important;color:#0D9488!important}
.cr-kpi-value{font-size:24px!important;font-weight:800!important;color:var(--mp-ink)!important;line-height:1.1!important;font-variant-numeric:tabular-nums!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
.cr-kpi-sub{font-size:12px!important;color:var(--mp-muted)!important;margin-top:6px!important;font-weight:500!important}
.cr-kpi-sub a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}

.cr-grid-2{display:grid!important;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr)!important;gap:20px!important;margin-bottom:24px!important}
.cr-grid-3{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:16px!important;margin-bottom:24px!important}

.cr-type-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;box-shadow:var(--mp-shadow-sm)!important;display:flex!important;flex-direction:column!important;gap:10px!important;text-decoration:none!important;color:var(--mp-ink)!important;transition:all .15s ease!important}
.cr-type-card:hover{border-color:#7C3AED!important;box-shadow:0 6px 18px rgba(124,58,237,.12)!important;text-decoration:none!important;color:var(--mp-ink)!important;transform:translateY(-2px)!important}
.cr-type-card .icon{width:44px!important;height:44px!important;border-radius:12px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:18px!important}
.cr-type-card .title{font-size:15px!important;font-weight:700!important;margin:0!important}
.cr-type-card .count{font-size:26px!important;font-weight:800!important;line-height:1!important}
.cr-type-card .meta{font-size:12px!important;color:var(--mp-muted)!important}

.cr-list{list-style:none!important;margin:0!important;padding:0!important}
.cr-list li{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;padding:12px 20px!important;border-bottom:1px solid var(--mp-border)!important;font-size:13px!important}
.cr-list li:last-child{border-bottom:none!important}
.cr-list .name{font-weight:600!important;color:var(--mp-ink)!important;min-width:0!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}
.cr-list .sub{font-size:12px!important;color:var(--mp-muted)!important;font-weight:500!important}
.cr-list .amt{font-weight:700!important;font-variant-numeric:tabular-nums!important;white-space:nowrap!important}

.cr-pill{display:inline-flex!important;align-items:center!important;gap:5px!important;padding:4px 10px!important;border-radius:999px!important;font-size:11px!important;font-weight:700!important;text-transform:capitalize!important;white-space:nowrap!important}
.cr-pill.digital{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.cr-pill.course{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}
.cr-pill.membership{background:rgba(236,72,153,.1)!important;color:#EC4899!important}
.cr-pill.product,.cr-pill.physical,.cr-pill.service{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.cr-pill.ok,.cr-pill.paid,.cr-pill.active,.cr-pill.completed{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.cr-pill.warn,.cr-pill.pending,.cr-pill.unpaid{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.cr-pill.danger,.cr-pill.cancelled,.cr-pill.expired,.cr-pill.failed{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.cr-pill.muted,.cr-pill.inactive{background:var(--mp-bg)!important;color:var(--mp-muted)!important}

.cr-tabs{display:flex!important;gap:6px!important;flex-wrap:wrap!important;margin-bottom:20px!important}
.cr-tabs a{padding:8px 16px!important;border-radius:999px!important;font-size:13px!important;font-weight:600!important;text-decoration:none!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;transition:all .15s ease!important}
.cr-tabs a:hover{background:var(--mp-bg)!important;text-decoration:none!important}
.cr-tabs a.active{background:#1E1B4B!important;color:#fff!important;border-color:#1E1B4B!important}

.cr-products{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(260px,1fr))!important;gap:16px!important}
.cr-product{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;overflow:hidden!important;box-shadow:0 1px 3px rgba(0,0,0,.04)!important;display:flex!important;flex-direction:column!important;transition:all .15s ease!important}
.cr-product:hover{box-shadow:0 6px 18px rgba(0,0,0,.06)!important;transform:translateY(-1px)!important}
.cr-product-media{aspect-ratio:16/9!important;background:var(--mp-bg)!important;position:relative!important;overflow:hidden!important;display:flex!important;align-items:center!important;justify-content:center!important;color:var(--mp-muted)!important;font-size:26px!important;font-weight:700!important}
.cr-product-media img{width:100%!important;height:100%!important;object-fit:cover!important;display:block!important}
.cr-product-media .cr-pill{position:absolute!important;top:8px!important;left:8px!important;font-size:10px!important;padding:3px 8px!important}
.cr-product-body{padding:14px!important;display:flex!important;flex-direction:column!important;gap:6px!important;flex:1!important}
.cr-product-name{font-size:14px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:0!important;line-height:1.35!important}
.cr-product-meta{font-size:12px!important;color:var(--mp-muted)!important;line-height:1.4!important}
.cr-product-price{font-size:18px!important;font-weight:800!important;color:var(--mp-ink)!important;font-variant-numeric:tabular-nums!important}
.cr-product-stats{display:flex!important;gap:12px!important;font-size:12px!important;color:var(--mp-muted)!important}
.cr-product-stats strong{color:var(--mp-ink)!important}
.cr-product-actions{display:flex!important;align-items:center!important;justify-content:flex-end!important;gap:4px!important;margin-top:auto!important;padding-top:10px!important;border-top:1px solid var(--mp-border)!important}
.cr-product-actions a{display:inline-flex!important;align-items:center!important;gap:5px!important;padding:5px 9px!important;border-radius:7px!important;font-size:11px!important;font-weight:600!important;text-decoration:none!important;white-space:nowrap!important;background:transparent!important;color:var(--mp-muted)!important;border:1px solid transparent!important;transition:all .15s ease!important}
.cr-product-actions a:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important;text-decoration:none!important}
.cr-product-actions a.primary{background:rgba(124,58,237,.08)!important;color:#7C3AED!important}
.cr-product-actions a.primary:hover{background:rgba(124,58,237,.14)!important;color:#6D28D9!important}
.cr-product-actions a.warn{color:var(--mp-warning)!important}
.cr-product-actions a.warn:hover{background:rgba(245,158,11,.08)!important;color:var(--mp-warning)!important}
.cr-product-actions a[href*=delete]{color:var(--mp-muted)!important}
.cr-product-actions a[href*=delete]:hover{background:rgba(220,38,38,.08)!important;color:var(--mp-danger)!important}

.cr-empty{padding:48px 24px!important;text-align:center!important;background:var(--mp-surface)!important;border:1px dashed var(--mp-border)!important;border-radius:16px!important}
.cr-empty .icon{width:56px!important;height:56px!important;border-radius:16px!important;background:rgba(124,58,237,.1)!important;color:#7C3AED!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;font-size:22px!important;margin-bottom:14px!important}
.cr-empty h3{font-size:16px!important;font-weight:700!important;margin:0 0 6px!important;color:var(--mp-ink)!important}
.cr-empty p{font-size:13px!important;color:var(--mp-muted)!important;margin:0 0 18px!important}

.cr-progress{height:6px!important;background:var(--mp-bg)!important;border-radius:3px!important;overflow:hidden!important;width:120px!important;display:inline-block!important;vertical-align:middle!important}
.cr-progress span{display:block!important;height:100%!important;background:#7C3AED!important;border-radius:3px!important}

.cr-table-actions{display:flex!important;gap:6px!important}
.cr-table-actions a{display:inline-flex!important;align-items:center!important;justify-content:center!important;padding:6px 12px!important;border-radius:8px!important;font-size:12px!important;font-weight:600!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;text-decoration:none!important;transition:all .15s ease!important}
.cr-table-actions a:hover{background:var(--mp-bg)!important;text-decoration:none!important}
.cr-table-actions a.primary{background:#7C3AED!important;border-color:#7C3AED!important;color:#fff!important}
.cr-table-actions a.danger:hover{border-color:var(--mp-danger)!important;color:var(--mp-danger)!important}

/* Builder (course modules & lessons) */
.cr-module{border:1px solid var(--mp-border)!important;border-radius:14px!important;background:var(--mp-surface)!important;margin-bottom:14px!important;overflow:hidden!important}
.cr-module-head{display:flex!important;align-items:center!important;gap:12px!important;padding:12px 16px!important;background:var(--mp-bg)!important;border-bottom:1px solid var(--mp-border)!important}
.cr-module-head .mp-form-control{flex:1!important}
.cr-module-body{padding:12px 16px!important;display:flex!important;flex-direction:column!important;gap:10px!important}
.cr-lesson{display:grid!important;grid-template-columns:minmax(0,1.3fr) minmax(0,1fr) auto!important;gap:10px!important;align-items:start!important;padding:10px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important}
.cr-lesson textarea{grid-column:1/-1!important}
.cr-icon-btn{width:34px!important;height:34px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-muted)!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;cursor:pointer!important;transition:all .15s ease!important;flex-shrink:0!important}
.cr-icon-btn:hover{border-color:var(--mp-danger)!important;color:var(--mp-danger)!important}
.cr-add-btn{display:inline-flex!important;align-items:center!important;gap:6px!important;padding:8px 14px!important;border-radius:8px!important;font-size:12px!important;font-weight:600!important;border:1px dashed var(--mp-border)!important;background:transparent!important;color:var(--mp-primary)!important;cursor:pointer!important}
.cr-add-btn:hover{background:var(--mp-bg)!important}

@media(max-width:1200px){.cr-kpis{grid-template-columns:repeat(2,minmax(0,1fr))!important}.cr-grid-2,.cr-grid-3{grid-template-columns:1fr!important}}
@media(max-width:1024px){.cr-hero{flex-direction:column!important;align-items:flex-start!important}.cr-hero-actions{justify-content:flex-start!important}.cr-lesson{grid-template-columns:1fr!important}}
</style>
