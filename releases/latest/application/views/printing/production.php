<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
$CI =& get_instance();
$col_colors = [
  'quote'      => '#64748b',
  'deposit'    => '#d97706',
  'artwork'    => '#7c3aed',
  'approval'   => '#dc2626',
  'production' => '#2563eb',
  'collection' => '#059669',
];

/**
 * A column is the job's earliest unmet gate, so advancing a job means doing
 * that gate's action — not dragging it to an arbitrary column. Each column
 * therefore declares the action that clears it:
 *
 *   oneclick = the action only needs the job id, so the board can run it
 *   url      = the action needs more input (an amount, artwork, an approver),
 *              so the board sends the user to the job screen instead of
 *              pretending a drag could supply it.
 *   perm     = the permission required to perform it
 */
$col_actions = [
  'quote'      => ['oneclick' => 'quote_issue', 'label' => 'Issue quotation',  'perm' => 'can_quote'],
  'deposit'    => ['url'      => 'printing/job/', 'label' => 'Record deposit', 'perm' => 'can_payments'],
  'artwork'    => ['url'      => 'printing/job/', 'label' => 'Upload artwork', 'perm' => 'can_artwork'],
  'approval'   => ['url'      => 'printing/job/', 'label' => 'Request print approval', 'perm' => 'can_authorize'],
  'production' => ['url'      => 'printing/job/', 'label' => 'Report progress', 'perm' => 'can_production'],
  'collection' => ['url'      => 'printing/job/', 'label' => 'Record collection', 'perm' => 'can_production'],
];

$total = 0; foreach ($board as $c) { $total += count($c['jobs']); }
$col_keys = array_keys($board);
?>
<div class="mp-section print-board" id="print-board">
  <div class="mp-page-head">
    <div>
      <h2>Production Board</h2>
      <div class="mp-page-sub">Every open job across the print lifecycle — quote → deposit → artwork → approval → production → collection. <?= (int)$total ?> open. Drag a card, or use its button, to take the next step.</div>
    </div>
    <?php if (!empty($can_production)): ?><a href="<?= base_url('printing') ?>" class="mp-card-link">Overview →</a><?php endif; ?>
  </div>

  <div class="kb-wrap">
    <?php foreach ($board as $key => $col):
      $act = $col_actions[$key] ?? null;
      $may = $act && !empty(${$act['perm']});
    ?>
    <div class="kb-col" data-col="<?= htmlspecialchars($key) ?>">
      <div class="kb-col-head" style="border-top:3px solid <?= $col_colors[$key] ?>;">
        <span class="kb-col-title"><?= htmlspecialchars($col['label']) ?></span>
        <span class="kb-col-count" style="background:<?= $col_colors[$key] ?>22;color:<?= $col_colors[$key] ?>;"><?= count($col['jobs']) ?></span>
      </div>
      <div class="kb-col-body" data-drop="<?= htmlspecialchars($key) ?>">
        <?php if (empty($col['jobs'])): ?>
        <div class="kb-empty">No jobs</div>
        <?php else: foreach ($col['jobs'] as $j): ?>
        <div class="kb-card" draggable="true"
             data-job="<?= (int)$j->id ?>"
             data-col="<?= htmlspecialchars($key) ?>"
             data-next="<?= $may && !empty($act['oneclick']) ? htmlspecialchars($act['oneclick']) : '' ?>"
             data-next-label="<?= $may ? htmlspecialchars($act['label']) : '' ?>"
             data-job-url="<?= $may && empty($act['oneclick']) ? base_url('printing/job/' . $j->id) : '' ?>">

          <a class="kb-open" href="<?= base_url('printing/job/' . $j->id) ?>">
            <div class="kb-card-top">
              <span class="kb-code"><?= htmlspecialchars($j->job_code) ?></span>
              <?php if ($j->due_date): $overdue = strtotime($j->due_date) < strtotime(date('Y-m-d')); ?>
              <span class="kb-due <?= $overdue ? 'overdue' : '' ?>"><i class="fa fa-clock-o"></i> <?= show_date($j->due_date) ?></span>
              <?php endif; ?>
            </div>
            <div class="kb-title"><?= htmlspecialchars($j->title ?: $j->description ?: 'Print job') ?></div>
            <div class="kb-client"><i class="fa fa-user-o"></i> <?= htmlspecialchars($j->customer_name ?: 'Walk-in') ?></div>
            <?php if ($j->category_key || $j->dimension || $j->material): ?>
            <div class="kb-meta">
              <?php if ($j->category_key): ?><span class="kb-tag"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $j->category_key))) ?></span><?php endif; ?>
              <?php if ($j->dimension): ?><span class="kb-tag"><?= htmlspecialchars($j->dimension) ?></span><?php endif; ?>
              <?php if ($j->material): ?><span class="kb-tag"><?= htmlspecialchars($j->material) ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="kb-foot">
              <span class="kb-amount"><?= $CI->currency($j->quote_amount, true) ?></span>
              <?php if ($j->balance > 0): ?><span class="kb-balance"><?= $CI->currency($j->balance, true) ?> due</span><?php else: ?><span class="kb-paid"><i class="fa fa-check"></i> Paid</span><?php endif; ?>
            </div>
          </a>

          <?php if ($may): ?>
          <button type="button" class="kb-next"
                  data-oneclick="<?= !empty($act['oneclick']) ? htmlspecialchars($act['oneclick']) : '' ?>"
                  data-url="<?= empty($act['oneclick']) ? base_url('printing/job/' . $j->id) : '' ?>"
                  data-job="<?= (int)$j->id ?>">
            <?= htmlspecialchars($act['label']) ?> <i class="fa fa-arrow-right"></i>
          </button>
          <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.print-board{font-family:'Inter',sans-serif}
