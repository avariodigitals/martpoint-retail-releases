<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.pv-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
.pv-sec h5{margin:0 0 10px;font-size:13px;text-transform:uppercase;color:var(--mp-muted)}
.pv-f{border:1px solid var(--mp-border);border-radius:8px;padding:10px;margin-bottom:8px}
.pv-f .l{font-size:13px;font-weight:600}.pv-f .t{font-size:11px;color:var(--mp-muted)}
.req{color:#DC2626}
</style>
<div class="mp-page-head"><div><h2>Preview — <?= htmlspecialchars($t->name); ?> v<?= (int)$t->version; ?></h2>
  <div class="mp-page-sub"><a href="<?= base_url('assessment_templates'); ?>">&larr; Templates</a>
    · status: <b><?= $t->status; ?></b><?= $t->provisional ? ' · provisional' : ''; ?></div>
</div></div>
<?php foreach($sections as $sec): ?>
<div class="pv-card pv-sec">
  <h5><?= htmlspecialchars($sec['title'] ?? 'Section'); ?></h5>
  <?php foreach(($sec['fields'] ?? array()) as $f): ?>
  <div class="pv-f"><div class="l"><?= htmlspecialchars($f['label'] ?? $f['key'] ?? ''); ?> <?= !empty($f['required']) ? '<span class="req">*</span>' : ''; ?></div>
    <div class="t"><?= htmlspecialchars($f['type'] ?? 'text'); ?></div></div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>
