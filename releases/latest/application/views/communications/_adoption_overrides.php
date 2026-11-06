<style>
/* Match Central and fleet Communications to the active MartPoint workspace. */
.comm{--ca:var(--mp-primary,#0057ff)!important;--ct:var(--mp-primary-08,#eff6ff)!important}
.comm .btn.secondary{color:var(--mp-primary,#0057ff)!important;border-color:var(--mp-primary-20,rgba(0,87,255,.24))!important;background:var(--mp-surface,#fff)!important;box-shadow:0 1px 2px rgba(15,23,42,.05)}
.comm .btn.secondary:hover,.comm .btn.secondary:focus-visible{background:var(--mp-primary-08,#eff6ff)!important;border-color:var(--mp-primary,#0057ff)!important;color:var(--mp-primary-dark,#0044cc)!important}
.comm .btn:hover,.comm .btn:focus-visible{filter:brightness(.94);box-shadow:0 2px 7px rgba(15,23,42,.14)}
.comm .btn:focus-visible,.comm button:focus-visible,.comm a:focus-visible,.comm select:focus-visible{outline:3px solid var(--mp-primary-20,rgba(0,87,255,.28));outline-offset:2px}
.comm select{appearance:none!important;-webkit-appearance:none!important;cursor:pointer;padding-right:42px!important;background-color:var(--mp-surface,#fff)!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23556476' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right 14px center!important}
.comm select:hover{border-color:var(--mp-primary,#0057ff)!important}
.comm .comm-toolbar button{color:var(--mp-text,#292524);border-color:var(--mp-border,#d6dbe3);background:var(--mp-surface,#fff);min-height:40px;box-shadow:0 1px 2px rgba(15,23,42,.05)}
.comm .comm-toolbar button:hover{border-color:var(--mp-primary,#0057ff);color:var(--mp-primary-dark,#0044cc);background:var(--mp-primary-08,#eff6ff)}
.comm .comm-toolbar button.active{background:var(--mp-primary,#0057ff);border-color:var(--mp-primary,#0057ff);color:#fff}
.comm .comm-select>button{position:relative;min-height:46px;padding-right:42px;background:var(--mp-surface,#fff);color:var(--mp-text,#292524);font-weight:600;cursor:pointer;box-shadow:0 1px 2px rgba(15,23,42,.05)}
.comm .comm-select>button:after{content:'';position:absolute;right:15px;top:50%;width:9px;height:9px;border-right:2px solid var(--mp-muted,#78716c);border-bottom:2px solid var(--mp-muted,#78716c);transform:translateY(-70%) rotate(45deg)}
.comm .comm-select.open>button:after{transform:translateY(-25%) rotate(225deg)}
.comm .comm-select-options{background:var(--mp-surface,#fff);box-shadow:0 8px 20px rgba(15,23,42,.1)}
.comm .comm-select-options button{min-height:42px;text-align:left;color:var(--mp-text,#292524);background:var(--mp-surface,#fff);cursor:pointer}
.comm .comm-select-options button:hover,.comm .comm-select-options button:focus-visible{background:var(--mp-primary-08,#eff6ff);color:var(--mp-primary-dark,#0044cc)}
.comm .comm-select-group{background:var(--mp-bg,#f5f4f0);color:var(--mp-muted,#78716c)}
.comm-mobile .comm-topbar a{color:var(--mp-primary,#0057ff)}
@media(max-width:700px){.comm .btn.secondary{min-height:44px}.comm .comm-toolbar button{min-height:44px}}
</style>
