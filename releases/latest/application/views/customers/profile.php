<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
<style>
.id-card-preview {
    width: 324px;
    height: 204px;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    margin: 20px auto;
    background: linear-gradient(135deg, #fdfbf7 0%, #f5f0e8 100%);
    border: 1px solid #e0d5c5;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.id-card-brand {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 42px;
    font-weight: 900;
    color: #8b7355;
    opacity: 0.06;
    letter-spacing: 2px;
    text-transform: uppercase;
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
}
.id-card-barcode {
    text-align: center;
    margin-top: 8px;
}
.id-card-barcode img {
    height: 42px;
}
.id-card-body {
    padding: 20px 16px 16px 16px;
    text-align: center;
}
.id-card-name {
    font-size: 18px;
    font-weight: 700;
    color: #3d3229;
    margin-bottom: 4px;
}
.id-card-phone {
    font-size: 14px;
    color: #6b5b4f;
    margin-bottom: 8px;
}
.id-card-id {
    font-size: 11px;
    color: #9e8e7e;
    letter-spacing: 2px;
    font-family: monospace;
}
.id-card-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.5);
    border-top: 1px solid rgba(0,0,0,0.05);
}
.id-card-signature {
    font-size: 10px;
    color: #8b7355;
    font-style: italic;
}
.id-card-expiry {
    font-size: 10px;
    color: #3d3229;
    font-weight: 600;
}
.voucher-card-preview {
    width: 324px;
    height: 204px;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    margin: 20px auto;
    background: linear-gradient(135deg, #fdfbf7 0%, #f5f0e8 100%);
    border: 1px solid #e0d5c5;
}
.voucher-card-brand {
    position: absolute;
    top: 10px;
    left: 14px;
    font-size: 12px;
    font-weight: 700;
    color: #8b7355;
    opacity: 0.3;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    transform: rotate(-12deg);
    transform-origin: left center;
}
.voucher-card-body {
    padding: 40px 20px 20px 20px;
    text-align: center;
}
.voucher-card-label {
    font-size: 10px;
    color: #9e8e7e;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}
.voucher-card-value {
    font-size: 28px;
    font-weight: 700;
    color: #3d3229;
    margin-bottom: 4px;
}
.voucher-card-number {
    font-size: 13px;
    font-family: monospace;
    color: #6b5b4f;
    letter-spacing: 3px;
    margin-bottom: 6px;
}
.voucher-card-name {
    font-size: 14px;
    color: #3d3229;
    font-weight: 600;
}
.voucher-card-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.5);
    border-top: 1px solid rgba(0,0,0,0.05);
}
.voucher-card-sig {
    font-size: 10px;
    color: #8b7355;
    font-style: italic;
}
.voucher-card-expiry {
    font-size: 10px;
    color: #3d3229;
    font-weight: 600;
}
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .id-card-preview, .voucher-card-preview {
        box-shadow: none;
        margin: 0;
        page-break-inside: avoid;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include APPPATH . "views/sidebar.php"; ?>
<div class="content-wrapper">
<section class="content-header">
<h1><i class="fa fa-user-circle"></i> <?=htmlspecialchars($customer->customer_name ?? 'Customer Profile');?></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url();?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?=base_url();?>customers">Customers</a></li>
<li class="active">Profile</li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-3">
<div class="box box-primary">
<div class="box-body box-profile">
<h3 class="profile-username text-center"><?=htmlspecialchars($customer->customer_name ?? '');?></h3>
<p class="text-muted text-center"><?=htmlspecialchars($customer->customer_code ?? '');?></p>
<ul class="list-group list-group-unbordered">
<li class="list-group-item"><b>Phone</b> <a class="pull-right"><?=htmlspecialchars($customer->mobile ?? '');?></a></li>
<li class="list-group-item"><b>Email</b> <a class="pull-right"><?=htmlspecialchars($customer->email ?? '-');?></a></li>
<li class="list-group-item"><b>Tier</b> <span class="pull-right label label-info"><?=htmlspecialchars($customer->loyalty_tier ?: 'Bronze');?></span></li>
<li class="list-group-item"><b>Loyalty Points</b> <a class="pull-right text-success"><?=number_format($customer->loyalty_points ?? 0, 0);?></a></li>
<li class="list-group-item"><b>Store Credit</b> <a class="pull-right text-warning"><?=store_number_format($customer->store_credit_balance ?? 0);?></a></li>
<li class="list-group-item"><b>Gift Card Bal</b> <a class="pull-right text-maroon"><?=store_number_format($customer->gift_card_balance ?? 0);?></a></li>
<li class="list-group-item"><b>Membership</b>
  <?php if($active_membership): ?>
    <span class="pull-right label label-success"><?=htmlspecialchars($active_membership->plan_name);?> (<?=$active_membership->discount_percent;?>% OFF)</span>
  <?php else: ?>
    <span class="pull-right text-muted">None</span>
  <?php endif; ?>
</li>
<li class="list-group-item"><b>Treatment Notes</b>
  <a class="pull-right text-info"><?=count($treatment_notes ?? []);?></a>
</li>
<li class="list-group-item"><b>Custom Orders</b>
  <a class="pull-right text-warning"><?=count($custom_orders ?? []);?></a>
</li>
<li class="list-group-item"><b>Total Due</b> <a class="pull-right text-danger"><?=store_number_format($total_due ?? 0);?></a></li>
</ul>
<a href="<?=base_url();?>customers/update/<?=$customer->id;?>" class="btn btn-primary btn-block no-print"><b>Edit Customer</b></a>
</div>
</div>
<div class="box box-default no-print">
<div class="box-header with-border"><h3 class="box-title">ID Card</h3></div>
<div class="box-body text-center">
<div class="id-card-preview" id="idCardPreview">
<div class="id-card-body">
<div class="id-card-name"><?=htmlspecialchars($customer->customer_name ?? '');?></div>
<div class="id-card-phone"><?=htmlspecialchars($customer->mobile ?? '');?></div>
<div class="id-card-id">ID: <?=str_pad($customer->id, 6, '0', STR_PAD_LEFT);?></div>
<div class="id-card-barcode">
<img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?=$customer->id;?>&scale=2&height=8" alt="barcode">
</div>
</div>
<div class="id-card-brand"><?=htmlspecialchars($SITE_TITLE ?? 'MartPoint');?></div>
<div class="id-card-footer">
<div class="id-card-signature"><?=htmlspecialchars($SITE_TITLE ?? 'MartPoint');?></div>
<div class="id-card-expiry"><?=date('M Y');?></div>
</div>
</div>
<button class="btn btn-default btn-sm" onclick="printCard('idCardPreview')"><i class="fa fa-print"></i> Print ID Card</button>
<button class="btn btn-success btn-sm" onclick="downloadCard('idCardPreview','id-card-<?=str_pad($customer->id,6,'0',STR_PAD_LEFT);?>.png')"><i class="fa fa-download"></i> PNG</button>
</div>
</div>
</div>
<div class="col-md-9">
<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<li class="active"><a href="#purchases" data-toggle="tab"><i class="fa fa-shopping-cart"></i> Purchase History</a></li>
<li><a href="#payments" data-toggle="tab"><i class="fa fa-money"></i> Statements</a></li>
<?php if(!empty($service_history)): ?>
<li><a href="#services" data-toggle="tab"><i class="fa fa-inbox"></i> Service History</a></li>
<?php endif; ?>
<li><a href="#giftcards" data-toggle="tab"><i class="fa fa-ticket"></i> Gift Cards</a></li>
<li><a href="#storecredit" data-toggle="tab"><i class="fa fa-credit-card"></i> Store Credit</a></li>
<li><a href="#coupons" data-toggle="tab"><i class="fa fa-tags"></i> Coupons</a></li>
<li><a href="#memberships" data-toggle="tab"><i class="fa fa-id-card"></i> Memberships</a></li>
<li><a href="#treatment_notes" data-toggle="tab"><i class="fa fa-file-text-o"></i> Treatment Notes</a></li>
<li><a href="#custom_orders" data-toggle="tab"><i class="fa fa-pencil-square-o"></i> Custom Orders</a></li>
<li><a href="#idcard" data-toggle="tab"><i class="fa fa-id-card"></i> ID Card</a></li>
</ul>
<div class="tab-content">
<div class="active tab-pane" id="purchases">
<table class="table table-bordered table-striped" id="purchaseTable">
<thead class="bg-gray"><tr><th>#</th><th>Date</th><th>Invoice</th><th>Amount</th><th>Paid</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php if(!empty($purchases)){ $i=1; foreach($purchases as $s){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=show_date($s->sales_date);?></td>
<td><a href="<?=base_url();?>sales/invoice/<?=$s->id;?>" target="_blank"><?=$s->sales_code;?></a></td>
<td><?=store_number_format($s->grand_total);?></td>
<td><?=store_number_format($s->paid_amount);?></td>
<td><?=store_number_format($s->grand_total - $s->paid_amount);?></td>
<td><span class="label label-<?=$s->payment_status=='Paid'?'success':($s->payment_status=='Partial'?'warning':'danger');?>"><?=$s->payment_status;?></span></td>
<td><a href="<?=base_url();?>sales/invoice/<?=$s->id;?>" class="btn btn-xs btn-primary" target="_blank"><i class="fa fa-eye"></i></a></td>
</tr>
<?php }} else { ?>
<tr><td colspan="8" class="text-center text-muted">No purchase records found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<?php if(!empty($service_history)): ?>
<div class="tab-pane" id="services">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Invoice</th><th>Items</th><th>Status</th><th>Drop-off</th><th>Last Update</th><th>Amount</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; foreach($service_history as $sh){ ?>
<tr>
<td><?=$i++;?></td>
<td><a href="<?=base_url();?>sales/invoice/<?=$sh->sales_id;?>" target="_blank"><?=$sh->sales_code;?></a></td>
<td><?=htmlspecialchars($sh->items_list ?: '-');?></td>
<td>
<?php
$status_class = [
	'dropped_off' => 'info', 'washing' => 'warning', 'ironing' => 'warning',
	'ready' => 'success', 'collected' => 'default'
];
$status_label = [
	'dropped_off' => 'Dropped Off', 'washing' => 'Washing', 'ironing' => 'Ironing',
	'ready' => 'Ready for Pickup', 'collected' => 'Picked Up'
];
$sc = $status_class[$sh->status] ?? 'default';
$sl = $status_label[$sh->status] ?? ucfirst(str_replace('_', ' ', $sh->status));
?>
<span class="label label-<?=$sc;?>"><?=$sl;?></span>
<?php if($sh->status == 'ready'): ?>
<span class="label label-danger" style="margin-left:4px;"><i class="fa fa-exclamation-circle"></i> Not Picked</span>
<?php endif; ?>
</td>
<td><?=show_date($sh->created_at);?></td>
<td><?=show_date($sh->updated_at);?></td>
<td><?=store_number_format($sh->grand_total);?></td>
<td><a href="<?=base_url();?>sales/invoice/<?=$sh->sales_id;?>" class="btn btn-xs btn-primary" target="_blank"><i class="fa fa-eye"></i></a></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php endif; ?>
<div class="tab-pane" id="payments">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Date</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead>
<tbody>
<?php if(!empty($payments)){ $i=1; foreach($payments as $p){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=show_date($p->payment_date);?></td>
<td><?=ucfirst(str_replace('_',' ',$p->payment_type));?></td>
<td><?=store_number_format($p->amount);?></td>
<td><?=htmlspecialchars($p->description ?? '-');?></td>
</tr>
<?php }} else { ?>
<tr><td colspan="5" class="text-center text-muted">No payment records found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="giftcards">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Card Number</th><th>Initial Value</th><th>Balance</th><th>Issue Date</th><th>Expiry</th><th>Status</th></tr></thead>
<tbody>
<?php if(!empty($gift_cards)){ $i=1; foreach($gift_cards as $g){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=$g->card_number;?></td>
<td><?=store_number_format($g->initial_value);?></td>
<td><?=store_number_format($g->current_balance);?></td>
<td><?=show_date($g->issue_date);?></td>
<td><?=show_date($g->expiry_date) ?: 'Never';?></td>
<td><span class="label label-<?=$g->status=='active'?'success':($g->status=='redeemed'?'info':($g->status=='expired'?'warning':'danger'));?>"><?=ucfirst($g->status);?></span></td>
</tr>
<?php }} else { ?>
<tr><td colspan="7" class="text-center text-muted">No gift cards found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="storecredit">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Code</th><th>Amount</th><th>Balance</th><th>Source</th><th>Expiry</th><th>Status</th></tr></thead>
<tbody>
<?php if(!empty($store_credits)){ $i=1; foreach($store_credits as $sc){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=$sc->credit_code;?></td>
<td><?=store_number_format($sc->amount);?></td>
<td><?=store_number_format($sc->balance);?></td>
<td><?=ucfirst(str_replace('_',' ',$sc->source));?></td>
<td><?=show_date($sc->expiry_date) ?: 'Never';?></td>
<td><span class="label label-<?=$sc->status=='active'?'success':($sc->status=='used'?'info':($sc->status=='expired'?'warning':'danger'));?>"><?=ucfirst($sc->status);?></span></td>
</tr>
<?php }} else { ?>
<tr><td colspan="7" class="text-center text-muted">No store credit found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="coupons">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Code</th><th>Type</th><th>Value</th><th>Expiry</th><th>Status</th></tr></thead>
<tbody>
<?php if(!empty($coupons)){ $i=1; foreach($coupons as $c){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=$c->code;?></td>
<td><?=ucfirst($c->type);?></td>
<td><?=$c->type=='percentage' ? $c->value.'%' : store_number_format($c->value);?></td>
<td><?=show_date($c->expire_date) ?: 'Never';?></td>
<td><span class="label label-<?=$c->status==1?'success':'danger';?>"><?=$c->status==1?'Active':'Expired';?></span></td>
</tr>
<?php }} else { ?>
<tr><td colspan="6" class="text-center text-muted">No coupons found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="idcard">
<div class="row">
<div class="col-md-6">
<h4>Customer ID Card</h4>
<div class="id-card-preview" id="idCardLarge">
<div class="id-card-body">
<div class="id-card-name"><?=htmlspecialchars($customer->customer_name ?? '');?></div>
<div class="id-card-phone"><?=htmlspecialchars($customer->mobile ?? '');?></div>
<div class="id-card-id">ID: <?=str_pad($customer->id, 6, '0', STR_PAD_LEFT);?></div>
<div class="id-card-barcode">
<img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?=$customer->id;?>&scale=3&height=10" alt="barcode">
</div>
</div>
<div class="id-card-brand"><?=htmlspecialchars($SITE_TITLE ?? 'MartPoint');?></div>
<div class="id-card-footer">
<div class="id-card-signature"><?=htmlspecialchars($SITE_TITLE ?? 'MartPoint');?></div>
<div class="id-card-expiry"><?=date('M Y');?></div>
</div>
</div>
<div class="text-center no-print">
<button class="btn btn-primary" onclick="printCard('idCardLarge')"><i class="fa fa-print"></i> Print ID Card</button>
<button class="btn btn-success" onclick="downloadCard('idCardLarge','id-card-<?=str_pad($customer->id,6,'0',STR_PAD_LEFT);?>.png')"><i class="fa fa-download"></i> Download PNG</button>
</div>
</div>
</div>
</div>
<div class="tab-pane" id="memberships">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Plan</th><th>Period</th><th>Status</th><th>Auto-Renew</th><th>Payment</th></tr></thead>
<tbody>
<?php if(!empty($memberships)){ $i=1; foreach($memberships as $m){ ?>
<tr>
<td><?=$i++;?></td>
<td><b><?=htmlspecialchars($m->plan_name);?></b><br><small><?=htmlspecialchars($m->plan_code);?></small></td>
<td><?=show_date($m->start_date);?> <i class="fa fa-arrow-right text-muted"></i> <?=show_date($m->end_date);?></td>
<td>
  <?php if($m->status == 'active'): ?>
    <span class="label label-success">Active</span>
  <?php elseif($m->status == 'expired'): ?>
    <span class="label label-danger">Expired</span>
  <?php else: ?>
    <span class="label label-default"><?=ucfirst($m->status);?></span>
  <?php endif; ?>
</td>
<td><?=$m->auto_renew ? '<span class="label label-info"><i class="fa fa-refresh"></i> Auto</span>' : '<span class="text-muted">-</span>';?></td>
<td><span class="label label-<?=$m->payment_status=='paid'?'success':($m->payment_status=='overdue'?'danger':'warning');?>"><?=ucfirst($m->payment_status);?></span></td>
</tr>
<?php }} else { ?>
<tr><td colspan="6" class="text-center text-muted">No membership records found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="treatment_notes">
<div class="clearfix" style="margin-bottom:10px;">
  <a href="<?=base_url();?>operations/treatment_note?customer_id=<?=$customer->id;?>" class="btn btn-sm btn-success pull-right no-print"><i class="fa fa-plus"></i> Add Note</a>
</div>
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Date</th><th>Service</th><th>Notes</th><th>Consumables</th><th>Staff</th></tr></thead>
<tbody>
<?php if(!empty($treatment_notes)){ $i=1; foreach($treatment_notes as $tn){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=show_date($tn->treatment_date);?></td>
<td><b><?=htmlspecialchars($tn->service_type);?></b></td>
<td><span class="text-muted" style="font-size:12px;"><?=nl2br(htmlspecialchars($tn->notes));?></span></td>
<td>
  <?php if(!empty($tn->consumables)){ ?>
    <ul class="list-unstyled" style="font-size:12px;margin-bottom:0;">
    <?php foreach($tn->consumables as $cons){ ?>
      <li><span class="label label-info"><?=htmlspecialchars($cons->qty . ' ' . ($cons->consumable_unit ?: 'units'));?></span> <?=htmlspecialchars($cons->item_name);?></li>
    <?php } ?>
    </ul>
  <?php } else { ?>
    <span class="text-muted" style="font-size:12px;"><?=nl2br(htmlspecialchars($tn->products_used));?></span>
  <?php } ?>
</td>
<td><?=htmlspecialchars($tn->staff_name ?: '-');?></td>
</tr>
<?php }} else { ?>
<tr><td colspan="6" class="text-center text-muted">No treatment notes found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="tab-pane" id="custom_orders">
<div class="clearfix" style="margin-bottom:10px;">
  <a href="<?=base_url();?>operations/custom_order?customer_id=<?=$customer->id;?>" class="btn btn-sm btn-success pull-right no-print"><i class="fa fa-plus"></i> New Custom Order</a>
</div>
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Order #</th><th>Item</th><th>Status</th><th>Due Date</th><th>Total</th><th>Deposit</th></tr></thead>
<tbody>
<?php if(!empty($custom_orders)){ $i=1; foreach($custom_orders as $co){ ?>
<tr>
<td><?=$i++;?></td>
<td><span class="label label-default"><?=htmlspecialchars($co->order_code);?></span></td>
<td><?=htmlspecialchars($co->item_name ?: '-');?></td>
<td><span class="label label-<?=Custom_orders_model::status_badge($co->status);?>"><?=Custom_orders_model::status_label($co->status);?></span></td>
<td><?=show_date($co->due_date);?></td>
<td><?=store_number_format($co->total_amount);?></td>
<td><?=store_number_format($co->deposit_paid);?> / <?=store_number_format($co->deposit_amount);?></td>
</tr>
<?php }} else { ?>
<tr><td colspan="7" class="text-center text-muted">No custom orders found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
</div>
<?php include APPPATH . "views/footer.php"; ?>
</div>
<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>
function printCard(elId){
    var el = document.getElementById(elId);
    if(!el) return;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>ID Card</title>');
    win.document.write('<style>');
    win.document.write('body{margin:0;padding:20px;background:#f5f5f5;text-align:center;}');
    win.document.write('.card{width:324px;height:204px;border-radius:12px;position:relative;overflow:hidden;background:linear-gradient(135deg,#fdfbf7 0%,#f5f0e8 100%);border:1px solid #e0d5c5;margin:0 auto;-webkit-print-color-adjust:exact;print-color-adjust:exact;}');
    win.document.write('.brand{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:42px;font-weight:900;color:#8b7355;opacity:0.06;letter-spacing:2px;text-transform:uppercase;white-space:nowrap;pointer-events:none;user-select:none;}');
    win.document.write('.bc{text-align:center;margin-top:8px;}');
    win.document.write('.bc img{height:42px;}');
    win.document.write('.body{padding:20px 16px 16px 16px;text-align:center;}');
    win.document.write('.name{font-size:18px;font-weight:700;color:#3d3229;margin-bottom:4px;}');
    win.document.write('.phone{font-size:14px;color:#6b5b4f;margin-bottom:8px;}');
    win.document.write('.id{font-size:11px;color:#9e8e7e;letter-spacing:2px;font-family:monospace;}');
    win.document.write('.foot{position:absolute;bottom:0;left:0;right:0;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.5);border-top:1px solid rgba(0,0,0,0.05);}');
    win.document.write('.sig{font-size:10px;color:#8b7355;font-style:italic;}');
    win.document.write('.exp{font-size:10px;color:#3d3229;font-weight:600;}');
    win.document.write('</style></head><body>');
    win.document.write(el.outerHTML);
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(function(){ win.print(); win.close(); }, 300);
}
$(function(){
    $('#purchaseTable').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false
    });
});
</script>
<script src="<?=base_url();?>theme/plugins/tableExporter/libs/html2canvas/html2canvas.min.js"></script>
<script>
function downloadCard(elementId, filename){
    var el = document.getElementById(elementId);
    if(!el) return;
    html2canvas(el, {scale: 2, useCORS: true, backgroundColor: null}).then(function(canvas){
        var link = document.createElement('a');
        link.download = filename;
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}
</script>
</body>
</html>
