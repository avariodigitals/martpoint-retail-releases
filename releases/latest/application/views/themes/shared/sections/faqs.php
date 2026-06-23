<?php $faqList = $faqs ?? []; ?>
<?php if(!empty($faqList)): ?>
<div class="mp-section">
  <div class="mp-section-title">Frequently Asked Questions</div>
  <div style="max-width:800px; margin:0 auto;">
    <?php foreach($faqList as $idx => $f): ?>
    <div style="border-bottom:1px solid var(--mp-border);">
      <button onclick="toggleFaq(<?= $idx; ?>)" style="width:100%; text-align:left; padding:16px 0; background:none; border:none; font-size:15px; font-weight:600; color:var(--mp-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
        <span><?= htmlspecialchars($f->question); ?></span>
        <span id="faq-icon-<?= $idx; ?>" style="transition:transform .2s; color:var(--mp-gray);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
      </button>
      <div id="faq-ans-<?= $idx; ?>" style="max-height:0; overflow:hidden; transition:max-height .3s ease;">
        <div style="padding-bottom:16px; font-size:14px; color:#334155; line-height:1.7;"><?= nl2br(htmlspecialchars($f->answer)); ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
function toggleFaq(idx){
  const ans = document.getElementById('faq-ans-' + idx);
  const icon = document.getElementById('faq-icon-' + idx);
  const isOpen = ans.style.maxHeight && ans.style.maxHeight !== '0px';
  document.querySelectorAll('[id^="faq-ans-"]').forEach(function(el){ el.style.maxHeight = '0px'; });
  document.querySelectorAll('[id^="faq-icon-"]').forEach(function(el){ el.style.transform = 'rotate(0deg)'; });
  if(!isOpen){
    ans.style.maxHeight = ans.scrollHeight + 'px';
    icon.style.transform = 'rotate(180deg)';
  }
}
</script>
<?php endif; ?>
