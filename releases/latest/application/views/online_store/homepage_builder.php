<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$hbTheme = !empty($settings->theme_id) ? $CI->storefront_model->getTheme($settings->theme_id) : null;
$hbThemeKey = $hbTheme->theme_key ?? '';
$hbLumiere = $hbThemeKey === 'lumiere';
$hbSkinora = $hbThemeKey === 'skinora';
$hbSkincare = $hbLumiere || $hbSkinora;
$hbBotanical = $hbThemeKey === 'botanical_elegance';
$hbBuilding = in_array($hbThemeKey, ['framework_atelier','vector_systems'], true);
$hbEditableImages = $hbSkincare || $hbBotanical || $hbBuilding;
$hbBuildingContent = [
  'hero_banner'=>['Hero & door visual','theme_content','Edit hero & side image','Theme Content supplies the hero text and door-side image. Fields here override the heading, description and background image only.'],
  'trust_badges'=>['Trust & commitments','settings','Edit commitments','Only your saved trust badges appear. No sample claims are published. Edit them under Trust Badges in Settings.'],
  'promo_banner'=>['Editorial banners','banners','Edit banners','Uses active Promotional banners. You can also create a single editorial panel using a heading or image here.'],
  'featured_categories'=>['System categories','products_online','Manage published systems','Displays categories containing published systems. Links open the filtered system catalogue, not checkout.'],
  'featured_products'=>['Featured systems','products_online','Choose featured systems','Uses Featured, Online systems; falls back to published systems. Cards lead to specifications and enquiries.'],
  'featured_services'=>['Featured services','services','Edit services','Publish Active, Available Online services and enable services in Appearance and Settings. No booking checkout is added.'],
  'best_sellers'=>['Proven systems','products_online','Manage systems','Uses the existing best-seller collection. No cart, prices or ordering controls are introduced.'],
  'new_arrivals'=>['New systems','products_online','Manage new systems','Uses the existing New Arrival flags, with the latest published systems as fallback.'],
  'brands'=>['Partner brands','brands','Edit partner logos','Only active partner brands appear.'],
  'testimonials'=>['Customer testimonials','testimonials','Edit testimonials','Only approved/enabled customer testimonials appear.'],
  'instagram_gallery'=>['Work gallery','instagram','Edit gallery','Uses active uploaded gallery photos and their captions. Links are optional.'],
  'store_info'=>['Company, delivery process & applications','theme_content','Edit company story & image','One editorial group: company introduction, delivery-process image and steps, then published applications. Edit Applications in Website Content. Heading fields here override the introduction.'],
  'faqs'=>['Frequently asked questions','faqs','Edit questions & answers','Add an Active question with a non-empty answer. Incomplete or inactive questions stay hidden.'],
  'contact_section'=>['Project enquiry invitation','theme_content','Edit enquiry invitation','Compact light-grey enquiry panel. Links to the connected enquiry form; hidden when the Contact page is disabled.'],
  'whatsapp_cta'=>['WhatsApp conversation','settings','Edit WhatsApp number','Requires a WhatsApp number and Show WhatsApp CTA in Settings.'],
  'newsletter'=>['Newsletter signup','subscribers','View subscribers','Edit the heading and description here. Signups enter your existing subscriber list—not CRM leads or service jobs.'],
  'store_hours'=>['Business hours','appearance','Edit business hours','Uses the business hours saved in Appearance. Empty hours stay hidden.']
];
if($hbBuilding){
  $homepage_sections = array_values(array_filter($homepage_sections, function($row){
    return !in_array(preg_replace('/_\d+$/','',$row->section_key), ['hero_banner','featured_products','store_info','contact_section'], true);
  }));
  foreach($homepage_sections as $row){
    $cfg = json_decode($row->config_json ?? '', true);
    $own = is_array($cfg) ? ($cfg['industry_content']['building_materials'] ?? []) : [];
    $row->is_enabled = !empty($own['is_enabled']) ? 1 : 0;
    $row->display_order = (int)($own['display_order'] ?? $row->display_order);
  }
  usort($homepage_sections, function($a,$b){return $a->display_order <=> $b->display_order;});
}
?>
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
.os-section-edit-btn{font-size:12px!important;color:var(--mp-muted)!important;cursor:pointer!important;background:none!important;border:1px solid var(--mp-border)!important;padding:6px 10px!important;border-radius:8px!important;transition:all .15s ease!important}
.os-section-edit-btn:hover{color:var(--mp-primary)!important;border-color:var(--mp-primary)!important}
.os-sec-edit{padding:4px 18px 16px 44px!important;background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-top:none!important;border-radius:0 0 12px 12px!important;margin:-10px 0 10px!important}
.os-sec-edit .mp-form-group{margin-bottom:10px!important}
.os-hb-guide{font-size:13px!important;color:var(--mp-ink)!important}
.os-hb-guide-item{padding:12px 0!important;border-bottom:1px solid var(--mp-border)!important}
.os-hb-guide-item:last-child{border-bottom:none!important}
.os-hb-guide-item strong{font-size:13px!important;color:var(--mp-primary)!important;display:flex!important;align-items:center!important;gap:6px!important;margin-bottom:4px!important}
.os-hb-guide-item p{font-size:12px!important;color:var(--mp-muted)!important;margin:0!important;line-height:1.5!important}
.os-hb-guide-item a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}
.os-hb-content-link{display:block;font-size:12px;font-weight:500;margin-top:7px}
.os-hb-content-state{display:block;font-size:11px;font-weight:400;color:var(--mp-muted);line-height:1.6;margin-top:5px}
.os-hb-notice{padding:16px 20px;margin-bottom:18px;border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface);font-size:13px;line-height:1.7;color:var(--mp-ink)}
.os-hb-building .os-section-row{flex-wrap:wrap!important}
.os-hb-building .os-section-label{min-width:160px}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $hbBuilding ? 'Optional additions to your existing Building Materials website' : 'Drag, reorder and toggle the sections that appear on your storefront homepage'; ?></div>
  </div>
  <a href="<?= $hbBuilding ? base_url('online_store/view_website') : base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" rel="noopener" class="mp-qa-btn blue"><i class="fa fa-external-link"></i> Preview Website</a>
