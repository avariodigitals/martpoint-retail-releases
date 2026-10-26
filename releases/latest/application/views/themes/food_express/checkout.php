<?php
$fxTagline = $fxCopy('brand_tagline') ?: 'From first craving to first bite.';
$fxOutlets = !empty($outlets) && is_array($outlets) ? array_values($outlets) : [];
$fxShippingMethods = array_values(array_filter(json_decode($settings->shipping_methods_json ?? '', true) ?: [], function($method){ return !empty($method['enabled']); }));
$fxCityZones = !empty($settings->city_shipping_enabled) ? (json_decode($settings->city_shipping_json ?? '', true) ?: []) : [];
$fxSelectedOutlet = count($fxOutlets) === 1 ? (int)$fxOutlets[0]->id : 0;
?>
<div class="fx-section" style="padding-top:16px; padding-bottom:60px;">
  <div class="fx-section-inner">
    <h1 style="font-size:clamp(24px, 3vw, 30px); font-weight:800; color:var(--fx-ink); margin-bottom:8px;">Checkout</h1>
    <p style="color:var(--fx-muted); margin-bottom:32px;"><?= $fxTagline; ?> — let's get your order confirmed.</p>

    <div class="fx-checkout-layout" style="display:grid; grid-template-columns:1fr 400px; gap:32px;">
      <!-- Form -->
      <div>
        <form id="checkout-form" method="POST" action="<?= base_url('storefront/place_order'); ?>">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="warehouse_id" id="fx-warehouse-id" value="<?= $fxSelectedOutlet; ?>">
          <input type="hidden" name="idempotency_key" id="fx-idempotency-key" value="">

          <section class="fx-location-picker" id="fx-outlet-picker" style="grid-template-columns:1fr; margin-bottom:24px;" aria-labelledby="fx-checkout-location-title">
            <div>
              <div class="fx-section-overline">Fulfilment</div>
              <h2 id="fx-checkout-location-title">Where should we prepare this?</h2>
              <p>Choose the kitchen closest to you so the order is routed correctly.</p>
            </div>
            <?php if(!empty($fxOutlets)): ?>
            <div class="fx-outlet-grid" role="radiogroup" aria-label="Checkout fulfilment location">
              <?php foreach($fxOutlets as $outlet): $outletId = (int)$outlet->id; ?>
              <label class="fx-outlet-option<?= $fxSelectedOutlet === $outletId ? ' active' : ''; ?>" data-outlet-id="<?= $outletId; ?>" onclick="selectCheckoutOutlet(this, <?= $outletId; ?>)">
                <input type="radio" name="fx_outlet" value="<?= $outletId; ?>"<?= $fxSelectedOutlet === $outletId ? ' checked' : ''; ?> aria-label="<?= htmlspecialchars($outlet->warehouse_name); ?>">
                <span class="fx-outlet-copy">
                  <strong><?= htmlspecialchars($outlet->warehouse_name); ?></strong>
                  <?php $outletAddress = trim(implode(', ', array_filter([(string)($outlet->address ?? ''), (string)($outlet->city ?? '')]))); ?>
                  <?php if($outletAddress): ?><small><?= htmlspecialchars($outletAddress); ?></small><?php endif; ?>
                </span>
                <svg class="fx-outlet-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              </label>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="fx-location-picker-note">We’ll use the store’s default fulfilment location.</p>
            <?php endif; ?>
          </section>

          <!-- Contact Info -->
          <fieldset style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
            <legend style="font-size:16px; font-weight:700; color:var(--fx-ink); padding:0 8px; margin-bottom:8px;">Contact Information</legend>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">First Name <span style="color:var(--fx-danger);">*</span></label>
                <input type="text" name="first_name" required class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="John">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Last Name <span style="color:var(--fx-danger);">*</span></label>
                <input type="text" name="last_name" required class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="Doe">
              </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Email <span style="color:var(--fx-danger);">*</span></label>
                <input type="email" name="email" required class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="john@example.com">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Phone <span style="color:var(--fx-danger);">*</span></label>
                <input type="tel" name="phone" required class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="+234 800 000 0000">
              </div>
            </div>
          </fieldset>

          <fieldset class="fx-delivery-options"><legend>How would you like your order?</legend>
            <?php
            $fxModes = $fxShippingMethods;
            if(!$fxModes) $fxModes = [['name'=>'Delivery','fee'=>0,'enabled'=>1]];
            foreach($fxModes as $index => $method):
              $mode = preg_match('/pick\\s*up|collect/i', $method['name']) ? 'pickup' : 'delivery';
              if($fxCityZones && $mode==='delivery' && $index>0) continue;
            ?>
              <label><input type="radio" name="delivery_method" value="<?= $mode; ?>" data-method="<?= htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8'); ?>" data-fee="<?= !empty($method['quote']) ? 0 : (float)($method['fee'] ?? 0); ?>" data-quote="<?= !empty($method['quote']) ? '1' : '0'; ?>" <?= $index===0?'checked':''; ?>><span><?= htmlspecialchars($method['name']); ?></span></label>
            <?php endforeach; ?>
            <?php if(!empty($table_number)): ?>
            <label><input type="radio" name="delivery_method" value="dine_in" data-method="Dine-in" data-fee="0"><span>Dine-in · Table <?= htmlspecialchars($table_number); ?></span></label>
            <?php endif; ?>
            <?php if($fxCityZones): ?>
            <div id="fx-city-options"><p>Delivery area</p>
            <?php foreach($fxCityZones as $zone): $zoneKey=trim($zone['city'] ?? '').'|'.trim($zone['state'] ?? ''); ?>
              <label><input type="radio" name="shipping_city" value="<?= htmlspecialchars($zoneKey, ENT_QUOTES, 'UTF-8'); ?>" data-fee="<?= (float)($zone['fee'] ?? 0); ?>" data-free-over="<?= (float)($zone['free_over'] ?? 0); ?>" required><span><?= htmlspecialchars(($zone['city'] ?? '')==='*'?'Other areas':str_replace('|', ', ', $zoneKey)); ?></span></label>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </fieldset>

          <!-- Address -->
          <div id="delivery-address-fields" style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
            <legend style="font-size:16px; font-weight:700; color:var(--fx-ink); padding:0 8px; margin-bottom:16px;">Delivery Address</legend>
            <div style="margin-bottom:16px;">
              <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Address <span style="color:var(--fx-danger);">*</span></label>
              <textarea name="address" rows="3" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none; resize:vertical;" placeholder="House number, street, landmark"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">City <span style="color:var(--fx-danger);">*</span></label>
                <input type="text" name="city" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="Lagos">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Area / LGA</label>
                <input type="text" name="area" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="Victoria Island">
              </div>
            </div>
          </div>

          <!-- Pickup Time -->
          <div id="pickup-fields" style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px; display:none;">
            <legend style="font-size:16px; font-weight:700; color:var(--fx-ink); padding:0 8px; margin-bottom:16px;">Pickup Time</legend>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Date <span style="color:var(--fx-danger);">*</span></label>
                <input type="date" name="pickup_date" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Time <span style="color:var(--fx-danger);">*</span></label>
                <input type="time" name="pickup_time" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none;">
              </div>
            </div>
          </div>

          <!-- Payment -->
          <fieldset style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
            <legend style="font-size:16px; font-weight:700; color:var(--fx-ink); padding:0 8px; margin-bottom:8px;">Payment Method</legend>
            <div style="display:flex; flex-direction:column; gap:12px;">
              <?php if($paystack_enabled ?? false): ?>
              <label style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid var(--fx-border); border-radius:var(--fx-radius-sm); cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--fx-green)'" onmouseout="this.style.borderColor='var(--fx-border)'">
                <input type="radio" name="payment_method" value="paystack" checked style="width:20px;height:20px;accent-color:var(--fx-green);">
                <div style="flex:1; display:flex; align-items:center; gap:12px;">
                  <img src="https://cdn.paystack.co/logo.png" alt="Paystack" style="height:24px;">
                  <span style="font-weight:600; color:var(--fx-ink);">Pay with Card / Bank Transfer (Paystack)</span>
                </div>
              </label>
              <?php endif; ?>
              <label style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid var(--fx-border); border-radius:var(--fx-radius-sm); cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--fx-green)'" onmouseout="this.style.borderColor='var(--fx-border)'">
                <input type="radio" name="payment_method" value="pay_on_delivery"<?= empty($paystack_enabled) ? ' checked' : ''; ?> style="width:20px;height:20px;accent-color:var(--fx-green);">
                <div style="flex:1;">
                  <div style="font-weight:600; color:var(--fx-ink);">Pay on Delivery</div>
                  <div style="font-size:13px; color:var(--fx-muted);">Cash or card when your order arrives</div>
                </div>
              </label>
              <?php if(!empty($settings->allow_whatsapp) && !empty($settings->whatsapp_number)): ?>
              <label style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid var(--fx-border); border-radius:var(--fx-radius-sm); cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--fx-green)'" onmouseout="this.style.borderColor='var(--fx-border)'">
                <input type="radio" name="payment_method" value="whatsapp" style="width:20px;height:20px;accent-color:var(--fx-green);">
                <div style="flex:1; display:flex; align-items:center; gap:12px;">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                  <span style="font-weight:600; color:#25D366;">Order via WhatsApp</span>
                </div>
              </label>
              <?php endif; ?>
            </div>
          </fieldset>

          <!-- Notes -->
          <fieldset style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
            <legend style="font-size:16px; font-weight:700; color:var(--fx-ink); padding:0 8px; margin-bottom:8px;">Order Notes</legend>
            <textarea name="notes" rows="3" class="fx-input" style="width:100%; padding:12px 16px; border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:15px; background:var(--fx-white); color:var(--fx-ink); outline:none; resize:vertical;" placeholder="Allergies, special instructions, gate code..."></textarea>
          </fieldset>

          <button type="submit" class="fx-card-add" style="width:100%; padding:18px; font-size:17px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12V7H5v5"/><path d="M7 12l5 5 10-10"/></svg>
            Place Order
          </button>
        </form>
      </div>

      <!-- Order Summary -->
      <div style="position:sticky; top:100px;">
        <div style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px;">
          <h2 style="font-size:18px; font-weight:800; color:var(--fx-ink); margin-bottom:20px;">Order Summary</h2>

          <div id="fx-checkout-lines"></div>
          <?php if(!empty($cart)): foreach($cart as $item): ?>
          <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--fx-border);">
            <div style="min-width:0;">
              <div style="font-weight:600; color:var(--fx-ink);"><?= htmlspecialchars($item['name']); ?></div>
              <div style="font-size:13px; color:var(--fx-muted);"><?= $item['qty']; ?> × <?= sf_currency($item['price'], $store_currency ?? null); ?></div>
            </div>
            <div style="font-weight:700; color:var(--fx-green); white-space:nowrap;"><?= sf_currency($item['price'] * $item['qty'], $store_currency ?? null); ?></div>
          </div>
          <?php endforeach; endif; ?>

          <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px;">
            <span style="color:var(--fx-muted);">Subtotal</span>
            <span style="font-weight:600; color:var(--fx-ink);" id="fx-checkout-subtotal"><?= sf_currency($cart_total ?? 0, $store_currency ?? null); ?></span>
          </div>

          <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px;">
            <span style="color:var(--fx-muted);">Delivery</span>
            <span style="font-weight:600; color:var(--fx-ink);" id="checkout-delivery">
              <?php 
                $deliveryFee = $settings->delivery_fee ?? 0;
                echo $deliveryFee > 0 ? sf_currency($deliveryFee, $store_currency ?? null) : 'Free';
              ?>
            </span>
          </div>



          <div style="height:1px; background:var(--fx-border); margin:16px 0;"></div>

          <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:800; color:var(--fx-ink);">
            <span>Total</span>
            <span id="checkout-total">—</span>
          </div>

          <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--fx-border); font-size:12px; color:var(--fx-muted); text-align:center;">
            By placing your order, you agree to our <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/terms'); ?>" style="color:var(--fx-green);">Terms</a> and <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/privacy'); ?>" style="color:var(--fx-green);">Privacy Policy</a>.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
