<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-hb-grid{display:grid!important;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)!important;gap:20px!important;align-items:start!important}
@media(max-width:1024px){.os-hb-grid{grid-template-columns:1fr!important}}
.os-section-row{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;padding:14px 18px!important;margin-bottom:10px!important;display:flex!important;align-items:center!important;gap:12px!important;transition:box-shadow .2s, transform .1s, border-color .15s ease!important}
.os-section-row:hover{border-color:var(--mp-primary)!important}
.os-section-row.sortable-ghost{opacity:.4!important;background:rgba(0,87,255,.06)!important;border-color:var(--mp-primary)!important}
.os-section-row.sortable-drag{box-shadow:0 8px 24px rgba(0,0,0,.15)!important;transform:scale(1.02)!important;z-index:1000!important}
.os-section-row.disabled{opacity:.55!important;background:var(--mp-bg)!important}
.os-section-handle{cursor:grab!important;color:var(--mp-muted)!important;font-size:18px!important;padding:4px!important}
.os-section-handle:active{cursor:grabbing!important}
.os-section-label{font-weight:600!important;flex:1!important;font-size:14px!important;color:var(--mp-ink)!important}
.os-section-actions{display:flex!important;align-items:center!important;gap:10px!important}
.os-section-duplicate{font-size:12px!important;color:var(--mp-primary)!important;cursor:pointer!important;background:none!important;border:1px solid transparent!important;padding:6px 10px!important;border-radius:8px!important;transition:all .15s ease!important;font-weight:600!important}
.os-section-duplicate:hover{background:rgba(0,87,255,.08)!important;border-color:rgba(0,87,255,.15)!important}
.os-section-duplicate:disabled{opacity:.4!important;cursor:not-allowed!important}
.os-section-badge{font-size:11px!important;padding:3px 10px!important;border-radius:20px!important;background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important;font-weight:700!important}
.os-section-badge.duplicable{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.os-toggle{width:42px!important;height:24px!important;border-radius:12px!important;background:var(--mp-border)!important;position:relative!important;cursor:pointer!important;transition:background .15s ease!important;border:none!important;flex-shrink:0!important}
.os-toggle.on{background:var(--mp-success)!important}
.os-toggle::after{content:''!important;position:absolute!important;top:2px!important;left:2px!important;width:20px!important;height:20px!important;border-radius:50%!important;background:#fff!important;transition:transform .15s ease!important;box-shadow:0 1px 3px rgba(0,0,0,.2)!important}
.os-toggle.on::after{transform:translateX(18px)!important}
.os-hb-guide{font-size:13px!important;color:var(--mp-ink)!important}
.os-hb-guide-item{padding:12px 0!important;border-bottom:1px solid var(--mp-border)!important}
.os-hb-guide-item:last-child{border-bottom:none!important}
.os-hb-guide-item strong{font-size:13px!important;color:var(--mp-primary)!important;display:flex!important;align-items:center!important;gap:6px!important;margin-bottom:4px!important}
.os-hb-guide-item p{font-size:12px!important;color:var(--mp-muted)!important;margin:0!important;line-height:1.5!important}
.os-hb-guide-item a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Drag, reorder and toggle the sections that appear on your storefront homepage</div>
  </div>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="mp-qa-btn blue"><i class="fa fa-external-link"></i> Preview Store</a>
</div>

<div class="os-hb-grid">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Homepage Sections</h3></div>
    <div class="mp-card-body" id="sections-container">
      <?php
      $duplicable = ['hero_banner','promo_banner','featured_products','featured_services','featured_categories','testimonials','brands','instagram_gallery'];
      foreach($homepage_sections as $s):
        $isDup = in_array($s->section_key, $duplicable) || preg_match('/^('.implode('|',$duplicable).')_\d+$/', $s->section_key);
      ?>
      <div class="section-row os-section-row <?= $s->is_enabled ? '' : 'disabled'; ?>" data-key="<?= htmlspecialchars($s->section_key); ?>" data-order="<?= (int)$s->display_order; ?>">
        <span class="os-section-handle" title="Drag to reorder">&#9776;</span>
        <span class="os-section-label"><?= htmlspecialchars($s->section_label); ?></span>
        <?php if($isDup): ?><span class="os-section-badge duplicable">Duplicate</span><?php endif; ?>
        <div class="os-section-actions">
          <?php if($isDup): ?>
          <button type="button" class="os-section-duplicate" onclick="duplicateSection(this)" title="Duplicate this section"><i class="fa fa-clone"></i> Copy</button>
          <?php endif; ?>
          <button type="button" class="os-toggle section-toggle <?= $s->is_enabled ? 'on' : ''; ?>" onclick="this.classList.toggle('on')" title="Toggle visibility"></button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($homepage_sections)): ?>
      <div class="mp-empty-state">No homepage sections configured.</div>
      <?php endif; ?>
    </div>
    <div class="mp-card-foot">
      <button type="button" class="mp-btn-primary" onclick="saveSections()"><i class="fa fa-save"></i> Save Layout</button>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>How to Create Content</h3></div>
    <div class="mp-card-body os-hb-guide">
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Home Banner</strong><p>Create banners in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. The first active banner appears here. If you have multiple banners, it becomes an auto-sliding carousel.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Promotional Banner</strong><p>Shows all banners after the first one as a distinct promotional card grid. Create in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. Stands separate from the main Home Banner.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Featured Products</strong><p>Products marked as "Online" with stock appear automatically. Manage in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a>.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Featured Services</strong><p>Services marked as "Online" appear here. Manage in <a href="<?= base_url('online_store/services'); ?>">Services</a>.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> Featured Categories</strong><p>Auto-populated from your product categories. No setup needed.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> Best Sellers</strong><p>Calculated automatically from online orders. No setup needed.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> New Arrivals</strong><p>Shows latest published products automatically. No setup needed.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Store Information</strong><p>Edit your store description in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Description.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Contact / WhatsApp CTA</strong><p>Edit phone, email, address and WhatsApp number in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Store Hours</strong><p>Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Hours (one per line).</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Headline & Sub-headline</strong><p>Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Store Branding.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Trust Badges</strong><p>Edit the 4 badge titles and descriptions in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Trust Badges.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Newsletter CTA</strong><p>Edit the headline and sub-headline in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Newsletter CTA.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Brands</strong><p>Add brand logos in <a href="<?= base_url('online_store/brands'); ?>">Brands</a>. Shows as a logo grid on the storefront.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Testimonials</strong><p>Add customer reviews in <a href="<?= base_url('online_store/testimonials'); ?>">Testimonials</a>. Shows with star ratings.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Instagram Gallery</strong><p>Upload images in <a href="<?= base_url('online_store/instagram'); ?>">Instagram</a>. Shows as a photo grid with optional captions.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> FAQs</strong><p>Add questions and answers in <a href="<?= base_url('online_store/faqs'); ?>">FAQs</a>. Shows as an accordion on the storefront.</p></div>
      <div class="os-hb-guide-item"><p style="font-size:12px;color:var(--mp-muted);"><i class="fa fa-info-circle"></i> Toggle sections above to show or hide them. Changes apply immediately after saving.</p></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };

new Sortable(document.getElementById('sections-container'), {
  handle: '.os-section-handle',
  animation: 150,
  ghostClass: 'sortable-ghost',
  dragClass: 'sortable-drag'
});

function saveSections(){
  const rows = document.querySelectorAll('#sections-container .section-row');
  const sections = {};
  rows.forEach((row, idx) => {
    sections[row.dataset.key] = {
      enabled: row.querySelector('.section-toggle').classList.contains('on') ? 1 : 0,
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
    if(res.status==='success'){ toastr.success(res.message); location.reload(); }
    else { toastr.error(res.message || 'Failed to duplicate'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-clone"></i> Copy'; }
  }).catch(()=>{ toastr.error('Error duplicating section'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-clone"></i> Copy'; });
}
</script>
<script>$(".online_store-homepage_builder-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
