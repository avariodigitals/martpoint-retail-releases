<?php
$ci =& get_instance();
if (empty($response_quote) || !$ci->permissions('quotation_view')) return;
?>
<div class="garden-quote-response" style="padding:18px 22px;background:#f4f8f2;border:1px solid #dfe8db;border-radius:12px;margin:18px 0">
<strong>Client response · <?= htmlspecialchars(ucfirst($response_quote->lifecycle_status ?? 'issued')); ?></strong>
<?php if (!empty($response_quote->response_reason)): ?><p><?= nl2br(htmlspecialchars($response_quote->response_reason)); ?></p><?php endif; ?>
<?php if (($response_quote->lifecycle_status ?? '')==='issued' && $ci->permissions('send_email')): ?>
<p style="font-size:13px;color:#6a796f">Email this saved quotation with a secure link. Your client can leave remarks, accept or decline without an account.</p>
<form method="post" action="<?= base_url('quotation/send_email/'.(int)$response_quote->id); ?>" data-quotation-response-email>
<?php $this->load->view('garden/form_token'); ?><button type="submit" class="garden-button mp-qa-btn">Email quotation & response link</button><span role="status" aria-live="polite" style="display:block;margin-top:9px"></span>
</form>
<?php endif; ?></div>
<script>
(function(){if(window.mpQuoteResponseEmailBound)return;window.mpQuoteResponseEmailBound=true;
document.addEventListener('submit',function(event){var form=event.target;if(!form.matches('[data-quotation-response-email]'))return;event.preventDefault();var button=form.querySelector('button'),status=form.querySelector('[role=status]');if(button.disabled)return;button.disabled=true;status.textContent='Sending…';fetch(form.action,{method:'POST',body:new FormData(form),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(response){if(!response.ok)throw new Error('Email could not be sent. Refresh and try again.');return response.json();}).then(function(result){status.textContent=result.message||(result.success?'Quotation emailed.':'Email could not be sent.');if(result.csrf_hash){document.querySelectorAll('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').forEach(function(input){input.value=result.csrf_hash;});}}).catch(function(error){status.textContent=error.message;}).then(function(){button.disabled=false;});});})();
</script>
