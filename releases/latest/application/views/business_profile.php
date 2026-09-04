<?php
/* Business Profile form — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
  /* Modern tab styling for mp_layout shell */
  .bp-tabs { display:flex; gap:0; border-bottom:2px solid var(--mp-border); margin-bottom:24px; flex-wrap:wrap; }
  .bp-tabs .bp-tab { padding:12px 20px; font-size:14px; font-weight:600; color:var(--mp-muted); cursor:pointer; border:none; background:none; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .15s ease; text-decoration:none; }
  .bp-tabs .bp-tab:hover { color:var(--mp-ink); text-decoration:none; }
  .bp-tabs .bp-tab.active { color:var(--mp-primary); border-bottom-color:var(--mp-primary); }
  .bp-tab-pane { display:none; }
  .bp-tab-pane.active { display:block; }

  .bp-flag-group-title { font-weight:700; font-size:13px; color:var(--mp-ink); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--mp-border); padding-bottom:8px; margin:24px 0 12px; cursor:pointer; display:flex; align-items:center; gap:8px; }
  .bp-flag-group-title:first-child { margin-top:0; }
  .bp-flag-group-title .fa { transition:transform .2s ease; }
  .bp-flag-group-title.collapsed .fa { transform:rotate(-90deg); }
  .bp-flag-group-content { margin-bottom:16px; }
  .bp-flag-group-content.collapsed { display:none; }
  .bp-flag-item { padding:8px 12px; display:flex; align-items:center; gap:12px; background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:10px; margin-bottom:8px; transition:all .15s ease; }
  .bp-flag-item:hover { border-color:var(--mp-primary); box-shadow:0 0 0 3px rgba(0,87,255,.06); }
  .bp-flag-item input[type="checkbox"] { display:none; }
  .bp-flag-switch { position:relative; width:44px; height:24px; background:var(--mp-border); border-radius:24px; cursor:pointer; flex-shrink:0; transition:background .2s ease; }
  .bp-flag-switch::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.2); transition:transform .2s ease; }
  .bp-flag-switch.on { background:var(--mp-success); }
  .bp-flag-switch.on::after { transform:translateX(20px); }
  .bp-flag-item label { font-weight:600; cursor:pointer; margin:0; font-size:14px; color:var(--mp-text); flex:1; }
  .bp-flag-status { font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; flex-shrink:0; }
  .bp-flag-status.on { background:rgba(5,150,105,.1); color:var(--mp-success); }
  .bp-flag-status.off { background:var(--mp-bg); color:var(--mp-muted); }
  .bp-label-item { margin-bottom:12px; }
  .bp-label-item label { font-size:12px; color:var(--mp-muted); margin-bottom:2px; display:block; }

  /* Preset preview */
  .bp-preset-preview { background:var(--mp-bg); border:1px solid var(--mp-border); border-radius:12px; padding:16px; margin-bottom:20px; display:none; }
  .bp-preset-preview.active { display:block; animation:fadeIn .3s ease; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
  .bp-preset-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
  .bp-preset-title { font-weight:700; color:var(--mp-text); font-size:15px; margin:0; }
  .bp-preset-badge { background:var(--mp-primary); color:#fff; font-size:11px; padding:2px 8px; border-radius:12px; }
  .bp-preset-body { display:flex; gap:16px; flex-wrap:wrap; }
  .bp-preset-card { flex:1; min-width:200px; background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:8px; padding:12px; }
  .bp-preset-card h5 { margin:0 0 8px; font-size:13px; color:var(--mp-ink); font-weight:600; }
  .bp-preset-features { display:flex; flex-wrap:wrap; gap:4px; }
  .bp-preset-feature-tag { background:rgba(0,87,255,.1); color:var(--mp-primary); font-size:11px; padding:2px 8px; border-radius:4px; }
  .bp-theme-card { display:flex; align-items:center; gap:10px; padding:8px; background:var(--mp-bg); border-radius:8px; margin-top:6px; }
  .bp-theme-swatch { width:32px; height:32px; border-radius:6px; flex-shrink:0; }
  .bp-theme-name { font-weight:600; font-size:13px; color:var(--mp-ink); }
  .bp-theme-industry { font-size:11px; color:var(--mp-muted); }

  .bp-section-title { font-weight:700; font-size:15px; color:var(--mp-text); margin:0 0 12px; padding-bottom:8px; border-bottom:1px solid var(--mp-border); }
  .bp-help-text { color:var(--mp-muted); font-size:12px; margin-top:4px; display:block; }
  .bp-catalogue-row { display:flex; gap:20px; flex-wrap:wrap; align-items:center; padding:8px 0; }
  .bp-catalogue-row label { font-weight:500; margin:0; }

  .bp-form-row { display:grid; grid-template-columns:200px 1fr; gap:8px 16px; align-items:center; margin-bottom:16px; }
  .bp-form-row label { font-size:13px; font-weight:600; color:var(--mp-ink); text-align:right; }
  .bp-form-row .bp-form-control { width:100%; }

  /* Balanced form grid for Business Identity tab */
  .bp-form-grid { display:grid!important; grid-template-columns:1fr 1fr!important; gap:20px 24px!important; }
  .bp-form-field { display:flex!important; flex-direction:column!important; gap:6px!important; }
  .bp-form-field .bp-field-label { font-size:13px!important; font-weight:600!important; color:var(--mp-ink)!important; margin:0!important; }
  .bp-form-field .bp-help-text { font-size:12px!important; color:var(--mp-muted)!important; margin-top:2px!important; }
  @media (max-width:768px){ .bp-form-grid{ grid-template-columns:1fr!important; } }

  /* Catalogue settings card */
  .bp-catalogue-card { background:var(--mp-bg)!important; border:1px solid var(--mp-border)!important; border-radius:12px!important; padding:20px!important; display:flex!important; flex-direction:column!important; gap:16px!important; }
  .bp-catalogue-toggle { display:flex!important; align-items:center!important; gap:12px!important; }
  .bp-catalogue-toggle input[type="checkbox"] { display:none!important; }
  .bp-catalogue-toggle .bp-flag-switch { position:relative!important; width:44px!important; height:24px!important; background:var(--mp-border)!important; border-radius:24px!important; cursor:pointer!important; flex-shrink:0!important; transition:background .2s ease!important; }
  .bp-catalogue-toggle .bp-flag-switch::after { content:''!important; position:absolute!important; top:3px!important; left:3px!important; width:18px!important; height:18px!important; border-radius:50%!important; background:#fff!important; box-shadow:0 1px 3px rgba(0,0,0,.2)!important; transition:transform .2s ease!important; }
  .bp-catalogue-toggle .bp-flag-switch.on { background:var(--mp-success)!important; }
  .bp-catalogue-toggle .bp-flag-switch.on::after { transform:translateX(20px)!important; }
  .bp-catalogue-toggle label { font-weight:600!important; cursor:pointer!important; margin:0!important; font-size:14px!important; color:var(--mp-text)!important; }
  .bp-catalogue-slug { display:flex!important; flex-direction:column!important; gap:6px!important; }
  .bp-catalogue-slug .bp-field-label { font-size:13px!important; font-weight:600!important; color:var(--mp-ink)!important; }
  .bp-catalogue-slug .form-control { max-width:300px!important; }
  .bp-catalogue-opts { display:flex!important; gap:24px!important; flex-wrap:wrap!important; }

  /* Label overrides grid */
  .bp-label-grid { display:grid!important; grid-template-columns:repeat(auto-fill,minmax(220px,1fr))!important; gap:16px!important; }
  .bp-label-item { display:flex!important; flex-direction:column!important; gap:4px!important; }
  .bp-label-item .bp-field-label { font-size:12px!important; font-weight:600!important; color:var(--mp-ink)!important; margin:0!important; }
  .bp-label-item .text-muted { font-weight:400!important; }
</style>
<div class="mp-page-head"><h1 class="mp-page-title"><?= $page_title; ?></h1><p class="mp-page-sub">Configure your business type, features, templates and storefront theme.</p></div>
<div class="mp-card">
<div class="mp-card-body">
  <div class="bp-tabs">
    <button type="button" class="bp-tab active" data-tab="tab_identity">Business Identity</button>
    <button type="button" class="bp-tab" data-tab="tab_features">Feature Flags</button>
    <button type="button" class="bp-tab" data-tab="tab_templates">Templates &amp; Labels</button>
    <button type="button" class="bp-tab" data-tab="tab_advanced">Advanced</button>
  </div>
  <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'businessProfileForm', 'method'=>'POST')); ?>
          <div class="bp-tab-content">

            <!-- TAB 1: Business Identity -->
            <div class="bp-tab-pane active" id="tab_identity">
              <div class="box-body">
                <!-- Two-column grid for primary selects -->
                <div class="bp-form-grid">
                  <div class="bp-form-field">
                    <label for="industry_type" class="bp-field-label">Business Type</label>
                    <select name="industry_type" id="industry_type" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Business Type --</option>
                      <?php foreach ($business_types as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['industry_type']) && $profile['industry_type']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span class="bp-help-text">Changing this shows a recommended preset. Click <strong>Apply Recommended</strong> to use it.</span>
                  </div>
                  <div class="bp-form-field">
                    <label for="business_model" class="bp-field-label">Business Model</label>
                    <select name="business_model" id="business_model" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Model --</option>
                      <?php foreach ($business_models as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['business_model']) && $profile['business_model']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Preset Preview Panel -->
                <div id="presetPreview" class="bp-preset-preview" style="margin-top:20px;">
                  <div class="bp-preset-header">
                    <h4 class="bp-preset-title"><i class="fa fa-magic text-primary"></i> Recommended Settings for <span id="presetName"></span></h4>
                    <span class="bp-preset-badge">PRESET</span>
                  </div>
                  <div class="bp-preset-body">
                    <div class="bp-preset-card">
                      <h5><i class="fa fa-check-square-o"></i> Recommended Features</h5>
                      <div id="presetFeatures" class="bp-preset-features"></div>
                    </div>
                    <div class="bp-preset-card">
                      <h5><i class="fa fa-paint-brush"></i> Recommended Theme</h5>
                      <div id="presetTheme" class="bp-theme-card"></div>
                    </div>
                    <div class="bp-preset-card">
                      <h5><i class="fa fa-cogs"></i> Templates</h5>
                      <div id="presetTemplates" style="font-size:12px;color:var(--mp-muted);"></div>
                    </div>
                  </div>
                  <div style="margin-top:12px; text-align:right;">
                    <button type="button" id="applyPresetBtn" class="mp-qa-btn green"><i class="fa fa-check"></i> Apply Recommended Settings</button>
                    <button type="button" id="dismissPresetBtn" class="mp-qa-btn" style="margin-left:6px;">Dismiss</button>
                  </div>
                </div>

                <!-- Two-column grid for theme + workflow -->
                <div class="bp-form-grid" style="margin-top:20px;">
                  <div class="bp-form-field">
                    <label for="storefront_theme_key" class="bp-field-label">Storefront Theme</label>
                    <select name="storefront_theme_key" id="storefront_theme_key" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Theme --</option>
                      <?php foreach ($storefront_themes as $key => $theme):
                        $theme_key = isset($theme->theme_key) ? $theme->theme_key : $key; ?>
                        <option value="<?= htmlspecialchars($theme_key); ?>" data-color="<?= htmlspecialchars($theme->default_primary_color ?? '#3B82F6'); ?>" <?= (isset($profile['storefront_theme_key']) && $profile['storefront_theme_key']==$theme_key)?'selected':''; ?>><?= htmlspecialchars($theme->theme_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span class="bp-help-text" id="themeSuggestionText"></span>
                  </div>
                  <div class="bp-form-field" id="themeSuggestionCard" style="display:none;">
                    <label class="bp-field-label">&nbsp;</label>
                    <div class="bp-theme-card" style="margin:0;">
                      <div class="bp-theme-swatch" id="themeSwatch" style="background:#3b82f6;"></div>
                      <div>
                        <div class="bp-theme-name" id="themeSuggestedName">General Retail</div>
                        <div class="bp-theme-industry" id="themeSuggestedIndustry">Recommended for your business type</div>
                      </div>
                      <button type="button" id="applyThemeBtn" class="mp-qa-btn green" style="padding:6px 12px;font-size:12px;"><i class="fa fa-check"></i> Apply</button>
                    </div>
                  </div>
                </div>

                <div class="bp-form-grid" style="margin-top:20px;">
                  <div class="bp-form-field">
                    <label for="workflow_template_key" class="bp-field-label">Workflow Template</label>
                    <select name="workflow_template_key" id="workflow_template_key" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Workflow --</option>
                      <?php foreach ($workflow_templates as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['workflow_template_key']) && $profile['workflow_template_key']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="bp-form-field">
                    <label for="dashboard_template_key" class="bp-field-label">Dashboard Template</label>
                    <select name="dashboard_template_key" id="dashboard_template_key" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Dashboard --</option>
                      <?php foreach ($dashboard_templates as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['dashboard_template_key']) && $profile['dashboard_template_key']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: Feature Flags -->
            <div class="bp-tab-pane" id="tab_features">
              <div class="box-body">
                <?php
                  $saved_flags = [];
                  if (!empty($profile['feature_flags_json'])) {
                    $decoded = json_decode($profile['feature_flags_json'], true);
                    if (is_array($decoded)) $saved_flags = $decoded;
                  }
                  // Determine recommended features for current business type
                  $current_preset_key = $profile['industry_type'] ?? '';
                  $recommended_features = [];
                  if (!empty($current_preset_key) && isset($presets[$current_preset_key]['features'])) {
                    $recommended_features = $presets[$current_preset_key]['features'];
                  }
                  $feature_groups = [
                    'Core Modules' => [
                      'accounts','warehouse','multi_store'
                    ],
                    'Sales & Storefront' => [
                      'online_store','qr_ordering','loyalty','gift_cards','store_credit','public_catalogue','price_catalogue'
                    ],
                    'Products & Inventory' => [
                      'multi_unit_inventory','batch_tracking','expiry_tracking','mfg_tracking','serial_number_tracking','imei_tracking','warranty_tracking','bundles'
                    ],
                    'Services & Appointments' => [
                      'appointments','service_workflow','custom_orders','packages','memberships'
                    ],
                    'Workflows & Operations' => [
                      'kitchen_workflow','table_management','laundry_workflow','treatment_notes','medical_notes','staff_assignment','staff_commission','delivery_scheduling','production_workflow','recipe_tracking'
                    ],
                    'Management' => [
                      'payplan','customer_notes','manager_approvals','cashier_shifts'
                    ],
                  ];
                  foreach ($feature_groups as $group_name => $keys): 
                    // Show all feature flags regardless of preset
                    $visible_keys = [];
                    foreach ($keys as $k) {
                      if (!isset($feature_flags[$k])) continue;
                      $visible_keys[] = $k;
                    }
                    if (empty($visible_keys)) continue; // skip empty groups
                  ?>
                  <div class="bp-flag-group-title" data-group="<?= htmlspecialchars($group_name); ?>"><i class="fa fa-folder-o"></i> <?= $group_name; ?></div>
                  <div class="bp-flag-group-content" data-group-content="<?= htmlspecialchars($group_name); ?>">
                  <div class="row">
                    <?php foreach ($visible_keys as $key):
                      $label = $feature_flags[$key];
                      // Defaults when the flag has never been saved: core modules default to enabled
                      if (!isset($saved_flags[$key])) {
                        if ($key === 'mfg_tracking') {
                          $is_checked = mp_feature_enabled('expiry_tracking');
                        } elseif ($key === 'accounts') {
                          $is_checked = accounts_module();
                        } elseif ($key === 'warehouse') {
                          $is_checked = warehouse_module();
                        } else {
                          $is_checked = false;
                        }
                      } else {
                        $is_checked = filter_var($saved_flags[$key], FILTER_VALIDATE_BOOLEAN);
                      }
                      $checked = $is_checked ? 'checked' : '';
                    ?>
                      <div class="col-md-4 col-sm-6 col-xs-12 bp-flag-col" data-feature-key="<?= $key; ?>">
                        <div class="bp-flag-item">
                          <input type="checkbox" name="feature_flags[<?= $key; ?>]" id="ff_<?= $key; ?>" value="1" <?= $checked; ?>>
                          <span class="bp-flag-switch <?= $is_checked ? 'on' : ''; ?>" onclick="bpToggle('ff_<?= $key; ?>', this)"></span>
                          <label for="ff_<?= $key; ?>" onclick="bpToggle('ff_<?= $key; ?>', document.getElementById('ff_<?= $key; ?>').nextElementSibling); return false;"><?= $label; ?></label>
                          <span class="bp-flag-status <?= $is_checked ? 'on' : 'off'; ?>" id="ff_status_<?= $key; ?>"><?= $is_checked ? 'ON' : 'OFF'; ?></span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  </div><!-- /.bp-flag-group-content -->
                <?php endforeach; ?>
              </div>
            </div>

            <!-- TAB 3: Templates & Labels -->
            <div class="bp-tab-pane" id="tab_templates">
              <div class="box-body">
                <!-- Public Catalogue Settings -->
                <h4 class="bp-section-title"><i class="fa fa-globe"></i> Public Catalogue</h4>
                <p class="bp-help-text" style="margin-bottom:16px;">A lighter browsing page for customers to view your products and services without full checkout.</p>
                <?php
                  $cat_settings = [];
                  if (!empty($profile['industry_settings_json'])) {
                    $decoded = json_decode($profile['industry_settings_json'], true);
                    if (is_array($decoded) && isset($decoded['catalogue'])) {
                      $cat_settings = $decoded['catalogue'];
                    }
                  }
                ?>
                <div class="bp-catalogue-card">
                  <div class="bp-catalogue-toggle">
                    <input type="checkbox" name="catalogue_enabled" id="catalogue_enabled" value="1" <?= (!empty($cat_settings['enabled']) ? 'checked' : ''); ?>>
                    <span class="bp-flag-switch <?= (!empty($cat_settings['enabled']) ? 'on' : ''); ?>" onclick="bpToggle('catalogue_enabled', this)"></span>
                    <label for="catalogue_enabled" onclick="bpToggle('catalogue_enabled', document.getElementById('catalogue_enabled').nextElementSibling); return false;">Enable Public Catalogue</label>
                  </div>
                  <div class="bp-catalogue-slug">
                    <label class="bp-field-label">Slug / URL</label>
                    <input type="text" name="catalogue_slug" id="catalogue_slug" class="form-control" value="<?= htmlspecialchars($cat_settings['slug'] ?? 'catalogue'); ?>" placeholder="catalogue">
                  </div>
                  <div class="bp-catalogue-opts">
                    <div class="bp-catalogue-toggle">
                      <input type="checkbox" name="catalogue_show_products" id="catalogue_show_products" value="1" <?= (!isset($cat_settings['show_products']) || $cat_settings['show_products'] ? 'checked' : ''); ?>>
                      <span class="bp-flag-switch <?= (!isset($cat_settings['show_products']) || $cat_settings['show_products'] ? 'on' : ''); ?>" onclick="bpToggle('catalogue_show_products', this)"></span>
                      <label for="catalogue_show_products" onclick="bpToggle('catalogue_show_products', document.getElementById('catalogue_show_products').nextElementSibling); return false;">Show Products</label>
                    </div>
                    <div class="bp-catalogue-toggle">
                      <input type="checkbox" name="catalogue_show_services" id="catalogue_show_services" value="1" <?= (!isset($cat_settings['show_services']) || $cat_settings['show_services'] ? 'checked' : ''); ?>>
                      <span class="bp-flag-switch <?= (!isset($cat_settings['show_services']) || $cat_settings['show_services'] ? 'on' : ''); ?>" onclick="bpToggle('catalogue_show_services', this)"></span>
                      <label for="catalogue_show_services" onclick="bpToggle('catalogue_show_services', document.getElementById('catalogue_show_services').nextElementSibling); return false;">Show Services</label>
                    </div>
                  </div>
                </div>
                <span class="bp-help-text" style="margin-top:8px;">The public catalogue appears at /catalogue/{slug} when enabled and the <strong>Public Catalogue</strong> feature flag is on.</span>

                <!-- Label Overrides -->
                <h4 class="bp-section-title" style="margin-top:32px;"><i class="fa fa-language"></i> Label Overrides</h4>
                <p class="bp-help-text" style="margin-bottom:16px;">Customize terminology for your industry. Leave blank to use the default.</p>
                <div class="bp-label-grid">
                  <?php
                    $saved_labels = [];
                    if (!empty($profile['label_overrides_json'])) {
                      $decoded = json_decode($profile['label_overrides_json'], true);
                      if (is_array($decoded)) $saved_labels = $decoded;
                    }
                    foreach ($label_defaults as $k => $v):
                      $val = isset($saved_labels[$k]) ? $saved_labels[$k] : '';
                      $default_label = ucwords(str_replace('_', ' ', $k));
                  ?>
                    <div class="bp-label-item">
                      <label class="bp-field-label"><?= $default_label; ?> <span class="text-muted">(<?= $v; ?>)</span></label>
                      <input type="text" name="label_overrides[<?= $k; ?>]" class="form-control"
                        value="<?= $val; ?>" placeholder="<?= $v; ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- TAB 4: Advanced -->
            <div class="bp-tab-pane" id="tab_advanced">
              <div class="box-body">
                <div class="bp-form-field" style="max-width:800px;">
                  <label for="industry_settings_json" class="bp-field-label">Industry Settings (JSON)</label>
                  <textarea name="industry_settings_json" id="industry_settings_json" class="form-control" rows="8" placeholder='{"custom_key": "value"}'><?= (!empty($profile['industry_settings_json']))?$profile['industry_settings_json']:''; ?></textarea>
                  <span class="bp-help-text">Optional raw JSON configuration for industry-specific settings.</span>
                </div>
              </div>
            </div>

          </div><!-- /.bp-tab-content -->

          <div class="mp-form-actions" style="margin-top:24px; display:flex; gap:12px; justify-content:center;">
              <button type="submit" id="saveBtn" class="mp-qa-btn green"><i class="fa fa-save"></i> Save Business Profile</button>
              <a href="<?= $base_url; ?>dashboard" class="mp-qa-btn">Close</a>
          </div>

          <?= form_close(); ?>
</div><!-- /.mp-card-body -->
</div><!-- /.mp-card -->
<script type="text/javascript">
  $(document).ready(function() {
    $(".business-profile-active-li").addClass("active");

    // Modern tab switching (no Bootstrap dependency)
    $('.bp-tabs .bp-tab').on('click', function() {
      var target = $(this).data('tab');
      $('.bp-tabs .bp-tab').removeClass('active');
      $(this).addClass('active');
      $('.bp-tab-pane').removeClass('active');
      $('#' + target).addClass('active');
      // Re-initialize select2 in the newly visible tab
      $('#' + target + ' .select2').each(function() {
        if ($(this).data('select2')) { $(this).select2(); }
      });
    });

    var currentPreset = null;
    var themeColors = {
      <?php $first = true; foreach($storefront_themes as $key => $theme):
        $theme_key = isset($theme->theme_key) ? $theme->theme_key : $key; ?>
        <?php if(!$first) echo ','; $first = false; ?>'<?= addslashes($theme_key); ?>': '<?= htmlspecialchars($theme->default_primary_color ?? '#3B82F6'); ?>'
      <?php endforeach; ?>
    };

    // Pre-load the preset for the currently-selected business type so the
    // Apply buttons work on page load (not only after a dropdown change).
    (function preLoadPreset() {
      var type = $('#industry_type').val();
      if (!type) return;
      $.ajax({
        url: '<?= base_url("business_profile/get_preset"); ?>',
        type: 'POST',
        data: { industry_type: type, '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 'success' && res.preset) {
            currentPreset = res.preset;
          }
        }
      });
    })();

    // Form submit — merged handler: build catalogue JSON then AJAX save
    $('#businessProfileForm').on('submit', function(e) {
      e.preventDefault();
      // Build catalogue settings into industry_settings_json
      var catalogue = {
        enabled: $('#catalogue_enabled').is(':checked') ? 1 : 0,
        slug: $('#catalogue_slug').val() || 'catalogue',
        show_products: $('input[name="catalogue_show_products"]').is(':checked') ? 1 : 0,
        show_services: $('input[name="catalogue_show_services"]').is(':checked') ? 1 : 0
      };
      var existing = $('#industry_settings_json').val().trim();
      var parsed = {};
      try { if (existing) parsed = JSON.parse(existing); } catch(err){}
      parsed.catalogue = catalogue;
      $('#industry_settings_json').val(JSON.stringify(parsed));

      var $btn = $('#saveBtn');
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
      $.ajax({
        url: '<?= base_url("business_profile/save"); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
          if (res.status === 'success') {
            toastr.success(res.message);
            setTimeout(function() { location.reload(); }, 800);
          } else {
            toastr.error(res.message);
          }
        },
        error: function() { toastr.error('An error occurred while saving.'); },
        complete: function() { $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save'); }
      });
    });

    // Business Type change: auto-populate form fields AND show preview
    $('#industry_type').on('change', function() {
      var type = $(this).val();
      currentPreset = null;
      $('#presetPreview').removeClass('active');
      $('#themeSuggestionCard').hide();
      if (!type) return;
      $.ajax({
        url: '<?= base_url("business_profile/get_preset"); ?>',
        type: 'POST',
        data: { industry_type: type, '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 'success' && res.preset) {
            currentPreset = res.preset;
            showPresetPreview(res.preset); // show preview panel
            showThemeSuggestion(res.preset.theme_key);
          }
        },
        error: function() { toastr.error('Could not load preset. Please try again.'); }
      });
    });

    function applyPresetValues(p) {
      if (!p) return;
      $('#business_model').val(p.business_model || '').trigger('change');
      $('#workflow_template_key').val(p.workflow_template || 'retail_standard').trigger('change');
      $('#dashboard_template_key').val(p.dashboard_template || 'general_retail').trigger('change');
      $('#storefront_theme_key').val(p.theme_key || 'general_retail').trigger('change');

      // Show/hide flags based on preset recommendations
      var recommended = Array.isArray(p.features) ? p.features : [];
      $('.bp-flag-col').each(function() {
        var key = $(this).data('feature-key');
        var $cb = $(this).find('input[type="checkbox"]');
        var $switch = $(this).find('.bp-flag-switch');
        var $status = $('#ff_status_' + key);
        if (recommended.indexOf(key) !== -1) {
          $(this).show();
          $cb.prop('checked', true);
          $switch.addClass('on');
          $status.removeClass('off').addClass('on').text('ON');
        } else {
          $(this).hide();
          $cb.prop('checked', false);
          $switch.removeClass('on');
          $status.removeClass('on').addClass('off').text('OFF');
        }
      });
      // Hide group titles that have no visible children
      $('.bp-flag-group-title').each(function() {
        var groupName = $(this).data('group');
        var $nextRow = $(this).next('.row');
        var visibleChildren = $nextRow.find('.bp-flag-col:visible').length;
        $(this).toggle(visibleChildren > 0);
        $nextRow.toggle(visibleChildren > 0);
      });

      if (p.labels) {
        for (var k in p.labels) {
          $('input[name="label_overrides[' + k + ']"]').val(p.labels[k]);
        }
      }
    }

    function showPresetPreview(preset) {
      var btLabel = $('#industry_type option:selected').text();
      $('#presetName').text(btLabel);

      var featuresHtml = '';
      if (Array.isArray(preset.features) && preset.features.length) {
        preset.features.forEach(function(f) {
          featuresHtml += '<span class="bp-preset-feature-tag">' + f.replace(/_/g, ' ') + '</span>';
        });
      }
      $('#presetFeatures').html(featuresHtml || '<span class="text-muted">No specific features</span>');

      var themeKey = preset.theme_key || 'general_retail';
      var themeLabel = $('#storefront_theme_key option[value="' + themeKey + '"]').text() || themeKey;
      var color = themeColors[themeKey] || '#3B82F6';
      $('#presetTheme').html(
        '<div style="display:flex;align-items:center;gap:8px;">' +
        '<div class="bp-theme-swatch" style="background:' + color + ';"></div>' +
        '<div><div class="bp-theme-name">' + themeLabel + '</div><div class="bp-theme-industry">Storefront Theme</div></div>' +
        '</div>'
      );

      var wf = $('#workflow_template_key option[value="' + (preset.workflow_template || 'retail_standard') + '"]').text() || preset.workflow_template;
      var db = $('#dashboard_template_key option[value="' + (preset.dashboard_template || 'general_retail') + '"]').text() || preset.dashboard_template;
      $('#presetTemplates').html(
        '<div><strong>Workflow:</strong> ' + (wf || 'Default') + '</div>' +
        '<div style="margin-top:4px;"><strong>Dashboard:</strong> ' + (db || 'Default') + '</div>'
      );

      $('#presetPreview').addClass('active');
    }

    function showThemeSuggestion(themeKey) {
      if (!themeKey) return;
      var currentTheme = $('#storefront_theme_key').val();
      if (currentTheme === themeKey) return; // already matches
      var themeLabel = $('#storefront_theme_key option[value="' + themeKey + '"]').text() || themeKey;
      var color = themeColors[themeKey] || '#3B82F6';
      $('#themeSwatch').css('background', color);
      $('#themeSuggestedName').text(themeLabel);
      $('#themeSuggestionText').text('Recommended: ' + themeLabel);
      $('#themeSuggestionCard').show();
    }

    // Apply Preset button
    $('#applyPresetBtn').on('click', function() {
      if (!currentPreset) { toastr.warning('Please select a business type first.'); return; }
      applyPresetValues(currentPreset);
      $('#presetPreview').removeClass('active');
      $('#themeSuggestionCard').hide();
      toastr.success('Recommended settings applied. Click Save to confirm.');
    });

    $('#dismissPresetBtn').on('click', function() {
      $('#presetPreview').removeClass('active');
    });

    // Apply Theme button
    $('#applyThemeBtn').on('click', function() {
      if (!currentPreset || !currentPreset.theme_key) { toastr.warning('No theme recommendation available.'); return; }
      $('#storefront_theme_key').val(currentPreset.theme_key).trigger('change');
      $('#themeSuggestionCard').hide();
      toastr.success('Theme applied. Click Save to confirm.');
    });

    // Collapsible feature flag groups
    $('.bp-flag-group-title').on('click', function() {
      $(this).toggleClass('collapsed');
      var groupName = $(this).data('group');
      $('[data-group-content="' + groupName + '"]').toggleClass('collapsed');
    });
  });

  // Global toggle function for feature flags and catalogue switches
  function bpToggle(checkboxId, switchEl) {
    var cb = document.getElementById(checkboxId);
    if (!cb) return;
    cb.checked = !cb.checked;
    if (switchEl) {
      if (cb.checked) switchEl.classList.add('on');
      else switchEl.classList.remove('on');
    }
    // Update status badge for feature flags
    var statusEl = document.getElementById('ff_status_' + checkboxId.replace('ff_', ''));
    if (statusEl) {
      if (cb.checked) {
        statusEl.classList.remove('off');
        statusEl.classList.add('on');
        statusEl.textContent = 'ON';
      } else {
        statusEl.classList.remove('on');
        statusEl.classList.add('off');
        statusEl.textContent = 'OFF';
      }
    }
    // Fire jQuery change event so any listeners pick it up
    $(cb).trigger('change');
  }
</script>
<script>$(".business-profile-active-li").addClass("active"); $(".business-profile-active-li").closest(".mp-nav-group").addClass("open");</script>
