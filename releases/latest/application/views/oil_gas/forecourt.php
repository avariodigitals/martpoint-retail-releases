<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
$duty_pending_here = 0;
foreach($duties_pending as $pending) if((int)$pending->warehouse_id === (int)$site_id) $duty_pending_here++;
// History feeds read as people, not accounts: first name only, never a username.
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
?>

<div class="gas-hero">
  <div>
    <h2>Forecourt &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>What each tank holds, what the pumps measured, what the till rang up, and what a dip says is really left. The till and the stock ledger already count the sales; this page only proves the day, it never books litres twice.</p>
  </div>
  <div class="gas-hero-actions">
    <?php if($duty): ?><a href="<?= base_url('oil_gas/duty?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-play-circle"></i> Finish the open duty</a>
    <?php else: ?><a href="<?= base_url('oil_gas/duty?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-play-circle"></i> Open a duty</a><?php endif; ?>
    <a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-database"></i> Tanks</a>
    <a href="<?= base_url('oil_gas/pumps?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-tachometer"></i> Pumps</a>
    <?php if(is_store_admin() || is_admin()): ?><a href="<?= base_url('oil_gas/forecourt_settings'); ?>" class="gas-btn"><i class="fa fa-sliders"></i> Loss limits</a><?php endif; ?>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/forecourt?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi <?= $duty ? 'watch' : ''; ?>"><strong><?= $duty ? 'Open' : 'Closed'; ?></strong><span>Duty at this <?= htmlspecialchars(strtolower($site_label)); ?></span><small><?= $duty ? 'Started ' . show_date($duty->opened_at) . ' by ' . htmlspecialchars($who($duty->opened_by_name)) : 'No duty is running. Open one before the first sale is handed over.'; ?></small></div>
  <div class="gas-kpi"><strong><?= count($tanks); ?></strong><span>Tanks registered here</span><small><?= count($pumps); ?> pump<?= count($pumps) === 1 ? '' : 's'; ?> read on a duty</small></div>
  <div class="gas-kpi <?= $summary['gaps'] ? 'alert' : ''; ?>"><strong><?= (int)$summary['gaps']; ?></strong><span>Dips waiting for a manager</span><small>Over the <?= mp_format_percent($tolerance_dip); ?> you allow on a tank</small></div>
  <div class="gas-kpi <?= $summary['awaiting'] ? 'alert' : ''; ?>"><strong><?= (int)$summary['awaiting']; ?></strong><span>Duties waiting for a manager</span><small>Over the <?= mp_format_percent($tolerance_duty); ?> you allow between pumps and till</small></div>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head">
      <h3>What the tanks hold</h3>
      <a class="mp-qa-btn" href="<?= base_url('oil_gas/tanks?site='.(int)$site_id); ?>">Manage tanks</a>
    </div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($tanks)): ?>
        <p class="gas-empty">No tanks are registered at this <?= htmlspecialchars(strtolower($site_label)); ?> yet. Add the ground tanks so the litres that arrive and the litres that leave have somewhere to be counted.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="width:26%">Tank</th>
              <th style="width:16%">Book litres</th>
              <th style="width:16%">Last dip</th>
              <th style="width:16%">Difference</th>
              <th style="width:26%">Work</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($tanks as $tank):
              $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0,'dip'=>null,'gap'=>null);
              $pct = (float)$tank->capacity_litres > 0 ? min(100, $pos['book'] / (float)$tank->capacity_litres * 100) : 0;
            ?>
            <tr>
              <td>
                <div class="loc-name"><?= htmlspecialchars($tank->tank_name); ?></div>
                <div class="loc-sub">Holds <?= mp_format_litres($tank->capacity_litres); ?> L · fills to <?= mp_format_litres($tank->safe_fill_litres); ?> L</div>
              </td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($pos['book']); ?></span> L
                <div class="gas-bar <?= $pos['book'] > (float)$tank->safe_fill_litres ? 'over' : ''; ?>"><i style="width:<?= (int)round($pct); ?>%"></i></div>
              </td>
              <td class="gas-qty">
                <?= $pos['dip'] === null ? '—' : mp_format_litres($pos['dip']).' L'; ?>
                <?php if($pos['dip_date']): ?><div class="loc-sub">dipped <?= show_date($pos['dip_date']); ?></div><?php endif; ?>
              </td>
              <td class="gas-qty">
                <?php if($pos['gap'] === null): ?><span class="text-muted">—</span>
                <?php elseif(abs($pos['gap']) < 0.0001): ?><span class="gas-status ok">matches</span>
                <?php else: ?>
                  <span class="<?= $pos['gap'] < 0 ? 'text-danger' : 'text-warning'; ?>"><?= ($pos['gap'] > 0 ? '+' : '').mp_format_litres($pos['gap']); ?> L</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= base_url('oil_gas/fill?tank='.(int)$tank->id); ?>" class="gas-status open" style="text-decoration:none!important;margin-right:6px;">Fill</a>
                <a href="<?= base_url('oil_gas/tanks?site='.(int)$site_id.'#dip-'.(int)$tank->id); ?>" class="gas-status transit" style="text-decoration:none!important;">Dip</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Needs a manager</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($duties_pending) && empty($dip_gaps)): ?>
        <p class="gas-empty">Nothing is waiting. Every duty matched its till, and every dip came in inside the loss you allow.</p>
      <?php else: ?>
      <div style="padding:4px 14px 14px;">
        <?php foreach($duties_pending as $pending): ?>
          <div class="mp-card-form" style="margin-top:12px;padding:14px;">
            <strong><?= htmlspecialchars($pending->shift_code ?: 'Duty #'.(int)$pending->id); ?></strong>
            <div class="loc-sub">Closed <?= show_date($pending->closed_at); ?> by <?= htmlspecialchars($who($pending->closed_by_name)); ?></div>
            <div class="gas-qty" style="margin-top:8px;">
              Pumps measured <?= mp_format_litres($pending->litres_metered); ?> L · till rang <?= mp_format_litres($pending->litres_till); ?> L
            </div>
            <div class="gas-qty text-danger"><?= ($pending->variance_litres > 0 ? '' : '+') . mp_format_litres($pending->variance_litres); ?> L unexplained
              <span class="loc-sub">(<?= mp_format_percent($pending->variance_percent); ?>, over your <?= mp_format_percent($tolerance_duty); ?>)</span>
            </div>
            <?php if($can_decide): ?>
            <?= form_open(base_url('oil_gas/decide')); ?>
            <input type="hidden" name="kind" value="duty">
            <input type="hidden" name="target_id" value="<?= (int)$pending->id; ?>">
            <input type="hidden" name="site_id" value="<?= (int)$pending->warehouse_id; ?>">
            <div class="mp-form-group" style="margin-top:10px;">
              <label>Your note <span class="text-danger">*</span></label>
              <textarea class="mp-form-control" name="decision_note" rows="2" required placeholder="Accepted: the pumps run 0.4% rich on this product."></textarea>
            </div>
            <div class="mp-form-actions" style="margin-top:10px;">
              <button type="submit" name="decision" value="approve" class="mp-btn-primary"><i class="fa fa-thumbs-up"></i> Accept</button>
              <button type="submit" name="decision" value="reject" class="mp-btn-secondary"><i class="fa fa-thumbs-down"></i> Reopen the duty</button>
            </div>
            <?= form_close(); ?>
            <?php else: ?>
            <p class="gas-note">A manager or the store owner must settle this before the duty is final.</p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php foreach($dip_gaps as $gap): ?>
          <div class="mp-card-form" style="margin-top:12px;padding:14px;">
            <strong><?= htmlspecialchars($gap->tank_name); ?></strong>
            <div class="loc-sub">Dipped <?= show_date($gap->dip_date); ?> by <?= htmlspecialchars($who($gap->created_by)); ?></div>
            <div class="gas-qty" style="margin-top:8px;">
              Book <?= mp_format_litres($gap->book_litres); ?> L · dip <?= mp_format_litres($gap->level_litres); ?> L
            </div>
            <div class="gas-qty <?= $gap->gap < 0 ? 'text-danger' : 'text-warning'; ?>">
              <?= ($gap->gap > 0 ? '+' : '') . mp_format_litres($gap->gap); ?> L
              <span class="loc-sub">(<?= mp_format_percent($gap->gap_percent); ?> of capacity, over your <?= mp_format_percent($tolerance_dip); ?>)</span>
            </div>
            <?php if($can_decide): ?>
            <?= form_open(base_url('oil_gas/decide')); ?>
            <input type="hidden" name="kind" value="dip">
            <input type="hidden" name="target_id" value="<?= (int)$gap->dip_id; ?>">
            <input type="hidden" name="site_id" value="<?= (int)$gap->warehouse_id; ?>">
            <div class="mp-form-group" style="margin-top:10px;">
              <label>Your note <span class="text-danger">*</span></label>
              <textarea class="mp-form-control" name="decision_note" rows="2" required placeholder="Written off: weep at the filler valve, seen on the last two loads."></textarea>
            </div>
            <div class="mp-form-actions" style="margin-top:10px;">
              <button type="submit" name="decision" value="accept" class="mp-btn-primary"><i class="fa fa-thumbs-up"></i> Adjust the tank book</button>
              <button type="submit" name="decision" value="reject" class="mp-btn-secondary"><i class="fa fa-thumbs-down"></i> Write off nothing</button>
            </div>
            <?= form_close(); ?>
            <?php else: ?>
            <p class="gas-note">Nothing has been written off. A manager decides whether the tank book follows the dip.</p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Recent duties at this <?= htmlspecialchars(strtolower($site_label)); ?></h3><a class="mp-qa-btn" href="<?= base_url('oil_gas/duty?site='.(int)$site_id); ?>">Duty sheet</a></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($duty_done)): ?>
      <p class="gas-empty">No duty has been closed here yet. Closing a duty is what compares the pump meters with the till, so a day is only proved once somebody reads the meters.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Duty</th><th>Litres measured</th><th>Till</th><th>Difference</th><th>Result</th></tr></thead>
        <tbody>
          <?php foreach($duty_done as $past): ?>
          <tr>
            <td><strong><?= htmlspecialchars($past->shift_code ?: 'Duty'); ?></strong><div class="loc-sub"><?= show_date($past->opened_at); ?><?= $past->closed_at ? ' → ' . show_date($past->closed_at) : ''; ?></div><div class="loc-sub"><?= htmlspecialchars($who($past->opened_by_name)); ?></div></td>
            <td class="gas-qty"><?= mp_format_litres($past->litres_metered); ?> L</td>
            <td class="gas-qty"><?= mp_format_litres($past->litres_till); ?> L</td>
            <td class="gas-qty <?= $past->approval_status === 'pending' ? 'text-danger' : ''; ?>"><?= mp_format_litres($past->variance_litres); ?> L</td>
            <td>
              <?php if($past->status === 'open'): ?><span class="gas-status open">running</span>
              <?php elseif($past->approval_status === 'pending'): ?><span class="gas-status held">waiting for a manager</span>
              <?php elseif($past->approval_status === 'approved'): ?><span class="gas-status held">signed off</span>
              <?php elseif(abs((float)$past->variance_litres) < 0.0001): ?><span class="gas-status ok">clean</span>
              <?php else: ?><span class="gas-status ok">inside tolerance</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  $(function(){ $('.oil_gas-forecourt-active-li').addClass('active'); });
</script>
