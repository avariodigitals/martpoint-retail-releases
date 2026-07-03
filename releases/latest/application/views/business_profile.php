<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php'); ?>
<style>
  .bp-flag-group-title { font-weight: 600; font-size: 14px; color: #3c8dbc; border-bottom: 1px solid #eee; padding-bottom: 6px; margin: 18px 0 10px; }
  .bp-flag-group-title:first-child { margin-top: 0; }
  .bp-flag-item { padding: 4px 0; }
  .bp-flag-item input { margin-right: 6px; cursor: pointer; }
  .bp-flag-item label { font-weight: 400; cursor: pointer; margin-bottom: 0; }
  .bp-label-item { margin-bottom: 12px; }
  .bp-label-item label { font-size: 12px; color: #777; margin-bottom: 2px; display: block; }

  /* Phase 3 enhancements */
  .bp-preset-preview { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px; display: none; }
  .bp-preset-preview.active { display: block; animation: fadeIn 0.3s ease; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
  .bp-preset-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
  .bp-preset-title { font-weight:700; color:#1e293b; font-size:15px; margin:0; }
  .bp-preset-badge { background:#3b82f6; color:#fff; font-size:11px; padding:2px 8px; border-radius:12px; }
  .bp-preset-body { display:flex; gap:16px; flex-wrap:wrap; }
  .bp-preset-card { flex:1; min-width:200px; background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:12px; }
  .bp-preset-card h5 { margin:0 0 8px; font-size:13px; color:#475569; font-weight:600; }
  .bp-preset-features { display:flex; flex-wrap:wrap; gap:4px; }
  .bp-preset-feature-tag { background:#dbeafe; color:#1e40af; font-size:11px; padding:2px 8px; border-radius:4px; }
  .bp-theme-card { display:flex; align-items:center; gap:10px; padding:8px; background:#f1f5f9; border-radius:6px; margin-top:6px; }
  .bp-theme-swatch { width:32px; height:32px; border-radius:6px; flex-shrink:0; }
  .bp-theme-name { font-weight:600; font-size:13px; color:#334155; }
  .bp-theme-industry { font-size:11px; color:#64748b; }

  .bp-section-title { font-weight:700; font-size:15px; color:#1e293b; margin:0 0 12px; padding-bottom:8px; border-bottom:1px solid #e2e8f0; }
  .bp-help-text { color:#64748b; font-size:12px; margin-top:4px; display:block; }
  .bp-catalogue-row { display:flex; gap:20px; flex-wrap:wrap; align-items:center; padding:8px 0; }
  .bp-catalogue-row label { font-weight:500; margin:0; }

  .nav-tabs-custom>.nav-tabs>li>a { font-weight:600; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php $this->load->view('sidebar'); ?>
  <div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active"><?= $page_title; ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_identity" data-toggle="tab">Business Identity</a></li>
            <li><a href="#tab_features" data-toggle="tab">Feature Flags</a></li>
            <li><a href="#tab_templates" data-toggle="tab">Templates &amp; Labels</a></li>
            <li><a href="#tab_advanced" data-toggle="tab">Advanced</a></li>
          </ul>
          <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'businessProfileForm', 'method'=>'POST')); ?>
          <div class="tab-content">

            <!-- TAB 1: Business Identity -->
            <div class="tab-pane active" id="tab_identity">
              <div class="box-body">
                <div class="form-group">
                  <label for="industry_type" class="col-sm-2 control-label">Business Type</label>
                  <div class="col-sm-4">
                    <select name="industry_type" id="industry_type" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Business Type --</option>
                      <?php foreach ($business_types as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['industry_type']) && $profile['industry_type']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span class="text-muted"><small>Changing this shows a recommended preset. Click <strong>Apply Recommended</strong> to use it.</small></span>
                  </div>
                  <label for="business_model" class="col-sm-2 control-label">Business Model</label>
                  <div class="col-sm-4">
                    <select name="business_model" id="business_model" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Model --</option>
                      <?php foreach ($business_models as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['business_model']) && $profile['business_model']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Preset Preview Panel -->
                <div class="form-group">
                  <div class="col-sm-12">
                    <div id="presetPreview" class="bp-preset-preview">
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
                          <div id="presetTemplates" style="font-size:12px;color:#475569;"></div>
                        </div>
                      </div>
                      <div style="margin-top:12px; text-align:right;">
                        <button type="button" id="applyPresetBtn" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Apply Recommended Settings</button>
                        <button type="button" id="dismissPresetBtn" class="btn btn-sm btn-default" style="margin-left:6px;">Dismiss</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Theme Suggestion -->
                <div class="form-group">
                  <label for="storefront_theme_key" class="col-sm-2 control-label">Storefront Theme</label>
                  <div class="col-sm-4">
                    <select name="storefront_theme_key" id="storefront_theme_key" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Theme --</option>
                      <?php foreach ($storefront_themes as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['storefront_theme_key']) && $profile['storefront_theme_key']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span class="bp-help-text" id="themeSuggestionText"></span>
                  </div>
                  <div class="col-sm-6" id="themeSuggestionCard" style="display:none;">
                    <div class="bp-theme-card" style="margin:0;">
                      <div class="bp-theme-swatch" id="themeSwatch" style="background:#3b82f6;"></div>
                      <div>
                        <div class="bp-theme-name" id="themeSuggestedName">General Retail</div>
                        <div class="bp-theme-industry" id="themeSuggestedIndustry">Recommended for your business type</div>
                      </div>
                      <button type="button" id="applyThemeBtn" class="btn btn-xs btn-success pull-right"><i class="fa fa-check"></i> Apply</button>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="workflow_template_key" class="col-sm-2 control-label">Workflow Template</label>
                  <div class="col-sm-4">
                    <select name="workflow_template_key" id="workflow_template_key" class="form-control select2" style="width: 100%;">
                      <option value="">-- Select Workflow --</option>
                      <?php foreach ($workflow_templates as $key => $label): ?>
                        <option value="<?= $key; ?>" <?= (isset($profile['workflow_template_key']) && $profile['workflow_template_key']==$key)?'selected':''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <label for="dashboard_template_key" class="col-sm-2 control-label">Dashboard Template</label>
                  <div class="col-sm-4">
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
            <div class="tab-pane" id="tab_features">
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
                    'Sales & Storefront' => [
                      'online_store','qr_ordering','loyalty','gift_cards','store_credit','public_catalogue','price_catalogue'
                    ],
                    'Products & Inventory' => [
                      'multi_unit_inventory','batch_tracking','expiry_tracking','serial_number_tracking','imei_tracking','warranty_tracking','bundles'
                    ],
                    'Services & Appointments' => [
                      'appointments','service_workflow','custom_orders','packages','memberships'
                    ],
                    'Workflows & Operations' => [
                      'kitchen_workflow','table_management','laundry_workflow','treatment_notes','staff_assignment','staff_commission','delivery_scheduling','production_workflow','recipe_tracking'
                    ],
                    'Management' => [
                      'payplan','customer_notes','manager_approvals'
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
                  <div class="row">
                    <?php foreach ($visible_keys as $key):
                      $label = $feature_flags[$key];
                      $checked = (isset($saved_flags[$key]) && filter_var($saved_flags[$key], FILTER_VALIDATE_BOOLEAN)) ? 'checked' : '';
                    ?>
                      <div class="col-md-3 col-sm-4 col-xs-6 bp-flag-col" data-feature-key="<?= $key; ?>">
                        <div class="bp-flag-item">
                          <input type="checkbox" name="feature_flags[<?= $key; ?>]" id="ff_<?= $key; ?>" value="1" <?= $checked; ?>>
                          <label for="ff_<?= $key; ?>"><?= $label; ?></label>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- TAB 3: Templates & Labels -->
            <div class="tab-pane" id="tab_templates">
              <div class="box-body">
                <!-- Public Catalogue Settings -->
                <div class="row">
                  <div class="col-md-12">
                    <h4 class="bp-section-title"><i class="fa fa-globe"></i> Public Catalogue</h4>
                    <p class="bp-help-text" style="margin-bottom:12px;">A lighter browsing page for customers to view your products and services without full checkout.</p>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php
                      $cat_settings = [];
                      if (!empty($profile['industry_settings_json'])) {
                        $decoded = json_decode($profile['industry_settings_json'], true);
                        if (is_array($decoded) && isset($decoded['catalogue'])) {
                          $cat_settings = $decoded['catalogue'];
                        }
                      }
                    ?>
                    <div class="bp-catalogue-row">
                      <label><input type="checkbox" name="catalogue_enabled" id="catalogue_enabled" value="1" <?= (!empty($cat_settings['enabled']) ? 'checked' : ''); ?>> Enable Public Catalogue</label>
                      <label>Slug / URL: <input type="text" name="catalogue_slug" id="catalogue_slug" class="form-control input-sm" style="display:inline-block;width:180px;" value="<?= htmlspecialchars($cat_settings['slug'] ?? 'catalogue'); ?>" placeholder="catalogue"></label>
                      <label><input type="checkbox" name="catalogue_show_products" value="1" <?= (!isset($cat_settings['show_products']) || $cat_settings['show_products'] ? 'checked' : ''); ?>> Show Products</label>
                      <label><input type="checkbox" name="catalogue_show_services" value="1" <?= (!isset($cat_settings['show_services']) || $cat_settings['show_services'] ? 'checked' : ''); ?>> Show Services</label>
                    </div>
                    <span class="bp-help-text">The public catalogue appears at /catalogue/{slug} when enabled and the <strong>Public Catalogue</strong> feature flag is on.</span>
                  </div>
                </div>

                <div class="row" style="margin-top:24px;">
                  <div class="col-md-12">
                    <h4 class="bp-section-title"><i class="fa fa-language"></i> Label Overrides — Customize terminology for your industry</h4>
                  </div>
                </div>
                <div class="row">
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
                    <div class="col-md-3 col-sm-4 col-xs-6">
                      <div class="bp-label-item">
                        <label><?= $default_label; ?> <span class="text-muted">(<?= $v; ?>)</span></label>
                        <input type="text" name="label_overrides[<?= $k; ?>]" class="form-control input-sm"
                          value="<?= $val; ?>" placeholder="<?= $v; ?>">
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- TAB 4: Advanced -->
            <div class="tab-pane" id="tab_advanced">
              <div class="box-body">
                <div class="form-group">
                  <label for="industry_settings_json" class="col-sm-2 control-label">Industry Settings (JSON)</label>
                  <div class="col-sm-8">
                    <textarea name="industry_settings_json" id="industry_settings_json" class="form-control" rows="8" placeholder='{"custom_key": "value"}'><?= (!empty($profile['industry_settings_json']))?$profile['industry_settings_json']:''; ?></textarea>
                    <span class="text-muted"><small>Optional raw JSON configuration for industry-specific settings.</small></span>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /.tab-content -->

          <div class="box-footer col-sm-12">
            <center>
              <div class="col-md-3 col-md-offset-3">
                <button type="submit" id="saveBtn" class="btn btn-block btn-success"><i class="fa fa-save"></i> Save</button>
              </div>
              <div class="col-md-3">
                <a href="<?= $base_url; ?>dashboard" class="btn btn-block btn-warning">Close</a>
              </div>
            </center>
          </div>

          <?= form_close(); ?>
        </div><!-- /.nav-tabs-custom -->
      </div><!-- /.col-md-12 -->
    </div><!-- /.row -->
  </section>
</div><!-- /.content-wrapper -->
<?php $this->load->view('footer.php'); ?>
<div class="control-sidebar-bg"></div>
</div><!-- /.wrapper -->
<?php $this->load->view('comman/code_js_language.php'); ?>
<!-- SOUND CODE -->
<?php $this->load->view('comman/code_js_sound.php'); ?>
<!-- TABLES CODE -->
<?php $this->load->view('comman/code_js.php'); ?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".business-profile-active-li").addClass("active");

    var currentPreset = null;
    var themeColors = {
      'general_retail': '#3B82F6', 'fresh_market': '#2E7D32', 'healthcare_pro': '#005EB8',
      'food_express': '#D32F2F', 'tech_hub': '#0A2540', 'urban_fashion': '#111111',
      'beauty_luxe': '#F8A4C8', 'service_pro': '#1A237E'
    };

    // Build catalogue settings into industry_settings_json before submit
    $('#businessProfileForm').on('submit', function(e) {
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
            applyPresetValues(res.preset); // auto-fill form fields
            showPresetPreview(res.preset); // show preview panel
            showThemeSuggestion(res.preset.theme_key);
          }
        }
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
        if (recommended.indexOf(key) !== -1) {
          $(this).show();
          $cb.prop('checked', true);
        } else {
          $(this).hide();
          $cb.prop('checked', false);
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
      if (!currentPreset) return;
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
      if (currentPreset && currentPreset.theme_key) {
        $('#storefront_theme_key').val(currentPreset.theme_key).trigger('change');
        $('#themeSuggestionCard').hide();
      }
    });

    // Form submit
    $('#businessProfileForm').on('submit', function(e) {
      e.preventDefault();
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
            setTimeout(function() {
              location.reload();
            }, 800);
          } else {
            toastr.error(res.message);
          }
        },
        error: function() {
          toastr.error('An error occurred while saving.');
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
        }
      });
    });
  });
</script>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
