<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); ?>
<style>
.os-nav-grid{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(0,1.5fr)!important;gap:20px!important;align-items:start!important}
@media(max-width:1024px){.os-nav-grid{grid-template-columns:1fr!important}}
.nv-group-label{font-size:11px!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important;color:var(--mp-muted)!important;margin:14px 0 8px!important}
.nv-pick{display:flex!important;align-items:center!important;gap:10px!important;padding:9px 12px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;margin-bottom:6px!important;background:var(--mp-surface)!important}
.nv-pick-name{flex:1!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}
.nv-pick-kind{font-size:11px!important;color:var(--mp-muted)!important}
.nv-add{font-size:12px!important;font-weight:700!important;color:var(--mp-primary)!important;background:none!important;border:1px solid var(--mp-border)!important;border-radius:7px!important;padding:4px 10px!important;cursor:pointer!important;transition:all .15s ease!important}
.nv-add:hover{background:rgba(0,87,255,.07)!important;border-color:var(--mp-primary)!important}
.nv-row{display:flex!important;align-items:center!important;gap:10px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:10px 12px!important;margin-bottom:8px!important}
.nv-row.sortable-ghost{opacity:.4!important;border-color:var(--mp-primary)!important}
.nv-handle{cursor:grab!important;color:var(--mp-muted)!important;font-size:16px!important;padding:2px 4px!important}
.nv-handle:active{cursor:grabbing!important}
.nv-badge{font-size:10px!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.04em!important;padding:3px 8px!important;border-radius:20px!important;background:var(--mp-bg)!important;color:var(--mp-muted)!important;border:1px solid var(--mp-border)!important;flex-shrink:0!important}
.nv-label{flex:1!important;min-width:0!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;background:transparent!important;border:1px solid transparent!important;border-radius:6px!important;padding:4px 8px!important}
.nv-label:hover{border-color:var(--mp-border)!important}
.nv-label:focus{outline:none!important;border-color:var(--mp-primary)!important;background:var(--mp-bg)!important}
.nv-url{font-size:11px!important;color:var(--mp-muted)!important;max-width:180px!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;flex-shrink:0!important}
.nv-btn{background:none!important;border:1px solid var(--mp-border)!important;border-radius:6px!important;color:var(--mp-muted)!important;cursor:pointer!important;font-size:12px!important;padding:4px 8px!important;transition:all .15s ease!important}
.nv-btn:hover{color:var(--mp-primary)!important;border-color:var(--mp-primary)!important}
.nv-btn.nv-del:hover{color:#DC2626!important;border-color:#DC2626!important}
.nv-sub{margin:0 0 8px 26px!important;padding-left:14px!important;border-left:2px solid var(--mp-border)!important}
.nv-empty{font-size:13px!important;color:var(--mp-muted)!important;padding:18px!important;text-align:center!important;border:1px dashed var(--mp-border)!important;border-radius:10px!important;margin-bottom:10px!important}
.nv-guide-item{padding:10px 0!important;border-bottom:1px solid var(--mp-border)!important;font-size:12px!important;color:var(--mp-muted)!important;line-height:1.55!important}
.nv-guide-item:last-child{border-bottom:none!important}
.nv-guide-item strong{color:var(--mp-ink)!important;display:block!important;margin-bottom:2px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Configure a primary pages menu and a secondary stripe before your hero. Show one or both, with categories in either row.</div>
  </div>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="mp-qa-btn blue"><i class="fa fa-external-link"></i> Preview Store</a>
</div>

<?php $dual=is_array($nav_configured)&&($nav_configured['version'] ?? 0)===2;
$navRows=$dual?$nav_configured:['version'=>2,'primary'=>['enabled'=>true,'source'=>'pages','items'=>[]],'secondary'=>['enabled'=>true,'source'=>empty($nav_configured)?'categories':'custom','items'=>$nav_configured ?: []]]; ?>
<section class="mp-card-form" style="margin-bottom:20px"><div class="mp-card-head"><h3>Two menu rows</h3></div><div class="mp-card-body">
<p>Choose a row to edit. Pages and Categories update automatically; Custom lets you choose and rename individual links.</p>
<div style="display:flex;gap:12px;flex-wrap:wrap">
<?php foreach(['primary'=>'Primary menu · top level','secondary'=>'Secondary menu · stripe'] as $key=>$label): ?>
<fieldset style="flex:1;min-width:240px;border:1px solid var(--mp-border);border-radius:12px;padding:16px"><legend style="font-size:15px;width:auto;border:0"><?= $label; ?></legend>
<label><input type="checkbox" id="nv-<?= $key; ?>-enabled" <?= !empty($navRows[$key]['enabled'])?'checked':''; ?>> Show this menu</label>
<div role="group" aria-label="<?= $label; ?> contents"><?php foreach(['pages'=>'Pages','categories'=>'Categories','custom'=>'Custom mix'] as $source=>$text): ?><label style="display:inline-flex;gap:5px;margin:8px"><input type="radio" name="nv-<?= $key; ?>-source" value="<?= $source; ?>" <?= $navRows[$key]['source']===$source?'checked':''; ?>><?= $text; ?></label><?php endforeach; ?></div>
<button type="button" class="nv-add" onclick="nvEdit('<?= $key; ?>')">Edit this row</button>
</fieldset><?php endforeach; ?></div><p id="nv-editing" role="status" style="margin-top:12px">Editing primary menu</p></div></section>
<div class="os-nav-grid">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Add Menu Items</h3></div>
    <div class="mp-card-body">
      <div class="nv-group-label">Pages</div>
      <?php
      $palettePages = [['k' => 'home', 'l' => 'Home']];
      if(!empty($catalogue_flags['sells_products'])) $palettePages[] = ['k' => 'products', 'l' => !empty($nav_building) ? 'Systems' : 'All Products'];
      if(!empty($catalogue_flags['sells_services'])) $palettePages[] = ['k' => 'services', 'l' => 'Services'];
      if(!empty($nav_building)){
        $palettePages[] = ['k' => 'applications', 'l' => 'Applications'];
        $palettePages[] = ['k' => 'projects', 'l' => 'Projects'];
        $palettePages[] = ['k' => 'insights', 'l' => 'Insights'];
      }
      $palettePages[] = ['k' => 'about', 'l' => 'About Us'];
      $palettePages[] = ['k' => 'contact', 'l' => 'Contact Us'];
      if(empty($nav_building)) $palettePages[] = ['k'=>'journal','l'=>'Journal'];
      $palettePages[] = ['k'=>'privacy','l'=>'Privacy policy'];
      foreach($palettePages as $pp): ?>
      <div class="nv-pick" data-nv-type="page" data-nv-key="<?= htmlspecialchars($pp['k'], ENT_QUOTES); ?>" data-nv-label="<?= htmlspecialchars($pp['l'], ENT_QUOTES); ?>">
        <span class="nv-pick-name"><?= htmlspecialchars($pp['l']); ?></span>
        <button type="button" class="nv-add nv-pick-btn"><i class="fa fa-plus"></i> Add</button>
      </div>
      <?php endforeach; ?>

      <div class="nv-group-label">Your Categories</div>
      <?php if(empty($nav_categories)): ?>
      <p class="mp-form-hint" style="margin:0;">No published categories yet. Add items in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a> or <a href="<?= base_url('online_store/services'); ?>">Services</a> first.</p>
      <?php else: foreach($nav_categories as $nc): ?>
      <div class="nv-pick" data-nv-type="cat" data-nv-id="<?= (int)$nc->id; ?>" data-nv-label="<?= htmlspecialchars($nc->category_name, ENT_QUOTES); ?>">
        <span class="nv-pick-name"><?= htmlspecialchars($nc->category_name); ?></span>
        <button type="button" class="nv-add nv-pick-btn"><i class="fa fa-plus"></i> Add</button>
      </div>
      <?php endforeach; endif; ?>

      <div class="nv-group-label">Custom Link</div>
      <div class="mp-form-group" style="margin-bottom:8px!important;">
        <input type="text" class="mp-form-control" id="nv-cl-label" placeholder="Link name (e.g. Delivery Policy)">
      </div>
      <div class="mp-form-group" style="margin-bottom:8px!important;">
        <input type="text" class="mp-form-control" id="nv-cl-url" placeholder="https://... or /your-page">
      </div>
      <button type="button" class="nv-add" onclick="navAddLink()"><i class="fa fa-plus"></i> Add Link</button>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Your Menu</h3></div>
    <div class="mp-card-body">
      <p class="mp-form-hint" style="margin-top:0;">
        Drag <i class="fa fa-ellipsis-v"></i> to reorder. Use <i class="fa fa-indent"></i> to tuck an item under the one above it as a submenu (one level deep), and <i class="fa fa-outdent"></i> to bring it back out. Rename any item by typing in its box.
      </p>
      <div id="nv-root" class="nv-list"></div>
      <div id="nv-empty" class="nv-empty" style="display:none;">No custom links in this row. Add items here and choose Custom mix, or select automatic Pages or Categories above.</div>
    </div>
    <div class="mp-card-foot" style="display:flex!important;gap:10px!important;">
      <button type="button" class="mp-btn-primary" onclick="navSave()"><i class="fa fa-save"></i> Save Menu</button>
      <button type="button" class="os-nv-reset" onclick="navReset()"><i class="fa fa-undo"></i> Reset to Automatic</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };

var nvRows = <?= json_encode($navRows, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
var nvEditing='primary',nvMenu=nvRows.primary.items;
function nvEdit(key){nvRows[nvEditing].items=nvCollect();nvEditing=key;nvMenu=nvRows[key].items;document.getElementById('nv-editing').textContent='Editing '+key+' menu';nvRender();}
var nvRoot = document.getElementById('nv-root');
var nvEmpty = document.getElementById('nv-empty');
var nvSortables = [];

document.querySelectorAll('.nv-pick-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var pick = btn.closest('.nv-pick');
    navAdd(pick.dataset.nvType, pick.dataset.nvKey || pick.dataset.nvId, pick.dataset.nvLabel);
  });
});

