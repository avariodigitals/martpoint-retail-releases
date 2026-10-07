<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$esc = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<style>
.cr-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.cr-section{min-width:0}.cr-section h3{font-size:16px;margin:0 0 14px}.cr-form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end}.cr-form-grid label{display:block;font-size:12px;font-weight:600;margin-bottom:5px}.cr-form-grid .form-control{min-width:0}.cr-list{margin-top:14px;border-top:1px solid #e2e8f0}.cr-row{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #e2e8f0;font-size:13px}.cr-row form{margin:0;flex:0 0 auto}.cr-row small{color:#64748b}.cr-remove{border:0;background:none;color:#a12622;padding:5px;cursor:pointer}.cr-empty{color:#64748b;font-size:13px;padding:12px 0}.cr-alert{padding:10px 14px;border-radius:5px;margin-bottom:14px}.cr-alert.success{background:#e8f5ee;color:#176b4d}.cr-alert.error{background:#fdecea;color:#a12622}@media(max-width:850px){.cr-grid{grid-template-columns:1fr}}
</style>
<?php if($message=$this->session->flashdata('success')): ?><div class="cr-alert success"><?= $esc($message); ?></div><?php endif; ?>
<?php if($message=$this->session->flashdata('error')): ?><div class="cr-alert error"><?= $esc($message); ?></div><?php endif; ?>
<div class="cr-grid">
  <section class="box box-primary cr-section">
    <div class="box-header with-border"><h3>Composite Bundles</h3></div>
    <div class="box-body">
      <form method="post" action="<?= base_url('online_store/save_commerce_rule'); ?>">
        <input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><input type="hidden" name="rule_type" value="bundle">
        <div class="cr-form-grid">
          <div><label for="cr-bundle">Bundle</label><select class="form-control" id="cr-bundle" name="bundle_item_id" required><option value="">Select bundle</option><?php foreach($bundles as $bundle): ?><option value="<?= (int)$bundle->id; ?>"><?= $esc($bundle->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-component">Component</label><select class="form-control" id="cr-component" name="component_item_id" required><option value="">Select item</option><?php foreach($products as $item): if(!empty($item->is_bundle)) continue; ?><option value="<?= (int)$item->id; ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-component-qty">Qty per bundle</label><input class="form-control" id="cr-component-qty" type="number" name="component_qty" min="0.01" step="0.01" value="1" required></div>
          <div><button class="btn btn-primary" type="submit">Add component</button></div>
        </div>
      </form>
      <div class="cr-list">
        <?php if(empty($bundle_components)): ?><div class="cr-empty">No bundle components configured.</div><?php endif; ?>
        <?php foreach($bundle_components as $component): ?>
          <div class="cr-row"><span><strong><?= $esc($component->bundle_name); ?></strong> &middot; <?= $esc($component->component_name); ?> <small>&times; <?= $esc($component->qty); ?></small></span>
            <form method="post" action="<?= base_url('online_store/delete_commerce_rule/component/'.(int)$component->id); ?>" onsubmit="return confirm('Remove this bundle component?')"><input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><button class="cr-remove" type="submit" aria-label="Remove component"><i class="fa fa-trash"></i></button></form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="box box-primary cr-section">
    <div class="box-header with-border"><h3>Product and Service Add-ons</h3></div>
    <div class="box-body">
      <form method="post" action="<?= base_url('online_store/save_commerce_rule'); ?>">
        <input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><input type="hidden" name="rule_type" value="addon">
        <div class="cr-form-grid">
          <div><label for="cr-parent-type">Attach to</label><select class="form-control" id="cr-parent-type" name="parent_type"><option value="product">Product</option><option value="service">Service</option></select></div>
          <div class="cr-product-parent"><label for="cr-product-id">Product</label><select class="form-control" id="cr-product-id" name="product_id"><option value="">Select product</option><?php foreach($products as $item): ?><option value="<?= (int)$item->id; ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div class="cr-service-parent" style="display:none"><label for="cr-service-id">Service</label><select class="form-control" id="cr-service-id" name="service_id" disabled><option value="">Select service</option><?php foreach($services as $service): ?><option value="<?= (int)$service->id; ?>"><?= $esc($service->service_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-addon-name">Name</label><input class="form-control" id="cr-addon-name" name="addon_name" maxlength="150" required></div>
          <div><label for="cr-addon-price">Price</label><input class="form-control" id="cr-addon-price" type="number" name="addon_price" min="0" step="0.01" value="0" required></div>
          <div><label for="cr-addon-max">Max qty</label><input class="form-control" id="cr-addon-max" type="number" name="addon_max_qty" min="1" step="1" value="1" required></div>
          <div><label for="cr-linked-item">Linked stock item</label><select class="form-control" id="cr-linked-item" name="linked_item_id"><option value="0">Not stock-tracked</option><?php foreach($products as $item): if(!empty($item->is_bundle)) continue; ?><option value="<?= (int)$item->id; ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label><input type="checkbox" name="addon_active" value="1" checked> Active</label><button class="btn btn-primary" type="submit">Save add-on</button></div>
        </div>
      </form>
      <div class="cr-list">
        <?php if(empty($addons)): ?><div class="cr-empty">No add-ons configured.</div><?php endif; ?>
        <?php foreach($addons as $addon): $parentName = $addon->product_name ?: ($addon->service_name ?? ''); ?>
          <div class="cr-row"><span><strong><?= $esc($addon->name); ?></strong> <small><?= $esc($parentName); ?> &middot; <?= $esc($addon->price); ?> &middot; max <?= (int)$addon->max_qty; ?><?= !empty($addon->linked_item_id) ? ' &middot; stock linked' : ''; ?></small></span>
            <form method="post" action="<?= base_url('online_store/delete_commerce_rule/addon/'.(int)$addon->id); ?>" onsubmit="return confirm('Remove this add-on?')"><input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><button class="cr-remove" type="submit" aria-label="Remove add-on"><i class="fa fa-trash"></i></button></form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="box box-primary cr-section">
    <div class="box-header with-border"><h3>Checkout Upsells</h3></div>
    <div class="box-body">
      <form method="post" action="<?= base_url('online_store/save_commerce_rule'); ?>">
        <input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><input type="hidden" name="rule_type" value="upsell">
        <div class="cr-form-grid">
          <div><label for="cr-trigger">Shown for</label><select class="form-control" id="cr-trigger" name="trigger_item_id"><option value="0">Any cart</option><?php foreach($products as $item): ?><option value="<?= (int)$item->id; ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-upsell">Suggest</label><select class="form-control" id="cr-upsell" name="upsell_item_id" required><option value="">Select product</option><?php foreach($products as $item): ?><option value="<?= (int)$item->id; ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-sort">Position</label><input class="form-control" id="cr-sort" type="number" name="sort_order" min="0" step="1" value="0"></div>
          <div><label><input type="checkbox" name="upsell_active" value="1" checked> Active</label><button class="btn btn-primary" type="submit">Save upsell</button></div>
        </div>
      </form>
      <div class="cr-list">
        <?php if(empty($upsells)): ?><div class="cr-empty">No upsells configured.</div><?php endif; ?>
        <?php foreach($upsells as $upsell): ?>
          <div class="cr-row"><span><strong><?= $esc($upsell->upsell_name); ?></strong> <small><?= $upsell->trigger_item_id ? 'When cart includes '.$esc($upsell->trigger_name) : 'Any cart'; ?></small></span>
            <form method="post" action="<?= base_url('online_store/delete_commerce_rule/upsell/'.(int)$upsell->id); ?>" onsubmit="return confirm('Remove this upsell?')"><input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><button class="cr-remove" type="submit" aria-label="Remove upsell"><i class="fa fa-trash"></i></button></form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="box box-primary cr-section">
    <div class="box-header with-border"><h3>Quantity Rules</h3></div>
    <div class="box-body">
      <form method="post" action="<?= base_url('online_store/save_commerce_rule'); ?>">
        <input type="hidden" name="<?= $esc($csrfName); ?>" value="<?= $esc($csrfHash); ?>"><input type="hidden" name="rule_type" value="quantity">
        <div class="cr-form-grid">
          <div><label for="cr-qty-item">Product</label><select class="form-control" id="cr-qty-item" name="item_id" required><option value="">Select product</option><?php foreach($products as $item): ?><option value="<?= (int)$item->id; ?>" data-min="<?= $esc($item->min_order_qty ?? ''); ?>" data-max="<?= $esc($item->max_order_qty ?? ''); ?>" data-step="<?= $esc($item->qty_step ?? ''); ?>"><?= $esc($item->item_name); ?></option><?php endforeach; ?></select></div>
          <div><label for="cr-min">Minimum</label><input class="form-control" id="cr-min" type="number" name="min_order_qty" min="0" step="0.01"></div>
          <div><label for="cr-max">Maximum</label><input class="form-control" id="cr-max" type="number" name="max_order_qty" min="0" step="0.01"></div>
          <div><label for="cr-step">Quantity step</label><input class="form-control" id="cr-step" type="number" name="qty_step" min="0" step="0.01"></div>
          <div><button class="btn btn-primary" type="submit">Save quantity rules</button></div>
        </div>
      </form>
    </div>
  </section>
</div>
<script>
(function(){
  var type=document.getElementById('cr-parent-type');if(type){type.addEventListener('change',function(){var service=type.value==='service';document.querySelector('.cr-product-parent').style.display=service?'none':'';document.querySelector('.cr-service-parent').style.display=service?'':'none';document.getElementById('cr-product-id').disabled=service;document.getElementById('cr-service-id').disabled=!service;});}
  var item=document.getElementById('cr-qty-item');if(item){item.addEventListener('change',function(){var opt=item.options[item.selectedIndex];document.getElementById('cr-min').value=opt.dataset.min||'';document.getElementById('cr-max').value=opt.dataset.max||'';document.getElementById('cr-step').value=opt.dataset.step||'';});}
})();
</script>
