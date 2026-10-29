(function(root){
'use strict';
function sms(text){
 var basic='@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà',extended='\f^{}\\[~]|€',chars=Array.from(text),gsm=chars.every(function(c){return basic.includes(c)||extended.includes(c);});
 var widths=chars.map(function(c){return gsm?(extended.includes(c)?2:1):c.length;}),units=widths.reduce(function(a,b){return a+b;},0),single=gsm?160:70,multi=gsm?153:67,pages=1;
 if(units>single){var used=0;widths.forEach(function(w){if(used+w>multi){pages++;used=0;}used+=w;});}
 return {encoding:gsm?'GSM-7':'UCS-2',units:units,segments:pages,characters:chars.length};
}
root.MartPointSms=sms;if(typeof module==='object'&&module.exports)module.exports=sms;
})(typeof window==='object'?window:globalThis);
