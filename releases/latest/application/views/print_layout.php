<?php
/**
 * Printing workspace shell.
 *
 * Reuses the standard mp_header (full topbar: Sync, POS, clock-in, licence,
 * offline badge, status pill, date range, user menu) and mp_sidebar (the full
 * menu: Sales, Catalog, Customers, Finance, Purchases, Reports, Online Store,
 * Marketing, Leads, Operations, Settings + the added Printing section) so a
 * print shop loses NO standard navigation. Only the accent colour is themed to
 * printing (ink cyan) via a CSS override — the shell itself stays shared.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// Full standard topbar + full standard sidebar.
$this->load->view('mp_header');
$this->load->view('mp_sidebar');

// Shared JS plugins, flashdata, per-page JS, CSRF globals — identical to mp_layout.
$this->load->view('comman/code_js.php');
$this->load->view('comman/code_flashdata');
if(!empty($extra_js_files) && is_array($extra_js_files)){
  $GLOBALS['__mp_extra_js_loaded'] = true;
  foreach($extra_js_files as $js){
    echo '<script src="' . $theme_link . $js . '"></script>';
  }
}
echo '<script>window.csrfName=' . json_encode($this->security->get_csrf_token_name())
   . ';window.csrfHash=' . json_encode($this->security->get_csrf_hash()) . ';</script>';

// Printing accent — theme the shared shell's primary colour to ink cyan so the
// workspace reads "print shop" without forking the standard chrome.
echo '<style>
:root{--mp-primary:#0e7490;--mp-primary-rgb:14,116,144;--mp-primary-dark:#0b5d73;}
.mp-nav-group-toggle .mp-nav-icon{color:#0e7490!important}
</style>';

echo $content;

$this->load->view('mp_footer');
?>
<script>
// Highlight the correct sidebar item on Printing routes. The top "Dashboard"
// link is hardcoded active in mp_sidebar, so it must be cleared whenever we are
// on a Printing screen (which IS the dashboard for a print shop) or a sub-route.
$(function(){
  var path = window.location.pathname.split('/').filter(Boolean);
  if(path.length >= 1 && path[0] === 'printing'){
    var route = path.length >= 2 ? path[1] : '';
    var map = { '':'Dashboard', 'job':'New Print Job', 'jobs':'Jobs', 'artworks':'Artwork',
                'authorizations':'Print Authorization', 'payments':'Payments', 'production':'Production Board', 'reports':'Printing Reports' };
    var label = map.hasOwnProperty(route) ? map[route] : null;
    if(label){
      $('.mp-nav-item').removeClass('active');
      $('.mp-nav-item').each(function(){
        if($.trim($(this).text()) === label){ $(this).addClass('active'); }
      });
    }
  }
});
</script>
