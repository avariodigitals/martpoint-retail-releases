<?php $this->load->helper('plant_documents'); ?>
<div style="max-width:940px;margin:8px auto 35px;color:#183028;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
  <div style="margin-bottom:22px;"><h2 style="font-size:27px;font-weight:800;margin:0 0 5px;">Quotation &amp; Invoice Banner</h2><p style="color:#64748b;margin:0;">Choose a plant document header. The banner replaces the logo when enabled.</p></div>
  <?php if($this->session->flashdata('error')): ?><div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:15px;"><?= htmlspecialchars($this->session->flashdata('error'),ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
  <?php if($this->session->flashdata('success')): ?><div style="background:#ecfdf3;border:1px solid #bbefd0;color:#14532d;padding:12px 16px;border-radius:10px;margin-bottom:15px;"><?= htmlspecialchars($this->session->flashdata('success'),ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
  <form action="<?= base_url('oil_processing/save_document_style'); ?>" method="post" enctype="multipart/form-data" style="background:#fff;border:1px solid #e1e9e5;border-radius:16px;padding:24px;box-shadow:0 8px 28px rgba(25,70,45,.045);">
    <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name(),ENT_QUOTES,'UTF-8'); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash(),ENT_QUOTES,'UTF-8'); ?>">
    <div style="display:flex;gap:26px;flex-wrap:wrap;margin-bottom:24px;">
      <label style="font-weight:700;display:flex;align-items:center;gap:9px;"><input type="checkbox" name="quotation_banner" value="1" <?= $quotation_banner_enabled ? 'checked' : ''; ?>> Use banner on quotations</label>
      <label style="font-weight:700;display:flex;align-items:center;gap:9px;"><input type="checkbox" name="invoice_banner" value="1" <?= $invoice_banner_enabled ? 'checked' : ''; ?>> Use banner on invoices</label>
    </div>
    <label for="plant-banner-upload" style="font-weight:700;display:block;margin-bottom:7px;">Custom banner image <span style="font-weight:400;color:#64748b;">(optional)</span></label>
    <input id="plant-banner-upload" type="file" name="banner" accept="image/png,image/jpeg" style="display:block;width:100%;border:1px solid #d8e2db;border-radius:9px;padding:10px;">
    <p style="color:#64748b;font-size:12px;margin:7px 0 18px;">PNG or JPG, up to 3 MB. A wide image works best. Without an image, MartPoint builds a clean banner from your store name and contact details.</p>
    <?php if($banner_path && mp_plant_banner_image()): ?><label style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:18px;"><input type="checkbox" name="remove_banner" value="1"> Remove uploaded image and use the designed banner</label><?php endif; ?>
    <div style="border:1px solid #e1e9e5;border-radius:12px;padding:16px;margin-bottom:18px;"><div style="font-size:12px;text-transform:uppercase;letter-spacing:.09em;color:#64748b;font-weight:800;margin-bottom:12px;">Banner preview</div><?php $this->load->view('oil_processing/document_banner', array('document_kind'=>'quotation','force_preview'=>true)); ?><?php if(!$quotation_banner_enabled): ?><p style="color:#64748b;margin:0;">Turn on the quotation switch to show the preview on documents.</p><?php endif; ?></div>
    <button type="submit" style="background:#166534;color:#fff;border:0;border-radius:9px;padding:12px 19px;font-weight:800;cursor:pointer;">Save document style</button>
  </form>
</div>
