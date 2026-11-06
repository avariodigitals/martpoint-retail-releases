<?php
/**
 * Outlet switcher — one control for every screen that scopes to a location.
 *
 * Usage:
 *   <?php $this->load->view('components/outlet_picker', [
 *           'opts'      => $branch_opts,        // [['id' => int, 'name' => string], …]
 *           'current'   => $branch,             // '' means every outlet
 *           'all_label' => $branch_all_label,
 *           'url_for'   => function ($id) { … },// href for an outlet id, '' for all
 *       ]); ?>
 *
 * A few outlets stay as one-click pills. Past a handful the pills grow wider
 * than the viewport and the row becomes unusable, so the same choices move
 * behind a search field: 30 outlets are all reachable without the header
 * stretching. Deliberately self-contained (its own style and script) so the
 * desktop workspace, the kitchen board and the phone screen all render the
 * identical control from the same file.
 */
$op_opts   = (array) ($opts ?? []);
$op_cur    = (string) ($current ?? '');
$op_all    = (string) ($all_label ?? 'All branches');
$op_key    = (string) ($label ?? 'Outlet');
$op_url    = $url_for ?? null;
$op_pills  = (int) ($max_pills ?? 4);
if (count($op_opts) < 2 || !is_callable($op_url)) return;

/* The chosen outlet's own name, when one is chosen. */
$op_cur_name = '';
foreach ($op_opts as $op_o) {
    if ((string) $op_o['id'] === $op_cur) { $op_cur_name = (string) $op_o['name']; break; }
}

