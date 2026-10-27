<?php $this->load->view('comman/usage_checkin'); ?>
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
  .mp-assist-fab {
    position: relative !important;
    overflow: visible !important;
    animation: mp-assist-breathe 3.2s ease-in-out infinite !important;
  }
  .mp-assist-fab::before {
    content: '' !important;
    position: absolute !important;
    inset: -5px !important;
    border: 2px solid rgba(255,255,255,.35) !important;
    border-radius: inherit !important;
    pointer-events: none !important;
    animation: mp-assist-ring 2.8s ease-out infinite !important;
  }
  @keyframes mp-assist-breathe { 0%,100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-2px) scale(1.025); } }
  @keyframes mp-assist-ring { 0% { opacity: .55; transform: scale(.96); } 75%,100% { opacity: 0; transform: scale(1.18); } }

  @media (max-width: 768px), (max-width: 1024px) and (orientation: portrait) {
    /* Smart floating chat card — inset from the screen edges, rounded,
       and kept above the mobile bottom nav instead of a giant sheet. */
    .mp-assist-panel {
      width: auto !important;
      left: 12px !important; right: 12px !important;
      bottom: var(--mp-mobile-assist-bottom, calc(96px + env(safe-area-inset-bottom, 0px))) !important;
      height: min(60vh, 480px) !important;
      min-height: 300px !important;
      max-height: calc(100vh - 140px) !important;
      border-radius: 18px !important;
      box-shadow: 0 12px 40px rgba(0,0,0,0.28) !important;
      transform: translateY(calc(100% + 140px)) !important;
      visibility: hidden !important;
      transition: transform 0.3s cubic-bezier(.32,.72,.35,1), visibility 0s linear 0.3s !important;
    }
    .mp-assist-panel.open {
      right: 12px !important;
      bottom: var(--mp-mobile-assist-bottom, calc(96px + env(safe-area-inset-bottom, 0px))) !important;
      border-radius: 18px !important;
      transform: translateY(0) !important;
      visibility: visible !important;
      transition: transform 0.3s cubic-bezier(.32,.72,.35,1) !important;
    }
    .mp-fab-wrapper { right: 14px !important; left: auto !important; bottom: var(--mp-mobile-assist-bottom, calc(96px + env(safe-area-inset-bottom, 0px))) !important; align-items: flex-end !important; }
    .mp-fab-menu { align-items: flex-end !important; }
    .mp-assist-fab-label { display: inline !important; }
    .mp-assist-fab { min-height: 46px !important; padding: 11px 15px !important; border-radius: 16px !important; animation: none !important; }
    .mp-assist-fab::before { display: none !important; }
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
  <div id="mp-assist-playback" class="mp-assist-playback" hidden>
    <span id="mp-assist-playback-status" role="status" aria-live="polite">Speaking</span>
    <button id="mp-assist-pause" type="button" onclick="MPAssist.toggleSpeechPause()" aria-label="Pause spoken reply"><i class="fa fa-pause" aria-hidden="true"></i> Pause</button>
    <button type="button" onclick="MPAssist._cancelSpeech()" aria-label="Stop spoken reply"><i class="fa fa-stop" aria-hidden="true"></i> Stop</button>
  </div>
  <div class="mp-assist-input-area">
    <div id="mp-assist-suggestions" class="mp-assist-suggestions"></div>
    <div id="mp-assist-voice-status" class="mp-assist-voice-status" role="status" aria-live="polite"></div>
    <div class="mp-assist-field">
      <textarea id="mp-assist-input" class="mp-assist-input" rows="1" placeholder="Ask MartPoint..." onkeydown="MPAssist.handleKey(event)"></textarea>
      <button id="mp-assist-mic" class="mp-assist-mic" type="button" onclick="MPAssist.toggleMic()" aria-label="Speak your request" aria-pressed="false" title="Speak your request">
        <i class="fa fa-microphone"></i>
      </button>
    </div>
    <button id="mp-assist-speak" class="mp-assist-speak" type="button" onclick="MPAssist.toggleSpeak()" aria-label="Spoken replies" aria-pressed="true" title="Spoken replies: on">
      <i class="fa fa-volume-up"></i>
    </button>
    <button class="mp-assist-send" onclick="MPAssist.send()">
      <i class="fa fa-paper-plane"></i>
    </button>
  </div>
</div>

<!-- Floating Button + Menu -->
<div class="mp-fab-wrapper">
  <div id="mp-fab-menu" class="mp-fab-menu">
    <button class="mp-fab-menu-item" onclick="MPSupportChat.open()">
      <i class="fa fa-comments"></i> <span>Live Chat with Support</span>
    </button>
    <button class="mp-fab-menu-item" onclick="MPAssist.openChat()">
      <i class="fa fa-lightbulb-o"></i> <span>Ask MartPoint AI</span>
    </button>
    <button class="mp-fab-menu-item" onclick="MPAssist.openSupportModal()">
      <i class="fa fa-envelope"></i> <span>Support Request</span>
    </button>
    <a class="mp-fab-menu-item" href="https://www.martpoint.com.ng/support" target="_blank" rel="noopener">
      <i class="fa fa-globe"></i> <span>Support Portal</span>
    </a>
    <a class="mp-fab-menu-item" href="https://wa.me/2348037978230?text=Hi%20MartPoint%20Support" target="_blank">
      <i class="fa fa-whatsapp"></i> <span>WhatsApp Support</span>
    </a>
    <button class="mp-fab-menu-item mp-fab-cancel" onclick="MPAssist.closeMenu()">
      <i class="fa fa-times"></i> <span>Cancel</span>
    </button>
  </div>
  <button id="mp-assist-fab" class="mp-assist-fab" onclick="MPAssist.toggleMenu(event)" aria-label="MartPoint Assist and support" aria-controls="mp-fab-menu" aria-expanded="false">
    <svg class="mp-assist-mark" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11.5a8 8 0 0 1-8 8H5l-4 3v-11a9 9 0 0 1 18-1"/><path d="M7 10v3m4-5v7m4-5v3"/></svg>
    <span class="mp-assist-fab-label">Ask MartPoint</span>
  </button>
</div>
<?php $this->load->view('assist/support_chat_widget'); ?>

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
