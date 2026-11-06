<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
// Only operations the company itself runs can be offered to a location.
$og_available = array();
foreach (($switches ?? array()) as $key => $question) {
    if (mp_feature_enabled($key)) $og_available[$key] = $question;
}
$og_locations = is_array($locations ?? null) ? $locations : array();
$og_labels    = function_exists('mp_get_feature_flags') ? mp_get_feature_flags() : array();
?>
<style>
.og-page-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap}
.og-page-head h2{margin:0 0 6px;font-size:22px;font-weight:800}.og-page-head p{margin:0;color:var(--mp-muted);font-size:12px;max-width:760px;line-height:1.5}
.og-savebar{position:sticky;top:0;z-index:3;background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:12px;padding:12px 16px;display:flex;gap:12px;align-items:center;margin-bottom:16px}
.og-savebar p{margin:0;flex:1;color:var(--mp-muted);font-size:12px;line-height:1.4}
.og-loc-card{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;margin-bottom:14px;overflow:hidden}
.og-loc-card>header{padding:15px 18px;border-bottom:1px solid var(--mp-border);display:flex;justify-content:space-between;gap:12px;align-items:baseline;flex-wrap:wrap}
.og-loc-card>header b{font-size:15px}.og-loc-card>header .meta{font-size:11px;color:var(--mp-muted)}
.og-q{display:flex;gap:11px;align-items:flex-start;padding:13px 18px;border-bottom:1px solid var(--mp-border)}
.og-q:last-child{border-bottom:0}.og-q input{margin-top:2px;width:16px;height:16px;flex:0 0 16px}
.og-q label{font-size:13px;line-height:1.4;cursor:pointer}.og-q small{display:block;color:var(--mp-muted);font-size:11px;margin-top:3px}
.og-chips{display:flex;flex-wrap:wrap;gap:6px;padding:14px 18px}.og-chip{font-size:11px;font-weight:700;padding:5px 9px;border-radius:999px;background:#e8f4f7;color:#0f4a5c}.og-chip.off{background:#f1f5f9;color:#64748b;text-decoration:line-through}
.og-empty{padding:20px;color:var(--mp-muted);font-size:12px;line-height:1.55;background:var(--mp-surface);border:1px dashed var(--mp-border);border-radius:14px}
.og-note{margin:0 0 14px;padding:12px 15px;border-radius:12px;background:#f0f9ff;color:#0c4a6e;border:1px solid #bae6fd;font-size:12px;line-height:1.5}
</style>

<div class="og-page-head">
  <div>
    <h2>Operating Locations</h2>
    <p>Tick the operations each site runs. Anything not listed here is a capability the business has not switched on, so it cannot be turned on for a single location.</p>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('oil_gas'); ?>"><i class="fa fa-arrow-left"></i> Workspace overview</a>
</div>

<?php if(!$og_available): ?>
  <div class="og-empty">No fuel operations are switched on for this business yet. Open <a href="<?= base_url('business_profile/capabilities'); ?>">Capabilities</a> and turn on the operations the company runs — gas refills and cylinders, tanks and pumps, station shifts, bulk dispatch, or supply and service jobs. Then come back here to say which location runs each one.</div>
  <?php return; ?>
<?php endif; ?>

<?php if(!$og_locations): ?>
  <div class="og-empty">No operating locations are set up. Add each station, gas outlet, depot or shop under <a href="<?= base_url('warehouse'); ?>">Branches</a> first.</div>
  <?php return; ?>
<?php endif; ?>

<?php if(!$can_edit): ?>
  <div class="og-note">You can see each location's operations, but not change them. Ask a Store Admin to adjust them.</div>
<?php endif; ?>

<?php if($can_edit): ?><form method="post" action="<?= base_url('oil_gas/save_locations'); ?>" accept-charset="utf-8">
  <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name(),ENT_QUOTES,'UTF-8'); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash(),ENT_QUOTES,'UTF-8'); ?>">
  <div class="og-savebar">
    <p>Saving keeps every record already made. Switching an operation off here only stops the site from using it going forward.</p>
    <button type="submit" class="mp-qa-btn"><i class="fa fa-check"></i> Save location operations</button>
  </div>
<?php endif; ?>

<?php foreach($og_locations as $loc):
  $figures = isset($rollup[(int)$loc->id]) ? $rollup[(int)$loc->id] : null;
  $site_flags = function_exists('mp_get_location_flags') ? mp_get_location_flags($loc->id) : array();
  // A site with no saved answer runs whatever the company runs; only an
  // explicit off switches an operation back off for this location.
  $og_site_on = function($key) use ($site_flags) {
      return !array_key_exists($key, $site_flags) || filter_var($site_flags[$key], FILTER_VALIDATE_BOOLEAN);
  };
  $og_meta = array();
  $town = trim((string)$loc->city);
  if($town !== '') $og_meta[] = $town;
  $og_meta[] = $figures ? (int)$figures['stock_lines'].' stock line'.((int)$figures['stock_lines'] === 1 ? '' : 's').' holding quantity' : 'no stock counted here yet';
  if($figures && $figures['arrivals_waiting']) $og_meta[] = (int)$figures['arrivals_waiting'].' load'.($figures['arrivals_waiting'] === 1 ? '' : 's').' waiting to be counted in';
  if($figures && $figures['arrivals_held']) $og_meta[] = (int)$figures['arrivals_held'].' held for a manager';
  if($figures && $figures['dispatches_month']) $og_meta[] = (int)$figures['dispatches_month'].' sent this month';
?>
  <div class="og-loc-card">
    <header>
      <b><?= htmlspecialchars($loc->warehouse_name !== '' ? $loc->warehouse_name : 'Unnamed location'); ?><?= !empty($loc->is_distribution_center) ? ' · depot' : ''; ?></b>
      <span class="meta"><?= htmlspecialchars(implode(' · ', $og_meta)); ?></span>
    </header>
    <?php if($can_edit): ?>
      <?php foreach($og_available as $key => $question): $on = $og_site_on($key); ?>
        <div class="og-q">
          <input type="hidden" name="location[<?= (int)$loc->id; ?>][<?= htmlspecialchars($key); ?>]" value="0">
          <input type="checkbox" id="cap-<?= (int)$loc->id; ?>-<?= htmlspecialchars($key); ?>" name="location[<?= (int)$loc->id; ?>][<?= htmlspecialchars($key); ?>]" value="1" <?= $on ? 'checked' : ''; ?>>
          <label for="cap-<?= (int)$loc->id; ?>-<?= htmlspecialchars($key); ?>"><?= htmlspecialchars($question); ?><small><?= htmlspecialchars($og_labels[$key] ?? $key); ?></small></label>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="og-chips">
        <?php foreach($og_available as $key => $question): ?>
          <span class="og-chip <?= $og_site_on($key) ? '' : 'off'; ?>"><?= htmlspecialchars($question); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if($can_edit): ?></form><?php endif; ?>
