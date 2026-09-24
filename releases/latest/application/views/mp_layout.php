<?php
/**
 * Shared MartPoint desktop layout.
 * Use this from any controller:
 *   $data['content'] = $this->load->view('sales', $data, TRUE);
 *   $this->load->view('mp_layout', $data);
 */
$this->load->view('mp_header');
$this->load->view('mp_sidebar');
/* Load shared JS plugins before page content so content views can rely on
   DataTables, Select2, toastr, xss_validation, etc. in inline scripts.
   mp_footer.php also includes this file, but its load guard prevents duplicates. */
$this->load->view('comman/code_js.php');
/* Render flash messages/alerts once, after toastr is loaded, so a single toast/alert appears. */
$this->load->view('comman/code_flashdata');
/* Load page-specific JS files before content so inline scripts can use them.
   mp_footer.php also loads extra_js_files, but this guard prevents duplicates. */
if(!empty($extra_js_files) && is_array($extra_js_files)){
  $GLOBALS['__mp_extra_js_loaded'] = true;
  foreach($extra_js_files as $js){
    echo '<script src="' . $theme_link . $js . '"></script>';
  }
}
// CSRF globals for every page script — emitted before $content so any inline
// JS (fleet commands, settings pushes, update drivers) can attach the token.
echo '<script>window.csrfName=' . json_encode($this->security->get_csrf_token_name())
   . ';window.csrfHash=' . json_encode($this->security->get_csrf_hash()) . ';</script>';
echo $content;
$this->load->view('mp_footer');
?>
<script>
// Auto-highlight Operations sidebar items based on current URL
$(function(){
  var path = window.location.pathname.split('/').filter(Boolean);
  if(path.length >= 2 && path[0] === 'operations'){
    // Map sub-routes to their parent menu item for active highlighting
    var route = path[1];
    var menuMap = {
      'custom_orders':'custom_orders', 'custom_order':'custom_orders',
      'production_schedule':'production_schedule', 'production_batch':'production_schedule', 'production':'production_schedule',
      'recipes':'recipes', 'recipe':'recipes', 'recipe_categories':'recipes', 'recipe-category':'recipes', 'recipe-categories':'recipes',
      'memberships':'memberships', 'membership_plan':'memberships', 'customer_memberships':'memberships', 'assign_membership':'memberships',
      'treatment_notes':'treatment_notes', 'treatment_note':'treatment_notes',
      'medical_notes':'medical_notes', 'medical_note':'medical_notes',
      'kitchen':'kitchen', 'menu_items':'kitchen',
      'laundry':'laundry',
      'delivery_scheduling':'delivery_scheduling', 'delivery_schedule_form':'delivery_scheduling', 'delivery_schedule_view':'delivery_scheduling', 'driver_profile':'delivery_scheduling',
      'warranty_lookup':'warranty_lookup',
      'staff_assignment':'staff_assignment',
      'staff_commission':'staff_commission',
      'table_management':'table_management',
      'price_catalogue':'warranty_lookup',
      'public_catalogue_settings':'public_catalogue_settings'
    };
    var menuKey = menuMap[route];
    if(menuKey){
      $('.operations-' + menuKey + '-active-li').addClass('active');
      // Open the Operations submenu group
      $('.operations-' + menuKey + '-active-li').closest('.mp-nav-group').addClass('open');
    }
  }
  // Creator workspace + courses/memberships highlighting
  var creatorRoutes = ['creator','courses','memberships'];
  if(path.length >= 1 && creatorRoutes.indexOf(path[0]) !== -1){
    var cls = path[0];
    if(path[0] === 'creator' && path.length >= 2) cls += '-' + path[1];
    if(path[0] === 'creator' && path.length >= 3 && path[1] === 'products') cls += '-' + path[2];
    var $el = $('.' + cls + '-active-li');
    if(!$el.length && path[0] === 'creator' && path.length >= 2){ $el = $('.creator-' + path[1] + '-active-li'); }
    if($el.length){
      $('.mp-nav-item.active').removeClass('active');
      $el.first().addClass('active').closest('.mp-nav-group').addClass('open');
    }
  }
});
</script>
<?php if ($this->session->userdata('inv_userid')): ?>
<script>
// Central auto-update + fleet check-in: silent tick on ANY staff page load —
// a cashier logging in phones home just as well as an admin. Server-side
// throttled (~6h); the browser gate re-arms every 20min so a stalled run
// retries instead of going quiet for the whole session.
(function(){
  var last = parseInt(sessionStorage.getItem('mpAutoUpdateAt') || '0', 10);
  if (Date.now() - last < 20 * 60 * 1000) return;
  if (/system_updates/i.test(window.location.pathname)) return; // the panel drives itself
  sessionStorage.setItem('mpAutoUpdateAt', String(Date.now()));

  function post(url, data, ok, fail){
    data = data || {};
    data[window.csrfName] = window.csrfHash;
    return $.post(url, data, ok, 'json').fail(fail);
  }

  function driveStep(){
    post('<?= base_url('system_updates/run_step'); ?>', {}, function(res){
      if (res.status === 'error' || res.failed) {
        if (window.toastr) toastr.warning('Auto-update paused: ' + (res.message || 'open System Update to resume'), 'MartPoint', {timeOut: 0});
        return;
      }
      if (res.done && (res.step || 0) >= 8) {
        if (window.toastr) toastr.success('MartPoint updated — reload the page to use the latest version.', 'Update complete', {timeOut: 0});
        return;
      }
      setTimeout(driveStep, 400);
    }, function(){ setTimeout(driveStep, 5000); });
  }

  post('<?= base_url('system_updates/auto_tick'); ?>', {}, function(res){
    if (!res || !res.status) return;
    if (res.status === 'update') {
      if (window.toastr) toastr.info('Updating MartPoint ' + res.from + ' → ' + res.to + ' — please keep this tab open.', 'System update', {timeOut: 0, extendedTimeOut: 0});
      driveStep();
    } else if (res.status === 'blocked') {
      if (window.toastr) toastr.warning(res.message || 'Update blocked by subscription status.', 'MartPoint');
    } else if (res.status === 'available') {
      if (window.toastr) toastr.info('Version ' + res.remote_version + ' is available — open System Update to apply it.', 'MartPoint');
    }
  }, 'json');
})();
</script>
<?php endif; ?>
