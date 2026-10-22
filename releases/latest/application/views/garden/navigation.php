<?php
/*
 * Garden workspace menu — the body of the sheet that the app bar's menu button
 * toggles (see mobile/garden.php).
 *
 * The app bar owns the control, so this partial only draws the groups: a flat
 * row for one-link groups (Overview, Settings, More) and a foldable section for
 * the rest, so the collapsed menu stays short enough to fit a phone screen.
 * Notices are loaded by the shell instead of here, so a flash message can never
 * end up trapped inside a closed menu.
 */
$ci =& get_instance();
$here = mp_garden_navigation_current($ci->uri->uri_string());
$garden_footer_routes = ['garden','garden/catalogue','garden/projects','garden/sales_report','garden/business_tools'];
?>
<div class="garden-mobile-menu-body">
<h2 class="gw-visually-hidden">Workspace menu</h2>
<nav aria-label="Garden workspace">
<?php foreach(mp_garden_navigation_groups() as $name=>$links):$visible=array_values(array_filter($links,function($link)use($garden_profile,$garden_footer_routes){return !in_array($link[1],$garden_footer_routes,true) && mp_garden_navigation_visible($link,$garden_profile);}));if(!$visible)continue;
$single=in_array($name,['Overview','Settings','More'],true);
if(!$single): ?><details class="garden-mobile-nav-group" <?= in_array($here,array_column($visible,1),true)?'open':''; ?>><summary><span class="gw-menu-group-name"><?= htmlspecialchars($name); ?></span><span class="gw-menu-count" aria-hidden="true"><?= count($visible); ?></span><i class="fa fa-chevron-down" aria-hidden="true"></i></summary><?php endif; ?><div class="garden-nav">
<?php foreach($visible as $link): ?><a href="<?= htmlspecialchars(mp_garden_mobile_url(base_url($link[1])),ENT_QUOTES,'UTF-8'); ?>" <?= $here===$link[1]?'aria-current="page"':''; ?>><i class="fa <?= htmlspecialchars($link[3]); ?>" aria-hidden="true"></i><span><?= $name==='Settings'?'Settings':htmlspecialchars($link[2]); ?></span></a><?php endforeach; ?>
</div><?php if(!$single): ?></details><?php endif;endforeach; ?></nav>
</div>
