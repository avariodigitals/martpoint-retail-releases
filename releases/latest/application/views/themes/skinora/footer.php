<?php
require_once APPPATH . 'views/themes/skinora/_helpers.php';
$snSlug = $settings->store_slug ?? '';
$snHome = base_url('store/' . $snSlug);
$snName = trim((string) ($store->store_name ?? '')) ?: 'Skinora';
$snAbout = trim(strip_tags((string) ($settings->footer_about_us ?? ''))) ?: 'Skincare is personal. Your beauty ritual should be too.';
$snEmail = trim((string) ($settings->store_email ?? ''));
$snPhone = trim((string) ($settings->store_phone ?? ''));
$snWa = preg_replace('/[^0-9]/', '', (string) ($settings->whatsapp_number ?? ''));
$snFooterSections = $homepage_sections ?? $this->theme_engine->homepageSections();
$snFooterFaqs = $faqs ?? $this->storefront_model->getStorefrontFaqs($settings->store_id);
$snShowFaqLink = isset($snFooterSections['faqs']) && !empty($snFooterFaqs);
$snShowNewsletterLink = isset($snFooterSections['newsletter']);
?>
<footer class="footer" id="contact"><div class="container"><div class="footer-grid"><div><a class="brand<?= !empty($logo_url) ? ' sn-brand-logo' : ''; ?>" href="<?= sn_e($snHome); ?>" aria-label="<?= sn_e($snName); ?> homepage"><?php if (!empty($logo_url)): ?><img src="<?= sn_e($logo_url); ?>" alt="<?= sn_e($snName); ?> logo"><?php else: ?><?= sn_e($snName); ?><span>PURE · CARE · YOU</span><?php endif; ?></a><p><?= sn_e($snAbout); ?></p><p>Made for beautiful skin moments.</p></div><div><h4>Explore</h4><?php if (!empty($sells_products)): ?><a href="<?= sn_e($snHome . '/products'); ?>">Shop all</a><a href="<?= sn_e($snHome . '#collections'); ?>">Collections</a><?php endif; ?><a href="<?= sn_e($snHome . '/about'); ?>">Our philosophy</a><?php if (!empty($sells_services) && !empty($settings->allow_services)): ?><a href="<?= sn_e($snHome . '/services'); ?>">Treatments</a><?php endif; ?></div><div><h4>Customer care</h4><?php if ($snEmail): ?><a href="mailto:<?= sn_e($snEmail); ?>">Contact us</a><?php endif; ?><?php if (!empty($cart_enabled)): ?><a href="<?= sn_e($snHome . '/cart'); ?>">Your bag</a><a href="<?= sn_e($snHome . '/checkout'); ?>">Checkout</a><?php endif; ?><a href="<?= sn_e($snHome . '/account/orders'); ?>">Your orders</a><?php if ($snShowFaqLink): ?><a href="<?= sn_e($snHome . '#faqs'); ?>">FAQs</a><?php endif; ?></div><div><h4>Stay inspired</h4><p>Build a ritual that feels like you.</p><?php if ($snShowNewsletterLink): ?><a href="<?= sn_e($snHome . '#newsletter'); ?>">Join our newsletter →</a><?php endif; ?><?php if ($snPhone): ?><a href="tel:<?= sn_e(preg_replace('/[^0-9+]/', '', $snPhone)); ?>"><?= sn_e($snPhone); ?></a><?php endif; ?><?php if ($snWa): ?><a href="https://wa.me/<?= sn_e($snWa); ?>" target="_blank" rel="noopener">WhatsApp ↗</a><?php endif; ?></div></div><div class="footer-bottom"><span>© <?= date('Y'); ?> <?= sn_e($snName); ?>. All rights reserved.</span><span>POWERED BY MARTPOINT · RETAIL, BEAUTIFULLY REIMAGINED</span></div></div></footer>
<div class="mp-toast sn-toast" id="toast" role="status" aria-live="polite"></div>
<?php if (!empty($cart_enabled)): ?>
<div class="overlay" id="sn-cart-overlay" hidden></div>
<aside class="drawer" id="sn-cart-drawer" aria-label="Your beauty bag" aria-hidden="true" role="dialog" aria-modal="true"><div class="drawer-header"><h3>Your beauty bag</h3><button type="button" class="icon-btn" id="sn-cart-close" aria-label="Close bag">✕</button></div><div class="drawer-body" id="sn-cart-items"></div><div class="drawer-foot"><div class="total"><span>Subtotal</span><span id="sn-cart-total"></span></div><a class="btn" href="<?= sn_e($snHome . '/cart'); ?>">VIEW YOUR BAG ↗</a><a class="sn-drawer-checkout" href="<?= sn_e($snHome . '/checkout'); ?>">Continue to checkout →</a></div></aside>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var trigger=document.getElementById('sn-cart-open'),drawer=document.getElementById('sn-cart-drawer'),overlay=document.getElementById('sn-cart-overlay'),body=document.getElementById('sn-cart-items');
  if(!trigger||!drawer||!overlay||!body)return;
  var previousFocus=null,base=<?= json_encode(base_url()); ?>;
  function close(){drawer.classList.remove('open');overlay.classList.remove('open');overlay.hidden=true;drawer.setAttribute('aria-hidden','true');trigger.setAttribute('aria-expanded','false');document.body.style.overflow='';if(previousFocus)previousFocus.focus();}
  function itemImage(value){if(!value)return '';return /^https?:\/\//i.test(value)?value:base+String(value).replace(/^\/+/, '');}
  function make(tag,className,value){var e=document.createElement(tag);if(className)e.className=className;if(value!==undefined)e.textContent=String(value);return e;}
  function render(){body.replaceChildren();var items=typeof cart!=='undefined'&&Array.isArray(cart)?cart:[];var total=0;if(!items.length){var empty=make('div','empty','Your beauty bag is waiting for something lovely.');var shop=make('a','text-link','EXPLORE THE COLLECTION ↗');shop.href=<?= json_encode($snHome . '/products'); ?>;empty.appendChild(document.createElement('br'));empty.appendChild(shop);body.appendChild(empty);}
    items.forEach(function(item,index){var line=typeof sfCartLineTotal==='function'?sfCartLineTotal(item):(Number(item.price)||0)*(Number(item.qty)||0);total+=line;var row=make('div','cart-row');var image=itemImage(item.image);if(image){var img=make('img','',undefined);img.src=image;img.alt=item.name||'Selected item';row.appendChild(img);}else{row.appendChild(make('div','sn-drawer-placeholder','✧'));}var info=make('div','sn-cart-row-copy');info.appendChild(make('h4','',item.name||'Selected item'));info.appendChild(make('p','',formatMoney(line)));var quantity=make('div','qty');var minus=make('button','','−'),plus=make('button','','+');minus.type=plus.type='button';minus.setAttribute('aria-label','Decrease '+(item.name||'item'));plus.setAttribute('aria-label','Increase '+(item.name||'item'));quantity.appendChild(minus);quantity.appendChild(make('span','',item.qty||1));quantity.appendChild(plus);info.appendChild(quantity);row.appendChild(info);body.appendChild(row);
      function change(delta){var next=(Number(item.qty)||0)+delta;if(delta>0&&item.type==='product'&&Number(item.stock)>0&&next>Number(item.stock)){showToast('Not enough stock');return;}if(next<=0)cart.splice(index,1);else item.qty=next;if(typeof saveCart==='function')saveCart();render();}minus.addEventListener('click',function(){change(-1)});plus.addEventListener('click',function(){change(1)});
    });document.getElementById('sn-cart-total').textContent=formatMoney(total);
  }
  trigger.addEventListener('click',function(e){if(/\/(cart|checkout)\/?$/.test(location.pathname))return;e.preventDefault();previousFocus=document.activeElement;render();overlay.hidden=false;requestAnimationFrame(function(){overlay.classList.add('open');drawer.classList.add('open')});drawer.setAttribute('aria-hidden','false');trigger.setAttribute('aria-expanded','true');document.body.style.overflow='hidden';document.getElementById('sn-cart-close').focus();});
  document.getElementById('sn-cart-close').addEventListener('click',close);overlay.addEventListener('click',close);document.addEventListener('keydown',function(e){if(e.key==='Escape'&&drawer.classList.contains('open'))close()});
});
</script>
<?php endif; ?>