function nvKindLabel(item){
  return item.t === 'page' ? 'Page' : (item.t === 'cat' ? 'Category' : 'Link');
}

function nvMakeRow(item, depth){
  var row = document.createElement('div');
  row.className = 'nv-row';
  row.dataset.t = item.t;
  if(item.t === 'page') row.dataset.k = item.k;
  if(item.t === 'cat') row.dataset.i = item.i;
  if(item.t === 'link') row.dataset.u = item.u || '';
  row.innerHTML =
    '<span class="nv-handle" title="Drag to reorder"><i class="fa fa-ellipsis-v"></i></span>' +
    '<span class="nv-badge">' + nvKindLabel(item) + '</span>' +
    '<input type="text" class="nv-label" maxlength="60">' +
    (item.t === 'link' ? '<span class="nv-url"></span>' : '') +
    (depth < 1 ? '<button type="button" class="nv-btn nv-indent" title="Tuck under the item above (submenu)"><i class="fa fa-indent"></i></button>' : '') +
    (depth > 0 ? '<button type="button" class="nv-btn nv-outdent" title="Bring out to the level above"><i class="fa fa-outdent"></i></button>' : '') +
    '<button type="button" class="nv-btn nv-del" title="Remove from menu"><i class="fa fa-times"></i></button>';
  row.querySelector('.nv-label').value = item.l || '';
  if(item.t==='link'){row.querySelector('.nv-url').textContent=item.u||'';row.querySelector('.nv-url').title=item.u||'';}
  row.querySelector('.nv-del').addEventListener('click', function(){ row.remove(); nvRefresh(); });
  var ind = row.querySelector('.nv-indent');
  if(ind) ind.addEventListener('click', function(){ nvIndent(row); });
  var out = row.querySelector('.nv-outdent');
  if(out) out.addEventListener('click', function(){ nvOutdent(row); });
  if(depth===0&&Array.isArray(item.c)&&item.c.length)row.appendChild(nvMakeSub(item.c));
  return row;
}

