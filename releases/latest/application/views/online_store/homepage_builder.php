<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.section-row { background:#fff; border:1px solid #ddd; border-radius:6px; padding:12px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px; transition:box-shadow .2s, transform .1s; }
.section-row.sortable-ghost { opacity:.4; background:#e3f2fd; }
.section-row.sortable-drag { box-shadow:0 8px 24px rgba(0,0,0,.15); transform:scale(1.02); z-index:1000; }
.section-row.disabled { opacity:.6; background:#f5f5f5; }
.section-handle { cursor:grab; color:#888; font-size:18px; padding:4px; }
.section-handle:active { cursor:grabbing; }
.section-label { font-weight:600; flex:1; }
.section-actions { display:flex; align-items:center; gap:10px; }
.section-duplicate { font-size:12px; color:#3c8dbc; cursor:pointer; background:none; border:none; padding:4px 8px; border-radius:4px; transition:background .2s; }
.section-duplicate:hover { background:#e3f2fd; }
.section-duplicate:disabled { opacity:.4; cursor:not-allowed; }
.section-badge { font-size:11px; padding:2px 8px; border-radius:4px; background:#e8f5e9; color:#2e7d32; }
.section-badge.duplicable { background:#e3f2fd; color:#1565c0; }
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-8">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Homepage Sections</h3>
              <div class="box-tools">
                <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="btn btn-sm btn-default">Preview Store</a>
              </div>
            </div>
            <div class="box-body" id="sections-container">
              <?php 
              $duplicable = ['hero_banner','promo_banner','featured_products','featured_services','featured_categories','testimonials','brands','instagram_gallery'];
              foreach($homepage_sections as $s): 
                $isDup = in_array($s->section_key, $duplicable) || preg_match('/^('.implode('|',$duplicable).')_\d+$/', $s->section_key);
              ?>
              <div class="section-row <?= $s->is_enabled ? '' : 'disabled'; ?>" data-key="<?= $s->section_key; ?>" data-order="<?= $s->display_order; ?>">
                <span class="section-handle" title="Drag to reorder">&#9776;</span>
                <span class="section-label"><?= htmlspecialchars($s->section_label); ?></span>
                <?php if($isDup): ?><span class="section-badge duplicable">Duplicate</span><?php endif; ?>
                <div class="section-actions">
                  <?php if($isDup): ?>
                  <button type="button" class="section-duplicate" onclick="duplicateSection(this)" title="Duplicate this section"><i class="fa fa-clone"></i> Copy</button>
                  <?php endif; ?>
                  <label class="checkbox-inline" style="margin:0;">
                    <input type="checkbox" class="section-toggle" <?= $s->is_enabled ? 'checked' : ''; ?>> Show
                  </label>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="box-footer">
              <button type="button" class="btn btn-primary" onclick="saveSections()">Save Layout</button>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">How to Create Content</h3></div>
            <div class="box-body">
              <p style="font-size:13px; color:#555; margin-bottom:12px;">Each section pulls content automatically. Use the guides below to manage content:</p>

              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Home Banner</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Create banners in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. The first active banner appears here. If you have multiple banners, it becomes an auto-sliding carousel.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Promotional Banner</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Shows all banners after the first one as a distinct promotional card grid. Create in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. Stands separate from the main Home Banner.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Featured Products</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Products marked as "Online" with stock appear automatically. Manage in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a>.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Featured Services</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Services marked as "Online" appear here. Manage in <a href="<?= base_url('online_store/services'); ?>">Services</a>.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#f39c12;"><i class="fa fa-magic"></i> Featured Categories</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Auto-populated from your product categories. No setup needed.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#f39c12;"><i class="fa fa-magic"></i> Best Sellers</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Calculated automatically from online orders. No setup needed.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#f39c12;"><i class="fa fa-magic"></i> New Arrivals</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Shows latest published products automatically. No setup needed.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Store Information</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit your store description in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Description.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Contact Section / WhatsApp CTA</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit phone, email, address and WhatsApp number in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Store Hours</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Hours (one per line).</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Headline & Sub-headline</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Store Branding.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Trust Badges</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit the 4 badge titles and descriptions in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Trust Badges.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Newsletter CTA</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Edit the headline and sub-headline in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Newsletter CTA.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Brands</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Add brand logos in <a href="<?= base_url('online_store/brands'); ?>">Brands</a>. Shows as a logo grid on the storefront.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Testimonials</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Add customer reviews in <a href="<?= base_url('online_store/testimonials'); ?>">Testimonials</a>. Shows with star ratings.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> Instagram Gallery</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Upload images in <a href="<?= base_url('online_store/instagram'); ?>">Instagram</a>. Shows as a photo grid with optional captions.</p>
              </div>
              <div style="margin-bottom:10px;">
                <strong style="font-size:13px; color:#3c8dbc;"><i class="fa fa-pencil-square-o"></i> FAQs</strong>
                <p style="font-size:12px; color:#777; margin:2px 0 8px;">Add questions and answers in <a href="<?= base_url('online_store/faqs'); ?>">FAQs</a>. Shows as an accordion on the storefront.</p>
              </div>
              <hr style="margin:12px 0;">
              <p style="font-size:12px; color:#888;"><i class="fa fa-info-circle"></i> Check/uncheck sections above to show or hide them. Changes apply immediately after saving.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };

// Initialize drag-and-drop
new Sortable(document.getElementById('sections-container'), {
  handle: '.section-handle',
  animation: 150,
  ghostClass: 'sortable-ghost',
  dragClass: 'sortable-drag'
});

function saveSections(){
  const rows = document.querySelectorAll('#sections-container .section-row');
  const sections = {};
  rows.forEach((row, idx) => {
    sections[row.dataset.key] = {
      enabled: row.querySelector('.section-toggle').checked ? 1 : 0,
      order: idx + 1
    };
  });
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  for(const key in sections){
    fd.append('sections['+key+'][enabled]', sections[key].enabled);
    fd.append('sections['+key+'][order]', sections[key].order);
  }
  fetch('<?= base_url('online_store/save_homepage_sections'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success') toastr.success(res.message); else toastr.error(res.message || 'Failed to save'); });
}

function duplicateSection(btn){
  const row = btn.closest('.section-row');
  const key = row.dataset.key;
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('section_key', key);
  fetch('<?= base_url('online_store/duplicate_homepage_section'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status==='success'){
      toastr.success(res.message);
      location.reload();
    } else {
      toastr.error(res.message || 'Failed to duplicate');
      btn.disabled = false; btn.innerHTML = '<i class="fa fa-clone"></i> Copy';
    }
  }).catch(()=>{ toastr.error('Error duplicating section'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-clone"></i> Copy'; });
}
</script>
</body>
</html>
