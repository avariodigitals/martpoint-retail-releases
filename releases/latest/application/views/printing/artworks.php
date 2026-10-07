<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section print-artworks">
    <div class="mp-page-head">
        <h2>Artwork</h2>
        <div class="mp-page-sub">Uploaded designs with version history, customer approval and designer clearance.</div>
    </div>

    <!-- Status chips: counts come from the unfiltered set so they never vanish -->
    <div class="pa-chips">
        <?php
        $chips = [
            'all'      => ['All', $counts['all']],
            'pending'  => ['Awaiting approval', $counts['pending']],
            'approved' => ['Approved', $counts['approved']],
            'rejected' => ['Rejected', $counts['rejected']],
        ];
        $current = $filters['status'] ?: 'all';
        foreach ($chips as $key => $c):
            $qs = $key === 'all' ? [] : ['status' => $key];
            if (!empty($filters['job_id'])) $qs['job_id'] = $filters['job_id'];
            if (!empty($filters['q'])) $qs['q'] = $filters['q'];
            $href = base_url('printing/artworks') . ($qs ? '?' . http_build_query($qs) : '');
        ?>
        <a class="pa-chip <?= $current === $key ? 'on' : '' ?>" href="<?= $href ?>">
            <?= htmlspecialchars($c[0]) ?><span class="pa-chip-n"><?= (int)$c[1] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <form method="get" action="<?= base_url('printing/artworks') ?>" class="pa-search">
        <?php if ($filters['status']): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>"><?php endif; ?>
        <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Search job code, title or file name…" class="mp-input">
        <button type="submit" class="mp-btn mp-btn-primary">Search</button>
        <?php if ($filters['q'] || $filters['status'] || $filters['job_id']): ?>
        <a href="<?= base_url('printing/artworks') ?>" class="mp-btn mp-btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($can_approve) && !empty($awaiting)): ?>
    <!-- Upload: only offered for jobs that actually need artwork -->
    <div class="pa-upload">
        <div class="pa-upload-head">
            <b>Upload artwork</b>
            <span><?= count($awaiting) ?> job<?= count($awaiting) === 1 ? '' : 's' ?> awaiting artwork</span>
        </div>
        <form id="pa-upload-form" class="pa-upload-form" enctype="multipart/form-data">
            <input type="hidden" name="<?= $CI->security->get_csrf_token_name(); ?>" value="<?= $CI->security->get_csrf_hash(); ?>">
            <select name="job_id" class="mp-input" required>
                <option value="">Select a job…</option>
                <?php foreach ($awaiting as $j): ?>
                <option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->job_code . ' — ' . ($j->title ?: 'Untitled')) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="artwork_file" class="mp-input" required
                   accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.svg,.tif,.tiff">
            <button type="submit" class="mp-btn mp-btn-primary">Upload</button>
        </form>
        <div class="pa-upload-hint">PDF, AI, EPS, SVG, PNG, JPG or TIFF up to 50 MB. Each upload is versioned automatically.</div>
    </div>
    <?php endif; ?>

    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped pa-table" style="min-width:880px;">
            <thead>
                <tr>
                    <th>Job</th><th>Version</th><th>File</th><th>Status</th>
                    <th>Customer Approved</th><th>Approved At</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($artworks)): ?>
                <tr>
                    <td colspan="7" class="pa-empty">
                        <?php if ($filters['q'] || $filters['status'] || $filters['job_id']): ?>
                            No artwork matches these filters.
                        <?php elseif (empty($awaiting)): ?>
                            No artwork uploaded yet. Artwork appears here once a job reaches the design stage.
                        <?php else: ?>
                            No artwork uploaded yet. Use the upload panel above to attach the first file.
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: foreach ($artworks as $a): ?>
                <tr>
                    <td>
                        <a href="<?= base_url('printing/job/' . $a->job_id) ?>" class="pa-code"><?= htmlspecialchars($a->job_code) ?></a>
                        <?php if (!empty($a->title)): ?><div class="pa-title"><?= htmlspecialchars($a->title) ?></div><?php endif; ?>
                    </td>
                    <td><span class="pa-ver">v<?= (int)$a->version_no ?></span></td>
                    <td>
                        <?php if (!empty($a->file_path) && file_exists(FCPATH . $a->file_path)): ?>
                        <a href="<?= base_url($a->file_path) ?>" target="_blank" rel="noopener" class="pa-file"><?= htmlspecialchars($a->file_name) ?></a>
                        <?php else: ?>
                        <span class="pa-file muted" title="File missing from disk"><?= htmlspecialchars($a->file_name) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $tone = ['approved' => 'ok', 'rejected' => 'bad'][$a->status] ?? 'wait'; ?>
                        <span class="pa-badge <?= $tone ?>"><?= htmlspecialchars(ucfirst($a->status)) ?></span>
                    </td>
                    <td><?= $a->customer_approved ? '<span class="pa-badge ok">Yes</span>' : '<span class="pa-dash">—</span>' ?></td>
                    <td class="pa-when"><?= $a->approved_at ? htmlspecialchars(date('d M Y, H:i', strtotime($a->approved_at))) : '<span class="pa-dash">—</span>' ?></td>
                    <td class="pa-actions">
                        <?php if (!empty($can_approve) && $a->status !== 'approved'): ?>
                        <button type="button" class="mp-btn mp-btn-primary pa-approve" data-id="<?= (int)$a->id ?>">Approve</button>
                        <?php endif; ?>
                        <a href="<?= base_url('printing/job/' . $a->job_id) ?>" class="mp-btn mp-btn-secondary">Open job</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.print-artworks .mp-page-head{margin-bottom:18px}

