<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2>Intelligence Report</h2>
      <div class="mp-page-sub"><?= htmlspecialchars($intel_subtitle ?? 'Insights based on your store records.'); ?></div>
    </div>
    <a href="<?= base_url('dashboard'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Dashboard</a>
  </div>

  <?php $this->load->view('comman/intelligence_card', [
    'intel' => $intel ?? [],
    'intel_title' => 'Current insights',
    'intel_subtitle' => $intel_subtitle ?? '',
    'intel_empty_message' => 'There are no evidence-based insights to report yet. New insights appear as more sales, stock, payment, or job history becomes available.',
  ]); ?>
</div>
