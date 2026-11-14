(function(root,factory){
  var number=factory();
  if(typeof module==='object'&&module.exports)module.exports=number;
  if(root)root.mpDocumentNumber=number;
})(typeof window!=='undefined'?window:null,function(){
  return function(value){
    var text=String(value==null?'':value).trim().replace(/,/g,'').replace(/[\s\u00a0\u202f]/g,'');
    if(text==='')return 0;
    var n=Number(text);return Number.isFinite(n)?n:0;
  };
});