const FX_OUTLETS = <?= json_encode(array_map(function($outlet){ return ['id' => (int)$outlet->id, 'name' => (string)$outlet->warehouse_name]; }, $fxOutlets), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
document.addEventListener('DOMContentLoaded',function(){
  const items = JSON.parse(localStorage.getItem('sf_cart_'+STORE_ID)||'[]');
  const lines = document.getElementById('fx-checkout-lines');
  items.forEach(item=>{
    const row=document.createElement('div');row.className='fx-order-row';
    const detail=document.createElement('div'), title=document.createElement('strong'), note=document.createElement('small'), amount=document.createElement('b');
    title.textContent=item.qty+' × '+item.name;
    note.textContent=[item.spice_level ? item.spice_level+' spice' : '', ...(item.addons||[]).map(a=>a.name+(a.qty>1?' × '+a.qty:'')), item.note||''].filter(Boolean).join(' · ');
    amount.textContent=formatMoney(sfCartLineTotal(item));detail.append(title,note);row.append(detail,amount);lines.append(row);
  });
  const subtotal=items.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  document.getElementById('fx-checkout-subtotal').textContent=formatMoney(subtotal);
  function totals(){
    const selected=document.querySelector('[name="delivery_method"]:checked');
    const isDelivery=selected&&selected.value==='delivery';
    const zones=document.getElementById('fx-city-options');
    if(zones){zones.hidden=!isDelivery;zones.querySelectorAll('input').forEach(el=>el.required=isDelivery);}
    document.getElementById('delivery-address-fields').style.display=isDelivery?'block':'none';
    document.getElementById('pickup-fields').style.display=selected&&selected.value==='pickup'?'block':'none';
    const zone=zones&&document.querySelector('[name="shipping_city"]:checked');
    let fee=zone?Number(zone.dataset.fee):Number(selected?.dataset.fee||0);
    if(zone && Number(zone.dataset.freeOver)>0 && subtotal>=Number(zone.dataset.freeOver))fee=0;
    const awaiting=isDelivery&&zones&&!zone;
    document.getElementById('checkout-delivery').textContent=awaiting?'Choose an area':(selected?.dataset.quote==='1'?'Confirmed by restaurant':formatMoney(fee));
    document.getElementById('checkout-total').textContent=awaiting?'Choose delivery area':formatMoney(subtotal+fee);
  }
  document.querySelectorAll('[name="delivery_method"],[name="shipping_city"]').forEach(el=>el.addEventListener('change',totals));totals();
});
let fxSelectedOutlet = parseInt(localStorage.getItem('sf_outlet_' + STORE_ID) || '<?= $fxSelectedOutlet; ?>', 10) || 0;
document.addEventListener('DOMContentLoaded',function(){
  const option=document.querySelector('.fx-outlet-option[data-outlet-id="'+fxSelectedOutlet+'"]');
  if(option) selectCheckoutOutlet(option,fxSelectedOutlet);
  else fxSelectedOutlet=0;
});

function selectCheckoutOutlet(element, outletId){
  fxSelectedOutlet = Number(outletId) || 0;
  document.querySelectorAll('.fx-outlet-option').forEach(option => {
    const active = Number(option.dataset.outletId) === fxSelectedOutlet;
    option.classList.toggle('active', active);
    const radio = option.querySelector('input[type="radio"]');
    if(radio) radio.checked = active;
  });
  const hidden = document.getElementById('fx-warehouse-id');
  if(hidden) hidden.value = fxSelectedOutlet || '';
  if(fxSelectedOutlet) localStorage.setItem('sf_outlet_' + STORE_ID, String(fxSelectedOutlet));
  const selected=FX_OUTLETS.find(outlet=>outlet.id===fxSelectedOutlet);
  if(selected) document.querySelectorAll('[data-fx-location-value]').forEach(el=>el.textContent=selected.name);
}

function getCheckoutIdempotencyKey(){
  const keyName = 'sf_checkout_key_' + STORE_ID;
  let key = localStorage.getItem(keyName);
  if(!key){
    key = 'fx-' + STORE_ID + '-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
    localStorage.setItem(keyName, key);
  }
  return key;
}

document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
  radio.addEventListener('change', function(){
    document.getElementById('delivery-address-fields').style.display = this.value === 'delivery' ? 'block' : 'none';
    document.getElementById('pickup-fields').style.display = this.value === 'pickup' ? 'block' : 'none';
  });
});

