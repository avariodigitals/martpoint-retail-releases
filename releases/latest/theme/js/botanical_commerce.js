/* Theme interactions only. Catalogue, authentication and orders use shared endpoints. */
(function(){
'use strict';
var detail=document.querySelector('[data-be-detail]');
if(detail){
 var product=JSON.parse(detail.dataset.beDetail),qty=1,needsVariant=detail.dataset.variant==='1',backorder=detail.dataset.backorder==='1';
 var feedback=document.getElementById('be-detail-feedback'),qtyValue=document.getElementById('detail-qty'),add=document.getElementById('detail-add-btn');
 function physical(){return product.type==='physical'||product.type==='product';}
 function imageUrl(value){try{var u=new URL(value,detail.dataset.base);return /^https?:$/.test(u.protocol)?u.href:'';}catch(e){return '';}}
 function refresh(){
  if(qtyValue)qtyValue.textContent=qty;
  if(add){var out=physical()&&product.stock<=0&&!backorder;add.disabled=out;add.textContent=out?'Out of stock':(detail.dataset.addLabel||'Add to bag')+' +';}
 }
 document.querySelectorAll('[data-be-quantity]').forEach(function(button){button.addEventListener('click',function(){
  var next=Math.max(1,qty+Number(button.dataset.beQuantity));
  if(physical()&&!backorder&&next>Number(product.stock)){feedback.textContent='Only '+product.stock+' available.';return;}
  qty=next;feedback.textContent='';refresh();
 });});
 document.addEventListener('sf:variant-picked',function(event){var v=event.detail;if(!v)return;
  product=Object.assign({},product,{id:v.id,name:v.name,price:Number(v.price),image:v.image,stock:Number(v.stock)});qty=1;
  var img=document.getElementById('detail-img'),url=imageUrl(v.image);if(img&&url){img.src=url;img.alt=v.name;}
  document.getElementById('detail-price').textContent=formatMoney(Number(v.price));
  document.getElementById('be-detail-availability').textContent=physical()&&v.stock<=0&&!backorder?'Currently out of stock':'Available to order';
  feedback.textContent='Selected: '+v.name;refresh();
 });
 if(add)add.addEventListener('click',function(){
  if(needsVariant&&!window.sfPickedVariant){feedback.textContent='Choose an option before adding this piece.';var picker=document.querySelector('.sf-variant-picker');if(picker)picker.scrollIntoView({block:'center',behavior:'smooth'});return;}
  if(addToCart(product.id,product.type,product.name,product.price,product.image,qty,product.stock)!==false){feedback.textContent='Added to your bag.';}
 });
}
var auth=document.querySelector('[data-be-auth]');
if(auth){
 var contact=document.getElementById('be-auth-contact'),codeForm=document.getElementById('be-auth-code'),phoneFields=auth.querySelector('[data-be-phone]'),emailFields=auth.querySelector('[data-be-email]');
 var method='phone',busy=false,cooldown=0,timer=null;
 var phone=document.getElementById('be-auth-phone'),name=document.getElementById('be-auth-name'),email=document.getElementById('be-auth-email'),otp=document.getElementById('be-auth-otp');
 var send=document.getElementById('be-auth-send'),verify=document.getElementById('be-auth-continue'),resend=document.getElementById('be-auth-resend'),change=document.getElementById('be-auth-change');
 var contactError=document.getElementById('be-auth-contact-error'),codeError=document.getElementById('be-auth-code-error'),description=document.getElementById('be-auth-description');
 function controls(disabled){busy=disabled;send.disabled=disabled;verify.disabled=disabled;resend.disabled=disabled||cooldown>0;change.disabled=disabled;auth.setAttribute('aria-busy',String(disabled));auth.querySelectorAll('[data-be-method]').forEach(function(b){b.disabled=disabled;});}
 function body(){var data={method:method};if(method==='email'){data.name=name.value.trim();data.email=email.value.trim();}else data.phone=phone.value.trim();return data;}
 function post(url,data){var payload=new URLSearchParams(data);if(auth.dataset.csrfName)payload.append(auth.dataset.csrfName,auth.dataset.csrfHash);return fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:payload.toString()}).then(function(response){return response.json();}).then(function(result){if(result.csrf_hash)auth.dataset.csrfHash=result.csrf_hash;return result;});}
 function startCooldown(){cooldown=60;if(timer)clearInterval(timer);resend.disabled=true;resend.textContent='Send again in '+cooldown+'s';timer=setInterval(function(){cooldown--;resend.textContent=cooldown>0?'Send again in '+cooldown+'s':'Send a new code';if(cooldown<=0){clearInterval(timer);resend.disabled=busy;}},1000);}
 function requestCode(){if(busy)return;contactError.textContent='';codeError.textContent='';var data=body();
  if(method==='phone'&&data.phone.length<7){contactError.textContent='Enter your phone number to continue.';phone.focus();return;}
  if(method==='email'&&(!data.name||!email.validity.valid||!data.email)){contactError.textContent='Enter your name and a valid email address.';return;}
  controls(true);send.textContent='Sending your code…';
  post(auth.dataset.send,data).then(function(result){if(!result.status)throw new Error(result.message||'We could not send a code. Please try again.');
   contact.hidden=true;codeForm.hidden=false;description.textContent='Enter the code sent to '+(method==='email'?data.email:data.phone)+'.';otp.focus();startCooldown();
  }).catch(function(error){(codeForm.hidden?contactError:codeError).textContent=error.message||'Connection interrupted. Please try again.';}).finally(function(){controls(false);send.textContent='Send my code ↗';});
 }
 auth.querySelectorAll('[data-be-method]').forEach(function(button){button.addEventListener('click',function(){if(busy)return;method=button.dataset.beMethod;auth.querySelectorAll('[data-be-method]').forEach(function(b){b.setAttribute('aria-pressed',String(b===button));});phoneFields.hidden=method!=='phone';emailFields.hidden=method!=='email';phone.disabled=method!=='phone';phone.required=method==='phone';name.disabled=email.disabled=method!=='email';name.required=email.required=method==='email';contactError.textContent='';});});
 contact.addEventListener('submit',function(event){event.preventDefault();requestCode();});
 resend.addEventListener('click',function(){if(cooldown===0)requestCode();});
 change.addEventListener('click',function(){if(busy)return;contact.hidden=false;codeForm.hidden=true;otp.value='';codeError.textContent='';description.textContent=auth.dataset.intro||'Sign in with a one-time code. No password to remember.';(method==='phone'?phone:email).focus();});
 codeForm.addEventListener('submit',function(event){event.preventDefault();if(busy)return;codeError.textContent='';if(!/^\d{6}$/.test(otp.value.trim())){codeError.textContent='Enter the six-digit code from your message.';return;}
  controls(true);verify.textContent='Signing you in…';var data=body();data.otp=otp.value.trim();
  post(auth.dataset.verify,data).then(function(result){if(!result.status)throw new Error(result.message||'The code did not match. Please try again.');window.location.assign(auth.dataset.account);}).catch(function(error){codeError.textContent=error.message||'Connection interrupted. Please try again.';}).finally(function(){controls(false);verify.textContent='Sign in ↗';});
 });
}
})();