function nvMakeSub(children){
  var sub = document.createElement('div');
  sub.className = 'nv-sub nv-list';
  (children || []).forEach(function(child){ sub.appendChild(nvMakeRow(child, 1)); });
  return sub;
}

function nvRender(){
  nvSortables.forEach(function(s){ s.destroy(); });
  nvSortables = [];
  nvRoot.innerHTML = '';
  (nvMenu || []).forEach(function(item){ nvRoot.appendChild(nvMakeRow(item, 0)); });
  nvRefresh();
}

function nvRefresh(){
  nvSortables.forEach(function(s){ s.destroy(); });
  nvSortables = [];
  document.querySelectorAll('.nv-list').forEach(function(list){
    nvSortables.push(new Sortable(list, {
      handle: '.nv-handle',
      group: 'nv-menu',
      animation: 150,
      ghostClass: 'sortable-ghost',
      onEnd: nvRefresh
    }));
  });
  var has = nvRoot.querySelectorAll(':scope > .nv-row').length > 0;
  nvEmpty.style.display = has ? 'none' : 'block';
}

function nvIndent(row){
  var prev = row.previousElementSibling;
  if(!prev || !prev.classList.contains('nv-row')) return;
  var sub = prev.querySelector(':scope > .nv-sub');
  if(!sub){ sub = nvMakeSub([]); prev.appendChild(sub); }
  sub.appendChild(row);
  var out = row.querySelector('.nv-outdent');
  if(!out){
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'nv-btn nv-outdent';
    btn.title = 'Bring out to the level above';
    btn.innerHTML = '<i class="fa fa-outdent"></i>';
    btn.addEventListener('click', function(){ nvOutdent(row); });
    row.appendChild(btn);
  }
  var ind = row.querySelector('.nv-indent');
  if(ind) ind.remove();
  nvRefresh();
}