const checkoutForm = document.getElementById('checkout-form');
if(checkoutForm){
  checkoutForm.addEventListener('submit', function(event){
    event.preventDefault();
    if(!this.reportValidity()) return;
    if(FX_OUTLETS.length > 1 && !fxSelectedOutlet){
      showToast('Please choose a fulfilment location');
      document.querySelector('.fx-location-picker')?.scrollIntoView({behavior:'smooth', block:'center'});
      return;
    }

    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const firstName = (form.querySelector('[name="first_name"]')?.value || '').trim();
    const lastName = (form.querySelector('[name="last_name"]')?.value || '').trim();
    const phone = (form.querySelector('[name="phone"]')?.value || '').trim();
    const email = (form.querySelector('[name="email"]')?.value || '').trim();
    const address = (form.querySelector('[name="address"]')?.value || '').trim();
    const city = (form.querySelector('[name="city"]')?.value || '').trim();
    const area = (form.querySelector('[name="area"]')?.value || '').trim();
    const deliveryMethod = form.querySelector('[name="delivery_method"]:checked')?.value || 'delivery';
    const paymentMethod = form.querySelector('[name="payment_method"]:checked')?.value || 'pay_on_delivery';
    const notes = (form.querySelector('[name="notes"]')?.value || '').trim();
    const cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
    if(!cart.length){ showToast('Your cart is empty'); return; }

    const data = new FormData();
    data.append('store_id', STORE_ID);
    data.append('customer_name', (firstName + ' ' + lastName).trim());
    data.append('customer_phone', phone);
    data.append('customer_email', email);
    data.append('customer_address', [address, area, city].filter(Boolean).join(', '));
    data.append('payment_method', paymentMethod);
    data.append('warehouse_id', fxSelectedOutlet || '');
    data.append('table_number', deliveryMethod === 'dine_in' ? <?= json_encode($table_number ?? ''); ?> : '');
    data.append('shipping_method', form.querySelector('[name="delivery_method"]:checked')?.dataset.method || '');
    data.append('shipping_city', form.querySelector('[name="shipping_city"]:checked')?.value || '');
    data.append('service_date', form.querySelector('[name="pickup_date"]')?.value || '');
    data.append('service_time', form.querySelector('[name="pickup_time"]')?.value || '');
    data.append('service_note', notes);
    data.append('cart', JSON.stringify(cart));
    data.append('idempotency_key',getCheckoutIdempotencyKey());
    const csrf = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
    if(csrf) data.append(csrf.name, csrf.value);

    if(submitButton){ submitButton.disabled = true; submitButton.textContent = 'Confirming order…'; }
    fetch('<?= base_url('storefront/place_order'); ?>', {method:'POST', body:data})
      .then(response => response.json())
      .then(result => {
        if(result.csrf_hash && csrf) csrf.value = result.csrf_hash;
        if(!result.status){ throw new Error(result.message || 'We could not place your order.'); }
        if(result.payment_required){
          if(!result.public_key || !window.PaystackPop) throw new Error('Payment could not open. Your basket is saved; please try again.');
          const paystackConfig = {
            key: result.public_key,
            email: result.email || email,
            amount: result.amount_kobo,
            currency: result.currency || 'NGN',
            ref: result.reference,
            callback: function(){
              localStorage.removeItem('sf_cart_' + STORE_ID);
              localStorage.removeItem('sf_checkout_key_' + STORE_ID);
              window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/'); ?>' + result.reference;
            },
            onClose: function(){
              if(submitButton){ submitButton.disabled = false; submitButton.textContent = 'Place Order'; }
              showToast('Payment cancelled — your order is still awaiting payment.');
            }
          };
          if(result.paystack_split && result.paystack_split.subaccount){
            paystackConfig.subaccount = result.paystack_split.subaccount;
            paystackConfig.transaction_charge = result.paystack_split.transaction_charge;
            paystackConfig.bearer = result.paystack_split.bearer;
          }
          const handler = PaystackPop.setup(paystackConfig);
          handler.openIframe();
          return;
        }
        localStorage.removeItem('sf_cart_' + STORE_ID);
        localStorage.removeItem('sf_checkout_key_' + STORE_ID);
        if(result.redirect_url) window.location.href = result.redirect_url;
      })
      .catch(error => {
        showToast(error.message || 'Network error. Please try again.');
        if(submitButton){ submitButton.disabled = false; submitButton.textContent = 'Place Order'; }
      });
  });
}

// Set min date for pickup
const pickupDate = document.querySelector('input[name="pickup_date"]');
if(pickupDate){
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  pickupDate.min = tomorrow.toISOString().split('T')[0];
  pickupDate.value = pickupDate.min;
}
</script>
