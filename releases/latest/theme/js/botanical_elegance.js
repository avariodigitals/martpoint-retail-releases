/* Botanical Elegance presentation. Commerce state/actions belong to shared storefront scripts. */
(function(){
'use strict';
var bag=document.getElementById('be-bag'),backdrop=document.getElementById('be-bag-backdrop'),returnFocus=null;
function button(text,label,handler){var b=document.createElement('button');b.type='button';b.textContent=text;b.setAttribute('aria-label',label);b.addEventListener('click',handler);return b;}
function cartItems(){return typeof cart!=='undefined'&&Array.isArray(cart)?cart:[];}
function renderBag(){
 if(!bag)return;var host=document.getElementById('be-bag-items'),items=cartItems(),total=0;host.replaceChildren();
 if(!items.length){var empty=document.createElement('p');empty.className='be-empty';empty.textContent='Your bag is empty. Explore the shop to find something lovely.';host.appendChild(empty);}
 items.forEach(function(item){
  total+=typeof sfCartLineTotal==='function'?sfCartLineTotal(item):(Number(item.price)||0)*(Number(item.qty)||0);
  var row=document.createElement('div');row.className='be-cart-row';
  if(item.image){var img=document.createElement('img');try{var url=new URL(item.image,bag.dataset.base);if(url.protocol==='https:'||url.protocol==='http:'){img.src=url.href;img.alt=item.name||'';row.appendChild(img);}}catch(error){}}
  var copy=document.createElement('div'),title=document.createElement('h4'),price=document.createElement('small');title.textContent=item.name||'Item';price.textContent=formatMoney(typeof sfCartUnitTotal==='function'?sfCartUnitTotal(item):Number(item.price)||0);copy.append(title,price);
  if(Array.isArray(item.addons)&&item.addons.length){var addons=document.createElement('p');addons.textContent=item.addons.map(function(a){return a.name+' × '+a.qty;}).join(', ');copy.appendChild(addons);}
  var qty=document.createElement('div');qty.className='be-qty';var value=document.createElement('span');value.textContent=item.qty;
  qty.append(button('−','Decrease '+item.name,function(){changeQty(item.key,-1);}),value,button('+','Increase '+item.name,function(){changeQty(item.key,1);}));copy.appendChild(qty);
  copy.appendChild(button('Remove','Remove '+item.name,function(){changeQty(item.key,-Number(item.qty));}));row.appendChild(copy);host.appendChild(row);
 });document.getElementById('be-bag-total').textContent=formatMoney(total);
}
function changeQty(key,change){
 var item=cartItems().find(function(i){return i.key===key;});if(!item)return;
 var next=Number(item.qty)+change,error=document.getElementById('be-bag-error');error.textContent='';
 if(change>0&&item.type==='product'&&bag.dataset.backorder!=='1'&&next>Number(item.stock)){error.textContent='Only '+item.stock+' available. You can review availability at checkout.';return;}
 var focusIndex=Array.from(document.querySelectorAll('#be-bag-items button')).indexOf(document.activeElement);
 if(next<=0)cart=cart.filter(function(i){return i.key!==key;});else item.qty=next;
 saveCart();updateCartUI();renderBag();var controls=document.querySelectorAll('#be-bag-items button');if(controls.length)controls[Math.min(Math.max(0,focusIndex),controls.length-1)].focus();else document.querySelector('[data-be-close-bag]').focus();
}
function closeMenu(){var nav=document.getElementById('be-mobile-menu');if(nav){nav.hidden=true;nav.classList.remove('be-open');}document.querySelectorAll('[data-be-menu]').forEach(function(b){b.setAttribute('aria-expanded','false');});}
function toggleBag(open){
 if(!bag)return;if(open){closeMenu();returnFocus=document.activeElement;renderBag();}bag.classList.toggle('be-open',open);backdrop.classList.toggle('be-open',open);backdrop.hidden=!open;bag.setAttribute('aria-hidden',String(!open));document.body.classList.toggle('be-scroll-lock',open);document.querySelectorAll('[data-be-bag]').forEach(function(b){b.setAttribute('aria-expanded',String(open));});
 if(open)document.querySelector('[data-be-close-bag]').focus();else if(returnFocus)returnFocus.focus();
}
document.querySelectorAll('[data-be-bag]').forEach(function(b){b.addEventListener('click',function(){toggleBag(true);});});document.querySelectorAll('[data-be-close-bag]').forEach(function(b){b.addEventListener('click',function(){toggleBag(false);});});if(backdrop)backdrop.addEventListener('click',function(){toggleBag(false);});
document.addEventListener('keydown',function(e){if(!bag||bag.getAttribute('aria-hidden')==='true')return;if(e.key==='Escape'){e.preventDefault();toggleBag(false);}if(e.key==='Tab'){var nodes=Array.from(bag.querySelectorAll('button:not(:disabled),a[href]')),first=nodes[0],last=nodes[nodes.length-1];if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}}});
document.querySelectorAll('[data-be-add]').forEach(function(b){b.addEventListener('click',function(){try{var p=JSON.parse(b.dataset.beAdd);if(addToCart(p.id,p.type,p.name,p.price,p.image,1,p.stock)!==false)updateCartUI();}catch(error){showToast('Please open this product to choose your options.');}});});
document.querySelectorAll('[data-be-filter]').forEach(function(b){b.addEventListener('click',function(){var section=b.closest('section'),shown=0;section.querySelectorAll('[data-be-filter]').forEach(function(other){var active=other===b;other.classList.toggle('be-active',active);other.setAttribute('aria-pressed',String(active));});section.querySelectorAll('[data-be-product]').forEach(function(p){p.hidden=b.dataset.beFilter!=='all'&&p.dataset.category!==b.dataset.beFilter;if(!p.hidden)shown++;});var empty=section.querySelector('[data-be-no-products]');if(empty)empty.hidden=shown>0;var status=section.querySelector('[data-be-filter-status]');if(status)status.textContent=shown+' products in this selection';});});
var menu=document.getElementById('be-mobile-menu');document.querySelectorAll('[data-be-menu]').forEach(function(b){b.addEventListener('click',function(){var open=menu.hidden;menu.hidden=!open;menu.classList.toggle('be-open',open);b.setAttribute('aria-expanded',String(open));});});
if(menu)menu.querySelectorAll('a').forEach(function(link){link.addEventListener('click',closeMenu);});
var enquiry=document.getElementById('be-enquiry-dialog'),enquiryFocus=null,enquiryBusy=false;
if(enquiry&&typeof enquiry.showModal==='function'){
 document.querySelectorAll('[data-be-enquiry]').forEach(function(link){link.addEventListener('click',function(event){
  if(event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;event.preventDefault();if(enquiryBusy)return;enquiryBusy=true;enquiryFocus=link;link.setAttribute('aria-busy','true');
  fetch(link.href,{credentials:'same-origin'}).then(function(response){if(!response.ok)throw new Error('request');return response.text();}).then(function(html){
   var doc=new DOMParser().parseFromString(html,'text/html'),form=doc.querySelector('.garden-enquiry');if(!form)throw new Error('form');
   enquiry.querySelector('[data-be-enquiry-content]').replaceChildren(document.importNode(form,true));enquiry.showModal();
  }).catch(function(){window.location.href=link.href;}).finally(function(){enquiryBusy=false;link.removeAttribute('aria-busy');});
 });});
 enquiry.querySelector('[data-be-close-enquiry]').addEventListener('click',function(){enquiry.close();});
 enquiry.addEventListener('close',function(){if(enquiryFocus)enquiryFocus.focus();});
 enquiry.addEventListener('click',function(e){if(e.target===enquiry){var r=enquiry.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)enquiry.close();}});
}
var search=document.getElementById('be-search');document.querySelectorAll('[data-be-search]').forEach(function(b){b.addEventListener('click',function(){closeMenu();search.hidden=!search.hidden;b.setAttribute('aria-expanded',String(!search.hidden));if(!search.hidden)search.querySelector('input').focus();});});
document.addEventListener('keydown',function(e){if(e.key!=='Escape')return;document.querySelectorAll('[data-be-menu],[data-be-search]').forEach(function(b){if(b.getAttribute('aria-expanded')==='true'){var target=document.getElementById(b.getAttribute('aria-controls'));if(target){target.hidden=true;target.classList.remove('be-open');}b.setAttribute('aria-expanded','false');b.focus();}});});
})();
