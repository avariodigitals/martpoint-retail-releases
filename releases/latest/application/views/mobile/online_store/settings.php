<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Store Settings</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .url-box { background: #F8FAFC; padding: 14px; border-radius: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; word-break: break-all; margin-bottom: 8px; }
    .status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .status-saved { background: #D1FAE5; color: #065F46; }
    .status-unsaved { background: #FEF3C7; color: #92400E; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); display: flex; justify-content: space-between; align-items: center; }
    .form-group { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; }
    .form-control:focus { border-color: var(--mp-primary); outline: none; }
    textarea.form-control { min-height: 90px; resize: vertical; }
    .choice-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .choice { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; }
    .choice input { position: absolute; opacity: 0; }
    .choice:has(input:checked) { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .check { display: flex; align-items: center; gap: 10px; font-size: 14px; margin-bottom: 10px; }
    .check input { width: 20px; height: 20px; }
    .sm-card { background: #F8FAFC; border-radius: 12px; padding: 12px; margin-bottom: 10px; }
    .sm-grid { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-ghost { background: #F1F5F9; color: var(--mp-ink); border: 1px solid var(--mp-border); }
    .btn-danger { background: #FEF2F2; color: #DC2626; }
    .hint { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Store Settings</h1>
        </div>
      </div>

      <?php
        $slug = $settings->store_slug ?? '';
        if(!$slug && !empty($store) && !empty($store->store_name)){
          $slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name));
          $slug = trim($slug, '-');
        }
        $slugVal = $settings->store_slug ?? '';
        if(!$slugVal && !empty($store) && !empty($store->store_name)){
          $slugVal = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name));
          $slugVal = trim($slugVal, '-');
        }
      ?>

      <div class="card">
        <div class="card-title">Storefront URL
          <span class="status <?= empty($is_saved) ? 'status-unsaved' : 'status-saved'; ?>"><?= empty($is_saved) ? 'Not Saved' : 'Saved'; ?></span>
        </div>
        <div class="form-group">
          <div class="url-box"><?= base_url('store/' . ($slug ?: 'your-slug')); ?></div>
          <p class="hint">Share this link with customers.</p>
        </div>
      </div>

      <form id="settings-form" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

        <div class="card">
          <div class="card-title">General</div>
          <div class="form-group">
            <label class="form-label">Store Slug</label>
            <input type="text" name="store_slug" class="form-control" value="<?= htmlspecialchars($slugVal); ?>" placeholder="your-store-name">
            <p class="hint">Lowercase letters, numbers, and hyphens only.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Store Status</label>
            <div class="choice-group">
              <?php $statuses = ['active' => 'Active', 'maintenance' => 'Maintenance', 'deactivated' => 'Deactivated']; ?>
              <?php foreach($statuses as $k => $v): ?>
                <label class="choice"><input type="radio" name="store_status" value="<?= $k; ?>" <?= ($settings->store_status ?? 'active') == $k ? 'checked' : ''; ?>><?= $v; ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="store_description" class="form-control" rows="3"><?= htmlspecialchars($settings->store_description ?? ''); ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">WhatsApp Number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars($settings->whatsapp_number ?? ''); ?>" placeholder="2348012345678">
            <p class="hint">Include country code (e.g. 234 for Nigeria)</p>
          </div>
          <div class="form-group">
            <label class="form-label">Store Email</label>
            <input type="email" name="store_email" class="form-control" value="<?= htmlspecialchars($settings->store_email ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Store Phone</label>
            <input type="text" name="store_phone" class="form-control" value="<?= htmlspecialchars($settings->store_phone ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Store Address</label>
            <textarea name="store_address" class="form-control" rows="3"><?= htmlspecialchars($settings->store_address ?? ''); ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Default Branch</label>
            <div class="choice-group">
              <label class="choice"><input type="radio" name="default_branch_id" value="0" <?= ($settings->default_branch_id ?? 0) == 0 ? 'checked' : ''; ?>>System Default</label>
              <?php foreach($warehouses as $w): ?>
                <label class="choice"><input type="radio" name="default_branch_id" value="<?= $w->id; ?>" <?= ($settings->default_branch_id ?? 0) == $w->id ? 'checked' : ''; ?>><?= htmlspecialchars($w->warehouse_name); ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Featured Products Limit</label>
            <input type="number" name="featured_products_limit" class="form-control" value="<?= (int)($settings->featured_products_limit ?? 8); ?>">
          </div>
        </div>

        <div class="card">
          <div class="card-title">Payment Options</div>
          <div class="form-group">
            <label class="check"><input type="checkbox" name="allow_paystack" value="1" <?= ($settings->allow_paystack ?? 1) ? 'checked' : ''; ?>> Allow Paystack Checkout <?= $paystack_enabled ? '' : '(not configured)'; ?></label>
            <label class="check"><input type="checkbox" name="allow_whatsapp" value="1" <?= ($settings->allow_whatsapp ?? 1) ? 'checked' : ''; ?>> Allow WhatsApp Orders</label>
            <label class="check"><input type="checkbox" name="allow_pay_on_delivery" value="1" <?= ($settings->allow_pay_on_delivery ?? 1) ? 'checked' : ''; ?>> Allow Pay on Delivery</label>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Shipping</div>
          <div class="form-group">
            <label class="form-label">Shipping Notice</label>
            <textarea name="shipping_notice" class="form-control" rows="3" placeholder="We deliver within Lagos only..."><?= htmlspecialchars($settings->shipping_notice ?? ''); ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Shipping Methods</label>
            <div id="shipping-methods-container">
              <?php
                $savedMethods = json_decode($settings->shipping_methods_json ?? '', true);
                if(!is_array($savedMethods) || empty($savedMethods)){ $savedMethods = [['name'=>'','fee'=>'','description'=>'','enabled'=>1]]; }
                foreach($savedMethods as $m):
              ?>
              <div class="sm-card">
                <input type="text" name="sm_name[]" class="form-control" value="<?= htmlspecialchars($m['name'] ?? ''); ?>" placeholder="Method name" style="margin-bottom:10px;">
                <div class="sm-grid">
                  <input type="number" step="0.01" min="0" name="sm_fee[]" class="form-control" value="<?= htmlspecialchars($m['fee'] ?? ''); ?>" placeholder="Fee">
                  <input type="text" name="sm_desc[]" class="form-control" value="<?= htmlspecialchars($m['description'] ?? ''); ?>" placeholder="Description">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                  <label class="check" style="margin:0;"><input type="checkbox" name="sm_enabled[]" value="1" <?= ($m['enabled'] ?? 1) ? 'checked' : ''; ?>> Enabled</label>
                  <button type="button" class="btn btn-danger" onclick="removeShippingMethod(this)" style="width:auto;padding:8px 12px;font-size:12px;"><i class="fa fa-trash"></i> Remove</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-ghost" onclick="addShippingMethod()" style="margin-top:6px;"><i class="fa fa-plus"></i> Add Method</button>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Store Features</div>
          <div class="form-group">
            <label class="check"><input type="checkbox" name="allow_services" value="1" <?= ($settings->allow_services ?? 1) ? 'checked' : ''; ?>> Allow Service Orders</label>
            <label class="check"><input type="checkbox" name="allow_backorder" value="1" <?= ($settings->allow_backorder ?? 0) ? 'checked' : ''; ?>> Allow Backorder (Out of Stock)</label>
            <label class="check"><input type="checkbox" name="show_search" value="1" <?= ($settings->show_search ?? 1) ? 'checked' : ''; ?>> Show Search Bar</label>
            <label class="check"><input type="checkbox" name="show_categories" value="1" <?= ($settings->show_categories ?? 1) ? 'checked' : ''; ?>> Show Category Chips</label>
            <label class="check"><input type="checkbox" name="show_whatsapp_cta" value="1" <?= ($settings->show_whatsapp_cta ?? 1) ? 'checked' : ''; ?>> Show WhatsApp CTA Button</label>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Instagram Integration</div>
          <div class="form-group">
            <label class="form-label">Access Token</label>
            <input type="text" name="instagram_access_token" class="form-control" value="<?= htmlspecialchars($settings->instagram_access_token ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="instagram_username" class="form-control" value="<?= htmlspecialchars($settings->instagram_username ?? ''); ?>" placeholder="@handle">
          </div>
        </div>

        <div class="card">
          <div class="card-title">Google Reviews</div>
          <div class="form-group">
            <label class="form-label">Places API Key</label>
            <input type="text" name="google_places_api_key" class="form-control" value="<?= htmlspecialchars($settings->google_places_api_key ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">GMB Place ID</label>
            <input type="text" name="gmb_place_id" class="form-control" value="<?= htmlspecialchars($settings->gmb_place_id ?? ''); ?>">
          </div>
        </div>

        <div class="card">
          <div class="card-title">Trust Badges</div>
          <?php $tb = json_decode($settings->trust_badges_json ?? '', true); ?>
          <?php for($i=1; $i<=4; $i++): ?>
          <div class="form-group">
            <label class="form-label">Badge <?= $i; ?></label>
            <input type="text" name="tb_<?= $i; ?>_title" class="form-control" value="<?= htmlspecialchars($tb[$i-1]['title'] ?? ''); ?>" placeholder="Title" style="margin-bottom:8px;">
            <input type="text" name="tb_<?= $i; ?>_desc" class="form-control" value="<?= htmlspecialchars($tb[$i-1]['desc'] ?? ''); ?>" placeholder="Description">
          </div>
          <?php endfor; ?>
        </div>

        <div class="card">
          <div class="card-title">Newsletter CTA</div>
          <div class="form-group">
            <label class="form-label">Headline</label>
            <input type="text" name="newsletter_title" class="form-control" value="<?= htmlspecialchars($settings->newsletter_title ?? 'Stay in the Loop'); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Sub-headline</label>
            <input type="text" name="newsletter_subtitle" class="form-control" value="<?= htmlspecialchars($settings->newsletter_subtitle ?? 'Subscribe for updates, deals and new arrivals.'); ?>">
          </div>
        </div>

        <div class="form-group" style="padding:0;">
          <button type="button" class="btn btn-primary" onclick="saveSettings()" id="btn-save">Save Settings</button>
        </div>
      </form>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function showToast(message, isError){
      const t = document.createElement('div');
      t.textContent = message;
      t.style.position='fixed'; t.style.top='16px'; t.style.left='16px'; t.style.right='16px';
      t.style.background=isError?'#DC2626':'#059669'; t.style.color='#fff'; t.style.padding='14px 16px';
      t.style.borderRadius='12px'; t.style.textAlign='center'; t.style.zIndex='1000'; t.style.fontWeight='600';
      document.body.appendChild(t); setTimeout(()=>t.remove(), 3000);
    }
    function addShippingMethod(){
      const c = document.getElementById('shipping-methods-container');
      const d = document.createElement('div');
      d.className = 'sm-card';
      d.innerHTML = '<input type="text" name="sm_name[]" class="form-control" placeholder="Method name" style="margin-bottom:10px;">'
        + '<div class="sm-grid"><input type="number" step="0.01" min="0" name="sm_fee[]" class="form-control" placeholder="Fee"><input type="text" name="sm_desc[]" class="form-control" placeholder="Description"></div>'
        + '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">'
        + '<label class="check" style="margin:0;"><input type="checkbox" name="sm_enabled[]" value="1" checked> Enabled</label>'
        + '<button type="button" class="btn btn-danger" onclick="removeShippingMethod(this)" style="width:auto;padding:8px 12px;font-size:12px;"><i class="fa fa-trash"></i> Remove</button></div>';
      c.appendChild(d);
    }
    function removeShippingMethod(btn){
      const c = document.getElementById('shipping-methods-container');
      if(c.children.length <= 1) return;
      btn.closest('.sm-card').remove();
    }
    function saveSettings(){
      const btn = document.getElementById('btn-save');
      const slug = document.querySelector('input[name="store_slug"]').value.trim();
      if(!slug){ showToast('Please enter a store slug', true); return; }
      btn.disabled = true; btn.textContent = 'Saving...';
      const fd = new FormData(document.getElementById('settings-form'));
      fetch('<?= base_url('online_store/save_settings'); ?>', {method:'POST', body:fd})
      .then(r=>r.text()).then(text=>{
        try { const res = JSON.parse(text); showToast(res.message || 'Saved', res.status !== 'success'); if(res.status === 'success' && res.store_url){ document.querySelector('.url-box').textContent = res.store_url; } } catch(e){ showToast('Unexpected response', true); console.log(text); }
        btn.disabled = false; btn.textContent = 'Save Settings';
      }).catch(()=>{ showToast('Server error', true); btn.disabled = false; btn.textContent = 'Save Settings'; });
    }
  </script>
</body>
</html>
