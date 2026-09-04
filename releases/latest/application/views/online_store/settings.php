<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
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
        <div class="mp-form-group"><label for="default_branch_id">Default Branch</label>
          <select class="mp-form-control" id="default_branch_id" name="default_branch_id">
            <option value="0">- System Default -</option>
            <?php foreach($warehouses as $w): ?><option value="<?= (int)$w->id; ?>" <?= ($settings->default_branch_id ?? 0) == $w->id ? 'selected' : ''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group full"><label for="store_address">Store Address</label><textarea class="mp-form-control" id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings->store_address ?? ''); ?></textarea></div>
        <div class="mp-form-group"><label for="featured_products_limit">Featured Products Limit</label><input type="number" class="mp-form-control" id="featured_products_limit" name="featured_products_limit" value="<?= (int)($settings->featured_products_limit ?? 8); ?>"></div>
      </div>

      <div class="os-section-title"><i class="fa fa-credit-card"></i> Payment Options</div>
      <div class="os-check-list">
        <label><input type="checkbox" id="allow_paystack" name="allow_paystack" <?= ($settings->allow_paystack ?? 1) ? 'checked' : ''; ?>> Allow Paystack Checkout</label>
        <label><input type="checkbox" id="allow_whatsapp" name="allow_whatsapp" <?= ($settings->allow_whatsapp ?? 1) ? 'checked' : ''; ?>> Allow WhatsApp Orders</label>
        <label><input type="checkbox" id="allow_pay_on_delivery" name="allow_pay_on_delivery" <?= ($settings->allow_pay_on_delivery ?? 1) ? 'checked' : ''; ?>> Allow Pay on Delivery</label>
      </div>

      <div class="os-section-title"><i class="fa fa-truck"></i> Shipping</div>
      <div class="mp-form-group" style="margin-bottom:14px;"><label for="shipping_notice">Shipping Notice</label><textarea class="mp-form-control" id="shipping_notice" name="shipping_notice" rows="3" placeholder="e.g. We deliver within Lagos only. Orders placed after 4pm ship next day."><?= htmlspecialchars($settings->shipping_notice ?? ''); ?></textarea><div class="mp-form-hint">Shown to customers at checkout above the shipping options.</div></div>
      <div class="mp-form-group"><label>Shipping Methods</label><div class="mp-form-hint" style="margin-bottom:10px;">Add the shipping/delivery options customers can pick at checkout. The fee is added to the order total.</div>
        <div id="shipping-methods-container">
          <?php
            $savedMethods = json_decode($settings->shipping_methods_json ?? '', true);
            if(!is_array($savedMethods) || empty($savedMethods)){
              $savedMethods = [['name'=>'','fee'=>'','description'=>'','enabled'=>1]];
            }
            foreach($savedMethods as $idx => $m):
          ?>
          <div class="os-ship-row shipping-method-row">
            <input type="text" class="os-ship-name sm-name" name="sm_name[]" value="<?= htmlspecialchars($m['name'] ?? ''); ?>" placeholder="Method name (e.g. Home Delivery)">
            <input type="number" step="0.01" min="0" class="os-ship-fee sm-fee" name="sm_fee[]" value="<?= htmlspecialchars($m['fee'] ?? ''); ?>" placeholder="Fee 0.00">
            <input type="text" class="os-ship-desc sm-desc" name="sm_desc[]" value="<?= htmlspecialchars($m['description'] ?? ''); ?>" placeholder="Description (optional)">
            <label class="os-ship-en"><input type="checkbox" class="sm-enabled" name="sm_enabled[]" value="1" <?= ($m['enabled'] ?? 1) ? 'checked' : ''; ?>> Enabled</label>
            <button type="button" class="os-ship-rm sm-remove" onclick="removeShippingMethod(this)"><i class="fa fa-trash"></i></button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="mp-qa-btn blue" onclick="addShippingMethod()" style="margin-top:8px;padding:8px 14px;font-size:13px;"><i class="fa fa-plus"></i> Add Method</button>
      </div>

      <div class="os-section-title"><i class="fa fa-sliders"></i> Store Features</div>
      <div class="os-check-list">
        <label><input type="checkbox" id="allow_services" name="allow_services" <?= ($settings->allow_services ?? 1) ? 'checked' : ''; ?>> Allow Service Orders</label>
        <label><input type="checkbox" id="allow_backorder" name="allow_backorder" <?= ($settings->allow_backorder ?? 0) ? 'checked' : ''; ?>> Allow Backorder (Out of Stock)</label>
        <label><input type="checkbox" id="show_search" name="show_search" <?= ($settings->show_search ?? 1) ? 'checked' : ''; ?>> Show Search Bar</label>
        <label><input type="checkbox" id="show_categories" name="show_categories" <?= ($settings->show_categories ?? 1) ? 'checked' : ''; ?>> Show Category Chips</label>
        <label><input type="checkbox" id="show_whatsapp_cta" name="show_whatsapp_cta" <?= ($settings->show_whatsapp_cta ?? 1) ? 'checked' : ''; ?>> Show WhatsApp CTA Button</label>
      </div>

      <div class="os-section-title"><i class="fa fa-instagram"></i> Instagram Integration</div>
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="instagram_access_token">Access Token</label><input type="text" class="mp-form-control" id="instagram_access_token" name="instagram_access_token" value="<?= htmlspecialchars($settings->instagram_access_token ?? ''); ?>" placeholder="Instagram Basic Display API token"><div class="mp-form-hint"><a href="https://developers.facebook.com/docs/instagram-basic-display-api" target="_blank">Get token from Facebook Developers</a></div></div>
        <div class="mp-form-group"><label for="instagram_username">Username</label><input type="text" class="mp-form-control" id="instagram_username" name="instagram_username" value="<?= htmlspecialchars($settings->instagram_username ?? ''); ?>" placeholder="@handle"></div>
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
      success: function(result){
        btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        $(".overlay").remove();
        try {
          var res = JSON.parse(result);
          if(res.status === 'success'){
            toastr.success(res.message);
            if(res.store_url){
              $('.os-url-box').fadeOut(200, function(){ $(this).text(res.store_url).fadeIn(200); });
            }
            $('.os-badge-unsaved').removeClass('os-badge-unsaved').html('<i class="fa fa-check"></i> Saved');
          } else {
            toastr.error(res.message || 'Failed to save settings');
          }
        } catch(err) {
          toastr.error('Unexpected server response. Check console.');
          console.log('Raw response:', result);
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

function addShippingMethod(){
  var container = document.getElementById('shipping-methods-container');
  var row = document.createElement('div');
  row.className = 'os-ship-row shipping-method-row';
  row.innerHTML = ''
    + '<input type="text" class="os-ship-name sm-name" name="sm_name[]" value="" placeholder="Method name (e.g. Home Delivery)">'
    + '<input type="number" step="0.01" min="0" class="os-ship-fee sm-fee" name="sm_fee[]" value="" placeholder="Fee 0.00">'
    + '<input type="text" class="os-ship-desc sm-desc" name="sm_desc[]" value="" placeholder="Description (optional)">'
    + '<label class="os-ship-en"><input type="checkbox" class="sm-enabled" name="sm_enabled[]" value="1" checked> Enabled</label>'
    + '<button type="button" class="os-ship-rm sm-remove" onclick="removeShippingMethod(this)"><i class="fa fa-trash"></i></button>';
  container.appendChild(row);
}
function removeShippingMethod(btn){
  var container = document.getElementById('shipping-methods-container');
  if(container.children.length <= 1){ return; }
  btn.closest('.shipping-method-row').remove();
}
</script>
<script>$(".online_store-settings-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
