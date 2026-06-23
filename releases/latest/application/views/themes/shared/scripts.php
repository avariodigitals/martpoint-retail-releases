<script>
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
  let modalProduct = null;
  let modalQty = 1;

  function saveCart(){
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    updateCartUI();
  }

  function updateCartUI(){
    let qty = 0, total = 0;
    cart.forEach(i => { qty += i.qty; total += i.price * i.qty; });
    const cartCountEl = document.getElementById('cart-count');
    if(cartCountEl) cartCountEl.textContent = qty;
    const headerAmt = document.getElementById('header-cart-amount');
    if(headerAmt) headerAmt.textContent = formatMoney(total);
    const stickyQtyEl = document.getElementById('sticky-qty');
    if(stickyQtyEl) stickyQtyEl.textContent = qty;
    const stickyTotalEl = document.getElementById('sticky-total');
    if(stickyTotalEl) stickyTotalEl.textContent = formatMoney(total);
    const sticky = document.getElementById('sticky-cart');
    if(sticky){
      if(qty > 0 && !window.location.pathname.endsWith('/cart')) sticky.classList.add('show');
      else sticky.classList.remove('show');
    }
  }

  function orderProductOnWhatsApp(name, price){
    let msg = 'Hello, I would like to order: ' + name + ' — ' + formatMoney(price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }

  function addToCart(id, type, name, price, image, qty, stock){
    if(type === 'product' && stock !== undefined && stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
      showToast('Out of stock'); return;
    }
    const key = type + '_' + id;
    const existing = cart.find(i => i.key === key);
    if(existing){
      if(type === 'product' && stock !== undefined && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
        if(existing.qty + qty > stock){ showToast('Not enough stock'); return; }
      }
      existing.qty += qty;
    } else {
      cart.push({key, id, type, name, price, image, qty: qty, stock: stock ?? 999});
    }
    saveCart();
    showToast(name + ' added to cart');
  }

  function openProductModal(id, name, price, image, desc, stock, oldPrice){
    modalProduct = {id, name, price, image, desc, stock};
    modalQty = 1;
    document.getElementById('modal-title').textContent = name;
    document.getElementById('modal-price').innerHTML = formatMoney(price) + (oldPrice > 0 ? ' <span style="text-decoration:line-through;font-size:16px;color:#94A3B8;margin-left:8px;">' + formatMoney(oldPrice) + '</span>' : '');
    document.getElementById('modal-desc').textContent = desc || '';
    document.getElementById('modal-img').src = image ? '<?= base_url(); ?>' + image : '';
    document.getElementById('modal-qty').textContent = '1';
    document.getElementById('product-modal').classList.add('show');
  }

  function closeModal(){
    document.getElementById('product-modal').classList.remove('show');
  }

  function adjustModalQty(delta){
    modalQty = Math.max(1, modalQty + delta);
    document.getElementById('modal-qty').textContent = modalQty;
  }

  function addModalToCart(){
    if(!modalProduct) return;
    addToCart(modalProduct.id, 'product', modalProduct.name, modalProduct.price, modalProduct.image, modalQty, modalProduct.stock);
    closeModal();
  }

  function doSearch(){
    const q = document.getElementById('search-input')?.value.trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?search=' + encodeURIComponent(q);
  }

  function sendWhatsAppOrder(){
    let msg = 'Hello, I would like to place an order from <?= htmlspecialchars(addslashes($store->store_name ?? 'your store')); ?>';
    if(cart.length > 0){
      msg += '\n\nItems:\n';
      let total = 0;
      cart.forEach(i => {
        msg += i.qty + ' x ' + i.name + ' — ' + formatMoney(i.price * i.qty) + '\n';
        total += i.price * i.qty;
      });
      msg += '\nTotal: ' + formatMoney(total);
    }
    msg += '\n\nThank you.';
    const phone = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(phone){
      window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
    } else {
      showToast('WhatsApp number not configured');
    }
  }

  function toggleMobileMenu(){
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    if(!menu) return;
    if(menu.style.display === 'block'){
      menu.style.display = 'none';
      if(overlay) overlay.style.display = 'none';
      document.body.style.overflow = '';
    } else {
      menu.style.display = 'block';
      if(overlay) overlay.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
  }

  document.addEventListener('click', function(e){
    const modal = document.getElementById('product-modal');
    if(e.target === modal) closeModal();
  });

  updateCartUI();

  // Highlight active mobile nav item
  (function(){
    const path = window.location.pathname;
    document.querySelectorAll('.mp-mobile-nav-item').forEach(function(el){
      const href = el.getAttribute('href');
      if(!href) return;
      try{
        const linkPath = new URL(href, window.location.href).pathname;
        const isHome = linkPath === path || (linkPath.replace(/\/$/, '') === path.replace(/\/$/, ''));
        const isNested = path.startsWith(linkPath + '/') && linkPath !== '/';
        if(isHome || isNested) el.classList.add('active');
        else el.classList.remove('active');
      }catch(e){}
    });
  })();

  // Analytics tracking
  (function(){
    if(!STORE_ID) return;
    const tracked = sessionStorage.getItem('sf_tracked_' + STORE_ID);
    if(tracked) return;
    const fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fd.append('store_id', STORE_ID);
    fd.append('page_url', window.location.href);
    fd.append('referrer', document.referrer);
    // Extract search term from URL query string
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search') || '';
    if(searchTerm) fd.append('search_term', searchTerm);
    fetch('<?= base_url('online_store/track_visit'); ?>', {method:'POST', body:fd, keepalive:true})
    .then(() => { sessionStorage.setItem('sf_tracked_' + STORE_ID, '1'); })
    .catch(() => {});
  })();

  // Back to top toggle
  (function(){
    const backtop = document.getElementById('backtop');
    if(!backtop) return;
    window.addEventListener('scroll', function(){
      if(window.scrollY > 400) backtop.classList.add('show');
      else backtop.classList.remove('show');
    });
  })();
</script>
