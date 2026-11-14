<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Shared chrome for the clinical report screens: the stylesheet and the
 * currency symbol. The rendering helpers (cr_kpi / cr_bar / cr_money /
 * cr_period_filter / cr_toolbar) live in application/helpers/
 * clinical_report_helper.php — a view is included inside the loader's function
 * scope, so closures declared here would not be visible to the report views.
 */
$CI =& get_instance();
$cr_from = $from ?? date('Y-m-01', strtotime('-2 months'));
$cr_to   = $to ?? date('Y-m-d');
?>
<style>
.cr-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.cr-head h1{margin:4px 0 5px;font:400 30px/1.15 'DM Serif Display',Georgia,serif;color:var(--mp-text)}
.cr-head p{margin:0;color:var(--mp-muted);font-size:14px;max-width:70ch}
.cr-eyebrow{color:var(--mp-primary);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.cr-eyebrow a{color:inherit;text-decoration:none}
.cr-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.cr-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 15px;border-radius:9px;border:1px solid var(--mp-border);background:#fff;color:var(--mp-text);font-size:13.5px;font-weight:600;text-decoration:none;cursor:pointer}
.cr-btn:hover{text-decoration:none;border-color:#91b9a6;color:var(--mp-text)}
.cr-btn.primary{background:var(--mp-primary);border-color:var(--mp-primary);color:#fff}
.cr-btn.primary:hover{background:var(--mp-primary-dark);color:#fff}

.cr-filter{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;padding:14px 16px;border:1px solid var(--mp-border);border-radius:12px;background:#fff;box-shadow:var(--mp-shadow-sm);margin-bottom:20px}
.cr-filter label{display:block;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mp-muted);margin-bottom:6px}
.cr-filter input[type=date]{padding:9px 11px;border:1px solid var(--mp-border);border-radius:8px;font-size:14px;background:#fff;color:var(--mp-text)}
.cr-quick{display:flex;gap:6px;flex-wrap:wrap}
.cr-chip{padding:7px 12px;border-radius:999px;border:1px solid var(--mp-border);background:#fff;font-size:12.5px;font-weight:600;color:var(--mp-muted);text-decoration:none}
.cr-chip:hover{text-decoration:none;color:var(--mp-primary);border-color:#91b9a6}

.cr-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:13px;margin-bottom:22px}
.cr-kpi{padding:16px 17px;border:1px solid var(--mp-border);border-radius:12px;background:#fff;box-shadow:var(--mp-shadow-sm)}
.cr-kpi .lbl{font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mp-muted);margin-bottom:9px}
.cr-kpi .val{font-size:27px;font-weight:700;line-height:1.1;letter-spacing:-.01em;overflow-wrap:break-word}
.cr-kpi .sub{margin-top:5px;font-size:12.5px;color:var(--mp-muted);line-height:1.45}
.cr-kpi.money .val{color:var(--mp-primary-dark)}
.cr-kpi.alert .val{color:var(--mp-danger)}

.cr-panel{border:1px solid var(--mp-border);border-radius:12px;background:#fff;box-shadow:var(--mp-shadow-sm);margin-bottom:18px;overflow:hidden}
.cr-panel>h3{margin:0;padding:14px 17px;border-bottom:1px solid var(--mp-border);font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--mp-muted)}
.cr-panel-body{padding:0}
.cr-table{width:100%;border-collapse:collapse;font-size:13.5px}
.cr-table th{text-align:left;padding:10px 17px;border-bottom:1px solid var(--mp-border);font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--mp-muted);white-space:nowrap}
.cr-table td{padding:10px 17px;border-bottom:1px solid #F1F5F9;vertical-align:middle}
.cr-table tbody tr:last-child td{border-bottom:none}
.cr-table .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.cr-table th.num{text-align:right}
.cr-bar{height:7px;border-radius:4px;background:#e3efe9;overflow:hidden;min-width:70px;flex:1}
.cr-bar>span{display:block;height:100%;background:var(--mp-primary);border-radius:4px}
.cr-empty{padding:26px 17px;text-align:center;color:var(--mp-muted);font-size:13.5px}
/* A secondary line inside a table cell (e.g. a plan's title under its code). */
.cr-sub{margin-top:2px;color:var(--mp-muted);font-size:12px;line-height:1.4}
.cr-note{margin:0 0 18px;padding:13px 16px;border-radius:11px;border:1px solid var(--mp-border);background:#f4f8f6;color:var(--mp-muted);font-size:13px;line-height:1.6}
.cr-note strong{color:var(--mp-text)}
.cr-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;align-items:start}
.cr-badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;background:#e3efe9;color:#104d40}
.cr-badge.warn{background:#f7eddc;color:#8a5a1c}
.cr-badge.bad{background:#f8e5e0;color:#a3452f}
@media(max-width:900px){
  .cr-head h1{font-size:24px}
  .cr-head{flex-direction:column}
  .cr-panel{overflow-x:auto}
  .cr-table{min-width:520px}
}
</style>
