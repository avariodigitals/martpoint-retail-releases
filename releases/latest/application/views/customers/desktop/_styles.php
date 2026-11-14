<?php
/**
 * Shared styles + CSRF bootstrap for the Customers desktop content views.
 * Reuses the marketing desktop style pack; customer-specific overrides can be added below.
 */
$this->load->view('marketing/desktop/_styles');
?>
<style>
/* Customer desktop specific tweaks (add here as needed) */
.mp-pill.muted{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.mp-pill.default{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.mp-pill.info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-pill.primary{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-pill.success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-pill.warning{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.mp-pill.danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}

/* Customer form action buttons right-aligned on the card */
#customers-form .mp-form-actions,#advance-form .mp-form-actions{justify-content:flex-end!important}

/* Customer list inline action buttons wrap in tight columns/responsive child rows */
.mp-dt-table .mp-actions{flex-wrap:wrap!important}
</style>