</div>

<?php if($hbBuilding): ?><div class="os-hb-notice"><strong>Your current website design stays unchanged.</strong> Continue editing the hero, side image, company story, systems and enquiry panel in <a href="<?= base_url('online_store/theme_content'); ?>">Theme Content</a>. This builder only adds optional components before the closing enquiry panel. Enable the ones you want, arrange their order and <strong>Save Layout</strong>. Their text uses the pencil and <strong>Apply</strong>. Building Materials has its own opt-ins and overrides: other business presets cannot activate or rename these sections. Empty sections stay hidden until their content is complete.</div><?php endif; ?>
<div class="os-hb-grid<?= $hbBuilding ? ' os-hb-building' : ''; ?>">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><?= $hbBuilding ? 'Optional Homepage Components' : 'Homepage Sections'; ?></h3></div>
    <div class="mp-card-body" id="sections-container">
      <?php
      $duplicable = ['hero_banner','promo_banner','featured_products','featured_services','featured_categories','testimonials','brands','instagram_gallery'];
      $hbSkinoraRightCard = false;
      foreach($homepage_sections as $hbSection){
        if($hbSection->section_key === 'promo_banner_2') $hbSkinoraRightCard = true;
      }
      foreach($homepage_sections as $s):
        $isDup = in_array($s->section_key, $duplicable) || preg_match('/^('.implode('|',$duplicable).')_\d+$/', $s->section_key);
        if($hbSkinora) $isDup = $s->section_key === 'promo_banner' && !$hbSkinoraRightCard;
        $isCopy = (bool)preg_match('/_\d+$/', $s->section_key);
        $sCfg = !empty($s->config_json) ? json_decode($s->config_json, true) : [];
        if($hbBuilding && is_array($sCfg)) $sCfg = $sCfg['industry_content']['building_materials'] ?? [];
        if(!is_array($sCfg)) $sCfg = [];
        $sTitle = $sCfg['title'] ?? (($hbSkincare || $hbBuilding) ? '' : $s->section_label);
        $sSub   = $sCfg['subtitle'] ?? '';
        $sBaseKey = preg_replace('/_\d+$/', '', $s->section_key);
        $sDisplayLabel = ($hbLumiere && $sBaseKey === 'featured_products') ? 'Featured Products' : $s->section_label;
        if($hbSkinora && $s->section_key === 'promo_banner') $sDisplayLabel = 'Editorial Card — Left';
        if($hbSkinora && $s->section_key === 'promo_banner_2') $sDisplayLabel = 'Editorial Card — Right';
        $sBuildingInfo = $hbBuildingContent[$sBaseKey] ?? null;
        if($hbBuilding && $sBuildingInfo) $sDisplayLabel = $sBuildingInfo[0] . ($isCopy ? ' — Copy' : '');
      ?>
      <div class="section-wrap">
        <div class="section-row os-section-row <?= $s->is_enabled ? '' : 'disabled'; ?>" data-key="<?= htmlspecialchars($s->section_key); ?>" data-order="<?= (int)$s->display_order; ?>">
          <span class="os-section-handle" title="Drag to reorder">&#9776;</span>
          <span class="os-section-label"><?= htmlspecialchars($sDisplayLabel); ?>
            <?php if($hbBuilding && $sBuildingInfo): ?>
            <a class="os-hb-content-link" href="<?= base_url('online_store/'.$sBuildingInfo[1]); ?>"><?= htmlspecialchars($sBuildingInfo[2]); ?> ↗</a>
            <?php if(isset($homepage_content_counts[$sBaseKey])): ?><span class="os-hb-content-state"><?= (int)$homepage_content_counts[$sBaseKey] > 0 ? (int)$homepage_content_counts[$sBaseKey].' ready to display' : 'Needs content — hidden on the website until completed'; ?></span><?php endif; ?>
            <?php endif; ?>
          </span>
          <?php if($isCopy): ?><span class="os-section-badge duplicable">Copy</span><?php endif; ?>
          <div class="os-section-actions">
            <button type="button" class="os-section-edit-btn" onclick="toggleSectionEdit(this)" title="Edit heading"><i class="fa fa-pencil"></i></button>
            <?php if($isDup): ?>
            <button type="button" class="os-section-duplicate" onclick="duplicateSection(this)" title="Duplicate this section"><i class="fa fa-clone"></i> Copy</button>
            <?php endif; ?>
            <?php if($isCopy): ?>
            <button type="button" class="os-section-duplicate" onclick="deleteSection(this)" title="Remove this copy" style="color:#DC2626;"><i class="fa fa-trash-o"></i></button>
            <?php endif; ?>
            <button type="button" class="os-toggle section-toggle <?= $s->is_enabled ? 'on' : ''; ?>" aria-label="Show <?= htmlspecialchars($sDisplayLabel, ENT_QUOTES); ?>" aria-pressed="<?= $s->is_enabled ? 'true' : 'false'; ?>" onclick="this.classList.toggle('on');this.setAttribute('aria-pressed',this.classList.contains('on'));this.closest('.section-row').classList.toggle('disabled',!this.classList.contains('on'))" title="Toggle visibility"></button>
          </div>
        </div>
        <div class="os-sec-edit" style="display:none;">
          <?php if($hbBuilding && $sBuildingInfo): ?><p class="mp-form-hint"><?= htmlspecialchars($sBuildingInfo[3]); ?> <a href="<?= base_url('online_store/'.$sBuildingInfo[1]); ?>"><?= htmlspecialchars($sBuildingInfo[2]); ?></a>. Leave overrides blank to keep Theme Content or the section’s default wording.</p><?php endif; ?>
          <?php if(!$hbBuilding && $hbEditableImages && $sBaseKey === 'trust_badges'): ?>
            <p class="mp-form-hint">This row controls visibility and placement. <?php if($hbBotanical): ?>Edit the three badge titles and descriptions in <a href="<?= base_url('online_store/theme_content'); ?>">Storefront Content → Homepage accents &amp; buttons</a>.<?php else: ?>Edit the four badge titles and descriptions in <a href="<?= base_url('online_store/settings'); ?>">Store Settings → Trust Badges</a>.<?php endif; ?></p>
          <?php else: ?>
            <?php if($hbSkincare && $sBaseKey === 'hero_banner'): ?><p class="mp-form-hint">The first active <a href="<?= base_url('online_store/banners'); ?>">Hero banner</a> controls the desktop and mobile images. Its title and subtitle override these fallback fields when filled.</p><?php endif; ?>
            <?php if($hbSkinora && $sBaseKey === 'promo_banner'): ?><p class="mp-form-hint">Skinora uses two editorial cards. The first active <a href="<?= base_url('online_store/banners'); ?>">Promotional banner</a> controls the left card; the second controls the right card. Banner images and text take priority over these fallback fields. Edit each card’s small label here.</p><?php endif; ?>
            <?php if($hbLumiere && $sBaseKey === 'promo_banner'): ?><p class="mp-form-hint">An active <a href="<?= base_url('online_store/banners'); ?>">Promotional banner</a> supplies this section’s image, text and button. These fields can provide fallback content.</p><?php endif; ?>
            <?php if($hbEditableImages && in_array($sBaseKey, ['hero_banner','promo_banner','store_info','contact_section'], true)): ?><div class="mp-form-group"><label>Text above headline <span style="font-weight:400;color:var(--mp-muted);">(optional)</span></label><input type="text" class="mp-form-control os-sec-eyebrow" value="<?= htmlspecialchars($sCfg['eyebrow'] ?? '', ENT_QUOTES); ?>" placeholder="<?= $sBaseKey === 'store_info' ? 'A MOMENT TO FEEL GOOD' : ($hbBotanical ? 'CURATED FOR YOUR SPACE' : ($sBaseKey === 'hero_banner' ? 'THE ART OF LOVING YOUR SKIN' : ($hbSkinora ? 'THE DAILY ESSENTIALS' : 'THE LUMIÈRE EDIT'))); ?>"></div><?php endif; ?>
            <div class="mp-form-group"><label>Section Heading</label><input type="text" class="mp-form-control os-sec-title" value="<?= htmlspecialchars($sTitle); ?>" placeholder="<?= htmlspecialchars($sDisplayLabel); ?>"></div>
            <div class="mp-form-group"><label>Description <span style="font-weight:400;color:var(--mp-muted);">(optional)</span></label><textarea class="mp-form-control os-sec-sub" rows="4" maxlength="1200" placeholder="Description shown under the heading"><?= htmlspecialchars($sSub); ?></textarea><p class="mp-form-hint">This replaces the section’s default description. Leave blank to use the theme default.</p></div>
            <?php if($hbEditableImages && in_array($sBaseKey, $hbBuilding ? ['hero_banner','promo_banner','store_info'] : ['hero_banner','promo_banner','store_info','contact_section'], true)): ?><div class="mp-form-group"><label><?= ($hbBotanical || $hbBuilding || $sBaseKey === 'store_info') ? 'Section image URL' : 'Fallback image URL'; ?> <span style="font-weight:400;color:var(--mp-muted);">(optional)</span></label><input type="url" class="mp-form-control os-sec-image" value="<?= htmlspecialchars($sCfg['image_url'] ?? '', ENT_QUOTES); ?>" placeholder="https://example.com/your-store-photo.jpg"></div><?php endif; ?>
            <?php if($hbEditableImages && in_array($sBaseKey,['hero_banner','promo_banner','store_info','contact_section'],true)): ?><div class="mp-form-group"><label>Upload section image <span style="font-weight:400;color:var(--mp-muted);">(optional)</span></label><input type="file" class="mp-form-control os-sec-image-file" accept="image/jpeg,image/png,image/webp,image/avif,.avif"><p class="mp-form-hint">JPG, PNG, WebP or AVIF, up to 3 MB. An upload replaces the image URL.<?php if($hbBuilding): ?> For the hero’s side image, use Theme Content → Hero &amp; navigation instead.<?php else: ?> Uploading is preferred; the image URL remains available for existing content.<?php endif; ?></p></div><?php endif; ?>
            <button type="button" class="mp-btn-primary" style="padding:8px 18px;font-size:13px;" onclick="saveSectionMeta(this)"><i class="fa fa-check"></i> Apply</button>
          <?php endif; ?>
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
    <?php if($hbBuilding): ?>
    <div class="mp-card-body os-hb-guide">
      <div class="os-hb-guide-item"><strong>Your editor remains in charge</strong><p><a href="<?= base_url('online_store/theme_content'); ?>">Theme Content</a> continues to control the existing homepage. These optional components are added after the main story and applications, before the enquiry invitation. Disabling them restores the same homepage—not a retail layout.</p></div>
      <div class="os-hb-guide-item"><strong>Add a component when you need it</strong><p>1. Use its content-editor link. 2. Complete and activate the content. 3. Enable it here and Save Layout. The saved optional order and copies work in Atelier and Vector. Another preset’s enabled switches and text are kept separate.</p></div>
      <div class="os-hb-guide-item"><strong>FAQs</strong><p>Add questions and answers in <a href="<?= base_url('online_store/faqs'); ?>">FAQs</a>. Only complete, active answers are published. The readiness count beside the row explains why an enabled section may be hidden.</p></div>
      <div class="os-hb-guide-item"><strong>Newsletter</strong><p>Use the Newsletter pencil for its heading and description. Subscribers are saved in <a href="<?= base_url('online_store/subscribers'); ?>">Subscribers</a> where you can export them. Signup includes consent and a privacy-policy link. This does not send campaigns automatically.</p></div>
      <div class="os-hb-guide-item"><strong>Enquiries stay connected</strong><p>Enquiry invitations lead to your existing website form and CRM intake. Newsletter subscriptions remain a separate opt-in audience. No cart or payment checkout is added by these components.</p></div>
      <div class="os-hb-guide-item"><strong>Images, applications &amp; wording</strong><p><a href="<?= base_url('online_store/theme_content'); ?>">Theme Content</a> manages your hero side image and company/process story. <a href="<?= base_url('online_store/journal'); ?>">Website Content</a> manages published applications. Use a row’s pencil for optional heading and description overrides.</p></div>
    </div>
    <?php else: ?>
    <?php if($hbBotanical): ?><div class="mp-card-body"><p class="mp-form-hint">Use each section’s pencil button for its heading and description. Hero, story and project panels also support image URLs or uploads. These image overrides take priority over Banners. Edit menu names, buttons, trust messages and inner-page wording in <a href="<?= base_url('online_store/theme_content'); ?>">Storefront Content</a>.</p></div><?php endif; ?>
    <div class="mp-card-body os-hb-guide">
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Home Banner</strong><p><?php if($hbEditableImages): ?>Section overrides above take priority. Otherwise, the first active Hero banner supplies the desktop and mobile photos, heading, description and button. Edit it in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>; blank text uses the Hero Banner fallback fields here.<?php else: ?>Create banners in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. The first active banner appears here. If you have multiple banners, it becomes an auto-sliding carousel.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Promotional Banner</strong><p><?php if($hbSkinora): ?>Skinora’s left and right editorial cards use the first and second active Promotional banners in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. Set each banner’s desktop/mobile image, title, subtitle, button and link there. Use the two Editorial Card pencil buttons here for small labels or fallback copy.<?php elseif($hbLumiere): ?>Add an active Promotional banner in <a href="<?= base_url('online_store/banners'); ?>">Banners</a> to display its photo, copy and button. Image-capable sections also accept direct uploads in this builder.<?php else: ?>Shows all banners after the first one as a distinct promotional card grid. Create in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>. Stands separate from the main Home Banner.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Featured Products</strong><p>Enable this section above, then switch on both Online and Featured for the products you want in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a>. A marked variant option appears through its parent product card.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Featured Services</strong><p>Turn this section on and save the layout. Services must be Active and Available Online in <a href="<?= base_url('online_store/services'); ?>">Services</a>; service orders must be enabled in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>, and Appearance must include services in What You Sell.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> Featured Categories</strong><p>Auto-populated from your product categories. No setup needed.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> Best Sellers</strong><p>Calculated automatically from online orders. No setup needed.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-magic"></i> New Arrivals</strong><p><?php if($hbLumiere): ?>Enable this section above, then mark published products as New Arrival in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a>. Marked variant options appear through their parent card.<?php else: ?>Shows marked New Arrival products, or the latest published products when none are marked. Manage flags in <a href="<?= base_url('online_store/products_online'); ?>">Online Products</a>.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Store Information</strong><p><?php if($hbEditableImages): ?>Use this section’s pencil button to edit the editorial heading, description and image URL. Its order and visibility are controlled above.<?php else: ?>Edit your store description in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Description.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Contact / WhatsApp CTA</strong><p>Edit phone, email, address and WhatsApp number in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Store Hours</strong><p>Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Hours (one per line).</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Headline & Sub-headline</strong><p><?php if($hbEditableImages): ?>Edit the hero title and subtitle in <a href="<?= base_url('online_store/banners'); ?>">Banners</a>, or use the Hero Banner pencil button here for fallback text. Other section headings and sub-headings use their own pencil buttons.<?php else: ?>Edit in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a> under Store Branding.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Trust Badges</strong><p>Use this page to show, hide or move the badges. <?php if($hbBotanical): ?>Edit the three titles and descriptions in <a href="<?= base_url('online_store/theme_content'); ?>">Storefront Content</a>.<?php else: ?>Edit the 4 titles and descriptions in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Trust Badges.<?php endif; ?></p></div>
      <?php if($hbEditableImages): ?><div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Announcements and SEO</strong><p>Edit the scrolling marquee, announcement colour, meta title, description and indexing in <a href="<?= base_url('online_store/appearance'); ?>">Appearance</a>.</p></div><?php endif; ?>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Newsletter CTA</strong><p><?php if($hbBotanical): ?>Use this section’s pencil button for its heading and description; edit the small label and button in <a href="<?= base_url('online_store/theme_content'); ?>">Storefront Content</a>.<?php else: ?>Edit the headline and sub-headline in <a href="<?= base_url('online_store/settings'); ?>">Settings</a> under Newsletter CTA.<?php endif; ?></p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Brands</strong><p>Add brand logos in <a href="<?= base_url('online_store/brands'); ?>">Brands</a>. Shows as a logo grid on the storefront.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Testimonials</strong><p>Add customer reviews in <a href="<?= base_url('online_store/testimonials'); ?>">Testimonials</a>. Shows with star ratings.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> Instagram Gallery</strong><p>Upload images in <a href="<?= base_url('online_store/instagram'); ?>">Instagram</a>. Shows as a photo grid with optional captions.</p></div>
      <div class="os-hb-guide-item"><strong><i class="fa fa-pencil-square-o"></i> FAQs</strong><p>Turn this section on, then add an Active question with a non-empty answer in <a href="<?= base_url('online_store/faqs'); ?>">FAQs</a>. Complete questions appear as an accordion on the storefront.</p></div>
      <div class="os-hb-guide-item"><p style="font-size:12px;color:var(--mp-muted);"><i class="fa fa-info-circle"></i> Toggle sections above to show or hide them. Changes apply immediately after saving.</p></div>
    </div>
    <?php endif; ?>
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

