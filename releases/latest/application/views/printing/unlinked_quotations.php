<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
 * Unlinked quotations — REVIEW ONLY.
 *
 * Lists quotations in this store that are not linked to any print job. These
 * may be perfectly valid general quotations (consultancy, deposits) or older
 * records raised before the printing workflow existed.
 *
 * Nothing here auto-attaches a quotation to a job or rewrites a historical
 * document. Raising a job is an explicit, deliberate action.
 */
$CI =& get_instance();
$currency = $CI->currency();
$can_add_job = $CI->permissions('print_jobs_add');
?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Unlinked Quotations</h2>
    <div class="mp-page-sub">
      <?= (int)$count ?> quotation<?= (int)$count === 1 ? '' : 's' ?> in this store are not connected to a print job.
      These are left untouched — attach one only if it genuinely needs production.
    </div>
    <a href="<?= base_url('printing') ?>" class="btn btn-default btn-sm">← Print Dashboard</a>
  </div>

  <div class="uq-note">
    <i class="fa fa-info-circle"></i>
    A <strong>general quotation</strong> cannot authorise print production. If one of these needs to be produced,
    use <strong>Create Print Job</strong> — that links the existing quotation to a new job and carries its lines
    across without altering the issued document.
  </div>

  <table class="uq-tbl">
    <thead>
      <tr>
        <th>Quotation</th>
        <th>Reference</th>
        <th>Date</th>
        <th class="num">Total</th>
        <th>Created by</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="7" class="uq-empty">Every quotation in this store is linked to a print job.</td></tr>
    <?php else: foreach ($rows as $r): ?>
      <tr>
        <td><a href="<?= base_url('quotation/invoice/' . (int)$r->id) ?>"><?= htmlspecialchars($r->quotation_code) ?></a></td>
        <td><?= $r->reference_no ? htmlspecialchars($r->reference_no) : '—' ?></td>
        <td><?= show_date($r->quotation_date) ?></td>
        <td class="num"><?= $currency . number_format((float)$r->grand_total, 2) ?></td>
        <td><?= htmlspecialchars($r->created_by ?: '—') ?></td>
        <td><?= $r->sales_status === 'Converted' ? '<span class="uq-tag conv">Converted</span>' : '<span class="uq-tag">Open</span>' ?></td>
        <td class="uq-act">
          <?php if ($can_add_job && $r->sales_status !== 'Converted'): ?>
          <button class="btn btn-default btn-sm uq-make" data-qid="<?= (int)$r->id ?>" data-code="<?= htmlspecialchars($r->quotation_code) ?>">
            <i class="fa fa-plus"></i> Create Print Job
          </button>
          <?php else: ?>
          <span class="uq-na">No action</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<style>
.uq-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:8px;padding:11px 14px;font-size:12.5px;margin-bottom:14px;line-height:1.6}
.uq-tbl{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2edf1;border-radius:10px;overflow:hidden}
.uq-tbl th{background:#f4f8fa;text-align:left;padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#5b7280;border-bottom:1px solid #e2edf1}
.uq-tbl td{padding:10px 12px;font-size:12.5px;color:#1f2937;border-bottom:1px solid #f1f5f9}
.uq-tbl .num{text-align:right}
.uq-empty{text-align:center;color:#94a3b8;padding:26px}
.uq-tag{display:inline-block;background:#e6f4f8;color:#0e7490;border-radius:999px;padding:2px 9px;font-size:11px;font-weight:700}
.uq-tag.conv{background:#ecfdf5;color:#065f46}
.uq-act{text-align:right;white-space:nowrap}
.uq-na{color:#cbd5e1;font-size:11.5px}
</style>

<script>
(function(){
  document.querySelectorAll('.uq-make').forEach(function(btn){
    btn.addEventListener('click', function(){
      var qid = btn.getAttribute('data-qid');
      var code = btn.getAttribute('data-code');
      if(!confirm('Create a print job from quotation ' + code + '?\n\nThis links the existing quotation to a new job and carries its lines across. The issued quotation document is not altered.')) return;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Working…';
      var fd = new FormData();
      fd.append('quotation_id', qid);
      <?php if (!empty($categories)): ?>
      fd.append('category_id', '<?= (int)$categories[0]->id ?>');
      <?php endif; ?>
      var csrfName = '<?= $CI->security->get_csrf_token_name() ?>';
      var csrfHash = '<?= $CI->security->get_csrf_hash() ?>';
      fd.append(csrfName, csrfHash);
      fetch('<?= base_url('printing/job_from_quotation') ?>', {
        method:'POST', body: fd, credentials:'same-origin',
        headers:{'X-Requested-With':'XMLHttpRequest'}
      })
        .then(function(r){ return r.json(); })
        .then(function(res){
          if (res.success) {
            window.location = '<?= base_url('printing/job/') ?>' + res.job_id;
          } else {
            alert(res.message || 'Could not create the print job.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-plus"></i> Create Print Job';
          }
        })
        .catch(function(){
          alert('Request failed. Please try again.');
          btn.disabled = false;
          btn.innerHTML = '<i class="fa fa-plus"></i> Create Print Job';
        });
    });
  });
})();
</script>
