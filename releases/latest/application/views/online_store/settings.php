<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); $isPharmacy = mp_pharmacy_industry(mp_get_store_profile()['industry_type'] ?? ''); $standardPharmacy = $isPharmacy && mp_pharmacy_standard(); ?>
<style>
.os-url-box{background:var(--mp-bg)!important;padding:14px 18px!important;border-radius:12px!important;font-family:'SFMono-Regular',Consolas,Menlo,monospace!important;font-size:13px!important;word-break:break-all!important;border:1px solid var(--mp-border)!important;color:var(--mp-ink)!important}
.os-form-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:18px 24px!important}
.os-form-grid .full{grid-column:1/-1!important}
.os-form-grid .mp-form-group{display:flex!important;flex-direction:column!important;gap:6px!important}
.os-form-grid .mp-form-group>label{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important}
.os-section-title{font-size:13px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.05em!important;margin:24px 0 14px!important;padding-bottom:10px!important;border-bottom:1px solid var(--mp-border)!important}
.os-check-list{display:grid!important;grid-template-columns:1fr 1fr!important;gap:12px 24px!important}
.os-check-list label{display:inline-flex!important;align-items:center!important;gap:10px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;cursor:pointer!important;margin:0!important;padding:10px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important}
.os-check-list input[type=checkbox]{width:18px!important;height:18px!important;cursor:pointer!important}
.os-ship-row{display:flex!important;gap:10px!important;align-items:center!important;margin-bottom:10px!important;flex-wrap:wrap!important}
.os-ship-row input[type=text],.os-ship-row input[type=number]{padding:9px 12px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:13px!important;background:var(--mp-surface)!important;color:var(--mp-text)!important}
.os-ship-name{flex:1 1 180px!important;min-width:160px!important}
.os-ship-fee{flex:0 0 130px!important}
.os-ship-desc{flex:2 1 220px!important;min-width:180px!important}
.os-ship-en{flex:0 0 auto!important;display:inline-flex!important;align-items:center!important;gap:6px!important;font-size:12px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important;white-space:nowrap!important}
.os-ship-en input{width:16px!important;height:16px!important}
.os-ship-rm{flex:0 0 auto!important;width:36px!important;height:36px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-danger)!important;cursor:pointer!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}
.os-ship-rm:hover{background:rgba(220,38,38,.06)!important}
.os-badge-saved{display:inline-flex!important;align-items:center!important;gap:6px!important;padding:5px 12px!important;border-radius:8px!important;font-size:12px!important;font-weight:700!important;background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.os-badge-unsaved{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Configure your storefront URL, payments, shipping and integrations</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-globe"></i> Storefront URL</h3></div>
  <div class="mp-card-body">
    <?php
      $slug = $settings->store_slug ?? '';
      if(!$slug && !empty($store) && !empty($store->store_name)){
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name));
        $slug = trim($slug, '-');
      }
    ?>
    <div class="os-url-box"><?= base_url('store/' . ($slug ?: 'your-slug')); ?></div>
    <div class="mp-form-hint" style="margin-top:8px;">Share this link with customers to access your online store.</div>
  </div>
</div>

