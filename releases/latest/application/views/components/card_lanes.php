<?php
/**
 * Balanced card lanes — spreads a set of content cards evenly across the
 * width: no empty grid holes, no cards stretched to the tallest in a row.
 *
 * Usage:
 *   <?php $this->load->view('components/card_lanes'); ?>
 *   <div class="mp-lanes">
 *     ... cards ...
 *   </div>
 *
 * Every direct child of .mp-lanes becomes one card in the balanced columns.
 * Full-width items (banners, referral panels, wide tables) stay outside the
 * .mp-lanes container, stacked above or below it.
 */
if(!empty($GLOBALS['__mp_card_lanes_loaded'])) return;
$GLOBALS['__mp_card_lanes_loaded'] = true;
?>
<style>
.mp-lanes{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:14px;align-items:start}
.mp-lane{flex:1 1 0;min-width:0;display:flex;flex-direction:column;gap:14px}
</style>
<script>
(function(){
  function layout(grid){
    if(!grid.__mpCards) grid.__mpCards = [].slice.call(grid.children);
    var cards = grid.__mpCards;
    if(!cards.length) return;
    var w = grid.clientWidth;
    var n = w >= 1180 ? 3 : (w >= 700 ? 2 : 1);
    n = Math.min(n, cards.length);
    if(grid.getAttribute('data-lanes') === String(n)) return;
    grid.innerHTML = '';
    grid.style.display = 'flex';
    grid.style.alignItems = 'flex-start';
    var cols = [], h = [], i;
    for(i = 0; i < n; i++){
      var lane = document.createElement('div');
      lane.className = 'mp-lane';
      grid.appendChild(lane);
      cols.push(lane);
      h.push(0);
    }
    cards.forEach(function(card){
      var m = 0;
      for(var j = 1; j < n; j++) if(h[j] < h[m]) m = j;
      cols[m].appendChild(card);
      h[m] += card.offsetHeight + 14;
    });
    grid.setAttribute('data-lanes', String(n));
  }
  function run(){
    [].slice.call(document.querySelectorAll('.mp-lanes')).forEach(layout);
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
  var t;
  window.addEventListener('resize', function(){
    clearTimeout(t);
    t = setTimeout(run, 150);
  });
})();
</script>