function toggleSectionEdit(btn){
  const panel = btn.closest('.section-wrap').querySelector('.os-sec-edit');
  panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function saveSectionMeta(btn){
  const wrap = btn.closest('.section-wrap');
  const key = wrap.querySelector('.section-row').dataset.key;
  const title = wrap.querySelector('.os-sec-title').value.trim();
  const subtitle = wrap.querySelector('.os-sec-sub').value.trim();
  btn.disabled = true;
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('section_key', key);
  fd.append('title', title);
  fd.append('subtitle', subtitle);
  const eyebrowInput = wrap.querySelector('.os-sec-eyebrow');
  if(eyebrowInput) fd.append('eyebrow', eyebrowInput.value.trim());
  const imageInput = wrap.querySelector('.os-sec-image');
  if(imageInput) fd.append('image_url', imageInput.value.trim());
  const imageFile = wrap.querySelector('.os-sec-image-file');
  if(imageFile && imageFile.files[0]) fd.append('section_image', imageFile.files[0]);
  fetch('<?= base_url('online_store/save_homepage_section_meta'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    btn.disabled = false;
    if(res.status==='success'){
      toastr.success(res.message);
      if(title) wrap.querySelector('.os-section-label').textContent = title;
      if(imageInput && res.image_url !== undefined) imageInput.value = res.image_url;
      if(imageFile) imageFile.value = '';
      wrap.querySelector('.os-sec-edit').style.display = 'none';
    } else {
      toastr.error(res.message || 'Failed to save');
    }
  }).catch(()=>{ btn.disabled = false; toastr.error('Error saving section'); });
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

function deleteSection(btn){
  const row = btn.closest('.section-row');
  const key = row.dataset.key;
  if(!confirm('Remove this section copy? This cannot be undone.')) return;
  btn.disabled = true;
  const fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('section_key', key);
  fetch('<?= base_url('online_store/delete_homepage_section'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status==='success'){ toastr.success(res.message); row.closest('.section-wrap').remove(); }
    else { toastr.error(res.message || 'Failed to remove'); btn.disabled = false; }
  }).catch(()=>{ toastr.error('Error removing section'); btn.disabled = false; });
}
</script>
<script>$(".online_store-homepage_builder-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