function nvOutdent(row){
  var sub = row.closest('.nv-sub');
  if(!sub) return;
  var parentRow = sub.closest('.nv-row');
  if(!parentRow) return;
  parentRow.after(row);
  var out = row.querySelector('.nv-outdent');
  if(out) out.remove();
  var ind = row.querySelector('.nv-indent');
  if(!ind){
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'nv-btn nv-indent';
    btn.title = 'Tuck under the item above (submenu)';
    btn.innerHTML = '<i class="fa fa-indent"></i>';
    btn.addEventListener('click', function(){ nvIndent(row); });
    row.insertBefore(btn, row.querySelector('.nv-del'));
  }
  if(!sub.querySelector('.nv-row')) sub.remove();
  nvRefresh();
}

function navAdd(type, keyOrId, label){
  document.querySelector('input[name="nv-'+nvEditing+'-source"][value=custom]').checked=true;
  var item = { t: type, l: label };
  if(type === 'page') item.k = keyOrId;
  if(type === 'cat') item.i = keyOrId;
  nvRoot.appendChild(nvMakeRow(item, 0));
  nvRefresh();
}

function navAddLink(){
  document.querySelector('input[name="nv-'+nvEditing+'-source"][value=custom]').checked=true;
  var labelEl = document.getElementById('nv-cl-label');
  var urlEl = document.getElementById('nv-cl-url');
  var label = labelEl.value.trim();
  var url = urlEl.value.trim();
  if(!label || !url){ toastr.error('Enter both a name and a web address for the link.'); return; }
  nvRoot.appendChild(nvMakeRow({ t: 'link', l: label, u: url }, 0));
  labelEl.value = ''; urlEl.value = '';
  nvRefresh();
}

function nvCollect(){
  var items = [];
  nvRoot.querySelectorAll(':scope > .nv-row').forEach(function(row){
    items.push(nvReadRow(row));
  });
  return items;
}

function nvReadRow(row){
  var item = { t: row.dataset.t, l: row.querySelector('.nv-label').value.trim() };
  if(row.dataset.t === 'page') item.k = row.dataset.k;
  if(row.dataset.t === 'cat') item.i = parseInt(row.dataset.i, 10);
  if(row.dataset.t === 'link') item.u = row.dataset.u || '';
  var sub = row.querySelector(':scope > .nv-sub');
  if(sub){
    var kids = [];
    sub.querySelectorAll(':scope > .nv-row').forEach(function(child){ kids.push(nvReadRow(child)); });
    if(kids.length) item.c = kids;
  }
  return item;
}

function navSave(){
  nvRows[nvEditing].items=nvCollect().filter(function(item){return item.l!=='';});
  ['primary','secondary'].forEach(function(key){nvRows[key].enabled=document.getElementById('nv-'+key+'-enabled').checked;nvRows[key].source=document.querySelector('input[name="nv-'+key+'-source"]:checked').value;});
  if(!nvRows.primary.enabled&&!nvRows.secondary.enabled){toastr.error('Keep at least one menu enabled.');return;}
  var items=nvRows;
  var fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('nav_json', JSON.stringify(items));
  fetch('<?= base_url('online_store/save_navigation'); ?>', { method: 'POST', body: fd })
  .then(function(r){ return r.json(); })
  .then(function(res){
    if(res.status === 'success') toastr.success(res.message);
    else toastr.error(res.message || 'Failed to save');
  }).catch(function(){ toastr.error('Error saving the menu'); });
}

function navReset(){
  mpConfirmAsync('Remove your saved menu? Your store goes back to the automatic menu (All Products + your first categories).').then(function(ok){
    if(!ok) return;
    var fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fd.append('reset', '1');
    fetch('<?= base_url('online_store/save_navigation'); ?>', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if(res.status === 'success'){ toastr.success(res.message); location.reload(); }
      else toastr.error(res.message || 'Failed to reset');
    }).catch(function(){ toastr.error('Error resetting the menu'); });
  });
}

nvRender();
</script>
<script>$(".online_store-navigation-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
