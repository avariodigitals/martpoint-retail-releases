<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.ms-table-wrap{width:100%;overflow-x:auto;}
.ms-table{width:100%;border-collapse:collapse;font-size:13px;}
.ms-table th,.ms-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--mp-border);vertical-align:top;}
.ms-table th{background:var(--mp-surface);font-weight:700;color:var(--mp-muted);text-transform:uppercase;font-size:11px;letter-spacing:.04em;}
.ms-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.02em;}
.ms-badge-open{background:rgba(16,185,129,.1);color:var(--mp-success);}
.ms-badge-closed{background:rgba(220,38,38,.1);color:var(--mp-danger);}
.ms-channel-whatsapp{color:#25d366;}
.ms-channel-instagram{color:#bc2a8d;}
.ms-channel-messenger{color:#0084ff;}
.ms-empty{padding:40px 20px;text-align:center;color:var(--mp-muted);}
.ms-empty i{font-size:40px;margin-bottom:12px;opacity:.6;}
.ms-channels{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
.ms-chip{padding:6px 12px;border-radius:20px;border:1px solid var(--mp-border);background:var(--mp-surface);color:var(--mp-ink);font-size:12px;cursor:pointer;text-decoration:none;}
.ms-chip.active{background:var(--mp-primary);color:#fff;border-color:var(--mp-primary);}
.ms-customer{font-weight:700;color:var(--mp-ink);}
.ms-preview{color:var(--mp-muted);font-size:12px;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ms-time{font-size:11px;color:var(--mp-muted);}
.ms-action{margin-top:6px;}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View and reply to conversations across WhatsApp Business API, Instagram Direct and Facebook Messenger.</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-comments"></i> Conversations</h3>
  </div>
  <div class="mp-card-body">
    <div class="ms-channels" style="margin-bottom:14px;">
      <?php foreach($channels as $ch): ?>
        <a href="<?= base_url('online_store/messaging?channel=' . $ch['key']); ?>" class="ms-chip <?= ($active_channel == $ch['key']) ? 'active' : ''; ?>">
          <i class="fa fa-<?= $ch['key'] == 'whatsapp' ? 'whatsapp' : ($ch['key'] == 'instagram' ? 'instagram' : 'facebook'); ?>"></i>
          <?= htmlspecialchars($ch['label']); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if(empty($conversations)): ?>
      <div class="ms-empty">
        <i class="fa fa-comments"></i>
        <p>No <?= htmlspecialchars($active_channel); ?> conversations yet.</p>
        <p class="mp-form-hint" style="margin-top:8px;">Inbound messages appear here once your webhook is configured and a customer messages you.</p>
      </div>
    <?php else: ?>
      <table class="ms-table">
        <thead>
          <tr>
            <th style="width:34%;">Customer</th>
            <th style="width:42%;">Last message</th>
            <th style="width:12%;">Channel</th>
            <th style="width:12%;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($conversations as $c): ?>
          <tr>
            <td>
              <div class="ms-customer"><?= htmlspecialchars($c->customer_name ?: ($c->customer_phone ?: 'Unknown customer')); ?></div>
              <div class="ms-time"><?= htmlspecialchars($c->customer_phone ?: ''); ?></div>
            </td>
            <td class="ms-preview"><?= htmlspecialchars($c->last_message ? '' : ''); ?></td>
            <td>
              <span class="ms-badge ms-channel-<?= $c->channel; ?>"><?= ucfirst($c->channel); ?></span>
            </td>
            <td>
              <span class="ms-badge ms-badge-<?= $c->status; ?>"><?= ucfirst($c->status); ?></span>
            </td>
            <td style="text-align:right;">
              <a href="<?= base_url('online_store/messaging_conversation/' . $c->id); ?>" class="mp-btn-primary" style="padding:6px 12px;font-size:12px;">Open</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script>$(".online_store-messaging-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
