<?php
$defaults = [
  ['title' => 'Quality Guaranteed', 'desc' => '100% authentic products'],
  ['title' => 'Fast Delivery', 'desc' => 'Quick shipping nationwide'],
  ['title' => 'Secure Payment', 'desc' => 'Multiple safe options'],
  ['title' => 'Expert Support', 'desc' => "We're here to help"]
];
$stored = json_decode($settings->trust_badges_json ?? '', true);
$trustItems = [];
$icons = [
  '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
  '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
  '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'
];
foreach($defaults as $idx => $d){
  $trustItems[] = [
    'icon' => $icons[$idx],
    'title' => !empty($stored[$idx]['title']) ? $stored[$idx]['title'] : $d['title'],
    'desc' => !empty($stored[$idx]['desc']) ? $stored[$idx]['desc'] : $d['desc']
  ];
}
?>
<div class="mp-trust-section">
  <div class="mp-section mp-trust-grid">
    <?php foreach($trustItems as $t): ?>
    <div class="mp-trust-item">
      <div class="mp-trust-icon"><?= $t['icon']; ?></div>
      <div class="mp-trust-text">
        <div class="mp-trust-title"><?= htmlspecialchars($t['title']); ?></div>
        <div class="mp-trust-desc"><?= htmlspecialchars($t['desc']); ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
