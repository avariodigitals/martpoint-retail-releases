<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
 * New Quotation (printing business) — choose the job first.
 *
 * A print quotation is only meaningful attached to a job: the job holds the
 * specification, artwork, deposit policy and production gates. Creating a
 * quotation on its own would produce a document that can never authorise
 * production, so that path is offered separately and clearly labelled.
 */
$CI =& get_instance();
$currency = $CI->currency();
$can_new_job = $CI->permissions('print_jobs_add');
?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>New Quotation</h2>
    <div class="mp-page-sub">A print quotation is priced against a print job. Choose which job you are quoting for.</div>
    <a href="<?= base_url('quotation') ?>" class="btn btn-default btn-sm">← Quotation History</a>
  </div>

  <div class="nq-grid">
    <!-- Option A: price an existing job -->
    <div class="nq-card">
      <div class="nq-card-head"><span class="nq-badge primary">Recommended</span><h3>Price an existing print job</h3></div>
      <p class="nq-desc">Pick a job that does not have a quotation yet and issue its quotation.</p>
      <?php if (empty($jobs_without_quote)): ?>
        <div class="nq-empty">Every open job already has a quotation.</div>
      <?php else: ?>
      <select id="nq-job" class="form-control">
        <option value="">Select a job…</option>
        <?php foreach ($jobs_without_quote as $j): ?>
        <option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->job_code) ?> — <?= htmlspecialchars($j->title ?: 'Print job') ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-block nq-go" id="nq-price-job" style="margin-top:10px;">Open job &amp; issue quotation</button>
      <?php endif; ?>
    </div>

    <!-- Option B: new job -->
    <?php if ($can_new_job): ?>
    <div class="nq-card">
      <div class="nq-card-head"><h3>Create a new print job</h3></div>
      <p class="nq-desc">Start a fresh job with its specification, then issue the quotation from there.</p>
      <a class="btn btn-default btn-block" href="<?= base_url('printing/job') ?>" style="margin-top:10px;">Go to New Print Job</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- General quotation: explicitly secondary -->
  <div class="nq-general">
    <div>
      <strong>General quotation (no production)</strong>
      <div class="nq-general-sub">
        For work that is not printed — consultancy, site survey, delivery, a deposit-only document.
        A general quotation <em>cannot</em> authorise print production.
      </div>
    </div>
    <a class="btn btn-default btn-sm" href="<?= base_url('quotation/add?general=1') ?>">Create General Quotation</a>
  </div>

  <?php if (!empty($unlinked_count)): ?>
  <div class="nq-note">
    <i class="fa fa-info-circle"></i>
    <?= (int)$unlinked_count ?> quotation<?= (int)$unlinked_count === 1 ? '' : 's' ?> in this store are not linked to a job.
    <a href="<?= base_url('printing/unlinked_quotations') ?>">Review them</a> — they are left untouched unless you deliberately raise a job from one.
  </div>
  <?php endif; ?>
</div>

<style>
.nq-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;margin-bottom:16px}
.nq-card{background:#fff;border:1px solid #e2edf1;border-radius:12px;padding:18px 20px}
.nq-card-head{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-bottom:7px}
.nq-card-head h3{margin:0;font-size:15px;font-weight:700;color:#0f172a}
.nq-badge{font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;border-radius:999px;padding:3px 9px}
.nq-badge.primary{background:#e0f2f8;color:#0b5d73}
.nq-desc{margin:0 0 10px;font-size:12.5px;color:#5b7280;line-height:1.6}
.nq-empty{font-size:12.5px;color:#94a3b8;padding:10px 0}
.nq-general{display:flex;gap:16px;align-items:center;justify-content:space-between;flex-wrap:wrap;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:14px 18px}
.nq-general strong{display:block;font-size:13.5px;color:#334155}
.nq-general-sub{font-size:12px;color:#64748b;line-height:1.6;max-width:620px;margin-top:3px}
.nq-note{margin-top:14px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:8px;padding:11px 14px;font-size:12.5px}
</style>

<script>
(function(){
  var btn = document.getElementById('nq-price-job');
  if(!btn) return;
  btn.addEventListener('click', function(){
    var sel = document.getElementById('nq-job');
    if(!sel.value){ alert('Choose a job first.'); return; }
    window.location = '<?= base_url('printing/job/') ?>' + sel.value;
  });
})();
</script>
