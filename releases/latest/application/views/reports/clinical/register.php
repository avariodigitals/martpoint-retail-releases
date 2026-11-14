<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Patient register — size, growth, gender and age profile. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$max = !empty($r['gender']) ? max($r['gender']) : 0;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/register'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Patients on register', number_format($r['total']), 'all records, including deceased');
  cr_kpi('Active', number_format($r['active']), 'available for new episodes', array('alert' => false));
  cr_kpi('New in period', number_format($r['new']), date('d M Y', strtotime($from)) . ' — ' . date('d M Y', strtotime($to)));
  cr_kpi('Deceased', number_format($r['deceased']), 'retained for record, excluded from care');
  ?>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Gender profile (active patients)</h3>
    <table class="cr-table">
      <tbody>
      <?php if(empty($r['gender'])): ?>
        <tr><td class="cr-empty">No active patients.</td></tr>
      <?php else: foreach($r['gender'] as $label => $n): ?>
        <tr>
          <td style="width:38%"><?= htmlspecialchars($label); ?></td>
          <td class="num" style="width:14%"><?= number_format($n); ?></td>
          <td><?= cr_bar($max > 0 ? 100 * $n / $max : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Age profile (active patients)</h3>
    <table class="cr-table">
      <tbody>
      <?php
      $maxAge = !empty($r['age_bands']) ? max($r['age_bands']) : 0;
      if(empty($r['age_bands'])): ?>
        <tr><td class="cr-empty">No date of birth recorded.</td></tr>
      <?php else: foreach($r['age_bands'] as $label => $n): ?>
        <tr>
          <td style="width:38%"><?= htmlspecialchars($label); ?></td>
          <td class="num" style="width:14%"><?= number_format($n); ?></td>
          <td><?= cr_bar($maxAge > 0 ? 100 * $n / $maxAge : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="cr-panel">
  <h3>New registrations by month</h3>
  <table class="cr-table">
    <thead><tr><th>Month</th><th class="num">New patients</th><th style="width:40%">Share</th></tr></thead>
    <tbody>
    <?php if(empty($r['monthly'])): ?>
      <tr><td colspan="3" class="cr-empty">No registrations in this period.</td></tr>
    <?php else: $mMax = max($r['monthly']); foreach($r['monthly'] as $ym => $n): ?>
      <tr>
        <td><?= htmlspecialchars(date('F Y', strtotime($ym . '-01'))); ?></td>
        <td class="num"><?= number_format($n); ?></td>
        <td><?= cr_bar($mMax > 0 ? 100 * $n / $mMax : null); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
