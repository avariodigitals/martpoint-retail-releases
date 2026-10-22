<?php
if(is_array($storefront_navigation_rows ?? null)){$this->load->view('themes/shared/navigation_rows',['navigation_row_only'=>!empty($storefront_primary_embedded)?'secondary':null]);return;}
$scTree=$storefront_category_tree??[];$scNav=$storefront_nav??null;$scRoot=base_url('store/'.$settings->store_slug.'/'.($category_navigation_route??'products').'?category=');

// A merchant-configured menu (Online Store → Navigation) replaces the
// automatic category rail entirely — same bar, same dropdown styling, but the
// merchant chose the items, their order and their submenus.
if(!empty($scNav)){
	$scRenderChild=function($nodes)use(&$scRenderChild){foreach($nodes as $node){echo '<div class="sf-category-child"><a href="'.htmlspecialchars($node['url'],ENT_QUOTES).'">'.htmlspecialchars($node['label']).'</a>';if($node['children']){echo '<div class="sf-category-descendants">';$scRenderChild($node['children']);echo '</div>';}echo '</div>';}};
	?>
	<link rel="stylesheet" href="<?= base_url('theme/dist/css/category_navigation.css?v=1'); ?>"><nav class="sf-category-navigation" aria-label="Store menu"><?php foreach($scNav as $node): ?><?php if(!empty($node['children'])): ?><details class="sf-category-dropdown"><summary><?= htmlspecialchars($node['label']); ?> <span aria-hidden="true">⌄</span></summary><div class="sf-category-panel"><a class="sf-category-all" href="<?= htmlspecialchars($node['url'],ENT_QUOTES); ?>"><?= htmlspecialchars($node['label']); ?> ↗</a><?php $scRenderChild($node['children']); ?></div></details><?php else: ?><a class="sf-category-root" href="<?= htmlspecialchars($node['url'],ENT_QUOTES); ?>"><?= htmlspecialchars($node['label']); ?></a><?php endif; endforeach; ?></nav>
	<script defer src="<?= base_url('theme/js/category_navigation.js?v=2'); ?>"></script>
	<?php return;
}

$scHierarchy=false;foreach($scTree as $node)if($node->children)$scHierarchy=true;
if((empty($sells_products)&&empty($sells_services))||!$scHierarchy)return;
$scChildren=function($nodes)use(&$scChildren,$scRoot){foreach($nodes as $node){echo '<div class="sf-category-child"><a href="'.htmlspecialchars($scRoot.(int)$node->id,ENT_QUOTES).'">'.htmlspecialchars($node->category_name).'</a>';if($node->children){echo '<div class="sf-category-descendants">';$scChildren($node->children);echo '</div>';}echo '</div>';}};
?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/category_navigation.css?v=1'); ?>"><nav class="sf-category-navigation" aria-label="Catalogue categories" data-category-route="<?= htmlspecialchars($category_navigation_route??'products',ENT_QUOTES); ?>"><?php foreach($scTree as $node): ?><?php if($node->children): ?><details class="sf-category-dropdown"><summary><?= htmlspecialchars($node->category_name); ?> <span aria-hidden="true">⌄</span></summary><div class="sf-category-panel"><a class="sf-category-all" href="<?= htmlspecialchars($scRoot.(int)$node->id,ENT_QUOTES); ?>">View all <?= htmlspecialchars($node->category_name); ?> ↗</a><?php $scChildren($node->children); ?></div></details><?php else: ?><a class="sf-category-root" href="<?= htmlspecialchars($scRoot.(int)$node->id,ENT_QUOTES); ?>"><?= htmlspecialchars($node->category_name); ?></a><?php endif; endforeach; ?></nav>
<script defer src="<?= base_url('theme/js/category_navigation.js?v=2'); ?>"></script>
