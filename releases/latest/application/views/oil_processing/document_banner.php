<?php
$this->load->helper('plant_documents');
$banner_kind = isset($document_kind) ? $document_kind : 'quotation';
$banner_store_id = (int)get_current_store_id();
if(!empty($force_preview) || mp_plant_banner_enabled($banner_kind, $banner_store_id)):
  $banner_image = mp_plant_banner_image($banner_store_id);
  $banner_store = $this->db->select('store_name,address,city,mobile,email,store_website')->where('id',$banner_store_id)->get('db_store')->row();
  $banner_escape = function($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<div class="mp-plant-document-banner" style="width:100%;margin:0 0 18px;page-break-inside:avoid;">
  <?php if($banner_image): ?>
    <img src="<?= $banner_image; ?>" alt="<?= $banner_escape($banner_store->store_name ?? ''); ?> banner" style="display:block;width:100%;height:auto;max-height:190px;object-fit:contain;object-position:left center;">
  <?php else: ?>
    <div style="border-top:9px solid #83bd16;border-bottom:3px solid #176534;padding:19px 20px 15px;background:#f7fbf6;">
      <div style="font-size:25px;font-weight:800;letter-spacing:.03em;color:#14532d;line-height:1.2;"><?= $banner_escape($banner_store->store_name ?? 'Processing Plant'); ?></div>
      <div style="font-size:10px;letter-spacing:.17em;text-transform:uppercase;color:#52745c;margin:4px 0 10px;">Processing Plant · Palm / Vegetable Oil</div>
      <div style="font-size:10px;color:#355443;line-height:1.5;">
        <?= $banner_escape(trim(($banner_store->address ?? '').' '.($banner_store->city ?? ''))); ?>
        <?php if(!empty($banner_store->mobile)): ?> &nbsp; | &nbsp; <?= $banner_escape($banner_store->mobile); ?><?php endif; ?>
        <?php if(!empty($banner_store->email)): ?> &nbsp; | &nbsp; <?= $banner_escape($banner_store->email); ?><?php endif; ?>
        <?php if(!empty($banner_store->store_website)): ?> &nbsp; | &nbsp; <?= $banner_escape($banner_store->store_website); ?><?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
