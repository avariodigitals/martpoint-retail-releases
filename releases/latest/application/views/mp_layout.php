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
/* Load page-specific JS files before content so inline scripts can use them.
   mp_footer.php also loads extra_js_files, but this guard prevents duplicates. */
if(!empty($extra_js_files) && is_array($extra_js_files)){
  $GLOBALS['__mp_extra_js_loaded'] = true;
  foreach($extra_js_files as $js){
    echo '<script src="' . $theme_link . $js . '"></script>';
  }
}
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
});
</script>
