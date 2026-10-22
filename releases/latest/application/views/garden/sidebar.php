<?php
$ci =& get_instance();$here=mp_garden_navigation_current($ci->uri->uri_string());
$bottom=false;
$icons=['Catalogue & stock'=>'fa-cubes','Projects & services'=>'fa-map-o','Sales & customers'=>'fa-users','Operations'=>'fa-exchange','Reports'=>'fa-bar-chart','Online store'=>'fa-globe'];
$this->load->view('garden/shell_styles');
?>
<div class="garden-rail-section garden-primary-navigation" aria-label="Garden workspace">
<?php foreach(mp_garden_navigation_groups() as $name=>$links):
$visible=array_values(array_filter($links,function($link)use($garden_profile){return mp_garden_navigation_visible($link,$garden_profile);}));if(!$visible)continue;
$groupActive=in_array($here,array_column($visible,1),true);
if(!$bottom && in_array($name,['Online store','Settings','More'],true)): $bottom=true; ?></div><div class="garden-rail-section garden-rail-bottom"><?php endif;
if(isset($icons[$name])): ?>
<details class="garden-rail-group" data-garden-nav-group <?= $groupActive?'open':''; ?>><summary <?= $groupActive?'class="has-current-page"':''; ?>><i class="fa <?= $icons[$name]; ?>" aria-hidden="true"></i><span><?= htmlspecialchars($name); ?></span><i class="fa fa-angle-down garden-group-chevron" aria-hidden="true"></i></summary><div class="garden-rail-children">
<?php endif; ?>
<?php foreach($visible as $link):$active=$here===$link[1]; ?>
<a class="garden-rail-link <?= $active?'is-current':''; ?>" <?= $active?'aria-current="page"':''; ?> href="<?= htmlspecialchars(base_url($link[1]),ENT_QUOTES,'UTF-8'); ?>"><i class="fa <?= $link[3]; ?>" aria-hidden="true"></i><span><?= $name==='Settings'?'Settings':htmlspecialchars($link[2]); ?></span></a>
<?php endforeach; ?>
<?php if(isset($icons[$name])): ?></div></details><?php endif;endforeach; ?>
</div>