<form id="settings-form" onsubmit="return false;">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?= $base_url; ?>">

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-cog"></i> Store Settings</h3>
      <?php if(empty($is_saved)): ?><span class="os-badge-unsaved os-badge-saved"><i class="fa fa-exclamation-triangle"></i> Not Saved Yet</span><?php else: ?><span class="os-badge-saved"><i class="fa fa-check"></i> Saved</span><?php endif; ?>
    </div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="store_slug">Store Slug</label><input type="text" class="mp-form-control" id="store_slug" name="store_slug" value="<?= htmlspecialchars($settings->store_slug ?? ''); ?>" placeholder="your-store-name"><div class="mp-form-hint">Used in the store URL. Lowercase letters, numbers, and hyphens only.</div></div>
        <div class="mp-form-group"><label for="store_status">Store Status</label>
          <select class="mp-form-control" id="store_status" name="store_status">
            <option value="active" <?= ($settings->store_status ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="maintenance" <?= ($settings->store_status ?? '') == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
            <option value="deactivated" <?= ($settings->store_status ?? '') == 'deactivated' ? 'selected' : ''; ?>>Deactivated</option>
          </select>
        </div>
        <div class="mp-form-group full"><label for="store_description">Description</label><textarea class="mp-form-control" id="store_description" name="store_description" rows="3" placeholder="Short description of your store"><?= htmlspecialchars($settings->store_description ?? ''); ?></textarea></div>
        <div class="mp-form-group"><label for="whatsapp_number">WhatsApp Number</label><input type="text" class="mp-form-control" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($settings->whatsapp_number ?? ''); ?>" placeholder="e.g. 2348012345678"><div class="mp-form-hint">Include country code (e.g. 234 for Nigeria)</div></div>
        <div class="mp-form-group"><label for="store_email">Store Email</label><input type="email" class="mp-form-control" id="store_email" name="store_email" value="<?= htmlspecialchars($settings->store_email ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="store_phone">Store Phone</label><input type="text" class="mp-form-control" id="store_phone" name="store_phone" value="<?= htmlspecialchars($settings->store_phone ?? ''); ?>"></div>
        <?php if (!$standardPharmacy): ?>
        <div class="mp-form-group"><label for="default_branch_id">Default Branch</label>
          <select class="mp-form-control" id="default_branch_id" name="default_branch_id">
            <option value="0">- System Default -</option>
            <?php foreach($warehouses as $w): ?><option value="<?= (int)$w->id; ?>" <?= ($settings->default_branch_id ?? 0) == $w->id ? 'selected' : ''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option><?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="mp-form-group full"><label for="store_address">Store Address</label><textarea class="mp-form-control" id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings->store_address ?? ''); ?></textarea></div>
        <div class="mp-form-group"><label for="featured_products_limit">Featured Products Limit</label><input type="number" class="mp-form-control" id="featured_products_limit" name="featured_products_limit" value="<?= (int)($settings->featured_products_limit ?? 8); ?>"></div>
      </div>

      <div class="os-section-title"><i class="fa fa-credit-card"></i> Payment Options</div>
      <div class="mp-form-hint" style="margin-bottom:10px;">Payment route controls apply store-wide across every online-store theme. MartPoint Pay checkout is for NGN; the PayPal section below lets a fleet activate an additional route for eligible foreign-currency checkouts.</div>
      <div class="os-check-list">
        <label><input type="checkbox" id="allow_paystack" name="allow_paystack" <?= ($settings->allow_paystack ?? 1) ? 'checked' : ''; ?>> Allow MartPoint Pay Checkout (NGN)</label>
        <label><input type="checkbox" id="allow_whatsapp" name="allow_whatsapp" <?= ($settings->allow_whatsapp ?? 1) ? 'checked' : ''; ?>> Allow WhatsApp Orders</label>
        <label><input type="checkbox" id="allow_pay_on_delivery" name="allow_pay_on_delivery" <?= ($settings->allow_pay_on_delivery ?? 1) ? 'checked' : ''; ?>> Allow Pay on Delivery</label>
      </div>

      <div class="os-section-title"><i class="fa fa-user"></i> Customer pricing sign-in popup</div>
      <?php if ($this->db->field_exists('customer_pricing_notice_enabled', 'db_storefront_settings')): ?>
        <div class="os-check-list">
          <label><input type="checkbox" name="customer_pricing_notice_enabled" value="1" <?= !empty($settings->customer_pricing_notice_enabled) ? 'checked' : ''; ?>> Show popup to shoppers who are not signed in</label>
        </div>
        <div class="mp-form-hint" style="margin:6px 0 12px;">This only invites shoppers to sign in. It does not grant a customer group price; staff must assign the customer to a pricing group.</div>
        <div class="os-form-grid">
          <div class="mp-form-group"><label for="customer_pricing_notice_title">Popup title</label><input class="mp-form-control" type="text" id="customer_pricing_notice_title" name="customer_pricing_notice_title" maxlength="120" value="<?= htmlspecialchars($settings->customer_pricing_notice_title ?? 'Customer pricing', ENT_QUOTES, 'UTF-8'); ?>"></div>
          <div class="mp-form-group"><label for="customer_pricing_notice_link_text">Sign-in button text</label><input class="mp-form-control" type="text" id="customer_pricing_notice_link_text" name="customer_pricing_notice_link_text" maxlength="80" value="<?= htmlspecialchars($settings->customer_pricing_notice_link_text ?? 'Sign in to My Account', ENT_QUOTES, 'UTF-8'); ?>"></div>
          <div class="mp-form-group full"><label for="customer_pricing_notice_text">Popup message</label><textarea class="mp-form-control" id="customer_pricing_notice_text" name="customer_pricing_notice_text" rows="3" maxlength="500"><?= htmlspecialchars($settings->customer_pricing_notice_text ?? 'Sign in to My Account to check whether the store has assigned your account to a customer pricing group.', ENT_QUOTES, 'UTF-8'); ?></textarea></div>
        </div>
      <?php else: ?>
        <div class="mp-form-hint">Apply database migration 4.0.9.263 to configure this popup.</div>
      <?php endif; ?>

      <?php $mcCurrencies=json_decode($settings->checkout_currencies_json ?? '',true) ?: ['NGN']; $mcRates=json_decode($settings->currency_rates_json ?? '',true) ?: []; ?>
      <div class="os-section-title"><i class="fa fa-percent"></i> Storefront tax</div>
      <div class="mp-form-hint" style="margin-bottom:10px;">Optional store-wide tax for online orders across all themes. It applies after coupons to the item and service subtotal; shipping is not taxed. Inclusive tax is extracted from displayed prices; exclusive tax is added at checkout. Ask your tax adviser which setting and rate apply to your store.</div>
      <div class="os-check-list"><label><input type="checkbox" name="storefront_tax_enabled" value="1" <?= !empty($settings->storefront_tax_enabled)?'checked':''; ?>> Apply storefront tax</label></div>
      <div class="os-form-grid" style="margin-top:8px;">
        <div class="mp-form-group"><label for="storefront_tax_rate">Tax rate (%)</label><input class="mp-form-control" type="number" min="0" max="100" step="0.0001" name="storefront_tax_rate" id="storefront_tax_rate" value="<?= htmlspecialchars((string)($settings->storefront_tax_rate ?? '0')); ?>"></div>
        <div class="mp-form-group"><label for="storefront_tax_mode">Price treatment</label><select class="mp-form-control" name="storefront_tax_mode" id="storefront_tax_mode"><option value="exclusive" <?= ($settings->storefront_tax_mode??'exclusive')==='exclusive'?'selected':''; ?>>Exclusive — add tax to order total</option><option value="inclusive" <?= ($settings->storefront_tax_mode??'exclusive')==='inclusive'?'selected':''; ?>>Inclusive — tax is already in displayed prices</option></select></div>
      </div>
      <div class="os-section-title"><i class="fa fa-exchange"></i> Multi-currency online sales</div>
      <div class="mp-form-hint" style="margin-bottom:10px;">Merchant-maintained checkout rates. Rates mean <strong>1 foreign currency unit = X units of your store's base currency</strong>. Your inventory, POS and product prices remain in the base currency. PayPal credentials and foreign-currency capture must be configured before enabling foreign checkout.</div>
      <div class="os-check-list">
        <label><input type="checkbox" name="multicurrency_enabled" value="1" <?= !empty($settings->multicurrency_enabled)?'checked':''; ?>> Enable multi-currency online sales</label>
        <?php foreach(['NGN','CAD','USD','EUR','GBP'] as $mc): ?>
          <label><input type="checkbox" name="checkout_currency[]" value="<?= $mc; ?>" <?= in_array($mc,$mcCurrencies,true)?'checked':''; ?>> <?= $mc; ?> checkout</label>
        <?php endforeach; ?>
      </div>
      <div class="os-form-grid" style="margin-top:12px;">
        <div class="mp-form-group"><label for="currency_default_code">Default checkout currency</label><select class="mp-form-control" id="currency_default_code" name="currency_default_code"><?php foreach(['NGN','CAD','USD','EUR','GBP'] as $mc): ?><option value="<?= $mc; ?>" <?= ($settings->currency_default_code ?? 'NGN')===$mc?'selected':''; ?>><?= $mc; ?></option><?php endforeach; ?></select></div>
        <?php foreach(['CAD','USD','EUR','GBP'] as $mc): ?><div class="mp-form-group"><label for="rate_<?= $mc; ?>">1 <?= $mc; ?> = base currency</label><input class="mp-form-control" type="number" step="0.0001" min="0.0001" name="rate_<?= $mc; ?>" id="rate_<?= $mc; ?>" value="<?= htmlspecialchars((string)($mcRates[$mc] ?? '')); ?>" placeholder="Enter current merchant rate"></div><?php endforeach; ?>
      </div>
      <?php $mcCountryDefaults=json_decode($settings->currency_country_defaults_json ?? '',true) ?: ['NG'=>'NGN','CA'=>'CAD','US'=>'USD','GB'=>'GBP','DE'=>'EUR','FR'=>'EUR']; ?>
      <div class="os-form-grid" style="margin-top:8px;">
        <?php foreach(['NG'=>'Nigeria','CA'=>'Canada','US'=>'United States','GB'=>'United Kingdom','DE'=>'Germany','FR'=>'France'] as $cc=>$countryLabel): ?>
          <div class="mp-form-group"><label><?= htmlspecialchars($countryLabel); ?> default currency</label><input type="hidden" name="country_code[]" value="<?= $cc; ?>"><select class="mp-form-control" name="country_currency[]"><?php foreach(['NGN','CAD','USD','EUR','GBP'] as $mc): ?><option value="<?= $mc; ?>" <?= ($mcCountryDefaults[$cc] ?? ['NG'=>'NGN','CA'=>'CAD','US'=>'USD','GB'=>'GBP','DE'=>'EUR','FR'=>'EUR'][$cc])===$mc?'selected':''; ?>><?= $mc; ?></option><?php endforeach; ?></select></div>
        <?php endforeach; ?>
      </div>
      <div class="mp-form-hint">Country selection suggests this currency. Shoppers can still choose another enabled currency.</div>

      <div class="mp-card-form" style="margin-top:18px;border:1px solid var(--mp-border);">
        <div class="mp-card-head"><h3><i class="fa fa-paypal"></i> PayPal online-store checkout</h3></div>
        <div class="mp-card-body">
          <?php $pa=$paypal_account ?? []; ?>
          <p class="mp-form-hint">Separate from MartPoint subscription PayPal. Use a PayPal REST app for this store. PayPal approval and country eligibility depend on the merchant account; guest card checkout is not guaranteed.</p>
          <div class="os-form-grid">
            <div class="mp-form-group"><label for="paypal_environment">Environment</label><select class="mp-form-control" id="paypal_environment" name="paypal_environment"><option value="sandbox" <?= ($pa['environment']??'sandbox')==='sandbox'?'selected':''; ?>>Sandbox</option><option value="live" <?= ($pa['environment']??'sandbox')==='live'?'selected':''; ?>>Live</option></select></div>
            <div class="mp-form-group"><label for="paypal_client_id">REST Client ID</label><input class="mp-form-control" type="text" id="paypal_client_id" name="paypal_client_id" value="<?= htmlspecialchars((string)($pa['client_id']??''),ENT_QUOTES); ?>" autocomplete="off"></div>
            <div class="mp-form-group"><label for="paypal_client_secret">REST Client Secret <?= !empty($pa['configured'])?'(leave blank to keep saved secret)':''; ?></label><input class="mp-form-control" type="password" id="paypal_client_secret" name="paypal_client_secret" value="" autocomplete="new-password"></div>
            <div class="mp-form-group"><label for="paypal_webhook_id">Webhook ID</label><input class="mp-form-control" type="text" id="paypal_webhook_id" name="paypal_webhook_id" value="" placeholder="<?= !empty($pa['webhook_configured'])?'Configured — blank keeps saved ID':'Set up a webhook in the PayPal developer dashboard'; ?>" autocomplete="off"></div>
            <div class="mp-form-group full"><label><input type="checkbox" name="paypal_checkout_enabled" value="1" <?= !empty($pa['enabled'])?'checked':''; ?>> Enable PayPal checkout for eligible currencies</label><div class="mp-form-hint">Webhook URL: <code><?= htmlspecialchars(base_url('storefront/paypal_webhook')); ?></code> · Subscribe to <code>PAYMENT.CAPTURE.COMPLETED</code>. NGN checkout stays on MartPoint Pay; PayPal must support the store's selected currency and merchant account.</div></div>
          </div>
          <button type="button" class="mp-btn-primary" id="save-paypal-settings" style="margin-top:12px;">Save PayPal settings</button>
          <span id="paypal-settings-result" class="mp-form-hint" style="margin-left:10px;"></span>
        </div>
      </div>

      <div class="os-section-title"><i class="fa fa-truck"></i> Shipping</div>
      <div class="mp-form-group" style="margin-bottom:14px;"><label for="shipping_notice">Shipping Notice</label><textarea class="mp-form-control" id="shipping_notice" name="shipping_notice" rows="3" placeholder="e.g. We deliver within Lagos only. Orders placed after 4pm ship next day."><?= htmlspecialchars($settings->shipping_notice ?? ''); ?></textarea><div class="mp-form-hint">Shown to customers at checkout above the shipping options.</div></div>
      <div class="mp-form-group"><label>Shipping Methods</label><div class="mp-form-hint" style="margin-bottom:10px;">Add store-wide options such as pickup or delivery by quote. Destination zones below set location-based delivery rates and take priority for delivery.</div>
        <div id="shipping-methods-container">
          <?php
            $savedMethods = json_decode($settings->shipping_methods_json ?? '', true);
            if(!is_array($savedMethods) || empty($savedMethods)){
              $savedMethods = [['name'=>'','fee'=>'','description'=>'','enabled'=>1]];
            }
            foreach($savedMethods as $idx => $m):
          ?>
          <div class="os-ship-row shipping-method-row">
            <input type="hidden" class="sm-rowid" name="sm_rowid[]" value="<?= $idx; ?>">
            <input type="text" class="os-ship-name sm-name" name="sm_name[]" value="<?= htmlspecialchars($m['name'] ?? ''); ?>" placeholder="Method name (e.g. Home Delivery)">
            <input type="number" step="0.01" min="0" class="os-ship-fee sm-fee" name="sm_fee[]" value="<?= htmlspecialchars($m['fee'] ?? ''); ?>" placeholder="Fee 0.00">
            <input type="text" class="os-ship-desc sm-desc" name="sm_desc[]" value="<?= htmlspecialchars($m['description'] ?? ''); ?>" placeholder="Description (optional)">
            <label class="os-ship-en"><input type="checkbox" class="sm-quote" name="sm_quote[<?= $idx; ?>]" value="1" <?= !empty($m['quote']) ? 'checked' : ''; ?>> Fee on quote</label>
            <label class="os-ship-en"><input type="checkbox" class="sm-enabled" name="sm_enabled[<?= $idx; ?>]" value="1" <?= ($m['enabled'] ?? 1) ? 'checked' : ''; ?>> Enabled</label>
            <button type="button" class="os-ship-rm sm-remove" onclick="removeShippingMethod(this)"><i class="fa fa-trash"></i></button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="mp-qa-btn blue" onclick="addShippingMethod()" style="margin-top:8px;padding:8px 14px;font-size:13px;"><i class="fa fa-plus"></i> Add Method</button>
      </div>

      <div class="mp-form-group" style="margin-top:18px;border-top:1px dashed var(--mp-border);padding-top:16px;">
        <label class="os-ship-en" style="font-size:13px;padding:10px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);display:inline-flex;">
          <input type="checkbox" id="city_shipping_enabled" name="city_shipping_enabled" value="1" <?= !empty($settings->city_shipping_enabled) ? 'checked' : ''; ?>>
          Enable Shipping Zones
        </label>
        <div class="mp-form-hint" style="margin-top:6px;">Customers choose a covered destination at checkout and its zone rate is applied. Pickup methods remain available. Zones work for every store type and match from the most specific location (city, then state, then country); use a broad or * rule for fallback coverage.</div>
      </div>
      <div class="mp-form-group"><label>Shipping Zones</label><div class="mp-form-hint" style="margin-bottom:10px;">Add a destination at country, state/region, or city level. Leave a location field blank to make that rule broader. Use city <strong>*</strong> for other cities within its selected state/country. If zones overlap, the most specific matching destination wins. <strong>Minimum order</strong> blocks smaller baskets; <strong>Free over</strong> removes the zone fee once the subtotal reaches that amount.</div>
        <div id="city-zones-container" data-location-states-url="<?= base_url('online_store/location_states'); ?>" data-location-cities-url="<?= base_url('online_store/location_cities'); ?>" data-location-states="<?= htmlspecialchars(json_encode($shipping_states ?? []), ENT_QUOTES, 'UTF-8'); ?>">
          <?php
            foreach(($shipping_zone_rows ?? []) as $z):
          ?>
          <?php $this->load->view('online_store/_shipping_zone_row', ['zone'=>$z,'shipping_countries'=>$shipping_countries ?? []]); ?>
          <?php endforeach; ?>
        </div>
        <template id="mp-zone-row-template"><?php $this->load->view('online_store/_shipping_zone_row', ['zone'=>['name'=>'','country'=>'','fee'=>'','min_order'=>'','free_over'=>'','states'=>[],'cities'=>[],'wildcard'=>false],'shipping_countries'=>$shipping_countries ?? []]); ?></template>
        <button type="button" class="mp-qa-btn blue" onclick="addCityZone()" style="margin-top:8px;padding:8px 14px;font-size:13px;"><i class="fa fa-plus"></i> Add Zone</button>
      </div>

      <div class="os-section-title"><i class="fa fa-sliders"></i> Store Features</div>
      <div class="os-check-list">
        <label><input type="checkbox" id="allow_services" name="allow_services" <?= ($settings->allow_services ?? 1) ? 'checked' : ''; ?>> Allow Service Orders</label>
        <label><input type="checkbox" id="allow_backorder" name="allow_backorder" <?= ($settings->allow_backorder ?? 0) ? 'checked' : ''; ?>> Allow Backorder (Out of Stock)</label>
        <label><input type="checkbox" id="show_search" name="show_search" <?= ($settings->show_search ?? 1) ? 'checked' : ''; ?>> Show Search Bar</label>
        <label><input type="checkbox" id="show_categories" name="show_categories" <?= ($settings->show_categories ?? 1) ? 'checked' : ''; ?>> Show Category Chips</label>
        <label><input type="checkbox" id="show_whatsapp_cta" name="show_whatsapp_cta" <?= ($settings->show_whatsapp_cta ?? 1) ? 'checked' : ''; ?>> Show WhatsApp CTA Button</label>
      </div>
      <?php if (!$isPharmacy): ?>
      <div class="os-form-grid" style="margin-top:12px;">
        <div class="mp-form-group">
          <label class="os-ship-en" style="font-size:13px;padding:10px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);display:inline-flex;">
            <input type="checkbox" id="partner_marketplace_enabled" name="partner_marketplace_enabled" value="1" <?= ($settings->partner_marketplace_enabled ?? 0) ? 'checked' : ''; ?>> Enable controlled partner catalogue and fulfilment
          </label>
          <div class="mp-form-hint" style="margin-top:6px;">Optional. Lets approved partners contribute location-specific offers and fulfil selected orders under this brand. It is off by default; partner permissions, stock and order routing must still be enforced by the backend.</div>
        </div>
      </div>
      <?php elseif (is_admin() || is_store_admin()): ?>
      <p class="mp-form-hint" style="margin-top:16px;">Pharmacy mode is managed under <a href="<?= base_url('pharmacy_workspace/configuration'); ?>">Critical settings → Pharmacy configuration</a>.</p>
      <?php endif; ?>
      <div class="os-form-grid" style="margin-top:12px;">
        <div class="mp-form-group"><label class="os-ship-en" style="font-size:13px;padding:10px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);display:inline-flex;"><input type="checkbox" id="cart_recovery_enabled" name="cart_recovery_enabled" value="1" <?= ($settings->cart_recovery_enabled ?? 1) ? 'checked' : ''; ?>> Track Abandoned Carts</label><div class="mp-form-hint" style="margin-top:6px;">Stores cart snapshots so you can see and follow up on abandoned checkouts.</div></div>
        <div class="mp-form-group"><label for="abandoned_after_hours">Consider abandoned after (hours)</label><input type="number" min="1" max="720" class="mp-form-control" id="abandoned_after_hours" name="abandoned_after_hours" value="<?= (int)($settings->abandoned_after_hours ?? 24); ?>"><div class="mp-form-hint">A cart with customer contact details older than this appears on the Abandoned Carts page.</div></div>
      </div>
      <div class="os-section-title" style="font-size:13px;margin-top:14px;">Automated recovery</div><p class="mp-form-hint">Set up abandoned-cart reminders in Communications → Automation. The workflow uses an Automation plan slot and SMS or email credits, stops after purchase or opt-out, and allows up to three reminders at least 24 hours apart.</p><a class="mp-btn-primary" href="<?= base_url('communications/channel/automation?section=create&recipe=abandoned_cart'); ?>">Set up cart recovery workflow</a>

      <div class="os-section-title"><i class="fa fa-instagram"></i> Instagram Integration</div>
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="instagram_access_token">Access Token</label><input type="text" class="mp-form-control" id="instagram_access_token" name="instagram_access_token" value="<?= htmlspecialchars($settings->instagram_access_token ?? ''); ?>" placeholder="Instagram Basic Display API token"><div class="mp-form-hint"><a href="https://developers.facebook.com/docs/instagram-basic-display-api" target="_blank">Get token from Facebook Developers</a></div></div>
        <div class="mp-form-group"><label for="instagram_username">Username</label><input type="text" class="mp-form-control" id="instagram_username" name="instagram_username" value="<?= htmlspecialchars($settings->instagram_username ?? ''); ?>" placeholder="@handle"></div>
      </div>

      <div class="os-section-title"><i class="fa fa-whatsapp"></i> WhatsApp Business API (Meta)</div>
      <div class="os-form-grid">
        <div class="mp-form-group full"><label class="os-ship-en" style="font-size:13px;padding:10px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);display:inline-flex;"><input type="checkbox" id="whatsapp_api_enabled" name="whatsapp_api_enabled" value="1" <?= (!empty($settings->whatsapp_api_enabled)) ? 'checked' : ''; ?>> Enable WhatsApp Business API (template + session messages)</label><div class="mp-form-hint" style="margin-top:6px;">When on, MartPoint can send WhatsApp messages (order confirmations, shipping updates, support replies) through the WhatsApp Business Platform instead of only click-to-chat links. Requires a verified Meta Business and a dedicated phone number.</div></div>
        <div class="mp-form-group"><label for="whatsapp_api_token">Access Token</label><input type="text" class="mp-form-control" id="whatsapp_api_token" name="whatsapp_api_token" value="<?= htmlspecialchars($settings->whatsapp_api_token ?? ''); ?>" placeholder="WhatsApp API access token"><div class="mp-form-hint">Long-lived system-user or page token from WhatsApp Manager → your app → WhatsApp.</div></div>
        <div class="mp-form-group"><label for="whatsapp_phone_number_id">Phone Number ID</label><input type="text" class="mp-form-control" id="whatsapp_phone_number_id" name="whatsapp_phone_number_id" value="<?= htmlspecialchars($settings->whatsapp_phone_number_id ?? ''); ?>" placeholder="e.g. 1029384756"><div class="mp-form-hint">The numeric phone number ID shown in WhatsApp Manager.</div></div>
        <div class="mp-form-group"><label for="whatsapp_webhook_verify_token">Webhook Verify Token</label><input type="text" class="mp-form-control" id="whatsapp_webhook_verify_token" name="whatsapp_webhook_verify_token" value="<?= htmlspecialchars($settings->whatsapp_webhook_verify_token ?? ''); ?>" placeholder="A secret string you choose"><div class="mp-form-hint">Set the same string in Meta's webhook config. Used to verify webhook callbacks for WhatsApp, Instagram DM and Messenger.</div></div>
      </div>

      <div class="os-section-title"><i class="fa fa-facebook"></i> Facebook Messenger & Instagram DM</div>
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="facebook_page_access_token">Page Access Token</label><input type="text" class="mp-form-control" id="facebook_page_access_token" name="facebook_page_access_token" value="<?= htmlspecialchars($settings->facebook_page_access_token ?? ''); ?>" placeholder="Facebook Page access token"><div class="mp-form-hint">Token for the Facebook Page used with Messenger. Requires pages_manage_metadata, pages_read_engagement, pages_messaging permissions.</div></div>
        <div class="mp-form-group"><label for="facebook_page_id">Page ID</label><input type="text" class="mp-form-control" id="facebook_page_id" name="facebook_page_id" value="<?= htmlspecialchars($settings->facebook_page_id ?? ''); ?>" placeholder="Facebook Page ID"><div class="mp-form-hint">The numeric ID of the Facebook Page.</div></div>
        <div class="mp-form-group"><label for="instagram_business_account_id">Instagram Business Account ID</label><input type="text" class="mp-form-control" id="instagram_business_account_id" name="instagram_business_account_id" value="<?= htmlspecialchars($settings->instagram_business_account_id ?? ''); ?>" placeholder="Instagram Business Account ID"><div class="mp-form-hint">The Instagram Business Account linked to your Facebook Page. Used to send Instagram Direct replies.</div></div>
      </div>

      <div class="os-section-title"><i class="fa fa-google"></i> Google Reviews Integration</div>
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="google_places_api_key">Places API Key</label><input type="text" class="mp-form-control" id="google_places_api_key" name="google_places_api_key" value="<?= htmlspecialchars($settings->google_places_api_key ?? ''); ?>" placeholder="Google Places API Key"></div>
        <div class="mp-form-group"><label for="gmb_place_id">GMB Place ID</label><input type="text" class="mp-form-control" id="gmb_place_id" name="gmb_place_id" value="<?= htmlspecialchars($settings->gmb_place_id ?? ''); ?>" placeholder="Google Place ID"><div class="mp-form-hint"><a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Find your Place ID</a></div></div>
      </div>

      <div class="os-section-title"><i class="fa fa-shield"></i> Trust Badges</div>
      <?php $tb = json_decode($settings->trust_badges_json ?? '', true); ?>
      <div class="os-form-grid">
        <?php for($i=1;$i<=4;$i++): ?>
        <div class="mp-form-group"><label>Badge <?= $i; ?> Title</label><input type="text" class="mp-form-control" name="tb_<?= $i; ?>_title" value="<?= htmlspecialchars($tb[$i-1]['title'] ?? ''); ?>"></div>
        <div class="mp-form-group"><label>Badge <?= $i; ?> Description</label><input type="text" class="mp-form-control" name="tb_<?= $i; ?>_desc" value="<?= htmlspecialchars($tb[$i-1]['desc'] ?? ''); ?>"></div>
        <?php endfor; ?>
      </div>

      <div class="os-section-title"><i class="fa fa-envelope"></i> Newsletter CTA</div>
      <div class="os-form-grid">
        <div class="mp-form-group"><label>Headline</label><input type="text" class="mp-form-control" name="newsletter_title" value="<?= htmlspecialchars($settings->newsletter_title ?? 'Stay in the Loop'); ?>"></div>
        <div class="mp-form-group"><label>Sub-headline</label><input type="text" class="mp-form-control" name="newsletter_subtitle" value="<?= htmlspecialchars($settings->newsletter_subtitle ?? 'Subscribe for updates, deals and new arrivals.'); ?>"></div>
      </div>
    </div>
  </div>

  <div class="mp-form-actions" style="margin-top:20px;">
    <button type="button" id="btn-save" class="mp-btn-primary"><i class="fa fa-save"></i> Save Settings</button>
    <a href="<?= base_url('dashboard'); ?>" class="mp-btn-secondary"><i class="fa fa-times"></i> Close</a>
  </div>
