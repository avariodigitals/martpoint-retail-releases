<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="bm-ops">
  <header class="bo-head"><div><span class="bo-eyebrow">MartPoint intelligence</span><h1>Action Centre</h1><p>Decisions from your live records, grouped by the work they affect.</p></div><a class="bo-button" href="<?= base_url('dashboard'); ?>">Overview <i class="fa fa-arrow-right"></i></a></header>
  <div class="bo-card-grid">
    <?php foreach($action_groups as $name=>$insights): ?>
    <section class="bo-card bo-intelligence"><div class="bo-section-head"><h2><?= htmlspecialchars($name); ?></h2><span class="bo-pill"><?= count($insights); ?> to review</span></div>
      <?php if(!$insights): ?><p class="bo-empty">No attention signals found in the available records.</p><?php else: foreach($insights as $insight): ?>
      <article class="bo-signal"><h3><?= htmlspecialchars($insight['text']); ?></h3><?php if(!empty($insight['detail'])): ?><p><?= htmlspecialchars($insight['detail']); ?></p><?php endif; ?><?php if(!empty($insight['action'])): ?><a href="<?= htmlspecialchars($insight['action']['url'],ENT_QUOTES,'UTF-8'); ?>"><?= htmlspecialchars($insight['action']['label']); ?> <i class="fa fa-arrow-right"></i></a><?php endif; ?></article>
      <?php endforeach; endif; ?>
    </section><?php endforeach; ?>
  </div>
  <?php if(!$action_groups): ?><section class="bo-card bo-empty">Your current role has no operational records available to review.</section><?php endif; ?>
</div>
