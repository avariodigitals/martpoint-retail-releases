<?php
$beSlug=$settings->store_slug ?? '';$beHome=base_url('store/'.$beSlug);$beName=($settings->store_name ?? '') ?: ($store->store_name ?? 'Store');
$beRequest=!empty($garden_profile) && (mp_garden_has_mode($garden_profile,'landscaping')||mp_garden_has_mode($garden_profile,'maintenance'));
$beSections=$homepage_sections ?? $this->theme_engine->homepageSections();
$beEnabled=function($key)use($beSections){return !empty($beSections[$key]->is_enabled);};
$beLinks=[];
if(!empty($sells_products)){
 $beLinks[]=[$beHome.'/products',$beValue('menu_shop')];
 if($beEnabled('featured_categories'))$beLinks[]=[$beHome.'#be-categories',$beValue('menu_categories')];
}
if(!empty($sells_services))$beLinks[]=[$beEnabled('featured_services')?$beHome.'#be-services':$beHome.'/services',$beValue('menu_services')];
if($beRequest)$beLinks[]=[$beEnabled('contact_section')?$beHome.'#be-landscaping':$garden_request_url,$beValue('menu_projects')];
if($beEnabled('store_info'))$beLinks[]=[$beHome.'#be-about',$beValue('menu_story')];
// A merchant-configured menu (Online Store → Navigation) is served by the
// menu bar this theme inherits from the shared layout, directly below the
// header — so the theme's own link row stands down rather than showing the
// same menu twice.
if(!empty($storefront_nav)||is_array($storefront_navigation_rows ?? null))$beLinks=[];
$beCollectionUrl=!empty($sells_products)?($beEnabled('featured_products')?$beHome.'#be-featured_products':$beHome.'/products'):$beHome.'/services';
?>
<?php if(!empty($settings->announcement_bar)): ?><div class="be-announcement"><?= htmlspecialchars($settings->announcement_bar); ?> <a href="<?= htmlspecialchars($beCollectionUrl); ?>"><?= $beCopy('announcement_cta'); ?></a></div><?php endif; ?>
<header class="be-head"><div class="be-shell be-topnav">
<a class="be-brand" href="<?= $beHome; ?>" aria-label="<?= htmlspecialchars($beName); ?> home"><?php if(!empty($logo_url)): ?><img src="<?= htmlspecialchars($logo_url); ?>" alt="<?= htmlspecialchars($beName); ?>"><?php endif; ?><span class="be-brandword"><?= htmlspecialchars($beName); ?><small><?= $beCopy('tagline'); ?></small></span></a>
<?php if(is_array($storefront_navigation_rows ?? null)): $this->load->view('themes/shared/navigation_rows',['navigation_row_only'=>'primary']);else: ?><nav class="be-navlinks" aria-label="Main navigation"><?php foreach($beLinks as $link): ?><a href="<?= htmlspecialchars($link[0]); ?>"><?= htmlspecialchars($link[1]); ?></a><?php endforeach; ?></nav><?php endif; ?>
<div class="be-navright">
<?php if($settings->show_search ?? true): ?><button type="button" class="be-iconbtn" data-be-search aria-label="Search store" aria-expanded="false" aria-controls="be-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button><?php endif; ?>
<?php if(!empty($cart_enabled)): ?><?php if(isset($cart_page)): ?><a href="<?= $beHome.'/cart'; ?>" class="be-iconbtn" aria-label="Review your bag"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg><span class="be-count" id="cart-count">0</span></a><?php else: ?><button type="button" class="be-iconbtn" data-be-bag aria-label="Open shopping bag" aria-expanded="false" aria-controls="be-bag"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg><span class="be-count" id="cart-count">0</span></button><?php endif; ?><?php endif; ?>
<a class="be-iconbtn be-account-icon" href="<?= $beHome.'/account'; ?>" aria-label="Your account"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg></a>
<button type="button" class="be-iconbtn be-menu-toggle" data-be-menu aria-label="Open menu" aria-expanded="false" aria-controls="be-mobile-menu"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
</div></div>
<nav class="be-mobile-menu" id="be-mobile-menu" aria-label="Mobile navigation" hidden><?php foreach($beLinks as $link): ?><a href="<?= htmlspecialchars($link[0]); ?>"><?= htmlspecialchars($link[1]); ?></a><?php endforeach; ?></nav>
<div class="be-shell" id="be-search" hidden><form class="be-search-form" method="get" action="<?= $beHome.(!empty($sells_products)?'/products':'/services'); ?>"><input type="search" name="search" placeholder="Search products" aria-label="Search products"><button class="be-pill">Search ↗</button></form></div>
</header>
<script defer src="<?= base_url('theme/js/botanical_elegance.js?v=2'); ?>"></script>
