<script>
(function(){function screen(){if(document.activeElement && document.activeElement.matches('input,textarea,[contenteditable=true]'))return;var mobile=window.matchMedia('(max-width:768px), (max-width:1024px) and (orientation:portrait)').matches;var url=new URL(location.href);if(mobile!==(url.searchParams.get('screen')==='mobile')){if(mobile)url.searchParams.set('screen','mobile');else url.searchParams.delete('screen');location.replace(url.toString());}}screen();window.addEventListener('resize',screen);})();
</script>