/* Filter chips */
.pa-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}
.pa-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;
  border:1px solid #d5e3e8;background:#fff;color:#0b5d73;font-size:13px;font-weight:600;text-decoration:none}
.pa-chip:hover{border-color:#0e7490;text-decoration:none}
.pa-chip.on{background:#0e7490;border-color:#0e7490;color:#fff}
.pa-chip-n{background:#eef5f7;color:#0b5d73;border-radius:999px;padding:1px 8px;font-size:11.5px;font-weight:800}
.pa-chip.on .pa-chip-n{background:rgba(255,255,255,.22);color:#fff}

/* Search */
.pa-search{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.pa-search .mp-input{flex:1;min-width:220px;padding:10px 14px;border:1px solid #d5e3e8;border-radius:8px;font-size:14px}
.pa-search .mp-btn{padding:10px 18px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;min-height:44px}
.mp-btn-primary{background:#0e7490;color:#fff;border:none;cursor:pointer}
.mp-btn-primary:hover{background:#0b5d73}
.mp-btn-secondary{background:#fff;color:#0e7490;border:1px solid #0e7490;cursor:pointer}
.mp-btn-secondary:hover{background:#f0f9fa}

/* Upload panel */
.pa-upload{border:1px solid #d5e3e8;border-left:3px solid #0e7490;border-radius:12px;background:#fff;
  padding:18px 20px;margin-bottom:20px}
.pa-upload-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.pa-upload-head b{font-size:15px;color:#102a35}
.pa-upload-head span{font-size:12.5px;color:#5b6f78;font-weight:600}
.pa-upload-form{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.pa-upload-form .mp-input{flex:1;min-width:200px;padding:10px 12px;border:1px solid #d5e3e8;border-radius:8px;font-size:14px;background:#fff}
.pa-upload-form .mp-btn{padding:10px 20px;border-radius:8px;font-weight:700;font-size:14px;min-height:44px}
.pa-upload-hint{font-size:12px;color:#5b6f78;margin-top:10px;line-height:1.5}

/* Table */
.pa-table td{padding:12px;border-bottom:1px solid #eef5f7;vertical-align:middle}
.pa-table tr:hover{background:#f8fbfc}
.pa-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12.5px;background:#eef5f7;
  border:1px solid #d5e3e8;border-radius:6px;padding:2px 8px;color:#0b5d73;text-decoration:none}
.pa-title{font-size:12px;color:#5b6f78;margin-top:5px}
.pa-ver{font-size:12.5px;font-weight:700;color:#5b6f78}
.pa-file{color:#0e7490;font-size:13px;font-weight:600;text-decoration:none;word-break:break-all}
.pa-file.muted{color:#94a3b8;font-weight:500}
.pa-badge{border-radius:999px;padding:3px 10px;font-size:11.5px;font-weight:700;display:inline-block}
.pa-badge.ok{background:#dcfce7;color:#15803d}
.pa-badge.bad{background:#fee2e2;color:#b91c1c}
.pa-badge.wait{background:#fef3c7;color:#b45309}
.pa-dash{color:#cbd5e1}
.pa-when{font-size:12.5px;color:#5b6f78}
.pa-actions{white-space:nowrap;text-align:right}
.pa-actions .mp-btn{padding:7px 13px;font-size:12.5px;border-radius:7px;margin-left:6px;min-height:36px}
.pa-empty{text-align:center;color:#5b6f78;padding:34px 18px!important;line-height:1.6}

@media (max-width:640px){
  .pa-chips{gap:6px}
  .pa-chip{padding:7px 12px;font-size:12.5px}
  .pa-search .mp-input,.pa-search .mp-btn{width:100%}
  .pa-upload-form .mp-input,.pa-upload-form .mp-btn{width:100%}
}
</style>

<script>
(function(){
  var form = document.getElementById('pa-upload-form');
  if (form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var btn = form.querySelector('button[type=submit]');
      var label = btn.textContent;
      btn.disabled = true; btn.textContent = 'Uploading…';
      fetch('<?= base_url('printing/artwork_save') ?>', { method: 'POST', body: new FormData(form) })
        .then(function(r){ return r.json(); })
        .then(function(res){
          if (res.success) { location.reload(); return; }
          alert(res.message || 'Upload failed');
          btn.disabled = false; btn.textContent = label;
        })
        .catch(function(){ alert('Upload failed'); btn.disabled = false; btn.textContent = label; });
    });
  }

  document.querySelectorAll('.pa-approve').forEach(function(btn){
    btn.addEventListener('click', function(){
      var fd = new FormData();
      fd.append('artwork_id', btn.dataset.id);
      fd.append('<?= $CI->security->get_csrf_token_name(); ?>', '<?= $CI->security->get_csrf_hash(); ?>');
      btn.disabled = true; btn.textContent = 'Approving…';
      fetch('<?= base_url('printing/artwork_approve') ?>', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res){
          if (res.success) { location.reload(); return; }
          alert(res.message || 'Could not approve');
          btn.disabled = false; btn.textContent = 'Approve';
        })
        .catch(function(){ alert('Could not approve'); btn.disabled = false; btn.textContent = 'Approve'; });
    });
  });
})();
</script>
