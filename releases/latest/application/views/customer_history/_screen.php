<script>
(function(){
 var renderedMobile=<?= !empty($history_mobile)?'true':'false'; ?>;
 function sync(){
  if(!/^https?:$/.test(location.protocol)||location.pathname.indexOf('/customer_history/')===-1)return;
  var mobile=innerWidth<=768||(innerWidth<=1024&&innerHeight>=innerWidth);
  if(mobile!==renderedMobile){var u=new URL(location.href);u.searchParams.set('screen',mobile?'mobile':'desktop');location.replace(u.href);}
 }
 sync();var timer;addEventListener('resize',function(){clearTimeout(timer);timer=setTimeout(sync,180);});
})();
</script>
