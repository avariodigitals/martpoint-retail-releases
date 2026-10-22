<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="bm-ops">
  <header class="bo-head"><div><span class="bo-eyebrow">Your workspace directory</span><h1>More tools</h1><p>Find website controls, team access, reports and settings available to your role.</p></div></header>
  <div class="bo-search"><i class="fa fa-search" aria-hidden="true"></i><label class="sr-only" for="bm-tool-search">Find a tool</label><input type="search" id="bm-tool-search" placeholder="Search website, teams, reports, settings…" autocomplete="off"></div>
  <div class="bo-card-grid" data-tool-directory>
    <?php foreach($tool_groups as $name=>$tools): ?><section class="bo-card" data-tool-group><h2><?= htmlspecialchars($name); ?></h2><div class="bo-tool-list"><?php foreach($tools as $tool): ?><a data-tool data-search="<?= htmlspecialchars($name.' '.$tool['label'].' '.$tool['description'],ENT_QUOTES,'UTF-8'); ?>" href="<?= htmlspecialchars($tool['url'],ENT_QUOTES,'UTF-8'); ?>"><span><strong><?= htmlspecialchars($tool['label']); ?></strong><small><?= htmlspecialchars($tool['description']); ?></small></span><i class="fa fa-angle-right"></i></a><?php endforeach; ?></div></section><?php endforeach; ?>
  </div><p class="bo-empty" id="bm-tools-empty" role="status" hidden>No matching tools are available to your role.</p>
</div>
