<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.url-box { background:#f4f4f4; padding:12px 15px; border-radius:4px; font-family:monospace; font-size:14px; word-break:break-all; margin-bottom:8px; }
.box-body .form-control { min-height: 38px; padding: 8px 12px; }
.box-body select.form-control { height: 38px; line-height: 1.4; }
.box-body input[type="text"].form-control, .box-body input[type="email"].form-control { height: 38px; }
/* Stabilize form-horizontal inside box-body for cross-browser consistency */
#settings-form.form-horizontal .form-group {
  margin-left: -8px;
  margin-right: -8px;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
}
#settings-form.form-horizontal .control-label,
#settings-form.form-horizontal .col-sm-2,
#settings-form.form-horizontal .col-sm-4,
#settings-form.form-horizontal .col-md-2,
#settings-form.form-horizontal .col-md-4,
#settings-form.form-horizontal .col-md-3,
#settings-form.form-horizontal .col-md-offset-3,
#settings-form.form-horizontal .col-md-6,
#settings-form.form-horizontal .col-sm-6 {
  padding-left: 8px;
  padding-right: 8px;
  position: relative;
  min-height: 1px;
}
#settings-form.form-horizontal .control-label {
  text-align: left;
  font-weight: 600;
  padding-top: 7px;
  margin-bottom: 4px;
}
#settings-form.form-horizontal .col-sm-2,
#settings-form.form-horizontal .col-md-2 { width: 16.666%; flex: 0 0 16.666%; }
#settings-form.form-horizontal .col-sm-3,
#settings-form.form-horizontal .col-md-3 { width: 25%; flex: 0 0 25%; }
#settings-form.form-horizontal .col-sm-4,
#settings-form.form-horizontal .col-md-4 { width: 33.333%; flex: 0 0 33.333%; }
#settings-form.form-horizontal .col-sm-6,
#settings-form.form-horizontal .col-md-6 { width: 50%; flex: 0 0 50%; }
#settings-form.form-horizontal .col-sm-8,
#settings-form.form-horizontal .col-md-8 { width: 66.666%; flex: 0 0 66.666%; }
#settings-form.form-horizontal .col-md-offset-3 { margin-left: 25%; }
#settings-form.form-horizontal .col-sm-offset-2 { margin-left: 16.666%; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-globe text-blue"></i> Storefront URL</h3>
            </div>
            <div class="box-body">
              <?php
                $slug = $settings->store_slug ?? '';
                if(!$slug && !empty($store) && !empty($store->store_name)){
                  $slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name));
                  $slug = trim($slug, '-');
                }
              ?>
              <div class="url-box"><?= base_url('store/' . ($slug ?: 'your-slug')); ?></div>
              <p class="text-muted">Share this link with customers to access your online store.</p>
            </div>
          </div>

          <div class="box box-info">
            <form class="form-horizontal" id="settings-form" onsubmit="return false;">
              <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
              <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cog text-orange"></i> Store Settings</h3>
                <div class="box-tools pull-right">
                  <?php if(empty($is_saved)): ?><span class="label label-warning"><i class="fa fa-exclamation-triangle"></i> Not Saved Yet</span><?php else: ?><span class="label label-success"><i class="fa fa-check"></i> Saved</span><?php endif; ?>
                </div>
              </div>
              <div class="box-body">

                <div class="form-group">
                  <label class="col-sm-2 control-label">Store Slug</label>
                  <div class="col-sm-4">
                    <?php $slugVal = $settings->store_slug ?? ''; if(!$slugVal && !empty($store) && !empty($store->store_name)){ $slugVal = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name)); $slugVal = trim($slugVal, '-'); } ?>
                    <input type="text" class="form-control input-sm" id="store_slug" name="store_slug" value="<?= htmlspecialchars($slugVal); ?>" placeholder="your-store-name">
                    <span class="text-muted" style="font-size:12px;">Used in the store URL. Lowercase letters, numbers, and hyphens only.</span>
                  </div>
                  <label class="col-sm-2 control-label">Store Status</label>
                  <div class="col-sm-4">
                    <select class="form-control input-sm" id="store_status" name="store_status">
                      <option value="active" <?= ($settings->store_status ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                      <option value="maintenance" <?= ($settings->store_status ?? '') == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                      <option value="deactivated" <?= ($settings->store_status ?? '') == 'deactivated' ? 'selected' : ''; ?>>Deactivated</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-2 control-label">Description</label>
                  <div class="col-sm-4">
                    <textarea class="form-control input-sm" id="store_description" name="store_description" rows="3" placeholder="Short description of your store"><?= htmlspecialchars($settings->store_description ?? ''); ?></textarea>
                  </div>
                  <label class="col-sm-2 control-label">WhatsApp Number</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($settings->whatsapp_number ?? ''); ?>" placeholder="e.g. 2348012345678">
                    <span class="text-muted" style="font-size:12px;">Include country code (e.g. 234 for Nigeria)</span>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-2 control-label">Store Email</label>
                  <div class="col-sm-4">
                    <input type="email" class="form-control input-sm" id="store_email" name="store_email" value="<?= htmlspecialchars($settings->store_email ?? ''); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Store Phone</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" id="store_phone" name="store_phone" value="<?= htmlspecialchars($settings->store_phone ?? ''); ?>">
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-2 control-label">Store Address</label>
                  <div class="col-sm-4">
                    <textarea class="form-control input-sm" id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings->store_address ?? ''); ?></textarea>
                  </div>
                  <label class="col-sm-2 control-label">Default Branch</label>
                  <div class="col-sm-4">
                    <select class="form-control input-sm" id="default_branch_id" name="default_branch_id">
                      <option value="0">- System Default -</option>
                      <?php foreach($warehouses as $w): ?>
                      <option value="<?= $w->id; ?>" <?= ($settings->default_branch_id ?? 0) == $w->id ? 'selected' : ''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-2 control-label">Featured Products Limit</label>
                  <div class="col-sm-4">
                    <input type="number" class="form-control input-sm" id="featured_products_limit" name="featured_products_limit" value="<?= (int)($settings->featured_products_limit ?? 8); ?>">
                  </div>
                </div>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-credit-card"></i> Payment Options</h4>

                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="allow_paystack" name="allow_paystack" <?= ($settings->allow_paystack ?? 1) ? 'checked' : ''; ?>> Allow Paystack Checkout</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="allow_whatsapp" name="allow_whatsapp" <?= ($settings->allow_whatsapp ?? 1) ? 'checked' : ''; ?>> Allow WhatsApp Orders</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="allow_pay_on_delivery" name="allow_pay_on_delivery" <?= ($settings->allow_pay_on_delivery ?? 1) ? 'checked' : ''; ?>> Allow Pay on Delivery</label>
                    </div>
                  </div>
                </div>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-sliders"></i> Store Features</h4>

                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="allow_services" name="allow_services" <?= ($settings->allow_services ?? 1) ? 'checked' : ''; ?>> Allow Service Orders</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="allow_backorder" name="allow_backorder" <?= ($settings->allow_backorder ?? 0) ? 'checked' : ''; ?>> Allow Backorder (Out of Stock)</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="show_search" name="show_search" <?= ($settings->show_search ?? 1) ? 'checked' : ''; ?>> Show Search Bar</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="show_categories" name="show_categories" <?= ($settings->show_categories ?? 1) ? 'checked' : ''; ?>> Show Category Chips</label>
                    </div>
                    <div class="checkbox icheck">
                      <label><input type="checkbox" id="show_whatsapp_cta" name="show_whatsapp_cta" <?= ($settings->show_whatsapp_cta ?? 1) ? 'checked' : ''; ?>> Show WhatsApp CTA Button</label>
                    </div>
                  </div>
                </div>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-instagram"></i> Instagram Integration</h4>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Access Token</label>
                  <div class="col-sm-6">
                    <input type="text" class="form-control input-sm" id="instagram_access_token" name="instagram_access_token" value="<?= htmlspecialchars($settings->instagram_access_token ?? ''); ?>" placeholder="Instagram Basic Display API token">
                    <span class="text-muted" style="font-size:12px;"><a href="https://developers.facebook.com/docs/instagram-basic-display-api" target="_blank">Get token from Facebook Developers</a></span>
                  </div>
                  <label class="col-sm-2 control-label">Username</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="instagram_username" name="instagram_username" value="<?= htmlspecialchars($settings->instagram_username ?? ''); ?>" placeholder="@handle">
                  </div>
                </div>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-google"></i> Google Reviews Integration</h4>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Places API Key</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" id="google_places_api_key" name="google_places_api_key" value="<?= htmlspecialchars($settings->google_places_api_key ?? ''); ?>" placeholder="Google Places API Key">
                  </div>
                  <label class="col-sm-2 control-label">GMB Place ID</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" id="gmb_place_id" name="gmb_place_id" value="<?= htmlspecialchars($settings->gmb_place_id ?? ''); ?>" placeholder="Google Place ID">
                    <span class="text-muted" style="font-size:12px;"><a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Find your Place ID</a></span>
                  </div>
                </div>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-shield"></i> Trust Badges</h4>
                <?php $tb = json_decode($settings->trust_badges_json ?? '', true); ?>
                <?php for($i=1;$i<=4;$i++): ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Badge <?= $i; ?> Title</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" name="tb_<?= $i; ?>_title" value="<?= htmlspecialchars($tb[$i-1]['title'] ?? ''); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Badge <?= $i; ?> Description</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" name="tb_<?= $i; ?>_desc" value="<?= htmlspecialchars($tb[$i-1]['desc'] ?? ''); ?>">
                  </div>
                </div>
                <?php endfor; ?>

                <hr style="margin:20px 0;">
                <h4 class="text-muted" style="margin-bottom:15px;"><i class="fa fa-envelope"></i> Newsletter CTA</h4>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Headline</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" name="newsletter_title" value="<?= htmlspecialchars($settings->newsletter_title ?? 'Stay in the Loop'); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Sub-headline</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control input-sm" name="newsletter_subtitle" value="<?= htmlspecialchars($settings->newsletter_subtitle ?? 'Subscribe for updates, deals and new arrivals.'); ?>">
                  </div>
                </div>

              </div>
              <div class="box-footer">
                <div class="col-sm-8 col-sm-offset-2 text-center">
                  <div class="col-sm-6">
                    <button type="button" id="btn-save" class="btn btn-block btn-success" title="Save Settings"><i class="fa fa-save"></i> Save Settings</button>
                  </div>
                  <div class="col-sm-6">
                    <a href="<?=base_url('dashboard');?>"><button type="button" class="btn btn-block btn-warning"><i class="fa fa-close"></i> Close</button></a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php $this->load->view('footer'); ?>
  <div class="control-sidebar-bg"></div>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
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
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
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
              $('.url-box').fadeOut(200, function(){ $(this).text(res.store_url).fadeIn(200); });
            }
            $('.box-tools .label-warning').removeClass('label-warning').addClass('label-success').html('<i class="fa fa-check"></i> Saved');
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
        console.log('AJAX Error:', status, error, xhr.responseText);
      }
    });
  });
});
</script>
<script>$('.online-store-settings-active-li').addClass("active");</script>
</body>
</html>
