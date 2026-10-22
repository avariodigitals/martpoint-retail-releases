(function(){document.querySelectorAll('.sf-category-navigation').forEach(function(nav){
if(nav.dataset.menuBound)return;nav.dataset.menuBound='1';
// Only legacy category rails replace duplicate category shortcuts.
if(nav.dataset.categoryRoute)document.querySelectorAll('a').forEach(function(link){if(nav.contains(link)||(!(link.compareDocumentPosition(nav)&4)&&!link.closest('[class*="drawer"]')))return;try{var url=new URL(link.href,location.href);if(url.origin===location.origin&&url.pathname.endsWith('/'+nav.dataset.categoryRoute)&&url.searchParams.has('category')){link.hidden=true;link.style.display='none';}}catch(e){}});
var menus=nav.querySelectorAll('details');
menus.forEach(function(menu){var summary=menu.querySelector('summary');summary.addEventListener('click',function(event){if(event.detail>0&&matchMedia('(hover:hover) and (min-width:1025px)').matches){event.preventDefault();menu.open=true;}});menu.addEventListener('pointerenter',function(){if(matchMedia('(hover:hover) and (min-width:1025px)').matches)menu.open=true;});menu.addEventListener('pointerleave',function(){if(matchMedia('(hover:hover) and (min-width:1025px)').matches&&!menu.matches(':focus-within'))menu.open=false;});menu.addEventListener('keydown',function(event){if(event.key==='Escape'){menu.open=false;summary.focus();}});});
document.addEventListener('click',function(event){if(!nav.contains(event.target))menus.forEach(function(menu){menu.open=false;});});
});})();
