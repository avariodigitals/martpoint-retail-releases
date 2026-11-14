<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title><?= html_escape($store_name); ?> — Invoice <?= html_escape($invoice->sales_code); ?></title>
	<style>
		:root{color-scheme:light;--brand:#155eef;--ink:#172033;--muted:#667085;--line:#e4e7ec;--surface:#f7f8fa}
		*{box-sizing:border-box}body{margin:0;background:var(--surface);color:var(--ink);font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
		main{width:min(620px,calc(100% - 28px));margin:8vh auto;background:#fff;border:1px solid var(--line);border-radius:18px;padding:clamp(22px,5vw,40px);box-shadow:0 8px 28px rgba(16,24,40,.07)}
		h1{font-size:1.55rem;line-height:1.2;margin:0 0 6px}.muted{color:var(--muted)}.number{font-variant-numeric:tabular-nums}
		.details{border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin:26px 0;padding:8px 0}.row{display:flex;justify-content:space-between;gap:18px;padding:11px 0}.row strong{text-align:right}.due{font-size:1.25rem;font-weight:700}
		table{width:100%;border-collapse:collapse;margin:22px 0}th,td{text-align:left;padding:10px 6px;border-bottom:1px solid var(--line)}th:last-child,td:last-child{text-align:right}.qty{white-space:nowrap;color:var(--muted)}
		button{width:100%;border:0;border-radius:10px;background:var(--brand);color:#fff;font:inherit;font-weight:650;padding:14px 18px;cursor:pointer}button:disabled{background:#98a2b3;cursor:not-allowed}.alert{padding:12px 14px;border-radius:10px;background:#fff4e5;color:#8a4b08;margin:18px 0}
		footer{text-align:center;color:var(--muted);font-size:.85rem;margin-top:24px}
	</style>
</head>
<body>
<main>
	<p class="muted" style="margin:0 0 18px"><?= html_escape($store_name); ?></p>
	<h1>Invoice payment</h1>
	<p class="muted" style="margin:6px 0 0">Invoice <?= html_escape($invoice->sales_code); ?></p>
	<?php if(!empty($items)): ?><table aria-label="Invoice items"><thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach($items as $item): $lineTotal=(float)($item->total_cost ?? ((float)$item->unit_total_cost*(float)$item->sales_qty)); ?><tr><td><?= html_escape($item->item_name); ?></td><td class="qty"><?= html_escape($item->sales_qty); ?></td><td class="number">₦<?= number_format($lineTotal,2); ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
	<div class="details">
		<div class="row"><span>Customer</span><strong><?= html_escape($invoice->customer_name ?: 'Customer'); ?></strong></div>
		<div class="row"><span>Invoice date</span><strong><?= html_escape($invoice->sales_date); ?></strong></div>
		<div class="row"><span>Invoice total</span><strong class="number">₦<?= number_format((float)$invoice->grand_total,2); ?></strong></div>
		<div class="row"><span>Already paid</span><strong class="number">₦<?= number_format((float)$paid,2); ?></strong></div>
		<div class="row due"><span>Amount due</span><strong class="number">₦<?= number_format((float)$due,2); ?></strong></div>
	</div>
	<?php if($payment_result === 'success'): ?><div class="alert" style="background:#ecfdf3;color:#067647">Payment confirmed. Thank you.</div><?php elseif($payment_result === 'review'): ?><div class="alert">Paystack reports a successful payment that needs a balance check by the store. Please contact the store and share your Paystack receipt.</div><?php elseif($payment_result === 'failed'): ?><div class="alert">That payment was not completed. You can try again below.</div><?php endif; ?>
	<?php if(!empty($error)): ?><div class="alert"><?= html_escape($error); ?></div><?php endif; ?>
	<?php if($due <= 0): ?>
		<div class="alert" style="background:#ecfdf3;color:#067647">This invoice is fully paid. Thank you.</div>
	<?php elseif($invoice->sales_status !== 'Final'): ?>
		<div class="alert">This invoice is not yet available for payment. Please contact the store.</div>
	<?php elseif(!$paystack_active): ?>
		<div class="alert">Online payment is temporarily unavailable. Please contact the store for another payment option.</div>
	<?php else: ?>
		<form method="post" action="<?= html_escape($checkout_url); ?>">
			<input type="hidden" name="<?= html_escape($csrf_name); ?>" value="<?= html_escape($csrf_hash); ?>">
			<button type="submit">Pay ₦<?= number_format((float)$due,2); ?> with MartPoint Pay</button>
		</form>
		<p class="muted" style="text-align:center;margin:14px 0 0">You’ll complete payment securely with Paystack.</p>
	<?php endif; ?>
	<footer>Payments are confirmed automatically after Paystack verifies the transaction.</footer>
</main>
</body>
</html>
