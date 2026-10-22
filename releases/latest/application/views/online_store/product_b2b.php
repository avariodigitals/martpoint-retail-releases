<?php
$this->load->view('online_store/content_editor_styles');
$mobile=$this->input->get('screen')==='mobile';
$suffix=$mobile?'?screen=mobile':'';
$specRows=is_array($product_specs)?$product_specs:[];
for($i=count($specRows);$i<max(3,count($specRows)+1);$i++)$specRows[]=['label'=>'','value'=>''];
$docTypes=['datasheet'=>'Datasheet','certificate'=>'Certificate','drawing'=>'Drawing','manual'=>'Manual','brochure'=>'Brochure','other'=>'Other'];
$sizeText=function($bytes){
  $bytes=(int)$bytes;
  if($bytes>=1048576)return round($bytes/1048576,1).' MB';
  if($bytes>=1024)return max(1,round($bytes/1024)).' KB';
  return $bytes.' B';
};
$productImage=function($product){
  $path=trim((string)($product->item_image??''));
  if($path==='')return '';
  return preg_match('~^https?://~i',$path)?$path:base_url($path);
};
?>
<?php if(!$mobile): ?><h2>Specifications &amp; documents</h2><?php endif; ?>
<nav class="sf-admin-links"><a href="<?= base_url('online_store/product_b2b').$suffix; ?>">Choose a product</a><a href="<?= base_url('online_store/products_online').$suffix; ?>">Online Products</a><a href="<?= base_url('online_store/theme_content').$suffix; ?>">Theme content</a><a href="<?= base_url('online_store/appearance').$suffix; ?>">Appearance</a><?php if($edit_product): ?><a href="<?= base_url('store/'.$settings->store_slug.'/product/'.(int)$edit_product->id); ?>" target="_blank" rel="noopener">Preview product ↗</a><?php endif; ?></nav>

<?php if(!$edit_product): ?>
<div class="sf-admin-card">
  <h3>Choose a product</h3>
  <p>Specifications and downloadable datasheets appear on the product page. Only products published to the online store are listed, because those are the ones customers can open.</p>
  <form method="get" action="<?= base_url('online_store/product_b2b'); ?>" style="display:flex;gap:10px;flex-wrap:wrap;margin:0 0 18px">
    <input type="search" name="q" value="<?= htmlspecialchars($product_search,ENT_QUOTES); ?>" placeholder="Search products or part numbers" style="flex:1 1 220px;padding:11px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit">
    <button type="submit">Search</button>
    <?php if($product_search!==''): ?><a class="sf-admin-action" href="<?= base_url('online_store/product_b2b'); ?>">Clear</a><?php endif; ?>
  </form>
  <?php if($product_list): ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
    <?php foreach($product_list as $product): $img=$productImage($product); ?>
    <a href="<?= base_url('online_store/product_b2b/'.(int)$product->id).$suffix; ?>" style="display:flex;gap:12px;align-items:center;padding:12px;border:1px solid var(--mp-border,#dce3df);border-radius:8px;text-decoration:none;color:inherit">
      <?php if($img!==''): ?><img src="<?= htmlspecialchars($img,ENT_QUOTES); ?>" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex:none"><?php endif; ?>
      <span><strong style="display:block;font-size:13px"><?= htmlspecialchars($product->item_name); ?></strong><span class="mp-form-hint"><?= htmlspecialchars($product->item_code?:'No part number'); ?><?= !empty($product->category_name)?' · '.htmlspecialchars($product->category_name):''; ?></span></span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php if($product_page>1||$product_has_more): ?>
  <div style="display:flex;justify-content:space-between;gap:12px;margin-top:18px">
    <?php if($product_page>1): $prev=base_url('online_store/product_b2b').'?'.http_build_query(['q'=>$product_search,'page'=>$product_page-1]); ?><a class="sf-admin-action" href="<?= htmlspecialchars($prev,ENT_QUOTES); ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
    <?php if($product_has_more): $next=base_url('online_store/product_b2b').'?'.http_build_query(['q'=>$product_search,'page'=>$product_page+1]); ?><a class="sf-admin-action" href="<?= htmlspecialchars($next,ENT_QUOTES); ?>">Next</a><?php endif; ?>
  </div>
  <?php endif; ?>
  <?php else: ?><p>No online products match that search. Publish products in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a> first.</p><?php endif; ?>
