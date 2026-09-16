<?php $this->load->view('creator/_styles');
$isEdit = !empty($course);
$selectedItem = $isEdit ? (int)$course->item_id : (int)($preselect_item ?? 0);
$selectedItemRow = null;
foreach($items as $i){ if((int)$i->id === $selectedItem){ $selectedItemRow = $i; break; } }
$defaultTitle = $isEdit ? $course->title : ($selectedItemRow ? $selectedItemRow->item_name : '');
// Group lessons by module
$lessonsByModule = [];
$orphanLessons = [];
foreach($lessons as $l){
  if(!empty($l->module_id)) $lessonsByModule[$l->module_id][] = $l; else $orphanLessons[] = $l;
}
?>

<div class="mp-page-head">
  <div>
    <h2><?= $isEdit ? 'Curriculum: ' . htmlspecialchars($course->title) : 'Build Curriculum'; ?></h2>
    <div class="mp-page-sub"><a href="<?= base_url('courses'); ?>"><i class="fa fa-arrow-left"></i> Back to Courses</a> &middot; Organise your lessons into modules. Students see them in this order.</div>
  </div>
  <?php if($isEdit): ?>
  <div style="display:flex;gap:8px;">
    <a href="<?= base_url('creator/edit/' . $course->item_id); ?>" class="mp-btn"><i class="fa fa-tag"></i> Edit Product &amp; Price</a>
  </div>
  <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<?php if(empty($items)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-play-circle"></i></div>
    <h3>Create the course product first</h3>
    <p>A course needs a product with a price and cover image so it can be sold on your storefront.</p>
    <a href="<?= base_url('creator/create/course'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Course Product</a>
  </div>
<?php else: ?>

<?= form_open(base_url('courses/save/' . ($course->id ?? '')), ['id' => 'courseForm', 'method' => 'POST']); ?>
<input type="hidden" name="id" value="<?= $course->id ?? ''; ?>">

<div class="cr-grid-2">
  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Course Details</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Course Product <span class="text-danger">*</span></label>
            <select name="item_id" class="mp-form-control" required>
              <option value="">Select the product this curriculum belongs to</option>
              <?php foreach($items as $i): ?>
              <option value="<?= $i->id; ?>" <?= ($selectedItem === (int)$i->id) ? 'selected' : ''; ?>><?= htmlspecialchars($i->item_name); ?></option>
              <?php endforeach; ?>
            </select>
            <p class="mp-form-hint">Price, image and storefront listing are managed on the product.</p>
          </div>
          <div class="mp-form-group">
            <label>Course Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="mp-form-control" value="<?= htmlspecialchars($defaultTitle); ?>" required placeholder="e.g. Launch Your First Digital Product">
          </div>
          <div class="mp-form-group full">
            <label>What students will learn</label>
            <textarea name="description" class="mp-form-control" rows="4" placeholder="Short overview shown to students inside the course"><?= $isEdit ? htmlspecialchars($course->description) : ''; ?></textarea>
          </div>
          <div class="mp-form-group full">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="status" value="1" <?= (!$isEdit || $course->status) ? 'checked' : ''; ?> style="width:16px;height:16px;"> Published &mdash; students can access this course
            </label>
          </div>
        </div>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head">
        <h3>Curriculum</h3>
        <button type="button" class="cr-add-btn" onclick="addModule()"><i class="fa fa-plus"></i> Add Module</button>
      </div>
      <div class="mp-card-body" id="modulesWrap">
        <?php $mIdx = 0; foreach($modules as $mod): $mKey = 'm' . $mIdx; ?>
        <div class="cr-module" data-module-key="<?= $mKey; ?>">
          <input type="hidden" name="modules[<?= $mKey; ?>][id]" value="<?= (int)$mod->id; ?>">
          <input type="hidden" name="modules[<?= $mKey; ?>][sort_order]" value="<?= $mIdx; ?>" class="module-sort">
          <div class="cr-module-head">
            <i class="fa fa-folder-open" style="color:#7C3AED;"></i>
            <input type="text" name="modules[<?= $mKey; ?>][title]" class="mp-form-control" value="<?= htmlspecialchars($mod->title); ?>" placeholder="Module title" required>
            <button type="button" class="cr-add-btn" onclick="addLesson(this)"><i class="fa fa-plus"></i> Lesson</button>
            <button type="button" class="cr-icon-btn" title="Remove module" onclick="removeModule(this)"><i class="fa fa-trash-o"></i></button>
          </div>
          <div class="cr-module-body">
            <?php $lIdx = 0; foreach(($lessonsByModule[$mod->id] ?? []) as $les): $lKey = $mKey . '_l' . $lIdx; ?>
            <div class="cr-lesson">
              <input type="hidden" name="lessons[<?= $lKey; ?>][id]" value="<?= (int)$les->id; ?>">
              <input type="hidden" name="lessons[<?= $lKey; ?>][module_key]" value="<?= $mKey; ?>" class="lesson-module-key">
              <input type="hidden" name="lessons[<?= $lKey; ?>][sort_order]" value="<?= $lIdx; ?>" class="lesson-sort">
              <input type="text" name="lessons[<?= $lKey; ?>][title]" class="mp-form-control" value="<?= htmlspecialchars($les->title); ?>" placeholder="Lesson title" required>
              <input type="url" name="lessons[<?= $lKey; ?>][video_url]" class="mp-form-control" value="<?= htmlspecialchars($les->video_url); ?>" placeholder="Video URL (YouTube, Vimeo, MP4)">
              <button type="button" class="cr-icon-btn" title="Remove lesson" onclick="removeLesson(this)"><i class="fa fa-times"></i></button>
              <textarea name="lessons[<?= $lKey; ?>][content]" class="mp-form-control" rows="2" placeholder="Lesson notes, links or text content (optional)"><?= htmlspecialchars($les->content); ?></textarea>
            </div>
            <?php $lIdx++; endforeach; ?>
          </div>
        </div>
        <?php $mIdx++; endforeach; ?>

        <?php if(!empty($orphanLessons)): $mKey = 'm' . $mIdx; ?>
        <div class="cr-module" data-module-key="<?= $mKey; ?>">
          <input type="hidden" name="modules[<?= $mKey; ?>][id]" value="">
          <input type="hidden" name="modules[<?= $mKey; ?>][sort_order]" value="<?= $mIdx; ?>" class="module-sort">
          <div class="cr-module-head">
            <i class="fa fa-folder-open" style="color:#7C3AED;"></i>
            <input type="text" name="modules[<?= $mKey; ?>][title]" class="mp-form-control" value="Lessons" placeholder="Module title" required>
            <button type="button" class="cr-add-btn" onclick="addLesson(this)"><i class="fa fa-plus"></i> Lesson</button>
            <button type="button" class="cr-icon-btn" title="Remove module" onclick="removeModule(this)"><i class="fa fa-trash-o"></i></button>
          </div>
          <div class="cr-module-body">
            <?php $lIdx = 0; foreach($orphanLessons as $les): $lKey = $mKey . '_l' . $lIdx; ?>
            <div class="cr-lesson">
              <input type="hidden" name="lessons[<?= $lKey; ?>][id]" value="<?= (int)$les->id; ?>">
              <input type="hidden" name="lessons[<?= $lKey; ?>][module_key]" value="<?= $mKey; ?>" class="lesson-module-key">
              <input type="hidden" name="lessons[<?= $lKey; ?>][sort_order]" value="<?= $lIdx; ?>" class="lesson-sort">
              <input type="text" name="lessons[<?= $lKey; ?>][title]" class="mp-form-control" value="<?= htmlspecialchars($les->title); ?>" placeholder="Lesson title" required>
              <input type="url" name="lessons[<?= $lKey; ?>][video_url]" class="mp-form-control" value="<?= htmlspecialchars($les->video_url); ?>" placeholder="Video URL (YouTube, Vimeo, MP4)">
              <button type="button" class="cr-icon-btn" title="Remove lesson" onclick="removeLesson(this)"><i class="fa fa-times"></i></button>
              <textarea name="lessons[<?= $lKey; ?>][content]" class="mp-form-control" rows="2" placeholder="Lesson notes, links or text content (optional)"><?= htmlspecialchars($les->content); ?></textarea>
            </div>
            <?php $lIdx++; endforeach; ?>
          </div>
        </div>
        <?php $mIdx++; endif; ?>

        <div id="emptyCurriculum" class="cr-empty" style="<?= $mIdx > 0 ? 'display:none;' : ''; ?>padding:32px!important;">
          <div class="icon"><i class="fa fa-list-ul"></i></div>
          <h3>No modules yet</h3>
          <p>Add a module (e.g. "Getting Started"), then add video lessons inside it.</p>
          <button type="button" class="mp-btn mp-btn-primary" onclick="addModule()"><i class="fa fa-plus"></i> Add First Module</button>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Publish</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-actions" style="flex-direction:column;align-items:stretch;gap:10px!important;padding:0!important;">
          <button type="submit" class="mp-btn mp-btn-primary" style="width:100%;"><i class="fa fa-check"></i> Save Curriculum</button>
          <a href="<?= base_url('courses'); ?>" class="mp-btn" style="width:100%;">Cancel</a>
        </div>
        <hr>
        <div style="font-size:13px;color:var(--mp-muted);line-height:1.6;">
          <div style="display:flex;justify-content:space-between;"><span>Modules</span><strong id="statModules" style="color:var(--mp-ink);"><?= count($modules); ?></strong></div>
          <div style="display:flex;justify-content:space-between;"><span>Lessons</span><strong id="statLessons" style="color:var(--mp-ink);"><?= count($lessons); ?></strong></div>
        </div>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Tips</h3></div>
      <div class="mp-card-body" style="font-size:13px;color:var(--mp-muted);line-height:1.7;">
        <p style="margin:0 0 8px;"><strong style="color:var(--mp-ink);">Video URLs</strong> — paste a YouTube, Vimeo or direct MP4 link. Unlisted YouTube videos work well.</p>
        <p style="margin:0 0 8px;"><strong style="color:var(--mp-ink);">Order matters</strong> — modules and lessons are shown to students top to bottom.</p>
        <p style="margin:0;"><strong style="color:var(--mp-ink);">Access</strong> — students are enrolled automatically when payment is confirmed and can track progress from their account.</p>
      </div>
    </div>
  </div>
</div>
<?= form_close(); ?>

<script>
var crModuleCounter = <?= (int)$mIdx; ?>;
function crModuleTemplate(key){
  return '<div class="cr-module" data-module-key="'+key+'">'+
    '<input type="hidden" name="modules['+key+'][id]" value="">'+
    '<input type="hidden" name="modules['+key+'][sort_order]" value="0" class="module-sort">'+
    '<div class="cr-module-head"><i class="fa fa-folder-open" style="color:#7C3AED;"></i>'+
    '<input type="text" name="modules['+key+'][title]" class="mp-form-control" placeholder="Module title, e.g. Getting Started" required>'+
    '<button type="button" class="cr-add-btn" onclick="addLesson(this)"><i class="fa fa-plus"></i> Lesson</button>'+
    '<button type="button" class="cr-icon-btn" title="Remove module" onclick="removeModule(this)"><i class="fa fa-trash-o"></i></button></div>'+
    '<div class="cr-module-body"></div></div>';
}
function crLessonTemplate(mKey, lKey){
  return '<div class="cr-lesson">'+
    '<input type="hidden" name="lessons['+lKey+'][id]" value="">'+
    '<input type="hidden" name="lessons['+lKey+'][module_key]" value="'+mKey+'" class="lesson-module-key">'+
    '<input type="hidden" name="lessons['+lKey+'][sort_order]" value="0" class="lesson-sort">'+
    '<input type="text" name="lessons['+lKey+'][title]" class="mp-form-control" placeholder="Lesson title" required>'+
    '<input type="url" name="lessons['+lKey+'][video_url]" class="mp-form-control" placeholder="Video URL (YouTube, Vimeo, MP4)">'+
    '<button type="button" class="cr-icon-btn" title="Remove lesson" onclick="removeLesson(this)"><i class="fa fa-times"></i></button>'+
    '<textarea name="lessons['+lKey+'][content]" class="mp-form-control" rows="2" placeholder="Lesson notes, links or text content (optional)"></textarea></div>';
}
function addModule(){
  var key = 'm' + (crModuleCounter++);
  var wrap = document.getElementById('modulesWrap');
  var empty = document.getElementById('emptyCurriculum');
  empty.insertAdjacentHTML('beforebegin', crModuleTemplate(key));
  var mod = wrap.querySelector('[data-module-key="'+key+'"]');
  addLesson(mod.querySelector('.cr-add-btn'));
  mod.querySelector('input[type=text]').focus();
  crRefresh();
}
function addLesson(btn){
  var mod = btn.closest('.cr-module');
  var mKey = mod.getAttribute('data-module-key');
  var body = mod.querySelector('.cr-module-body');
  var lKey = mKey + '_l' + Date.now() + Math.floor(Math.random()*1000);
  body.insertAdjacentHTML('beforeend', crLessonTemplate(mKey, lKey));
  crRefresh();
}
function removeModule(btn){
  var mod = btn.closest('.cr-module');
  var n = mod.querySelectorAll('.cr-lesson').length;
  if(n > 0 && !confirm('Remove this module and its ' + n + ' lesson(s)?')) return;
  mod.remove(); crRefresh();
}
function removeLesson(btn){ btn.closest('.cr-lesson').remove(); crRefresh(); }
function crRefresh(){
  var mods = document.querySelectorAll('#modulesWrap .cr-module');
  var lessons = 0;
  mods.forEach(function(m, i){
    m.querySelector('.module-sort').value = i;
    m.querySelectorAll('.cr-lesson').forEach(function(l, j){ l.querySelector('.lesson-sort').value = j; lessons++; });
  });
  document.getElementById('emptyCurriculum').style.display = mods.length ? 'none' : '';
  document.getElementById('statModules').textContent = mods.length;
  document.getElementById('statLessons').textContent = lessons;
}
document.getElementById('courseForm').addEventListener('submit', function(){ crRefresh(); });
</script>
<?php endif; ?>
