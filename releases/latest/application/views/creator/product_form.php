<?php $this->load->view('creator/_styles'); $CI =& get_instance();
$isEdit = !empty($item);
$meta = [
  'digital'    => ['label' => 'Digital Product', 'icon' => 'fa-download', 'hint' => 'Ebooks, templates, audio, video files, presets — anything customers download after paying.'],
  'course'     => ['label' => 'Course', 'icon' => 'fa-play-circle', 'hint' => 'Video lessons organised into modules. Students get instant access after payment.'],
  'membership' => ['label' => 'Membership', 'icon' => 'fa-star', 'hint' => 'A recurring plan that gives members ongoing access to your content and community.'],
];
$m = $meta[$type];
$v = function($field, $default = '') use ($item){ return $item && isset($item->$field) ? $item->$field : $default; };
$img = ($isEdit && !empty($item->item_image) && file_exists($item->item_image)) ? $item->item_image : '';
$currency = $CI->currency(0);
$symbol = trim(preg_replace('/[\d.,\s]/', '', $currency)) ?: '';
?>
<style>
.cr-form-grid{display:grid!important;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)!important;gap:20px!important;align-items:start!important}
@media(max-width:1024px){.cr-form-grid{grid-template-columns:1fr!important}}
.cr-upload{border:2px dashed var(--mp-border)!important;border-radius:14px!important;padding:22px!important;text-align:center!important;background:var(--mp-bg)!important;cursor:pointer!important;transition:all .15s ease!important;position:relative!important}
.cr-upload:hover{border-color:#7C3AED!important;background:rgba(124,58,237,.04)!important}
.cr-upload input[type=file]{position:absolute!important;inset:0!important;opacity:0!important;cursor:pointer!important;width:100%!important;height:100%!important}
.cr-upload .ico{width:44px!important;height:44px!important;border-radius:12px!important;background:rgba(124,58,237,.1)!important;color:#7C3AED!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;font-size:18px!important;margin-bottom:10px!important}
.cr-upload strong{display:block!important;font-size:13px!important;color:var(--mp-ink)!important}
.cr-upload span{font-size:12px!important;color:var(--mp-muted)!important}
.cr-cover-preview{width:100%!important;aspect-ratio:16/9!important;object-fit:cover!important;border-radius:12px!important;border:1px solid var(--mp-border)!important;margin-bottom:10px!important;display:block!important}
.cr-price-wrap{position:relative!important}
.cr-price-wrap .sym{position:absolute!important;left:14px!important;top:50%!important;transform:translateY(-50%)!important;font-weight:700!important;color:var(--mp-muted)!important;font-size:14px!important}
.cr-price-wrap input{padding-left:<?= $symbol ? '34px' : '14px'; ?>!important}
.cr-switch{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;padding:12px 0!important;border-bottom:1px solid var(--mp-border)!important}
.cr-switch:last-child{border-bottom:none!important}
.cr-switch .t{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important}
.cr-switch .s{font-size:12px!important;color:var(--mp-muted)!important}
.cr-switch input{width:18px!important;height:18px!important;flex-shrink:0!important;cursor:pointer!important}
.cr-file-current{display:flex!important;align-items:center!important;gap:10px!important;padding:10px 12px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important;font-size:13px!important;margin-bottom:10px!important}
.cr-file-current i{color:var(--mp-success)!important}
.cr-type-badge{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:6px 12px!important;border-radius:999px!important;background:rgba(124,58,237,.1)!important;color:#7C3AED!important;font-size:12px!important;font-weight:700!important}
</style>

<div class="mp-page-head">
  <div>
    <div class="cr-type-badge" style="margin-bottom:8px;"><i class="fa <?= $m['icon']; ?>"></i> <?= $m['label']; ?></div>
    <h2><?= $isEdit ? htmlspecialchars($item->item_name) : 'New ' . $m['label']; ?></h2>
    <div class="mp-page-sub"><a href="<?= base_url('creator/products/' . $type); ?>"><i class="fa fa-arrow-left"></i> Back</a> &middot; <?= $m['hint']; ?></div>
  </div>
  <?php if($isEdit): ?>
  <div style="display:flex;gap:8px;">
    <?php if($type === 'course' && !empty($course)): ?><a href="<?= base_url('courses/edit/' . $course->id); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-list-ul"></i> Curriculum (<?= (int)$lesson_count; ?> lessons)</a><?php endif; ?>
    <a href="<?= base_url('creator/delete/' . $item->id); ?>" class="mp-btn" onclick="return confirm('Remove this product from your store?')"><i class="fa fa-trash-o"></i></a>
  </div>
  <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<?= form_open_multipart(base_url('creator/save/' . ($item->id ?? '')), ['id' => 'crProductForm']); ?>
<input type="hidden" name="product_type" value="<?= $type; ?>">

<div class="cr-form-grid">
  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Basics</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group full">
            <label><?= $m['label']; ?> Name <span class="text-danger">*</span></label>
            <input type="text" name="item_name" class="mp-form-control" value="<?= htmlspecialchars($v('item_name')); ?>" required placeholder="<?= $type === 'course' ? 'e.g. Launch Your First Digital Product' : ($type === 'membership' ? 'e.g. Inner Circle' : 'e.g. The Creator Pricing Playbook (PDF)'); ?>" style="font-size:16px!important;font-weight:600!important;">
          </div>
          <div class="mp-form-group full">
            <label>Description</label>
            <textarea name="description" class="mp-form-control" rows="6" placeholder="<?= $type === 'course' ? 'What will students learn? Who is it for? What is included?' : ($type === 'membership' ? 'Describe the membership and who it is for.' : 'What is inside, what format, who it is for.'); ?>"><?= htmlspecialchars($v('description')); ?></textarea>
            <p class="mp-form-hint">Shown on the storefront product page.</p>
          </div>
          <div class="mp-form-group">
            <label>Category</label>
            <select name="category_id" class="mp-form-control">
              <option value="0">Auto (<?= ['digital' => 'Digital Products', 'course' => 'Courses', 'membership' => 'Memberships'][$type]; ?>)</option>
              <?php foreach($categories as $c): ?>
              <option value="<?= $c->id; ?>" <?= ((int)$v('category_id') === (int)$c->id) ? 'selected' : ''; ?>><?= htmlspecialchars($c->category_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <?php if($type === 'digital'): ?>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>File &amp; Delivery</h3></div>
      <div class="mp-card-body">
        <?php if(!empty($item->digital_file)): ?>
        <div class="cr-file-current"><i class="fa fa-check-circle"></i> <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(basename($item->digital_file)); ?></span><a href="<?= base_url($item->digital_file); ?>" target="_blank" style="font-size:12px;font-weight:600;">Download</a></div>
        <?php endif; ?>
        <label class="cr-upload">
          <input type="file" name="digital_file" accept=".pdf,.zip,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp3,.mp4,.m4v,.mov,.epub,.txt,.csv,.png,.jpg,.webp,.psd,.ai" onchange="crFileName(this,'dfName')" <?= empty($item->digital_file) ? 'required' : ''; ?>>
          <div class="ico"><i class="fa fa-cloud-upload"></i></div>
          <strong id="dfName"><?= !empty($item->digital_file) ? 'Replace file' : 'Upload the file customers will receive'; ?></strong>
          <span>PDF, ZIP, audio, video, docs &middot; up to 200MB</span>
        </label>
        <div class="mp-form-grid" style="margin-top:16px;">
          <div class="mp-form-group">
            <label>Download limit</label>
            <input type="number" name="download_limit" min="1" class="mp-form-control" value="<?= (int)$v('download_limit', 3); ?>">
            <p class="mp-form-hint">Times each buyer can download.</p>
          </div>
          <div class="mp-form-group">
            <label>Link expires after (hours)</label>
            <input type="number" name="download_expiry_hours" min="1" class="mp-form-control" value="<?= (int)$v('download_expiry_hours', 72); ?>">
            <p class="mp-form-hint">72 = 3 days. Buyers can always re-download from their library.</p>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if($type === 'membership'): ?>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Billing</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Billing cycle <span class="text-danger">*</span></label>
            <select name="billing_interval" class="mp-form-control" required>
              <?php $bi = $membership ? $membership->billing_interval : 'monthly'; ?>
              <option value="weekly" <?= $bi === 'weekly' ? 'selected' : ''; ?>>Every week</option>
              <option value="monthly" <?= $bi === 'monthly' ? 'selected' : ''; ?>>Every month</option>
              <option value="yearly" <?= $bi === 'yearly' ? 'selected' : ''; ?>>Every year</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Free trial (days)</label>
            <input type="number" name="trial_days" min="0" class="mp-form-control" value="<?= $membership ? (int)$membership->trial_days : 0; ?>">
            <p class="mp-form-hint">0 = no trial.</p>
          </div>
          <div class="mp-form-group full">
            <label>What members get</label>
            <textarea name="benefits" class="mp-form-control" rows="4" placeholder="One benefit per line, e.g.&#10;Access to all courses&#10;Monthly live Q&amp;A&#10;Private community"><?= $membership ? htmlspecialchars($membership->description) : ''; ?></textarea>
            <p class="mp-form-hint">Shown as a checklist on the storefront pricing card.</p>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if($type === 'course'): ?>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Curriculum</h3></div>
      <div class="mp-card-body">
        <?php if($isEdit && !empty($course)): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="font-size:13px;color:var(--mp-muted);"><strong style="color:var(--mp-ink);font-size:20px;display:block;"><?= (int)$lesson_count; ?></strong> lessons published</div>
            <a href="<?= base_url('courses/edit/' . $course->id); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-list-ul"></i> Open Curriculum Builder</a>
          </div>
        <?php else: ?>
          <p style="font-size:13px;color:var(--mp-muted);margin:0;">Save this course first — you'll be taken straight to the curriculum builder to add modules and video lessons.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Pricing</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-group" style="margin-bottom:14px;">
          <label>Price <span class="text-danger">*</span><?= $type === 'membership' ? ' <span style="color:var(--mp-muted);font-weight:500;">per billing cycle</span>' : ''; ?></label>
          <div class="cr-price-wrap"><?php if($symbol): ?><span class="sym"><?= htmlspecialchars($symbol); ?></span><?php endif; ?><input type="number" step="0.01" min="0" name="sales_price" class="mp-form-control" value="<?= $isEdit ? (float)$item->sales_price : ''; ?>" required placeholder="0.00" style="font-size:18px!important;font-weight:700!important;"></div>
        </div>
        <div class="mp-form-group">
          <label>Launch discount (%)</label>
          <input type="number" step="0.01" min="0" max="100" name="discount" class="mp-form-control" value="<?= (float)$v('discount', 0) > 0 ? (float)$item->discount : ''; ?>" placeholder="0">
          <p class="mp-form-hint">Shows a strike-through price on the storefront.</p>
        </div>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Cover Image</h3></div>
      <div class="mp-card-body">
        <img id="crCoverPreview" class="cr-cover-preview" src="<?= $img ? base_url($img) : ''; ?>" alt="" style="<?= $img ? '' : 'display:none;'; ?>">
        <label class="cr-upload">
          <input type="file" name="item_image" accept="image/*" onchange="crPreviewCover(this)">
          <div class="ico"><i class="fa fa-image"></i></div>
          <strong><?= $img ? 'Replace cover' : 'Upload cover image'; ?></strong>
          <span>16:9 recommended &middot; JPG, PNG or WebP &middot; up to 4MB</span>
        </label>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Visibility</h3></div>
      <div class="mp-card-body" style="padding-top:8px!important;padding-bottom:8px!important;">
        <label class="cr-switch"><div><div class="t">Publish on storefront</div><div class="s">Customers can see and buy this</div></div><input type="checkbox" name="publish_online" value="1" <?= (!$isEdit || (int)$v('publish_online', 1)) ? 'checked' : ''; ?>></label>
        <label class="cr-switch"><div><div class="t">Feature on homepage</div><div class="s">Show in the featured section</div></div><input type="checkbox" name="is_featured" value="1" <?= (int)$v('is_featured', 0) ? 'checked' : ''; ?>></label>
        <label class="cr-switch"><div><div class="t">Mark as new</div><div class="s">Appears under New Arrivals</div></div><input type="checkbox" name="is_new_arrival" value="1" <?= (int)$v('is_new_arrival', 0) ? 'checked' : ''; ?>></label>
      </div>
    </div>

    <div class="mp-form-actions" style="flex-direction:column;align-items:stretch;gap:10px!important;">
      <button type="submit" class="mp-btn mp-btn-primary" style="width:100%;padding:13px!important;"><i class="fa fa-check"></i> <?= $isEdit ? 'Save Changes' : ($type === 'course' ? 'Create & Build Curriculum' : 'Publish ' . $m['label']); ?></button>
      <a href="<?= base_url('creator/products/' . $type); ?>" class="mp-btn" style="width:100%;">Cancel</a>
    </div>
  </div>
</div>
<?= form_close(); ?>

<script>
function crPreviewCover(input){
  if(!input.files || !input.files[0]) return;
  var r = new FileReader();
  r.onload = function(e){ var p = document.getElementById('crCoverPreview'); p.src = e.target.result; p.style.display = 'block'; };
  r.readAsDataURL(input.files[0]);
}
function crFileName(input, id){ if(input.files && input.files[0]) document.getElementById(id).textContent = input.files[0].name; }
</script>
