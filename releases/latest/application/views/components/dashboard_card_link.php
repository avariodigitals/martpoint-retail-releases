<?php
$cardHref=mp_dashboard_detail_link($card_metric, !empty($card_mobile), $card_dates ?? array());
if($cardHref): ?>
<a class="mp-kpi-detail-link" href="<?= htmlspecialchars($cardHref,ENT_QUOTES,'UTF-8'); ?>" aria-label="<?= htmlspecialchars($card_link_label ?? 'View details',ENT_QUOTES,'UTF-8'); ?>"><span class="sr-only"><?= htmlspecialchars($card_link_label ?? 'View details',ENT_QUOTES,'UTF-8'); ?></span></a>
<?php endif; ?>
