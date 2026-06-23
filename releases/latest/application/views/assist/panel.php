<script>var base_url = '<?php echo base_url(); ?>'; var mpUserId = '<?php echo $this->session->userdata('inv_userid') ?? '0'; ?>';</script>
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
