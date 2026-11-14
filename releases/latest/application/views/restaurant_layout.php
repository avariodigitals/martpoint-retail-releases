<?php
/**
 * RestaurantOS desktop layout.
 * Same content lifecycle as mp_layout.php. The restaurant workspace keeps the
 * shared MartPoint document shell so its full navigation remains available.
 */
if(!function_exists('physio_enabled')) $this->load->helper('physio');
// Keep the shared MartPoint shell so all business-management and restaurant
// navigation remains available. Restaurant-specific styling is applied by the
// restaurant workspace CSS loaded by mp_header.
$this->load->view('mp_header');
$this->load->view('mp_sidebar',['garden_tools_only'=>false]);
/* Load shared JS plugins before page content so content views can rely on
   DataTables, Select2, toastr, xss_validation, etc. in inline scripts. */
$this->load->view('comman/code_js.php');
/* Render flash messages/alerts once, after toastr is loaded. */
$this->load->view('comman/code_flashdata');
/* Load page-specific JS files before content. */
if(!empty($extra_js_files) && is_array($extra_js_files)){
  $GLOBALS['__mp_extra_js_loaded'] = true;
  foreach($extra_js_files as $js){
    echo '<script src="' . $theme_link . $js . '"></script>';
  }
}
// CSRF globals for every page script.
echo '<script>window.csrfName=' . json_encode($this->security->get_csrf_token_name())
   . ';window.csrfHash=' . json_encode($this->security->get_csrf_hash()) . ';</script>';
echo $content;
$this->load->view('mp_footer');
?>
<script>
// Auto-highlight sidebar items based on current URL
$(function(){
  var path = window.location.pathname.split('/').filter(Boolean);
  if(path.length >= 2 && path[0] === 'operations'){
    var route = path[1];
    var menuMap = {
      'kitchen':'kitchen', 'menu_items':'kitchen',
      'recipes':'recipes', 'recipe':'recipes', 'recipe_categories':'recipes', 'recipe-category':'recipes', 'recipe-categories':'recipes',
      'table_management':'table_management',
      'delivery_scheduling':'delivery_scheduling', 'delivery_schedule_form':'delivery_scheduling', 'delivery_schedule_view':'delivery_scheduling', 'driver_profile':'delivery_scheduling',
    };
    var menuKey = menuMap[route];
    if(menuKey){
      $('.operations-' + menuKey + '-active-li').addClass('active');
      $('.operations-' + menuKey + '-active-li').closest('.mp-nav-group').addClass('open');
    }
  }
  if(path.length >= 1 && path[0] === 'restaurantos'){
    var rKey = path[1] || 'index';
    var $r = $('.restaurantos-' + rKey + '-active-li');
    if($r.length){
      $('.mp-nav-item.active').removeClass('active');
      $r.first().addClass('active').closest('.mp-nav-group').addClass('open');
    }
  }
  if(path.length >= 1 && path[0] === 'pos'){
    var $pos = $('.pos-active-li');
    if($pos.length){
      $('.mp-nav-item.active').removeClass('active');
      $pos.first().addClass('active').closest('.mp-nav-group').addClass('open');
    }
  }
  // Keep the rail quiet by default. The current destination remains open;
  // every other group is still available with one click.
  $('.mp-nav-group').each(function(){
    var $group = $(this);
    if(!$group.find('.mp-nav-item.active').length) $group.removeClass('open');
  });
});
</script>
<?php if ($this->session->userdata('inv_userid')): ?>
<script>
// Central auto-update + fleet check-in
(function(){
  var last = parseInt(sessionStorage.getItem('mpAutoUpdateAt') || '0', 10);
  if (Date.now() - last < 20 * 60 * 1000) return;
  if (/system_updates/i.test(window.location.pathname)) return;
  sessionStorage.setItem('mpAutoUpdateAt', String(Date.now()));
  function post(url, data, ok, fail){
    data = data || {};
    data[window.csrfName] = window.csrfHash;
    return $.post(url, data, ok, 'json').fail(fail);
  }
  function driveStep(){
    post('<?= base_url('system_updates/run_step'); ?>', {}, function(res){
      if (res.status === 'error' || res.failed) { return; }
      if (res.done && (res.step || 0) >= 8) { return; }
      setTimeout(driveStep, 400);
    }, function(){ setTimeout(driveStep, 5000); });
  }
  post('<?= base_url('system_updates/auto_tick'); ?>', {}, function(res){
    if (!res || !res.status) return;
    if (res.status === 'update') { driveStep(); }
  }, 'json');
})();
</script>
<?php endif; ?>
