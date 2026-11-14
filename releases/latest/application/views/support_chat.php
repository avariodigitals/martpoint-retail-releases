<?php
/**
 * Support Chat — MartPoint merchant live chat with support team.
 * Messages are proxied to Next.js /api/azera/* and support replies from
 * the Next.js admin console at /admin/(protected)/azera.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title); ?> — MartPoint</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  *{box-sizing:border-box}
  body{margin:0;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#EEF2F7;color:#111827}
  .mp-header{background:#fff;border-bottom:1px solid #E5E7EB;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
  .mp-back{display:flex;align-items:center;gap:8px;color:#0057FF;text-decoration:none;font-weight:600;font-size:14px}
  .mp-title{font-size:18px;font-weight:700}
  .mp-subtitle{font-size:12px;color:#6B7280}
  #app{max-width:560px;margin:0 auto;padding:0;min-height:0}
  .mp-store-bar{background:#fff;border:1px solid #E5E7EB;border-radius:8px;padding:12px 16px;margin-bottom:24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
  .mp-store-info{flex:1;min-width:0}
  .mp-store-name{font-weight:700;font-size:14px;color:#111827}
  .mp-store-id{font-size:12px;color:#6B7280}
  .mp-status-chip{font-size:11px;padding:2px 8px;border-radius:10px;background:#DCFCE7;color:#166534;font-weight:600}
  .mp-messages{background:#fff;border:1px solid #E5E7EB;border-radius:16px;height:min(680px,calc(100vh - 190px));min-height:420px;overflow:hidden;display:flex;flex-direction:column;margin-bottom:12px;box-shadow:0 18px 45px rgba(15,23,42,.12)}
  .mp-messages-header{padding:14px 16px;border-bottom:1px solid #E5E7EB;background:#F9FAFB}
  .mp-messages-title{font-weight:700;font-size:14px}
  .mp-messages-sub{font-size:12px;color:#6B7280}
  .mp-list{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;background:#F3F4F6}
  .mp-msg{max-width:80%;display:flex;flex-direction:column}
  .mp-msg.outbound{align-self:flex-end}
  .mp-msg.inbound{align-self:flex-start}
  .mp-bubble{padding:10px 14px;border-radius:10px;font-size:14px;line-height:1.5;word-break:break-word}
  .mp-msg.outbound .mp-bubble{background:#0057FF;color:#fff;border-bottom-right-radius:2px}
  .mp-msg.inbound .mp-bubble{background:#fff;color:#111827;border:1px solid #E5E7EB;border-bottom-left-radius:2px}
  .mp-meta{font-size:11px;color:#9CA3AF;margin-top:4px;display:flex;align-items:center;gap:6px}
  .mp-msg.outbound .mp-meta{margin-left:auto}
  .mp-msg-role{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.8}
  .mp-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6B7280}
  .mp-empty i{font-size:32px;opacity:0.4;margin-bottom:8px}
  .mp-composer{border-top:1px solid #E5E7EB;padding:14px 16px;background:#fff;position:sticky;bottom:0}
  .mp-composer-inner{display:flex;gap:8px}
  #msg-input{flex:1;min-height:44px;border:1px solid #D1D5DB;border-radius:8px;padding:10px 14px;font-size:14px;resize:none;font-family:inherit}
  #msg-input:focus{outline:none;border-color:#0057FF}
  .mp-send-btn{min-width:44px;height:44px;border:none;border-radius:8px;background:#0057FF;color:#fff;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center}
  .mp-send-btn:disabled{opacity:0.5;cursor:not-allowed}
  .mp-toast{position:fixed;top:20px;left:50%;transform:translateX(-50%);padding:10px 16px;border-radius:8px;color:#fff;font-size:14px;font-weight:600;z-index:1000;opacity:0;transition:opacity 0.3s}
  .mp-toast.show{opacity:1}
  .mp-toast.success{background:#166534}
  .mp-toast.error{background:#DC2626}
  .mp-typing{font-size:12px;color:#6B7280;font-style:italic;margin-bottom:8px}
  .mp-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#E5E7EB;color:#4B5563;font-weight:600;font-size:12px}
  .mp-msg.outbound .mp-avatar{align-self:flex-end;background:#0057FF;color:#fff}
  .mp-msg-row{display:flex;align-items:flex-end;gap:8px}
  .mp-msg.outbound .mp-msg-row{flex-direction:row-reverse}
  .mp-msg-content{position:relative}
  @media(max-width:640px){body{padding:12px 8px}.mp-header{border-radius:12px 12px 0 0;padding:12px 14px}.mp-msg{max-width:90%}.mp-messages{height:calc(100vh - 150px);min-height:360px}}
</style>
</head>
<body>

<div class="mp-header">
  <a href="<?= base_url('dashboard'); ?>" class="mp-back"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
  <div>
    <div class="mp-title"><?= htmlspecialchars($page_title); ?></div>
    <div class="mp-subtitle">Chat with MartPoint Support Team</div>
  </div>
</div>

<div id="app">
  <div class="mp-store-bar">
    <div class="mp-store-info">
      <div class="mp-store-name"><?= $storeName; ?></div>
      <div class="mp-store-id">Store ID: <?= $storeId; ?> · Session: <?= substr($externalId, -8); ?></div>
    </div>
    <span class="mp-status-chip"><i class="fa fa-circle" style="font-size:6px"></i> Connected</span>
  </div>

  <div class="mp-messages">
    <div class="mp-messages-header">
      <div class="mp-messages-title">Support Conversation</div>
      <div class="mp-messages-sub">Messages are sent to MartPoint support. Replies appear here.</div>
    </div>
    <div class="mp-list" id="msg-list">
      <div class="mp-empty">
        <i class="fa fa-message"></i>
        <div>No messages yet. Start the conversation below.</div>
      </div>
    </div>
    <div class="mp-typing" id="typing-indicator" style="display:none">Support is typing…</div>
  </div>

  <div class="mp-composer">
    <div class="mp-composer-inner">
      <textarea id="msg-input" placeholder="Type your message to support…" rows="1"></textarea>
      <button class="mp-send-btn" id="send-btn" onclick="sendMessage()"><i class="fa fa-paper-plane"></i></button>
    </div>
  </div>
</div>

<div id="toast" class="mp-toast"></div>

<script>
  const storeId = <?= (int)$storeId; ?>;
  const externalId = '<?= $externalId; ?>';
  const merchantName = '<?= $merchantName; ?>';
  const merchantEmail = '<?= $merchantEmail; ?>';
  const baseUrl = '<?= base_url(); ?>';
  const csrfName = window.csrfName || <?= json_encode($this->security->get_csrf_token_name()); ?>;
  const csrfHash = window.csrfHash || <?= json_encode($this->security->get_csrf_hash()); ?>;

  const msgList = document.getElementById('msg-list');
  const msgInput = document.getElementById('msg-input');
  const sendBtn = document.getElementById('send-btn');
  const toast = document.getElementById('toast');

  let polling = false;

  function showToast(msg, type='success'){
    toast.textContent = msg;
    toast.className = 'mp-toast show ' + (type === 'error' ? 'error' : 'success');
    setTimeout(()=>{ toast.classList.remove('show'); }, 3000);
  }

  function scrollBottom(){
    msgList.scrollTop = msgList.scrollHeight;
  }

  function escapeHtml(text){
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
  }

  function renderMessage(msg){
    const isOut = msg.direction === 'outbound';
    const author = msg.author_type === 'agent' ? 'Support Agent' :
                   msg.author_type === 'ai' ? 'Azera AI' : 'You';
    const roleClass = msg.author_type === 'agent' ? 'support-agent' :
                      msg.author_type === 'ai' ? 'ai-agent' : 'merchant';
    const time = new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    const html = escapeHtml(msg.message);

    const el = document.createElement('div');
    el.className = `mp-msg ${isOut ? 'outbound' : 'inbound'}`;
    el.innerHTML = `
      <div class="mp-msg-row">
        ${!isOut ? `<div class="mp-avatar">${msg.author_type === 'agent' ? 'S' : msg.author_type === 'ai' ? 'A' : 'M'}</div>` : ''}
        <div class="mp-msg-content">
          <div class="mp-meta"><span class="mp-msg-role ${roleClass}">${author}</span><span>${time}</span></div>
          <div class="mp-bubble">${html}</div>
        </div>
        ${isOut ? `<div class="mp-avatar">M</div>` : ''}
      </div>
    `;
    return el;
  }

  async function loadMessages(){
    try {
      const res = await fetch(baseUrl + 'support/chat/messages?store_id=' + storeId + '&external_id=' + encodeURIComponent(externalId));
      const json = await res.json();
      const payload = json.data || json;
      if(json.success && payload.messages){
        msgList.innerHTML = '';
        if(payload.messages.length === 0){
          const empty = document.createElement('div');
          empty.className = 'mp-empty';
          empty.innerHTML = '<i class="fa fa-message"></i><div>No messages yet. Start the conversation below.</div>';
          msgList.appendChild(empty);
        } else {
          payload.messages.forEach(m => msgList.appendChild(renderMessage(m)));
        }
        scrollBottom();
      } else if(json.error || json.message) {
        showToast(json.error || json.message || 'Unable to load messages', 'error');
      }
    } catch(e){
      showToast('Unable to connect to support chat. Please try again later.', 'error');
    }
  }

  async function sendMessage(){
    const text = msgInput.value.trim();
    if(!text) return;
    sendBtn.disabled = true;
    msgInput.disabled = true;

    // Optimistic render
    const tempEl = document.createElement('div');
    tempEl.className = 'mp-msg outbound';
    tempEl.innerHTML = `
      <div class="mp-msg-row">
        <div class="mp-msg-content">
          <div class="mp-meta"><span class="mp-msg-role merchant">You</span><span>now</span></div>
          <div class="mp-bubble">${escapeHtml(text)}</div>
        </div>
        <div class="mp-avatar">M</div>
      </div>
    `;
    // Replace empty state if present
    const existingEmpty = msgList.querySelector('.mp-empty');
    if(existingEmpty) existingEmpty.remove();
    msgList.appendChild(tempEl);
    scrollBottom();
    msgInput.value = '';

    try {
      const res = await fetch(baseUrl + 'support/chat/send', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfHash,
        },
        body: JSON.stringify({
          store_id: storeId,
          external_id: externalId,
          customer_name: merchantName,
          message: text,
          [csrfName]: csrfHash,
        }),
      });
      const json = await res.json();
      if(!json.success){
        tempEl.remove();
        msgInput.value = text;
        showToast(json.message || json.error || 'Failed to send', 'error');
      } else {
        showToast('Message sent');
        // Refresh to get real message IDs and any replies
        setTimeout(loadMessages, 800);
      }
    } catch(e){
      tempEl.remove();
      msgInput.value = text;
      showToast('Network error', 'error');
    } finally {
      sendBtn.disabled = false;
      msgInput.disabled = false;
      msgInput.focus();
    }
  }

  msgInput.addEventListener('keydown', function(e){
    if(e.key === 'Enter' && !e.shiftKey){
      e.preventDefault();
      sendMessage();
    }
  });

  // Auto-resize textarea
  msgInput.addEventListener('input', function(){
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });

  // Poll for new messages every 5 seconds
  async function poll(){
    if(polling) return;
    polling = true;
    try {
      const res = await fetch(baseUrl + 'support/chat/messages?store_id=' + storeId + '&external_id=' + encodeURIComponent(externalId));
      if(!res.ok) { polling = false; return; }
      const json = await res.json();
      const payload = json.data || json;
      if(json.success && payload.messages && payload.messages.length > 0){
        const last = payload.messages[payload.messages.length - 1];
        const existing = msgList.querySelector('.mp-msg:last-child');
        // Only add if it's a new message we haven't rendered
        if(existing && existing.textContent.includes(last.message)){ polling = false; return; }
        // Re-render full list to stay in sync
        loadMessages();
      }
    } catch(e){ /* ignore poll errors */ }
    polling = false;
  }

  setInterval(poll, 5000);

  // Initial load
  loadMessages();
</script>

</body>
</html>
