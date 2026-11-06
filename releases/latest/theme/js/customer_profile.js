(function($, window, document){
  'use strict';

  function fitIdCards(){
    $('.id-card-fit').each(function(){
      var card = this.querySelector('.id-card-preview');
      if(!card) return;
      // Older customer profile markup uses an absolutely positioned fixed-size
      // card. Newer layouts keep it in normal flow and need no transform.
      if(window.getComputedStyle(card).position !== 'absolute') return;
      var scale = Math.min(1, this.clientWidth / 324);
      card.style.transform = 'translateX(-50%) scale(' + scale + ')';
      this.style.height = (204 * scale) + 'px';
    });
  }

  window.printCard = function(elId){
    var el = document.getElementById(elId);
    if(!el) return;
    var clone = el.cloneNode(true);
    clone.removeAttribute('style');
    clone.removeAttribute('id');
    var win = window.open('', '_blank');
    if(!win) return;
    win.document.write('<html><head><title>ID Card</title><style>');
    win.document.write('body{margin:0;padding:20px;background:#f5f5f5;text-align:center;}');
    win.document.write('.id-card-preview{width:324px;height:204px;border-radius:12px;position:relative;overflow:hidden;background:linear-gradient(135deg,#fdfbf7 0%,#f5f0e8 100%);border:1px solid #e0d5c5;margin:0 auto;-webkit-print-color-adjust:exact;print-color-adjust:exact;}');
    win.document.write('.id-card-brand{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:42px;font-weight:900;color:#8b7355;opacity:0.06;letter-spacing:2px;text-transform:uppercase;white-space:nowrap;pointer-events:none;user-select:none;}');
    win.document.write('.id-card-barcode{text-align:center;margin-top:8px;}.id-card-barcode img{height:42px;}');
    win.document.write('.id-card-body{padding:20px 16px 16px;text-align:center;}.id-card-name{font-size:18px;font-weight:700;color:#3d3229;margin-bottom:4px;}');
    win.document.write('.id-card-phone{font-size:14px;color:#6b5b4f;margin-bottom:8px;}.id-card-id{font-size:11px;color:#9e8e7e;letter-spacing:2px;font-family:monospace;}');
    win.document.write('.id-card-footer{position:absolute;bottom:0;left:0;right:0;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,.5);border-top:1px solid rgba(0,0,0,.05);}');
    win.document.write('.id-card-signature{font-size:10px;color:#8b7355;font-style:italic;}.id-card-expiry{font-size:10px;color:#3d3229;font-weight:600;}');
    win.document.write('</style></head><body>' + clone.outerHTML + '</body></html>');
    win.document.close();
    setTimeout(function(){ win.print(); win.close(); }, 300);
  };

  window.downloadCard = function(elementId, filename){
    var el = document.getElementById(elementId);
    if(!el || typeof window.html2canvas !== 'function') return;
    var prevTransform = el.style.transform;
    el.style.transform = 'none';
    window.html2canvas(el, {scale:2, useCORS:true, backgroundColor:null}).then(function(canvas){
      el.style.transform = prevTransform;
      var link = document.createElement('a');
      link.download = filename;
      link.href = canvas.toDataURL('image/png');
      link.click();
    }).catch(function(){ el.style.transform = prevTransform; });
  };

  $(window).on('resize', fitIdCards);
  $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', fitIdCards);

  $(function(){
    fitIdCards();
    if($.fn.DataTable && $('#purchaseTable').length && !$.fn.DataTable.isDataTable('#purchaseTable')){
      var purchaseTable = $('#purchaseTable').DataTable({
        paging:true, lengthChange:false, searching:true, ordering:true, info:true, autoWidth:false
      });
      $(document).on('shown.bs.tab', 'a[href="#purchases"]', function(){ purchaseTable.columns.adjust(); });
    }

    $('.mp-nav-item').removeClass('active');
    $('.customers_list-active-li').addClass('active').closest('.mp-nav-group').addClass('open');
    if(window.location.hash){
      var $tabLink = $('a[href="' + window.location.hash + '"][data-toggle="tab"]');
      if($tabLink.length && $.fn.tab) $tabLink.tab('show');
    }
  });

  $(document).on('click', '.note-edit-toggle', function(){
    var $form = $(this).closest('tr').find('.note-edit-form');
    $form.toggle();
    if($form.is(':visible')) $form.find('textarea').focus();
  });
})(jQuery, window, document);
