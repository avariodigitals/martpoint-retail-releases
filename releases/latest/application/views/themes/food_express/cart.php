<?php $home = base_url('store/' . $settings->store_slug); $fxOutlets = $outlets ?? []; ?>
<main class="fx-profile fx-basket">
  <div class="fx-profile-heading"><span>Your basket</span><h1>Your next good meal.</h1></div>
  <div id="fx-basket-items"></div>
  <a class="fx-profile-back" href="<?= $home; ?>/products">+ Add more items</a>
  <details class="fx-product-location" id="fx-basket-location">
    <summary><span>Ordering from</span><strong id="fx-basket-kitchen">Choose a kitchen</strong><span>Change ›</span></summary>
    <div class="fx-product-location-options">
      <?php foreach($fxOutlets as $outlet): ?>
      <label class="fx-basket-outlet"><input type="radio" name="basket_outlet" value="<?= (int)$outlet->id; ?>" data-name="<?= htmlspecialchars($outlet->warehouse_name, ENT_QUOTES, 'UTF-8'); ?>"><span><strong><?= htmlspecialchars($outlet->warehouse_name); ?></strong><small><?= htmlspecialchars($outlet->address ?? ''); ?></small></span></label>
      <?php endforeach; ?>
    </div>
  </details>
  <section class="fx-profile-recent" id="fx-basket-summary"><div class="fx-profile-section-heading"><h2>Basket total</h2><strong id="fx-basket-total"></strong></div><p>Delivery and any applicable fees are confirmed at checkout.</p><a class="fx-profile-cta" id="fx-basket-checkout" href="<?= $home; ?>/checkout">Continue to checkout →</a></section>
</main>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const root=document.getElementById('fx-basket-items');
  const empty=document.createElement('section');
  function node(tag,text,cls){const el=document.createElement(tag);if(text!==undefined)el.textContent=text;if(cls)el.className=cls;return el;}
  function render(){
    root.replaceChildren();
    document.getElementById('fx-basket-summary').hidden=!cart.length;
    document.getElementById('fx-basket-location').hidden=!cart.length;
    if(!cart.length){
      empty.className='fx-profile-empty';empty.replaceChildren(node('h2','Something delicious awaits.'),node('p','Explore the menu and find your next favourite.'));root.append(empty);return;
    }
    cart.forEach((item,index)=>{
      const row=node('article',undefined,'fx-basket-line');
      const media=node('a');media.href=<?= json_encode($home . '/product/'); ?>+encodeURIComponent(item.id);
      if(item.image && /^(https?:\/\/|\/|uploads\/)/i.test(item.image)){
        const img=node('img');img.src=new URL(item.image,<?= json_encode(base_url()); ?>).href;img.alt=item.name;media.append(img);
      }
      const content=node('div',undefined,'fx-basket-copy');content.append(node('h2',item.name));
      const parts=[];if(item.spice_level)parts.push(item.spice_level==='extra'?'Extra hot':item.spice_level+' spice');
      (item.addons||[]).forEach(a=>parts.push(a.name+(Number(a.qty)>1?' × '+a.qty:'')));
      if(parts.length)content.append(node('p',parts.join(' · ')));
      if(item.note)content.append(node('p',item.note,'fx-basket-note'));
      content.append(node('strong',formatMoney(sfCartLineTotal(item))));
      const controls=node('div',undefined,'fx-basket-controls');
      const minus=node('button','−'), plus=node('button','+'), remove=node('button','Remove','fx-basket-remove');
      minus.type=plus.type=remove.type='button';
      minus.setAttribute('aria-label','Decrease '+item.name+' quantity');plus.setAttribute('aria-label','Increase '+item.name+' quantity');minus.disabled=Number(item.qty)<=1;
      minus.onclick=()=>{item.qty=Math.max(1,Number(item.qty)-1);saveCart();render();};
      plus.onclick=()=>{
        const used=cart.filter(i=>i.id===item.id&&i.type===item.type).reduce((n,i)=>n+Number(i.qty),0);
        if(!<?= !empty($settings->allow_backorder) ? 'true' : 'false'; ?> && item.type==='product' && used>=Number(item.stock)){showToast('Not enough stock');return;}
        item.qty=Number(item.qty)+1;saveCart();render();
      };
      remove.onclick=()=>{cart.splice(index,1);saveCart();render();};
      controls.append(minus,node('span',String(item.qty)),plus,remove);content.append(controls);row.append(media,content);root.append(row);
    });
    document.getElementById('fx-basket-total').textContent=formatMoney(cart.reduce((sum,item)=>sum+sfCartLineTotal(item),0));
  }
  const options=Array.from(document.querySelectorAll('[name="basket_outlet"]'));
  function syncKitchen(){
    const saved=localStorage.getItem('sf_outlet_'+STORE_ID);
    options.forEach(input=>input.checked=input.value===saved);
    const selected=options.find(input=>input.checked);
    if(selected){document.getElementById('fx-basket-kitchen').textContent=selected.dataset.name;document.querySelectorAll('[data-fx-location-value]').forEach(el=>el.textContent=selected.dataset.name);}
  }
  options.forEach(input=>input.onchange=()=>{localStorage.setItem('sf_outlet_'+STORE_ID,input.value);syncKitchen();document.getElementById('fx-basket-location').open=false;});
  document.getElementById('fx-basket-checkout').onclick=event=>{
    if(options.length && !options.some(input=>input.checked)){
      event.preventDefault();document.getElementById('fx-basket-location').open=true;showToast('Choose your kitchen first');
    }
  };
  syncKitchen();render();
});
</script>
