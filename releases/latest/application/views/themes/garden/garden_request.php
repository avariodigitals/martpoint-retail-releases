<section class="garden-enquiry"><h1>Tell Us About Your Garden</h1>
<?php if ($garden_request_received): ?><p role="status">Thank you. Your request has been received. The team will contact you to confirm the scope, price and availability.</p>
<?php else: ?>
<?php if ($garden_request_error): ?><p role="alert"><?= htmlspecialchars($garden_request_error); ?></p><?php endif; ?>
<p>Preferred dates are requests and will be confirmed by the team.</p>
<form method="post" action="<?= htmlspecialchars($garden_request_url); ?>">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" name="submission_ref" value="<?= htmlspecialchars($garden_request_key); ?>">
<input type="hidden" name="service_id" value="<?= (int)($garden_requested_service->id ?? 0); ?>">
<input type="hidden" name="product_id" value="<?= (int)($garden_requested_product->id ?? 0); ?>">
<input name="website" tabindex="-1" autocomplete="off" hidden>
<?php if ($garden_requested_service): ?><h2><?= htmlspecialchars($garden_requested_service->service_name); ?></h2><?php endif; ?>
<?php if (!empty($garden_requested_product)): ?><h2><?= htmlspecialchars($garden_requested_product->item_name); ?></h2><?php endif; ?>
<?php foreach (['landscaping'=>'Landscaping Project','maintenance'=>'Garden Maintenance / Service'] as $mode=>$label): if (!mp_garden_has_mode($garden_profile,$mode)) continue; ?><label><input style="width:auto" type="radio" name="request_kind" value="<?= $mode; ?>" required> <?= $label; ?></label><?php endforeach; ?>
<?php foreach (['name'=>'Your Name','phone'=>'Phone','email'=>'Email (optional)','site_location'=>'Site Location','preferred_date'=>'Preferred Visit Date (optional)','budget'=>'Budget (optional)'] as $key=>$label): ?><label><?= $label; ?><input type="<?= $key==='email' ? 'email' : ($key==='preferred_date' ? 'date' : ($key==='budget' ? 'number' : 'text')); ?>" name="<?= $key; ?>" <?= in_array($key,['name','phone','site_location'],true) ? 'required' : ''; ?> value="<?= htmlspecialchars((string)($this->input->post($key) ?? '')); ?>"></label><?php endforeach; ?>
<label>Project or Service Description<textarea name="description" rows="5" maxlength="4000" required><?= htmlspecialchars((string)($this->input->post('description') ?? '')); ?></textarea></label>
<button>Send Request</button></form><?php endif; ?></section>
