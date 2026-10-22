<?php if(!is_array($storefront_navigation_rows ?? null))return;
$render=function($nodes)use(&$render){foreach($nodes as $node){if(!empty($node['children'])){echo '<details class="sf-category-dropdown"><summary>'.htmlspecialchars($node['label']).' <span aria-hidden="true">⌄</span></summary><div class="sf-category-panel"><a class="sf-category-all" href="'.htmlspecialchars($node['url'],ENT_QUOTES).'">'.htmlspecialchars($node['label']).' ↗</a>';$render($node['children']);echo '</div></details>';}else echo '<a class="sf-category-root" href="'.htmlspecialchars($node['url'],ENT_QUOTES).'">'.htmlspecialchars($node['label']).'</a>';}};
?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/category_navigation.css?v=3'); ?>">
<?php foreach($storefront_navigation_rows as $key=>$nodes):if(!$nodes||(!empty($navigation_row_only)&&$navigation_row_only!==$key))continue; ?>
<nav class="sf-category-navigation sf-menu-<?= $key; ?>" aria-label="<?= $key==='primary'?'Primary pages menu':'Secondary store menu'; ?>" data-store-menu="<?= $key; ?>"><?php $render($nodes); ?></nav>
<?php endforeach; ?>
<script defer src="<?= base_url('theme/js/category_navigation.js?v=3'); ?>"></script>
