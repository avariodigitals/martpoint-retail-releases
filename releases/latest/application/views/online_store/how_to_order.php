<?php $this->load->view('online_store/content_editor_styles');$mobile=$this->input->get('screen')==='mobile';$guide=$how_to_order??[]; ?>
<?php if(!$mobile): ?><h2>How to order</h2><?php endif; ?>
<p>Write ordering instructions that fit your store. This page is saved for this store and can be shown or hidden in its storefront footer.</p>
<nav class="sf-admin-links"><a href="<?= base_url('store/'.rawurlencode($settings->store_slug).'/how-to-order'); ?>" target="_blank" rel="noopener">Preview customer page ↗</a><a href="<?= base_url('store/'.rawurlencode($settings->store_slug)); ?>" target="_blank" rel="noopener">View storefront ↗</a></nav>
<form method="post" enctype="multipart/form-data" action="<?= base_url('online_store/save_how_to_order'); ?>">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
  <div class="sf-admin-grid">
    <div>
      <section class="sf-admin-card">
        <h3>Page content</h3>
        <label><input type="checkbox" name="enabled" value="1" <?= !empty($guide['enabled'])?'checked':''; ?>> Show “How to order” in the storefront footer</label>
        <label for="how-order-title">Page heading</label>
        <input id="how-order-title" name="title" maxlength="120" required value="<?= htmlspecialchars($guide['title']??'How to order',ENT_QUOTES,'UTF-8'); ?>">
        <label for="how-order-steps">Ordering instructions</label>
        <textarea id="how-order-steps" name="steps" rows="12" maxlength="12000" placeholder="Write one step per line. For example:\nChoose the products you want.\nAdd them to your cart.\nEnter delivery and payment details at checkout."><?= htmlspecialchars($guide['steps']??'',ENT_QUOTES,'UTF-8'); ?></textarea>
        <p>Put each step on a new line. Customers will see the same text you save here.</p>
      </section>
    </div>
    <aside>
      <section class="sf-admin-card">
        <h3>Image and video</h3>
        <label for="guide-image">Upload an image (JPG, PNG, WebP or AVIF; up to 3 MB)</label>
        <input id="guide-image" type="file" name="guide_image" accept="image/jpeg,image/png,image/webp,image/avif">
        <?php if(!empty($guide['image_url'])): ?>
          <img src="<?= htmlspecialchars($guide['image_url'],ENT_QUOTES,'UTF-8'); ?>" alt="Current How to order image" style="display:block;max-width:100%;max-height:240px;object-fit:contain;margin-top:14px;border-radius:8px">
          <label><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
        <?php endif; ?>
        <label for="guide-video">Video link (optional)</label>
        <input id="guide-video" type="url" name="video_url" maxlength="1500" placeholder="https://youtu.be/..." value="<?= htmlspecialchars($guide['video_url']??'',ENT_QUOTES,'UTF-8'); ?>">
        <p>Paste a public video link. It will appear as a “Watch how to order” button on the customer page.</p>
      </section>
      <button type="submit" class="sf-admin-action">Save How to order</button>
    </aside>
  </div>
</form>