</form>

<script>
$(function(){
  toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true, timeOut: 3000 };

  $('#btn-save').on("click", function(e){
    var base_url = $("#base_url").val();
    var btn = $(this);
    var slug = $('#store_slug').val().trim();
    if(!slug){
      toastr.warning('Please enter a store slug (URL name)');
      $('#store_slug').focus();
      return;
    }
    $(".mp-card-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    var data = new FormData($('#settings-form')[0]);
    $.ajax({
      type: 'POST',
      url: base_url + 'online_store/save_settings',
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(result){
        btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        $(".overlay").remove();
        var res = result;
        try { if(typeof res === 'string'){ res = JSON.parse(res); } } catch(err){ res = null; }
        if(res && res.status === 'success'){
          toastr.success(res.message);
          if(res.store_url){
            $('.os-url-box').fadeOut(200, function(){ $(this).text(res.store_url).fadeIn(200); });
          }
          $('.os-badge-unsaved').removeClass('os-badge-unsaved').html('<i class="fa fa-check"></i> Saved');
        } else {
          toastr.error((res && res.message) || 'Failed to save settings');
          if(!res){ console.log('Raw response:', result); }
        }
      },
      error: function(xhr, status, error){
        btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        $(".overlay").remove();
        toastr.error('Server error: ' + (error || 'Request failed'));
      }
    });
  });
});

