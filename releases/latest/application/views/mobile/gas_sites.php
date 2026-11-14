<?php
/** Move between the metered locations on a mobile fuel screen. */
?>
<div class='sites'>
  <?php foreach($sites as $site): ?>
    <a class='site-chip <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>'
       href='<?= base_url('oil_gas/' . $gas_route . '?site=' . (int)$site->id); ?>'><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
