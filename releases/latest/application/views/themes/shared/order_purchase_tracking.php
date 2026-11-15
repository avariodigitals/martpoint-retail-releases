<?php
  // Paid-conversion tracking — standalone page, so the consent gate and
  // pixel loaders are self-contained here. purchase fires ONLY when the
  // order's payment_status is 'paid' (verified server-side), never for
  // unpaid / pay-on-delivery / WhatsApp orders.
  $mpNeedConsent = ((int)($settings->require_tracking_consent ?? 1) === 1);
  $mpConsentKey  = 'mp_track_consent_' . (int)($settings->store_id ?? 0);
  $mpGaId = trim((string)($settings->google_analytics_id ?? ''));
  $mpFbId = trim((string)($settings->facebook_pixel_id ?? ''));
  $mpTtId = trim((string)($settings->tiktok_pixel_id ?? ''));
  $mpPaid = ($order->payment_status === 'paid');
?>
<script>
(function(){
  var PAID = <?= $mpPaid ? 'true' : 'false'; ?>;
  var NEED = <?= $mpNeedConsent ? 'true' : 'false'; ?>;
  var KEY  = <?= json_encode($mpConsentKey); ?>;
  var EID  = <?= json_encode('purchase_' . $order->order_code); ?>;
  var VAL  = <?= (float)($order->transaction_grand_total ?? $order->grand_total); ?>;
  var TXC  = <?= json_encode($orderCurrency); ?>;
  var ok = function(){ if(!NEED) return true; try{return localStorage.getItem(KEY)==='yes';}catch(e){return false;} };
  var fired = function(){ try{return localStorage.getItem('mp_purch_' + EID)==='1';}catch(e){return false;} };
  var markFired = function(){ try{localStorage.setItem('mp_purch_' + EID,'1');}catch(e){} };
  if(!PAID || !ok() || fired()) return;
  markFired();
  var fire = function(){
    try{
      if(window.gtag) gtag('event','purchase',{value:VAL,currency:TXC,transaction_id:<?= json_encode($order->order_code); ?>});
      if(window.fbq) fbq('track','Purchase',{value:VAL,currency:TXC},{eventID:EID});
      if(window.ttq) ttq.track('CompletePayment',{value:VAL,currency:TXC,event_id:EID});
      // Server record — deduped by event_id against the payment-transition record.
      var b = new URLSearchParams({store_id:<?= (int)$order->store_id; ?>,event:'purchase',event_id:EID,value:VAL,consent:'1',page:window.location.pathname});
      fetch('<?= base_url('storefront/track_event'); ?>',{method:'POST',body:b.toString(),headers:{'Content-Type':'application/x-www-form-urlencoded'},keepalive:true}).catch(function(){});
    }catch(e){}
  };
  var pending = 0, done = false;
  var go = function(){ if(done) return; done = true; fire(); };
  var load = function(src){ pending++; var s=document.createElement('script'); s.async=true; s.onload=function(){ if(--pending<=0) go(); }; s.onerror=function(){ if(--pending<=0) go(); }; s.src=src; document.head.appendChild(s); };
  <?php if($mpGaId !== ''): ?>
  load('https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($mpGaId); ?>');
  window.dataLayer=window.dataLayer||[];window.gtag=function(){dataLayer.push(arguments);};gtag('js',new Date());gtag('config','<?= htmlspecialchars($mpGaId); ?>');
  <?php endif; ?>
  <?php if($mpFbId !== ''): ?>
  load('https://connect.facebook.net/en_US/fbevents.js');
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];}(window,document,'script');
  fbq('init','<?= htmlspecialchars($mpFbId); ?>');
  <?php endif; ?>
  <?php if($mpTtId !== ''): ?>
  load('https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=<?= htmlspecialchars($mpTtId); ?>&lib=ttq');
  window.TiktokAnalyticsObject='ttq';var ttq=window.ttq=window.ttq||[];ttq.methods=['track'];ttq.track=ttq.track||function(){ttq.push(arguments);};ttq._i=ttq._i||{};
  <?php endif; ?>
  setTimeout(go, 2500); // fire even if a pixel CDN is slow/blocked
})();
</script>
