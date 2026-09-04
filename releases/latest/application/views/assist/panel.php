<script>var base_url = '<?php echo base_url(); ?>'; var mpUserId = '<?php echo $this->session->userdata('inv_userid') ?? '0'; ?>';</script>
<!-- MartPoint Assist Panel — Critical layout styles inline so the panel is always correctly sized regardless of page CSS/cache -->
<style>
  .mp-assist-overlay {
    position: fixed !important; inset: 0 !important; z-index: 9998 !important;
    background: rgba(0,0,0,0.3) !important; display: none !important;
  }
  .mp-assist-overlay.active { display: block !important; }

  .mp-assist-panel {
    position: fixed !important; z-index: 9999 !important;
    bottom: 24px !important; right: -420px !important;
    width: 380px !important; max-width: 95vw !important;
    height: 70vh !important; max-height: 560px !important; min-height: 360px !important;
    background: #fff !important; border-radius: 12px !important;
    box-shadow: -4px 0 20px rgba(0,0,0,0.15) !important;
    display: flex !important; flex-direction: column !important;
    transition: right 0.3s ease, bottom 0.3s ease !important;
    overflow: hidden !important; box-sizing: border-box !important;
  }
  .mp-assist-panel.open { right: 24px !important; }

  .mp-fab-wrapper {
    position: fixed !important; z-index: 9997 !important;
    bottom: 24px !important; right: 24px !important;
    display: flex !important; flex-direction: column !important;
    align-items: flex-end !important; gap: 10px !important;
  }

  @media (max-width: 768px) {
    .mp-assist-panel { width: 100vw !important; right: -100vw !important; height: 75vh !important; max-height: none !important; bottom: 0 !important; }
    .mp-assist-panel.open { right: 0 !important; bottom: 0 !important; border-radius: 0 !important; }
    .mp-fab-wrapper { right: 14px !important; bottom: 14px !important; }
    .mp-assist-fab-label { display: none !important; }
  }
</style>
<!-- MartPoint Assist Panel -->
<div id="mp-assist-overlay" class="mp-assist-overlay" onclick="MPAssist.toggle()"></div>
<div id="mp-assist-panel" class="mp-assist-panel">
  <div class="mp-assist-header">
    <div class="mp-assist-header-left">
      <div class="mp-assist-avatar">
        <img src="<?php echo base_url('uploads/assist/avatar.jpg'); ?>" onerror="this.onerror=null;this.src='<?php echo base_url('uploads/site/default.png'); ?>';" alt="MartPoint Assist">
      </div>
      <div class="mp-assist-header-info">
        <div class="mp-assist-title">MartPoint Assist</div>
        <div class="mp-assist-status">Online</div>
      </div>
    </div>
    <button class="mp-assist-close" onclick="MPAssist.toggle()">
      <i class="fa fa-times"></i>
    </button>
  </div>
  <div class="mp-assist-body" id="mp-assist-messages">
    <!-- Messages injected here -->
  </div>
  <div class="mp-assist-input-area">
    <div id="mp-assist-suggestions" class="mp-assist-suggestions"></div>
    <input type="text" id="mp-assist-input" class="mp-assist-input" placeholder="Ask MartPoint..." onkeydown="MPAssist.handleKey(event)">
    <button class="mp-assist-send" onclick="MPAssist.send()">
      <i class="fa fa-paper-plane"></i>
    </button>
  </div>
</div>

<!-- Floating Button + Menu -->
<div class="mp-fab-wrapper">
  <div id="mp-fab-menu" class="mp-fab-menu">
    <button class="mp-fab-menu-item" onclick="MPAssist.openChat()">
      <i class="fa fa-comment"></i> <span>Chat with MartPoint</span>
    </button>
    <button class="mp-fab-menu-item" onclick="MPAssist.openSupportModal()">
      <i class="fa fa-envelope"></i> <span>Support Request</span>
    </button>
    <a class="mp-fab-menu-item" href="https://wa.me/2348036028069?text=Hi%20MartPoint%20Support" target="_blank">
      <i class="fa fa-whatsapp"></i> <span>WhatsApp Support</span>
    </a>
  </div>
  <button id="mp-assist-fab" class="mp-assist-fab" onclick="MPAssist.toggleMenu(event)">
    <i class="fa fa-comment"></i>
    <span class="mp-assist-fab-label">Ask MartPoint</span>
  </button>
</div>

<!-- Support Request Modal -->
<div id="mp-support-modal" class="mp-support-modal">
  <div class="mp-support-content">
    <div class="mp-support-header">
      <h3><i class="fa fa-life-ring"></i> Support Request</h3>
      <button class="mp-support-close" onclick="MPAssist.closeSupportModal()">&times;</button>
    </div>
    <div class="mp-support-body">
      <div class="mp-support-form">
        <div class="mp-form-group">
          <label>Your Name</label>
          <input type="text" id="mp-support-name" class="mp-form-input" placeholder="Enter your name">
        </div>
        <div class="mp-form-group">
          <label>Your Email</label>
          <input type="email" id="mp-support-email" class="mp-form-input" placeholder="Enter your email">
        </div>
        <div class="mp-form-group">
          <label>Subject</label>
          <input type="text" id="mp-support-subject" class="mp-form-input" placeholder="Enter subject">
        </div>
        <div class="mp-form-group">
          <label>Message</label>
          <textarea id="mp-support-message" class="mp-form-textarea" rows="4" placeholder="Describe your issue..."></textarea>
        </div>
        <button class="mp-support-submit" onclick="MPAssist.submitSupport()">
          <i class="fa fa-paper-plane"></i> Send Request
        </button>
      </div>
      <div id="mp-support-status" class="mp-support-status"></div>
    </div>
  </div>
</div>
