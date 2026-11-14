<?php
  $bmContactPhone = trim((string)($settings->store_phone ?? ''));
  $bmContactEmail = trim((string)($settings->store_email ?? ''));
  $bmContactAddress = trim((string)($settings->store_address ?? ''));
  $bmContactSubjectPrefill = trim((string)($contact_subject ?? ''));
  $bmContactType = trim((string)($this->input->post('enquiry_type') ?? ''));
  if($bmContactType === ''){
      $bmContactType = strtolower(trim((string)($this->input->get('type')))) === 'service' ? 'Maintenance / repair' : 'New project / quotation';
  }
  $bmContactName = trim((string)($this->input->post('name') ?? ''));
  $bmContactEmailValue = trim((string)($this->input->post('email') ?? ''));
  $bmContactPhoneValue = trim((string)($this->input->post('phone') ?? ''));
  $bmContactCompany = trim((string)($this->input->post('company') ?? ''));
  $bmContactLocation = trim((string)($this->input->post('project_location') ?? ''));
  $bmContactMessage = trim((string)($this->input->post('message') ?? ''));
  $bmContactSubject = trim((string)($this->input->post('subject') ?? ''));
  if($bmContactSubjectPrefill === '' && $bmContactSubject === '' && !empty($contact_product)) $bmContactSubjectPrefill = (string)($contact_product->item_name ?? '');
  if($bmContactSubject === '' && $bmContactSubjectPrefill !== '') $bmContactSubject = $bmContactSubjectPrefill;
  $bmEnquiryTypes = ['New project / quotation','Technical specification','Site survey','Maintenance / repair','General enquiry'];
