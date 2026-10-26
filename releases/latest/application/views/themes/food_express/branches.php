<?php
$branches = $branches ?? [];
$fxStoreName = $store->store_name ?? 'Restaurant';
$fxBranchCount = count($branches);
?>
<main class="fx-branches-page">
  <section class="fx-branches-hero">
    <div class="fx-branches-hero-inner">
      <div class="fx-branches-hero-copy">
        <div class="fx-section-overline">Pickup + delivery</div>
        <h1>Meet us where you are.</h1>
        <p>Choose a <?= htmlspecialchars($fxStoreName); ?> location to see the menu, pickup options and delivery coverage for that area.</p>
      </div>
      <div class="fx-branches-hero-mark" aria-hidden="true">
        <span class="fx-branches-hero-mark-ring"></span>
        <span class="fx-branches-hero-mark-pin"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
        <span class="fx-branches-hero-mark-label">Your nearest table<br><strong>starts here</strong></span>
      </div>
    </div>
  </section>

  <section class="fx-branches-list" aria-labelledby="fx-branches-title">
    <div class="fx-branches-list-head">
      <div>
        <div class="fx-section-overline">Choose a location</div>
        <h2 id="fx-branches-title"><?= $fxBranchCount ? $fxBranchCount . ' ' . ($fxBranchCount === 1 ? 'kitchen' : 'kitchens') . ' serving you' : 'Choose your kitchen'; ?></h2>
      </div>
      <a class="fx-view-all" href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">View the menu <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>

    <?php if(!empty($branches)): ?>
    <div class="fx-branches-grid" role="list">
      <?php $loopIndex = 1; foreach($branches as $branch):
        $branchId = (int)($branch->id ?? 0);
        $branchName = (string)($branch->warehouse_name ?? $branch->name ?? $fxStoreName);
        $branchAddress = trim(implode(', ', array_filter([(string)($branch->address ?? ''), (string)($branch->city ?? ''), (string)($branch->state ?? '')])));
        $branchPhone = preg_replace('/[^+0-9]/', '', (string)($branch->mobile ?? $branch->phone ?? ''));
      ?>
      <article class="fx-branch-card" data-outlet-id="<?= $branchId; ?>" role="listitem">
        <div class="fx-branch-index" aria-hidden="true"><?= str_pad((string)$loopIndex, 2, '0', STR_PAD_LEFT); ?></div>
        <div class="fx-branch-card-main">
          <div class="fx-branch-card-top">
            <span class="fx-branch-pin"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            <span class="fx-branch-status">Open for orders</span>
          </div>
          <h3><?= htmlspecialchars($branchName); ?></h3>
          <address><?= htmlspecialchars($branchAddress ?: 'Location details available at checkout.'); ?></address>
        </div>
        <div class="fx-branch-actions">
          <button type="button" class="fx-card-add" onclick="chooseFxBranch(<?= $branchId; ?>, this)">Choose this kitchen <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          <?php if($branchPhone): ?><a href="tel:<?= $branchPhone; ?>" class="fx-branch-phone" aria-label="Call <?= htmlspecialchars($branchName); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z"/></svg></a><?php endif; ?>
        </div>
      </article>
      <?php $loopIndex++; endforeach; ?>
    </div>
    <?php else: ?>
    <div class="fx-branches-empty">
      <h2>One menu. Your nearest kitchen.</h2>
      <p>No restaurant location has been published for this store yet. Browse the menu and we will confirm the best fulfilment point at checkout.</p>
      <a class="fx-card-add" href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">View the menu</a>
    </div>
    <?php endif; ?>
  </section>
</main>

<script>
function chooseFxBranch(branchId, button){
  localStorage.setItem('sf_outlet_' + STORE_ID, String(branchId));
  document.querySelectorAll('.fx-branch-card').forEach(card => card.classList.toggle('active', Number(card.dataset.outletId) === Number(branchId)));
  if(button) button.textContent = 'Location selected';
  setTimeout(function(){ window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>'; }, 260);
}
</script>