.kb-wrap{display:grid;grid-template-columns:repeat(6,minmax(230px,1fr));gap:14px;overflow-x:auto;padding-bottom:8px}
.kb-col{background:#f4f8fa;border:1px solid #d5e3e8;border-radius:12px;display:flex;flex-direction:column;min-height:160px;transition:background .15s ease,border-color .15s ease}
.kb-col.kb-over{background:#e8f4fa;border-color:#0e7490}
.kb-col-head{display:flex;align-items:center;justify-content:space-between;padding:11px 13px;background:#fff;border-radius:11px 11px 0 0;border-bottom:1px solid #e2edf1}
.kb-col-title{font-size:12.5px;font-weight:700;color:#102a35;letter-spacing:.02em}
.kb-col-count{font-size:11px;font-weight:800;border-radius:999px;padding:1px 9px}
.kb-col-body{padding:10px;display:flex;flex-direction:column;gap:10px;flex:1}
.kb-empty{color:#9db2bb;font-size:12px;text-align:center;padding:16px 0}
.kb-card{background:#fff;border:1px solid #d5e3e8;border-radius:10px;padding:12px;box-shadow:0 1px 2px rgba(14,90,110,.05);transition:border-color .15s ease,box-shadow .15s ease,opacity .15s ease;cursor:grab}
.kb-card:hover{border-color:#0e7490;box-shadow:0 4px 12px -6px rgba(14,90,110,.35)}
.kb-card.kb-dragging{opacity:.45;cursor:grabbing}
.kb-card.kb-busy{opacity:.6;pointer-events:none}
.kb-open{display:block;text-decoration:none;color:#102a35}
.kb-open:hover{text-decoration:none;color:#102a35}
.kb-card-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}
.kb-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10.5px;background:#eef5f7;border:1px solid #d5e3e8;border-radius:5px;padding:1px 6px;color:#0b5d73}
.kb-due{font-size:10.5px;color:#5b6f78}
.kb-due.overdue{color:#dc2626;font-weight:700}
.kb-title{font-size:13.5px;font-weight:700;line-height:1.35;margin-bottom:4px}
.kb-client{font-size:12px;color:#5b6f78;margin-bottom:8px}
.kb-client i{width:14px;color:#9db2bb}
.kb-meta{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:9px}
.kb-tag{font-size:10.5px;background:#e0f2f8;color:#0b5d73;border-radius:999px;padding:2px 8px;font-weight:600}
.kb-foot{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #eef5f7;padding-top:8px}
.kb-amount{font-size:13px;font-weight:800;color:#0b5d73;font-family:'Bricolage Grotesque',sans-serif}
.kb-balance{font-size:11px;color:#d97706;font-weight:700}
.kb-paid{font-size:11px;color:#059669;font-weight:700}
/* The next-step button is the primary affordance on each card. */
.kb-next{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;margin-top:10px;
  padding:9px 12px;border:1px solid #0e7490;border-radius:8px;background:#fff;color:#0b5d73;
  font-size:12px;font-weight:700;cursor:pointer;min-height:38px}
.kb-next:hover{background:#0e7490;color:#fff}
.kb-next i{font-size:11px}
.kb-toast{position:fixed;left:50%;bottom:26px;transform:translateX(-50%);z-index:9999;
  background:#102a35;color:#fff;padding:11px 20px;border-radius:9px;font-size:13.5px;font-weight:600;
  box-shadow:0 8px 26px -8px rgba(0,0,0,.5);display:none}
.kb-toast.show{display:block}
@media(max-width:1024px){.kb-wrap{grid-template-columns:repeat(6,minmax(210px,1fr))}}
</style>

<div class="kb-toast" id="kb-toast"></div>

<script>
(function(){
  var CSRF_NAME = '<?= $CI->security->get_csrf_token_name() ?>';
  var CSRF_HASH = '<?= $CI->security->get_csrf_hash() ?>';
  var board = document.getElementById('print-board');
  if (!board) return;

  function toast(msg){
    var t = document.getElementById('kb-toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._h);
    t._h = setTimeout(function(){ t.classList.remove('show'); }, 2600);
  }

  /** Run a job's next action, or send the user to the job screen when the
   *  action needs input the board cannot supply (an amount, artwork, approver). */
  function advance(card){
    var action = card.dataset.next;
    var url = card.dataset.jobUrl;
    if (!action && url) { window.location.href = url; return; }

    card.classList.add('kb-busy');
    var fd = new FormData();
    fd.append('job_id', card.dataset.job);
    fd.append(CSRF_NAME, CSRF_HASH);
    fetch('<?= base_url('printing') ?>/' + action, {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (d && d.csrf_hash) CSRF_HASH = d.csrf_hash;
        if (d && d.success) { location.reload(); return; }
        card.classList.remove('kb-busy');
        toast((d && d.message) ? d.message : 'Could not advance this job.');
      })
      .catch(function(){ card.classList.remove('kb-busy'); toast('Request failed.'); });
  }

  // Clicking the card's button is the same transition as dropping it.
  board.querySelectorAll('.kb-next').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault(); e.stopPropagation();
      var card = btn.closest('.kb-card');
      if (btn.dataset.url) { window.location.href = btn.dataset.url; return; }
      card.dataset.next = btn.dataset.oneclick;
      advance(card);
    });
  });

  var dragged = null;
  board.querySelectorAll('.kb-card').forEach(function(card){
    card.addEventListener('dragstart', function(e){
      dragged = card;
      card.classList.add('kb-dragging');
      e.dataTransfer.effectAllowed = 'move';
      // Firefox needs data set for the drag to start at all.
      try { e.dataTransfer.setData('text/plain', card.dataset.job); } catch (err) {}
    });
    card.addEventListener('dragend', function(){
      card.classList.remove('kb-dragging');
      dragged = null;
    });
  });

  board.querySelectorAll('.kb-col').forEach(function(col){
    col.addEventListener('dragover', function(e){
      if (!dragged) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      col.classList.add('kb-over');
    });
    col.addEventListener('dragleave', function(){ col.classList.remove('kb-over'); });
    col.addEventListener('drop', function(e){
      e.preventDefault();
      col.classList.remove('kb-over');
      if (!dragged) return;

      var from = dragged.dataset.col;
      var to = col.dataset.col;
      if (from === to) return;

      // Columns are ordered gates: a job can only clear its own next gate, so a
      // drop anywhere other than the following column is rejected with a reason
      // rather than silently ignored.
      var order = <?= json_encode($col_keys) ?>;
      if (order.indexOf(to) !== order.indexOf(from) + 1) {
        toast('Jobs move one step at a time — clear "' + col.querySelector('.kb-col-title').textContent + '" first.');
        return;
      }
      advance(dragged);
    });
  });
})();
</script>
