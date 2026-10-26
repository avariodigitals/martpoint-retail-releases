<?php
// Navigation preset picker. Card-per-preset with a hidden radio (the form's
// real input) plus a live preview of the side menu that preset would give this
// store — built by the same helper that renders the real rail, so the preview
// cannot lie about what saving would do.
$preset_icons = [
    'quick_service'  => 'fa-bolt',
    'casual_dining'  => 'fa-cutlery',
    'delivery_only'  => 'fa-truck',
    'multi_location' => 'fa-map-marker',
];
?>
<?= $this->load->view('restaurantos/_assets', [], TRUE); ?>

<div class="ro-wrap ro-stack">
  <div class="ro-head">
    <div>
      <h2>Restaurant Setup</h2>
      <div class="ro-head-sub">
        Pick how this outlet's navigation is arranged — the screens stay the same,
        the menu follows your operating model.
      </div>
    </div>
    <div class="ro-head-tools">
      <a href="<?= base_url('restaurantos'); ?>" class="ro-act slate" style="padding:8px 14px;">
        <span class="ic"><i class="fa fa-dashboard"></i></span><span class="tx"><b>Overview</b></span>
      </a>
    </div>
  </div>

  <?= form_open('restaurantos/settings', ['id' => 'ro-preset-form']); ?>
  <div class="ro-setgrid">
    <div class="ro-presets">
      <?php foreach ($presets as $key => $p): ?>
        <label class="ro-preset">
          <input type="radio" name="nav_preset" value="<?= htmlspecialchars($key); ?>"
            <?= $current === $key ? 'checked' : '' ?> data-preset="<?= htmlspecialchars($key); ?>">
          <span class="ro-preset-body">
            <span class="ttl">
              <i class="fa <?= $preset_icons[$key] ?? 'fa-th-large'; ?>" style="color:var(--mp-primary,#0057FF);"></i>
              <?= htmlspecialchars($p['label']); ?>
              <?php if ($current === $key): ?><span class="now">Current</span><?php endif; ?>
            </span>
            <span class="desc"><?= htmlspecialchars($p['description']); ?></span>
            <span class="flags">
              <span class="flag <?= !empty($p['tables']) ? 'on' : ''; ?>">
                <?= !empty($p['tables']) ? '✓' : '—'; ?> Tables &amp; Reservations
              </span>
              <span class="flag <?= !empty($p['outlets']) ? 'on' : ''; ?>">
                <?= !empty($p['outlets']) ? '✓' : '—'; ?> Outlets
              </span>
              <span class="flag on">✓ Walk In · Kitchen · Menu · Reports</span>
            </span>
          </span>
        </label>
      <?php endforeach; ?>
    </div>

    <!-- Live preview: swaps to the picked preset's real destination list -->
    <div class="ro-panel">
      <div class="ro-panel-head">
        <div>
          <div class="t"><i class="fa fa-bars"></i> Your side menu</div>
          <div class="s" id="ro-preview-sub">Showing the layout as it stands today</div>
        </div>
      </div>
      <div class="ro-panel-body tight">
        <?php foreach ($presets as $key => $p): ?>
          <div class="ro-preview" data-preview="<?= htmlspecialchars($key); ?>"
               style="<?= $current === $key ? '' : 'display:none;' ?>">
            <?php if (empty($previews[$key])): ?>
              <div class="ro-empty"><i class="fa fa-user-times"></i>
                <div class="t">Nothing for this role</div>
                <div class="s">Your user can't open the restaurant screens, so this layout would show an empty menu to you.</div>
              </div>
            <?php else: ?>
              <div class="ro-preview-list">
                <?php foreach ($previews[$key] as $item): ?>
                  <span class="ro-preview-item">
                    <i class="fa fa-arrow-right"></i><?= htmlspecialchars($item['label']); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($promises[$key])): ?>
              <div style="padding:0 10px 10px;">
                <?php foreach ($promises[$key] as $held): ?>
                  <div class="ro-banner warn" style="margin-top:8px;">
                    <span class="bi"><i class="fa fa-lock"></i></span>
                    <span class="bb"><?= $held; ?></span>
                    <a class="bl" href="<?= base_url('business_profile'); ?>">Turn on &rarr;</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="ro-save">
    <button type="submit" class="ro-btn"><i class="fa fa-check"></i> Save layout</button>
    <span class="ro-kpi-note">Applies to the side menu and the Overview shortcuts for everyone in this store.</span>
  </div>
  <?= form_close(); ?>
</div>

<script>
(function () {
  var labels = <?= json_encode(array_map(function ($p) { return $p['label']; }, $presets)) ?>;
  var current = <?= json_encode($current) ?>;
  var form = document.getElementById('ro-preset-form');
  if (!form) return;
  form.addEventListener('change', function (ev) {
    var t = ev.target;
    if (!t || t.name !== 'nav_preset') return;
    var key = t.value;
    var panels = form.querySelectorAll('[data-preview]');
    for (var i = 0; i < panels.length; i++) {
      panels[i].style.display = panels[i].getAttribute('data-preview') === key ? '' : 'none';
    }
    var sub = document.getElementById('ro-preview-sub');
    if (sub) {
      sub.innerHTML = key === current
        ? 'Showing the layout as it stands today'
        : 'Preview of the <b>' + (labels[key] || key) + '</b> layout — not saved yet';
    }
  });
})();
</script>