var smIndex = <?= count($savedMethods); ?>;
function addShippingMethod(){
  var container = document.getElementById('shipping-methods-container');
  var row = document.createElement('div');
  var i = smIndex++;
  row.className = 'os-ship-row shipping-method-row';
  row.innerHTML = ''
    + '<input type="hidden" class="sm-rowid" name="sm_rowid[]" value="' + i + '">'
    + '<input type="text" class="os-ship-name sm-name" name="sm_name[]" value="" placeholder="Method name (e.g. Home Delivery)">'
    + '<input type="number" step="0.01" min="0" class="os-ship-fee sm-fee" name="sm_fee[]" value="" placeholder="Fee 0.00">'
    + '<input type="text" class="os-ship-desc sm-desc" name="sm_desc[]" value="" placeholder="Description (optional)">'
    + '<label class="os-ship-en"><input type="checkbox" class="sm-quote" name="sm_quote[' + i + ']" value="1"> Fee on quote</label>'
    + '<label class="os-ship-en"><input type="checkbox" class="sm-enabled" name="sm_enabled[' + i + ']" value="1" checked> Enabled</label>'
    + '<button type="button" class="os-ship-rm sm-remove" onclick="removeShippingMethod(this)"><i class="fa fa-trash"></i></button>';
  container.appendChild(row);
}
function removeShippingMethod(btn){
  var container = document.getElementById('shipping-methods-container');
  if(container.children.length <= 1){ return; }
  btn.closest('.shipping-method-row').remove();
}
function addCityZone(){
  var container = document.getElementById('city-zones-container');
  var node=document.getElementById('mp-zone-row-template').content.firstElementChild.cloneNode(true);
  container.appendChild(node);window.MPShippingZones.initRow(node);
}
function removeCityZone(btn){
  var container = document.getElementById('city-zones-container');
  var rows=container.querySelectorAll('[data-zone-row]');
  if(rows.length <= 1){
    window.MPShippingZones.clear(btn.closest('[data-zone-row]'));
    return;
  }
  btn.closest('.city-zone-row').remove();
}
</script>
<script src="<?= base_url('theme/js/shipping_zones.js?v=20261113-3'); ?>"></script>
<script>
(function(){
  var button=document.getElementById('save-paypal-settings');
  if(!button)return;
  button.addEventListener('click',function(){
    var form=document.getElementById('settings-form'),data=new FormData();
    ['paypal_environment','paypal_client_id','paypal_client_secret','paypal_webhook_id','paypal_checkout_enabled'].forEach(function(name){
      var el=form.querySelector('[name="'+name+'"]');
      if(el){if(el.type==='checkbox'){if(el.checked)data.append(name,'1');}else data.append(name,el.value);}
    });
    var csrf=form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');if(csrf)data.append(csrf.name,csrf.value);
    var result=document.getElementById('paypal-settings-result');button.disabled=true;result.textContent='Saving…';
    fetch('<?= base_url('online_store/save_paypal_settings'); ?>',{method:'POST',body:data,credentials:'same-origin'})
      .then(function(r){return r.json();}).then(function(r){result.textContent=r.message||'Saved.';if(r.status==='success'){var secret=form.querySelector('[name="paypal_client_secret"]');if(secret)secret.value='';}else result.style.color='#b91c1c';})
      .catch(function(){result.textContent='Could not save PayPal settings.';result.style.color='#b91c1c';})
      .finally(function(){button.disabled=false;});
  });
})();
</script>
<script>$(".online_store-settings-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