</div>
<?php else: ?>
<div class="sf-admin-grid">
<section class="sf-admin-card">
  <?php $editImage=$productImage($edit_product); ?>
  <div style="display:flex;gap:14px;align-items:center;margin-bottom:6px">
    <?php if($editImage!==''): ?><img src="<?= htmlspecialchars($editImage,ENT_QUOTES); ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;flex:none"><?php endif; ?>
    <div><h3 style="margin:0"><?= htmlspecialchars($edit_product->item_name); ?></h3><p class="mp-form-hint" style="margin:4px 0 0"><?= htmlspecialchars($edit_product->item_code?:'No part number'); ?><?= !empty($edit_product->category_name)?' · '.htmlspecialchars($edit_product->category_name):''; ?></p></div>
  </div>
  <p>These rows become the specification table on the product page. Use the wording a specifier would look for, and keep one value per row.</p>
  <form method="post" action="<?= base_url('online_store/save_product_specs'); ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="item_id" value="<?= (int)$edit_product->id; ?>">
    <input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
    <div id="spec-rows">
      <?php foreach($specRows as $i=>$row): ?>
      <div class="spec-row" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr) auto;gap:8px;margin-bottom:8px">
        <input type="text" name="specs[<?= $i; ?>][label]" maxlength="120" value="<?= htmlspecialchars($row['label']??'',ENT_QUOTES); ?>" placeholder="Label, e.g. Clear opening width" aria-label="Specification label" style="padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit">
        <input type="text" name="specs[<?= $i; ?>][value]" maxlength="500" value="<?= htmlspecialchars($row['value']??'',ENT_QUOTES); ?>" placeholder="Value, e.g. 1,200 mm" aria-label="Specification value" style="padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit">
        <button type="button" data-remove-spec aria-label="Remove this row" style="background:transparent;color:var(--mp-muted,#64748b);border:1px solid var(--mp-border,#dce3df);border-radius:5px;padding:0 12px;cursor:pointer">×</button>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" id="spec-add" class="sf-admin-action" style="background:transparent;color:var(--mp-primary,#24553c);border:1px solid var(--mp-border,#dce3df);margin-bottom:14px">Add another row</button>
    <div><button type="submit">Save specifications</button></div>
  </form>
</section>
<aside class="sf-admin-card">
  <h3>Documents</h3>
  <p>Datasheets, certificates, drawings and manuals. Public files appear on the product page; private files stay available to your team only.</p>
  <?php foreach($product_documents as $doc): ?>
  <article class="sf-admin-article">
    <strong><?= htmlspecialchars($doc->title); ?></strong>
    <p><?= htmlspecialchars($docTypes[$doc->doc_type] ?? 'Other'); ?> · <?= $doc->visibility==='public'?'Public':'Private'; ?> · <?= htmlspecialchars($sizeText($doc->file_size)); ?></p>
    <a href="<?= base_url('online_store/document_download/'.(int)$doc->id); ?>">Download</a>
    <?php if($doc->visibility==='public'): ?> · <a href="<?= base_url('store/'.$settings->store_slug.'/document/'.(int)$edit_product->id.'/'.(int)$doc->id); ?>" target="_blank" rel="noopener">Public link ↗</a><?php endif; ?>
    <details style="margin-top:10px">
      <summary style="cursor:pointer;font-size:13px">Edit details</summary>
      <form method="post" action="<?= base_url('online_store/save_product_document'); ?>" style="margin-top:10px">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="item_id" value="<?= (int)$edit_product->id; ?>">
        <input type="hidden" name="id" value="<?= (int)$doc->id; ?>">
        <input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
        <label for="doc-title-<?= (int)$doc->id; ?>">Title</label>
        <input id="doc-title-<?= (int)$doc->id; ?>" name="title" type="text" maxlength="180" value="<?= htmlspecialchars($doc->title,ENT_QUOTES); ?>" required>
        <label for="doc-type-<?= (int)$doc->id; ?>">Type</label>
        <select id="doc-type-<?= (int)$doc->id; ?>" name="doc_type" style="width:100%;padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit;background:transparent;color:inherit">
          <?php foreach($docTypes as $value=>$label): ?><option value="<?= $value; ?>" <?= $doc->doc_type===$value?'selected':''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
        </select>
        <label for="doc-visibility-<?= (int)$doc->id; ?>">Visible to</label>
        <select id="doc-visibility-<?= (int)$doc->id; ?>" name="visibility" style="width:100%;padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit;background:transparent;color:inherit">
          <option value="public" <?= $doc->visibility==='public'?'selected':''; ?>>Customers (public)</option>
          <option value="private" <?= $doc->visibility==='private'?'selected':''; ?>>My team only (private)</option>
        </select>
        <label for="doc-order-<?= (int)$doc->id; ?>">Sort order</label>
        <input id="doc-order-<?= (int)$doc->id; ?>" name="sort_order" type="number" value="<?= (int)$doc->sort_order; ?>">
        <input name="document_file" type="file" accept=".pdf,.doc,.docx,.dwg,.dxf,.jpg,.jpeg,.png,.webp" style="margin-top:10px">
        <p class="mp-form-hint">Leave the file empty to keep the current document. A new file replaces it.</p>
        <button type="submit">Save document</button>
      </form>
      <form method="post" action="<?= base_url('online_store/delete_product_document'); ?>" style="margin-top:10px">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="item_id" value="<?= (int)$edit_product->id; ?>">
        <input type="hidden" name="id" value="<?= (int)$doc->id; ?>">
        <input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
        <button type="submit" style="background:transparent;color:#b42318;border:1px solid #e3b4a2">Remove document</button>
      </form>
    </details>
  </article>
  <?php endforeach; ?>
  <?php if(!$product_documents): ?><p>No documents yet.</p><?php endif; ?>
  <h3 style="margin-top:24px">Add a document</h3>
  <form method="post" action="<?= base_url('online_store/save_product_document'); ?>" enctype="multipart/form-data">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="item_id" value="<?= (int)$edit_product->id; ?>">
    <input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
    <label for="new-doc-title">Title</label>
    <input id="new-doc-title" name="title" type="text" maxlength="180" placeholder="e.g. Technical datasheet" required>
    <label for="new-doc-type">Type</label>
    <select id="new-doc-type" name="doc_type" style="width:100%;padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit;background:transparent;color:inherit">
      <?php foreach($docTypes as $value=>$label): ?><option value="<?= $value; ?>"><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
    </select>
    <label for="new-doc-visibility">Visible to</label>
    <select id="new-doc-visibility" name="visibility" style="width:100%;padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit;background:transparent;color:inherit">
      <option value="public">Customers (public)</option>
      <option value="private">My team only (private)</option>
    </select>
    <label for="new-doc-file">File — PDF, Word, DWG, DXF, JPG, PNG or WebP, up to 12 MB</label>
    <input id="new-doc-file" name="document_file" type="file" accept=".pdf,.doc,.docx,.dwg,.dxf,.jpg,.jpeg,.png,.webp" required>
    <div><button type="submit">Upload document</button></div>
  </form>
</aside>
</div>
<script>
(function(){
  var rows=document.getElementById('spec-rows'),add=document.getElementById('spec-add');
  if(!rows||!add)return;
  var next=<?= count($specRows); ?>;
  add.addEventListener('click',function(){
    if(next>=40)return;
    var row=document.createElement('div');
    row.className='spec-row';
    row.style.cssText='display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr) auto;gap:8px;margin-bottom:8px';
    row.innerHTML='<input type="text" name="specs['+next+'][label]" maxlength="120" placeholder="Label, e.g. Clear opening width" aria-label="Specification label" style="padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit"><input type="text" name="specs['+next+'][value]" maxlength="500" placeholder="Value, e.g. 1,200 mm" aria-label="Specification value" style="padding:10px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit"><button type="button" data-remove-spec aria-label="Remove this row" style="background:transparent;color:var(--mp-muted,#64748b);border:1px solid var(--mp-border,#dce3df);border-radius:5px;padding:0 12px;cursor:pointer">&times;</button>';
    rows.appendChild(row);next++;
  });
  rows.addEventListener('click',function(event){
    var button=event.target.closest('[data-remove-spec]');
    if(!button)return;
    var row=button.closest('.spec-row');
    if(rows.querySelectorAll('.spec-row').length>1){row.remove();}
    else{row.querySelectorAll('input').forEach(function(input){input.value='';});}
  });
})();
</script>
<?php endif; ?>