$op_bead = !empty($GLOBALS['__mp_outlet_picker_css']);
if (!$op_bead) { $GLOBALS['__mp_outlet_picker_css'] = true; ?>
<style>
.ro-pick{--op-line:var(--ro-line,var(--mp-border,#E7E5E4));--op-surface:var(--ro-surface,var(--mp-surface,#fff));
  --op-ink:var(--ro-ink,var(--mp-text,#292524));--op-muted:var(--ro-muted,var(--mp-muted,#78716C));
  --op-brand:var(--ro-brand,var(--mp-primary,#5E4D66));position:relative;display:inline-flex;min-width:0;vertical-align:middle;}
.ro-pick-btn{display:inline-flex;align-items:center;gap:7px;min-width:0;padding:7px 12px;border:1px solid var(--op-line);
  border-radius:999px;background:var(--op-surface);font:inherit;font-size:12.5px;font-weight:700;color:#57534E;cursor:pointer;}
.ro-pick-btn:hover,.ro-pick.is-open .ro-pick-btn{border-color:var(--op-brand);color:var(--op-brand);}
.ro-pick-key{font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--op-muted);}
.ro-pick.is-open .ro-pick-key{color:var(--op-brand);}
.ro-pick-cur{max-width:220px;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--op-ink);}
.ro-pick.is-open .ro-pick-cur{color:var(--op-brand);}
.ro-pick-n{flex:0 0 auto;font-size:11px;font-weight:800;padding:1px 7px;border-radius:999px;background:var(--op-line);color:var(--op-muted);}
.ro-pick-caret{flex:0 0 auto;transition:transform .15s ease;}
.ro-pick.is-open .ro-pick-caret{transform:rotate(180deg);}
.ro-pick-pop{position:absolute;z-index:70;top:calc(100% + 7px);right:0;width:302px;max-width:calc(100vw - 24px);
  padding:8px;border:1px solid var(--op-line);border-radius:14px;background:var(--op-surface);color:var(--op-ink);
  box-shadow:0 14px 34px rgba(41,37,36,.16);text-align:left;}
.ro-pick-pop[hidden]{display:none;}
.ro-pick-search{display:flex;align-items:center;gap:7px;padding:7px 10px;margin-bottom:6px;border:1px solid var(--op-line);
  border-radius:10px;background:var(--op-surface);color:var(--op-muted);}
.ro-pick-search input{flex:1 1 auto;min-width:0;border:0;outline:0;background:transparent;font-size:13px;font-weight:600;color:var(--op-ink);}
.ro-pick-list{max-height:262px;overflow-y:auto;-webkit-overflow-scrolling:touch;}
.ro-pick-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:9px;font-size:13px;font-weight:700;
  color:var(--op-ink);text-decoration:none;}
.ro-pick-item:hover{background:#F5F5F4;color:var(--op-brand);}
.ro-pick-item.is-on{background:var(--op-brand);color:#fff;}
.ro-pick-item.is-filtered{display:none;}
.ro-pick-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ro-pick-note{margin-left:auto;flex:0 0 auto;font-size:11px;font-weight:700;color:var(--op-muted);}
.ro-pick-item.is-on .ro-pick-note{color:rgba(255,255,255,.8);}
.ro-pick-none{display:none;padding:14px 10px;font-size:12.5px;font-weight:600;color:var(--op-muted);text-align:center;}
.ro-pick-none.is-shown{display:block;}
/* Pills: the shape staff already know, kept for the stores that fit in it. */
.ro-pick-pills{display:inline-flex;align-items:center;gap:2px;padding:3px;background:var(--op-surface);
  border:1px solid var(--op-line);border-radius:999px;flex-wrap:wrap;}
.ro-pick-pill{padding:6px 13px;border-radius:999px;font-size:12.5px;font-weight:700;color:#57534E;text-decoration:none;
  white-space:nowrap;transition:background .14s ease,color .14s ease;}
.ro-pick-pill:hover{color:var(--op-brand);}
.ro-pick-pill.is-on{background:var(--op-brand);color:#fff;}
.ro-pick-pill-key{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--op-muted);margin-right:8px;padding-left:4px;}
@media (max-width:700px){.ro-pick-pop{position:fixed;left:12px;right:12px;top:auto;bottom:calc(14px + env(safe-area-inset-bottom,0px));width:auto;max-width:none;}}
</style>
<script>
/* Delegated, so the control works however many of them a page renders and no
   per-screen wiring is needed. Navigation is left to the links themselves. */
(function () {
  function panel(pick) { return pick.querySelector('.ro-pick-pop'); }
  function search(pick) { return pick.querySelector('.ro-pick-search input'); }
  function close(pick) {
    pick.classList.remove('is-open');
    var p = panel(pick);
    if (p) p.hidden = true;
    var trigger = pick.querySelector('.ro-pick-btn');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  }
  function closeAll(except) {
    [].forEach.call(document.querySelectorAll('.ro-pick.is-open'), function (pick) {
      if (pick !== except) close(pick);
    });
  }
  function open(pick) {
    var p = panel(pick);
    if (!p) return;
    pick.classList.add('is-open');
    p.hidden = false;
    var trigger = pick.querySelector('.ro-pick-btn');
    if (trigger) trigger.setAttribute('aria-expanded', 'true');
    var field = search(pick);
    if (field) { field.value = ''; filter(pick); field.focus(); }
  }
  function filter(pick) {
    var field = search(pick);
    var p = panel(pick);
    if (!field || !p) return;
    var term = field.value.trim().toLowerCase();
    var shown = 0;
    [].forEach.call(p.querySelectorAll('.ro-pick-item'), function (item) {
      var hit = term === '' || (item.getAttribute('data-name') || '').toLowerCase().indexOf(term) > -1;
      item.classList.toggle('is-filtered', !hit);
      if (hit) shown++;
    });
    var none = p.querySelector('.ro-pick-none');
    if (none) none.classList.toggle('is-shown', shown === 0);
  }
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest ? e.target.closest('.ro-pick-btn') : null;
    if (trigger) {
      var pick = trigger.closest('.ro-pick');
      if (pick.classList.contains('is-open')) { close(pick); return; }
      closeAll(pick);
      open(pick);
      e.preventDefault();
      return;
    }
    if (e.target.closest && e.target.closest('.ro-pick')) return;
    closeAll(null);
  });
  document.addEventListener('input', function (e) {
    if (e.target.matches && e.target.matches('.ro-pick-search input')) filter(e.target.closest('.ro-pick'));
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var pick = document.querySelector('.ro-pick.is-open');
    if (!pick) return;
    close(pick);
    var trigger = pick.querySelector('.ro-pick-btn');
    if (trigger) trigger.focus();
  });
})();
</script>
<?php } ?>

<?php if (count($op_opts) <= $op_pills): ?>
  <div class="ro-pick-pills" role="group" aria-label="<?= htmlspecialchars('Choose ' . strtolower($op_key)); ?>">
    <span class="ro-pick-pill-key"><?= htmlspecialchars($op_key); ?></span>
    <a class="ro-pick-pill <?= $op_cur === '' ? 'is-on' : ''; ?>" href="<?= htmlspecialchars($op_url('')); ?>"><?= htmlspecialchars($op_all); ?></a>
    <?php foreach ($op_opts as $op_o): ?>
      <a class="ro-pick-pill <?= (string) $op_o['id'] === $op_cur ? 'is-on' : ''; ?>"
         href="<?= htmlspecialchars($op_url((string) (int) $op_o['id'])); ?>"><?= htmlspecialchars($op_o['name']); ?></a>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="ro-pick">
    <button type="button" class="ro-pick-btn" aria-expanded="false" aria-haspopup="true">
      <span class="ro-pick-key"><?= htmlspecialchars($op_key); ?></span>
      <span class="ro-pick-cur"><?= htmlspecialchars($op_cur_name !== '' ? $op_cur_name : $op_all); ?></span>
      <span class="ro-pick-n"><?= (int) (count($op_opts)); ?></span>
      <svg class="ro-pick-caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="ro-pick-pop" hidden>
      <label class="ro-pick-search">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16.7" y2="16.7"/></svg>
        <input type="text" autocomplete="off" placeholder="Find an outlet" aria-label="Find an outlet">
      </label>
      <div class="ro-pick-list">
        <a class="ro-pick-item <?= $op_cur === '' ? 'is-on' : ''; ?>" data-name="<?= htmlspecialchars($op_all); ?>"
           href="<?= htmlspecialchars($op_url('')); ?>">
          <span class="ro-pick-name"><?= htmlspecialchars($op_all); ?></span>
          <span class="ro-pick-note"><?= (int) count($op_opts); ?> outlets</span>
        </a>
        <?php foreach ($op_opts as $op_o): ?>
          <a class="ro-pick-item <?= (string) $op_o['id'] === $op_cur ? 'is-on' : ''; ?>"
             data-name="<?= htmlspecialchars($op_o['name']); ?>"
             href="<?= htmlspecialchars($op_url((string) (int) $op_o['id'])); ?>">
            <span class="ro-pick-name"><?= htmlspecialchars($op_o['name']); ?></span>
          </a>
        <?php endforeach; ?>
        <div class="ro-pick-none">No outlet matches that name.</div>
      </div>
    </div>
  </div>
<?php endif; ?>