?>
<section class="page-heading contact-heading">
  <p class="eyebrow"><?= htmlspecialchars($bm_values['contact_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <?= mp_building_heading($bm_values['contact_title'] ?? '', 'h1'); ?>
  <p><?= nl2br(htmlspecialchars($bm_values['contact_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
</section>

<section class="contact-grid">
  <aside class="contact-aside">
    <p class="eyebrow">THE NEXT STEP</p>
    <h2><?= htmlspecialchars($bm_values['contact_aside_title'] ?? 'Good projects begin with the right questions.', ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Include the site, application and any known dimensions. Drawings and specifications help the team respond precisely.</p>
    <div class="aside-list">
      <div>01 <span>Project context</span></div>
      <div>02 <span>Technical requirements</span></div>
      <div>03 <span>Next conversation</span></div>
    </div>

    <?php if($bmContactPhone !== '' || $bmContactEmail !== '' || $bmContactAddress !== ''): ?>
    <div class="contact-details">
      <p class="eyebrow">DIRECT CONTACT</p>
      <address>
        <?php if($bmContactAddress !== ''): ?><span><?= nl2br(htmlspecialchars($bmContactAddress, ENT_QUOTES, 'UTF-8')); ?></span><?php endif; ?>
        <?php if($bmContactPhone !== ''): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $bmContactPhone), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($bmContactPhone, ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?>
        <?php if($bmContactEmail !== ''): ?><a href="mailto:<?= htmlspecialchars($bmContactEmail, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($bmContactEmail, ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?>
      </address>
    </div>
    <?php endif; ?>

    <?php if(!empty($business_hours)): ?>
    <div class="contact-hours">
      <p class="eyebrow">OPENING HOURS</p>
      <ul>
        <?php foreach($business_hours as $bmHours): ?><li><?= htmlspecialchars($bmHours, ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </aside>

  <?php if($contact_received): ?>
  <div class="enquiry-form enquiry-success" role="status">
    <p class="eyebrow">ENQUIRY RECEIVED</p>
    <h2>Thank you. The team will be in touch.</h2>
    <p>Your enquiry has reached the store. We will respond using the phone number or email address you provided.</p>
    <a class="button" href="<?= $bm_products_url; ?>">Continue browsing systems<span aria-hidden="true">↗</span></a>
  </div>
  <?php elseif(empty($contact_can_submit)): ?>
  <div class="enquiry-form enquiry-unavailable">
    <h2>Enquiries are temporarily unavailable.</h2>
    <p>Please contact the team directly using the details alongside, or return a little later.</p>
  </div>
  <?php else: ?>
  <form id="enquiry-form" class="enquiry-form" method="post" action="<?= $bm_contact_url; ?>#enquiry-form" enctype="multipart/form-data">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="submission_ref" value="<?= htmlspecialchars((string)($this->input->post('submission_ref') ?: $contact_key), ENT_QUOTES, 'UTF-8'); ?>">
    <input class="hp-field" type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true">
    <?php if(!empty($contact_product)): ?>
    <input type="hidden" name="product_id" value="<?= (int)$contact_product->id; ?>">
    <?php endif; ?>
    <?php if(!empty($contact_service)): ?>
    <input type="hidden" name="service_id" value="<?= (int)$contact_service->id; ?>">
    <?php endif; ?>

    <?php if($contact_error): ?>
    <div class="enquiry-feedback error" role="alert"><?= htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="form-intro">
      <p class="eyebrow">PROJECT ENQUIRY</p>
      <h2>Tell us about the opening.</h2>
      <p>Fields marked required help the team respond with the right information.</p>
    </div>

    <div class="form-row">
      <label>Full name <span class="req">*</span>
        <input type="text" name="name" autocomplete="name" maxlength="150" required value="<?= htmlspecialchars($bmContactName, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <label>Work email <span class="req">*</span>
        <input type="email" name="email" autocomplete="email" maxlength="255" required value="<?= htmlspecialchars($bmContactEmailValue, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
    </div>

    <div class="form-row">
      <label>Company
        <input type="text" name="company" autocomplete="organization" maxlength="180" value="<?= htmlspecialchars($bmContactCompany, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <label>Phone
        <input type="tel" name="phone" autocomplete="tel" maxlength="30" value="<?= htmlspecialchars($bmContactPhoneValue, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
    </div>

    <div class="form-row">
      <label>Enquiry type
        <select name="enquiry_type">
          <?php foreach($bmEnquiryTypes as $bmType): ?>
          <option value="<?= htmlspecialchars($bmType, ENT_QUOTES, 'UTF-8'); ?>"<?= $bmContactType === $bmType ? ' selected' : ''; ?>><?= htmlspecialchars($bmType, ENT_QUOTES, 'UTF-8'); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Project location
        <input type="text" name="project_location" maxlength="180" placeholder="City / site" value="<?= htmlspecialchars($bmContactLocation, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
    </div>

    <?php if($bmContactSubjectPrefill !== ''): ?>
    <input type="hidden" name="subject" value="<?= htmlspecialchars($bmContactSubject, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="enquiry-context">
      <span class="eyebrow">ABOUT</span>
      <strong><?= htmlspecialchars($bmContactSubjectPrefill, ENT_QUOTES, 'UTF-8'); ?></strong>
      <?php if(!empty($contact_product)): ?><span>Product enquiry</span><?php endif; ?>
      <?php if(!empty($contact_service)): ?><span>Service request</span><?php endif; ?>
    </div>
    <?php else: ?>
    <label>Interested system
      <select name="subject">
        <option value="Not sure yet"<?= $bmContactSubject === 'Not sure yet' ? ' selected' : ''; ?>>Not sure yet</option>
        <?php foreach(($categories ?? []) as $bmCategory): ?>
        <?php $bmCategoryName = (string)($bmCategory->category_name ?? ''); ?>
        <option value="<?= htmlspecialchars($bmCategoryName, ENT_QUOTES, 'UTF-8'); ?>"<?= $bmContactSubject === $bmCategoryName ? ' selected' : ''; ?>><?= htmlspecialchars($bmCategoryName, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
        <option value="General enquiry"<?= $bmContactSubject === 'General enquiry' ? ' selected' : ''; ?>>General enquiry</option>
      </select>
    </label>
    <?php endif; ?>

    <label>Project details <span class="req">*</span>
      <textarea name="message" rows="6" maxlength="4000" required placeholder="Site use, dimensions, desired timeline, known technical requirements..."><?= htmlspecialchars($bmContactMessage, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </label>

    <label class="file-label">Drawings or supporting documents
      <input id="enquiry-documents" type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.webp,.dwg,.dxf,.doc,.docx" multiple>
      <small>Up to 3 files, 8 MB each (20 MB total). PDF, Word, DWG, DXF, JPG, PNG or WebP.</small>
      <small id="enquiry-file-hint" class="file-hint" aria-live="polite"></small>
    </label>

    <label class="checkbox-label">
      <input type="checkbox" name="consent" value="1" required<?= $this->input->post('consent') ? ' checked' : ''; ?>>
      <span>I agree that the store may contact me about this enquiry. See the <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/privacy'); ?>">privacy policy</a>.</span>
    </label>

    <button class="button" type="submit">Send enquiry<span aria-hidden="true">↗</span></button>
    <p class="tiny">Submitting this form does not place an order or reserve stock. The team will confirm requirements first.</p>
    <div id="formResult" aria-live="polite"></div>
  </form>
  <?php endif; ?>
</section>
