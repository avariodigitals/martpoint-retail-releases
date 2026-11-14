<?php if(!empty($currency_selector_enabled) && !empty($currency_choices) && count($currency_choices)>1): ?>
<div class="mp-currency-switch" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12px;font-weight:700;margin:6px 12px;">
  <?php if(!empty($currency_country_options)): ?>
  <details style="position:relative;">
    <summary style="cursor:pointer;border:1px solid currentColor;border-radius:7px;padding:6px 9px;list-style:none;">Country: <?= htmlspecialchars($currency_country_options[$currency_country_selected]['label'] ?? 'Choose',ENT_QUOTES); ?></summary>
    <div style="max-height:220px;overflow-y:auto;min-width:190px;padding:6px;border:1px solid #bbb;border-radius:8px;background:#fff;color:#222;">
      <?php foreach($currency_country_options as $countryCode=>$countryOption): ?>
      <button type="button" data-country="<?= htmlspecialchars($countryCode,ENT_QUOTES); ?>" data-currency="<?= htmlspecialchars($countryOption['currency'],ENT_QUOTES); ?>" onclick="mpChangeStoreCurrency(this.dataset.currency,this.dataset.country,this)" style="display:block;width:100%;text-align:left;padding:7px;border:0;background:transparent;color:inherit;cursor:pointer;"><?= htmlspecialchars($countryOption['label'],ENT_QUOTES); ?> (<?= htmlspecialchars($countryOption['currency'],ENT_QUOTES); ?>)</button>
      <?php endforeach; ?>
    </div>
  </details>
  <?php endif; ?>
  <details style="position:relative;">
    <summary style="cursor:pointer;border:1px solid currentColor;border-radius:7px;padding:6px 9px;list-style:none;">Currency: <?= htmlspecialchars($currency_choices[$currency_selected] ?? $currency_selected,ENT_QUOTES); ?></summary>
    <div style="max-height:220px;overflow-y:auto;min-width:150px;padding:6px;border:1px solid #bbb;border-radius:8px;background:#fff;color:#222;">
      <?php foreach($currency_choices as $code=>$label): ?>
      <button type="button" data-currency="<?= htmlspecialchars($code,ENT_QUOTES); ?>" onclick="mpChangeStoreCurrency(this.dataset.currency,'',this)" style="display:block;width:100%;text-align:left;padding:7px;border:0;background:transparent;color:inherit;cursor:pointer;"><?= htmlspecialchars($label,ENT_QUOTES); ?></button>
      <?php endforeach; ?>
    </div>
  </details>
</div>
<script>
function mpChangeStoreCurrency(code,country,trigger){
  var data=new FormData();data.append('store_slug',<?= json_encode($currency_store_slug); ?>);data.append('currency',code);if(country)data.append('country',country);
  data.append('<?= $this->security->get_csrf_token_name(); ?>',<?= json_encode($this->security->get_csrf_hash()); ?>);
  if(trigger)trigger.disabled=true;
  fetch(<?= json_encode($currency_select_url); ?>,{method:'POST',body:data,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(res){
    if(!res.status)throw new Error(res.message||'Currency could not be changed.');
    var newRate=parseFloat(res.rate)||0;if(newRate<=0)throw new Error('The exchange rate is unavailable.');
    var sid=<?= (int)$currency_store_id; ?>,key='sf_cart_'+sid,rateKey='sf_rate_'+sid,oldRate=1;
    try{oldRate=parseFloat(localStorage.getItem(rateKey))||1;var cart=JSON.parse(localStorage.getItem(key)||'[]');var ratio=oldRate/newRate;
      cart.forEach(function(item){if(typeof item.price==='number')item.price=Math.round(item.price*ratio*100)/100;(item.addons||[]).forEach(function(a){if(typeof a.price==='number')a.price=Math.round(a.price*ratio*100)/100;});});
      localStorage.setItem(key,JSON.stringify(cart));localStorage.setItem(rateKey,String(newRate));
    }catch(e){}
    location.reload();
  }).catch(function(e){alert(e.message||'Currency could not be changed.');if(trigger)trigger.disabled=false;});
}
(function(){try{localStorage.setItem('sf_rate_'+<?= (int)$currency_store_id; ?>,<?= json_encode((string)$currency_conversion_rate); ?>);}catch(e){}})();
</script>
<?php endif; ?>
