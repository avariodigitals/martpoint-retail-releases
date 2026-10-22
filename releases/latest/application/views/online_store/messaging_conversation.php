<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.mc-back{margin-bottom:14px;}
.mc-back a{color:var(--mp-primary);text-decoration:none;font-size:13px;}
.mc-back a i{margin-right:6px;}
.mc-meta{display:flex;gap:16px;margin-bottom:16px;padding:12px 16px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);font-size:13px;}
.mc-meta div{display:flex;flex-direction:column;}
.mc-meta .k{font-size:11px;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;}
.mc-meta .v{color:var(--mp-ink);font-weight:600;}
.mc-channel-whatsapp{color:#25d366;}
.mc-channel-instagram{color:#bc2a8d;}
.mc-channel-messenger{color:#0084ff;}
.mc-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.02em;}
.mc-badge-open{background:rgba(16,185,129,.1);color:var(--mp-success);}
.mc-badge-closed{background:rgba(220,38,38,.1);color:var(--mp-danger);}

.mc-messages{height:430px;overflow-y:auto;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-bg);padding:12px;display:flex;flex-direction:column;gap:10px;margin-bottom:14px;}
.mc-msg{max-width:75%;display:flex;flex-direction:column;}
.mc-msg.outbound{align-self:flex-end;}
.mc-msg.inbound{align-self:flex-start;}
.mc-bubble{padding:8px 12px;border-radius:10px;font-size:13px;line-height:1.45;word-break:break-word;}
.mc-msg.outbound .mc-bubble{background:var(--mp-primary);color:#fff;border-bottom-right-radius:2px;}
.mc-msg.inbound .mc-bubble{background:#fff;color:var(--mp-ink);border:1px solid var(--mp-border);border-bottom-left-radius:2px;}
.mc-msg .meta{font-size:11px;color:var(--mp-muted);margin-top:3px;}
.mc-msg.outbound .meta{margin-left:auto;}
.mc-msg.inbound .meta{margin-right:auto;}

.mc-compose{border-top:1px solid var(--mp-border);padding:12px 16px;background:var(--mp-surface);display:flex;gap:10px;}
.mc-compose textarea{flex:1;min-height:44px;border:1px solid var(--mp-border);border-radius:8px;padding:8px 12px;font-size:13px;resize:vertical;font-family:inherit;}
.mc-compose button{padding:10px 18px;}
.ms-empty{padding:40px 20px;text-align:center;color:var(--mp-muted);}
</style>

<div class="mp-page-head">
  <div class="mc-back">
    <a href="<?= base_url('online_store/messaging?channel=' . $conversation->channel); ?>"><i class="fa fa-arrow-left"></i> Back to inbox</a>
  </div>
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Conversation with <?= htmlspecialchars($conversation->customer_name ?: $conversation->customer_phone ?: 'Unknown customer'); ?>.</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-comment"></i> Conversation details</h3>
  </div>
  <div class="mp-card-body">
    <div class="mc-meta">
      <div><span class="k">Customer</span><span class="v"><?= htmlspecialchars($conversation->customer_name ?: $conversation->customer_phone ?: 'Unknown'); ?></span></div>
      <div><span class="k">Channel</span><span class="v mc-channel-<?= $conversation->channel; ?>"><i class="fa fa-<?= $conversation->channel == 'whatsapp' ? 'whatsapp' : ($conversation->channel == 'instagram' ? 'instagram' : 'facebook'); ?>"></i> <?= ucfirst($conversation->channel); ?></span></div>
      <div><span class="k">Status</span><span class="v"><span class="mc-badge mc-badge-<?= $conversation->status; ?>"><?= ucfirst($conversation->status); ?></span></span></div>
      <div><span class="k">Last activity</span><span class="v"><?= htmlspecialchars($conversation->last_message_at ?: $conversation->created_at); ?></span></div>
    </div>
  </div>
</div>

<?php if(empty($messages)): ?>
<div class="ms-empty">
  <i class="fa fa-comment"></i>
  <p>No messages in this conversation yet.</p>
</div>
<?php else: ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-comments"></i> Messages</h3></div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mc-messages" id="mc-messages">
      <?php foreach($messages as $m): ?>
      <div class="mc-msg <?= $m->direction; ?>">
        <div class="mc-bubble"><?= nl2br(htmlspecialchars($m->body ?: '(no text)')); ?></div>
        <div class="meta">
          <?= ucfirst($m->direction); ?> · <?= $m->message_type; ?>
          <?php if(!empty($m->status)): ?> <span class="ms-badge" style="background:transparent;padding:0;border:none;font-size:10px;color:inherit;"><?= ucfirst($m->status); ?></span><?php endif; ?>
          · <?= htmlspecialchars($m->created_at); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-reply"></i> Send a reply</h3></div>
  <div class="mp-card-body">
    <form id="mc-send-form" onsubmit="return false;">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation->id; ?>">
      <div class="mc-compose">
        <textarea id="mc-reply-text" name="text" placeholder="Type your reply..."></textarea>
        <button type="button" class="mp-btn-primary" id="mc-send-btn"><i class="fa fa-paper-plane"></i> Send</button>
      </div>
      <div id="mc-send-status" class="mp-form-hint" style="margin-top:8px;min-height:18px;"></div>
    </form>
  </div>
</div>

<script>
$(function(){
  toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true, timeOut: 3000 };
  var base_url = $('#base_url').length ? $('#base_url').val() : '<?= base_url(); ?>';
  var scrollBox = $('#mc-messages');
  if(scrollBox.length){ scrollBox.scrollTop(scrollBox[0].scrollHeight); }

  $('#mc-send-btn').on('click', function(){
    var btn = $(this);
    var text = $('#mc-reply-text').val().trim();
    if(!text){ toastr.warning('Type a message first'); return; }
    btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    $('#mc-send-status').text('');
    $.ajax({
      type: 'POST',
      url: base_url + 'online_store/messaging_send',
      data: { conversation_id: $('input[name="conversation_id"]').val(), text: text },
      dataType: 'json',
      success: function(r){
        btn.attr('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
        if(r.status === 'success'){
          $('#mc-reply-text').val('');
          toastr.success(r.message);
          location.reload();
        } else {
          $('#mc-send-status').text(r.message || 'Send failed').css('color', 'var(--mp-danger)');
          toastr.error(r.message || 'Send failed');
        }
      },
      error: function(){
        btn.attr('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
        toastr.error('Server error');
      }
    });
  });
  $('#mc-reply-text').on('keydown', function(e){
    if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); $('#mc-send-btn').click(); }
  });
});
</script>

<script>$(".online_store-messaging-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
