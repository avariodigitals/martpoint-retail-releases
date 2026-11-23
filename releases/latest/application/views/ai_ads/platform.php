<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('ai_ads/styles'); $checks=array(
  'HTTPS Next Admin URL'=>$diagnostics['api_url_https'],
  'Central service secret available to PHP'=>$diagnostics['service_secret_present'],
  'PHP cURL enabled'=>$diagnostics['curl_available'],
  'Next Admin settings route reachable'=>$diagnostics['next_settings_status']==='connected',
  'Meta App ID configured'=>$diagnostics['app_id_present'],
  'Business Login configuration ID configured'=>$diagnostics['login_config_present'],
  'OAuth redirect URI configured'=>$diagnostics['redirect_uri_present'],
  'Meta App Secret configured'=>$diagnostics['app_secret_present'],
  'Token encryption master key configured'=>$diagnostics['master_key_present'],
  'Meta App Secret fetched from Next Admin'=>$diagnostics['next_app_secret_present'],
  'Encryption master key fetched from Next Admin'=>$diagnostics['next_master_key_present'],
  'Meta OAuth ready'=>$diagnostics['oauth_ready'],
  'Token encryption ready'=>$diagnostics['encryption_ready']); ?>
<section class="aiads">
  <header class="aiads-head"><div><span class="aiads-kicker">Central · Platform operations</span><h1>AI Ads Operations</h1><p>Platform credential and billing health. Secrets are never displayed here.</p></div><span class="aiads-pill <?= !empty($billing_configured)?'ok':'pending'; ?>"><?= !empty($billing_configured)?'Next billing route configured':'Next billing route unavailable'; ?></span></header>
  <section class="aiads-card"><div class="card-title"><div><h2>Central → Next Admin Meta settings</h2><p><?= html_escape($diagnostics['next_settings_message']); ?></p></div></div>
    <?php if(!empty($billing_config_message)): ?><div class="aiads-note"><?= html_escape($billing_config_message); ?></div><?php endif; ?>
    <?php if(!empty($billing_health['message'])): ?><div class="aiads-note"><?= html_escape($billing_health['message']); ?></div><?php endif; ?>
    <div class="campaign-list"><?php foreach($checks as $label=>$ok): ?><div class="campaign-row"><strong><?= html_escape($label); ?></strong><span class="aiads-pill <?= $ok?'ok':'pending'; ?>"><?= $ok?'Ready':'Needs attention'; ?></span></div><?php endforeach; ?></div>
    <p class="muted">Manage the Meta App Secret and encryption master key in Next Admin → Integrations → Meta Ads Platform. Manage AI Ads prices, tax and included allowances in Next Admin → Finance → Central Billing Catalogue.</p>
  </section>
  <section class="aiads-card"><div class="card-title"><div><h2>Next billing readiness</h2><p>Signed health probe from Central to the Next Admin billing API.</p></div></div>
    <?php $billingRows=array(
      'Next Supabase'=>$billing_health['supabase_ready']??false,
      'Central billing enabled'=>$billing_health['central_billing_enabled']??false,
      'Catalogue synchronized'=>$billing_health['catalogue_ready']??false,
      'Paystack configured'=>$billing_health['paystack_ready']??false,
      'OpenAI image generation configured'=>$billing_health['ai_ads_image_provider_ready']??false,
      'Gemini video generation configured'=>$billing_health['ai_ads_video_provider_ready']??false,
      'Video renderer available'=>$billing_health['ai_ads_renderer_ready']??false,
      'AI creative generation stack ready'=>$billing_health['ai_ads_creative_ready']??false,
      'Creative features released for sale'=>$billing_health['ai_ads_creative_release_enabled']??false,
      'AI Ads monthly offer active'=>$billing_health['ai_ads_monthly_ready']??false,
      'AI Ads annual offer active'=>$billing_health['ai_ads_annual_ready']??false,
      'Central Fleet subscription registry installed'=>$subscription_registry_ready??false
    ); ?><div class="campaign-list"><?php foreach($billingRows as $label=>$ok): ?><div class="campaign-row"><strong><?= html_escape($label); ?></strong><span class="aiads-pill <?= $ok?'ok':'pending'; ?>"><?= $ok?'Ready':'Needs attention'; ?></span></div><?php endforeach; ?></div>
    <?php if(!empty($billing_health['ai_ads_renderer_reason'])): ?><div class="aiads-note"><strong>Video renderer:</strong> <?= html_escape($billing_health['ai_ads_renderer_reason']); ?></div><?php endif; ?>
  <section class="aiads-card"><div class="card-title"><div><h2>Published plan allowances</h2><p>These limits come from the Next Admin catalogue and are sent to Fleet stores.</p></div></div><div class="campaign-list"><?php foreach(($billing_health['ai_ads_offers']??array()) as $offer): $caps=$offer['entitlements']??array(); $catalogueStatus=array_key_exists('catalogue_active',$offer)?(!empty($offer['catalogue_active'])?'Active':'Disabled'):'Unknown'; $blockers=is_array($offer['blockers']??null)?$offer['blockers']:array(); ?><div class="campaign-row"><strong><?= html_escape(($offer['plan_name']??'AI Ads').' · '.ucfirst((string)($offer['billing_period']??''))); ?></strong><span><?= number_format(((int)($offer['total_kobo']??0))/100,2); ?> NGN total · <?= (int)($caps['copy_generations_per_month']??0); ?> copy suggestions · <?= (int)($caps['ai_images_per_month']??0); ?> AI images · <?= (int)($caps['ai_video_generations_per_month']??0); ?> AI-generated videos/month · <?= (int)($caps['media_storage_mb']??0); ?> MB media storage · Next catalogue: <?= html_escape($catalogueStatus); ?> · Purchase: <?= !empty($offer['enabled'])?'Available':'Unavailable'; ?><?php if($blockers): ?> · Blocked by: <?= html_escape(implode('; ',array_map('strval',$blockers))); ?><?php endif; ?></span></div><?php endforeach; ?></div><p class="muted">Annual plans renew the monthly allowances each month. Ad spend remains charged by Meta to each merchant. A video made from uploaded media uses one of the same included AI-video credits.</p></section>
    <div class="aiads-note">A paid term grants MartPoint software access. Renewal is manual until a recurring mandate is explicitly implemented. Advertising spend stays in the merchant’s Meta account; campaigns remain there until their scheduled end time or the merchant pauses them.</div>
  </section>
  <section class="aiads-card"><div class="card-title"><div><h2>Fleet subscription delivery</h2><p>Last 100 verified AI Ads invoice entitlements tracked by Central.</p></div></div>
    <?php if(empty($central_subscriptions)): ?><div class="aiads-empty">No verified AI Ads subscription invoices have been delivered to a Fleet.</div><?php else: ?><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:13px"><thead><tr><th style="text-align:left;padding:10px">Fleet</th><th style="text-align:left;padding:10px">Store</th><th style="text-align:left;padding:10px">Plan</th><th style="text-align:left;padding:10px">Term</th><th style="text-align:left;padding:10px">Status</th><th style="text-align:left;padding:10px">Access through (UTC)</th></tr></thead><tbody><?php foreach($central_subscriptions as $row): $expired=(new DateTimeImmutable($row['expires_at'],new DateTimeZone('UTC')))->getTimestamp()<time(); $status=$row['status']==='active'&&$expired?'expired':$row['status']; ?><tr style="border-top:1px solid #eef0f3"><td style="padding:10px"><?= html_escape($row['install_url']??('Fleet #'.(int)$row['fleet_install_id'])); ?></td><td style="padding:10px"><?= (int)$row['store_id']; ?></td><td style="padding:10px"><?= html_escape(ucwords(str_replace('_',' ',$row['plan_code']??'AI Ads'))); ?></td><td style="padding:10px"><?= html_escape(ucfirst($row['billing_period'])); ?></td><td style="padding:10px"><span class="aiads-pill <?= $status==='active'?'ok':'pending'; ?>"><?= html_escape(ucwords(str_replace('_',' ',$status))); ?></span></td><td style="padding:10px"><?= html_escape($row['expires_at']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
</section>
