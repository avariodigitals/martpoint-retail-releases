(function(){
  'use strict';

  window.MPAssist = {
    sessionId: null,
    isOpen: false,
    draftId: null,

    _storageKey: function(key){
      var uid = (typeof mpUserId !== 'undefined' && mpUserId) ? mpUserId : '0';
      return 'mp_assist_' + uid + '_' + key;
    },

    init: function(){
      this.sessionId = this._generateSessionId();
      this._bindEvents();
      this._bindAutocomplete();
      this._restoreHistory();
    },

    toggle: function(){
      var panel = document.getElementById('mp-assist-panel');
      var overlay = document.getElementById('mp-assist-overlay');
      var fab = document.getElementById('mp-assist-fab');
      this.isOpen = !this.isOpen;

      if(this.isOpen){
        panel.classList.add('open');
        overlay.classList.add('active');
        if(fab) fab.style.display = 'none';
        document.getElementById('mp-assist-input').focus();
        // Show welcome if empty and no saved history
        var container = document.getElementById('mp-assist-messages');
        if(container.children.length === 0 && !localStorage.getItem(this._storageKey('history'))){
          this._sendToServer('help');
        }
      } else {
        panel.classList.remove('open');
        overlay.classList.remove('active');
        if(fab) fab.style.display = 'flex';
      }
      this._closeMenu();
    },

    toggleMenu: function(e){
      e.stopPropagation();
      var menu = document.getElementById('mp-fab-menu');
      if(menu.classList.contains('open')){
        this._closeMenu();
      } else {
        menu.classList.add('open');
      }
    },

    _closeMenu: function(){
      var menu = document.getElementById('mp-fab-menu');
      if(menu) menu.classList.remove('open');
    },

    openChat: function(){
      this._closeMenu();
      this.toggle();
    },

    openSupportModal: function(){
      this._closeMenu();
      var modal = document.getElementById('mp-support-modal');
      modal.classList.add('open');
    },

    closeSupportModal: function(){
      var modal = document.getElementById('mp-support-modal');
      modal.classList.remove('open');
      document.getElementById('mp-support-status').textContent = '';
      document.getElementById('mp-support-status').className = 'mp-support-status';
    },

    loadGuide: function(btn){
      if(btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Opening...'; }
      window.open(window.base_url + 'assist/kb_guide', '_blank');
      setTimeout(function(){
        if(btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-book"></i> Read Guide'; }
      }, 800);
    },

    clearChat: function(){
      var container = document.getElementById('mp-assist-messages');
      if(container){
        container.innerHTML = '';
        this._saveHistory();
      }
      // Re-show welcome
      var welcome = document.getElementById('mp-assist-welcome');
      if(welcome) welcome.style.display = 'block';
    },

    submitSupport: function(){
      var name = document.getElementById('mp-support-name').value.trim();
      var email = document.getElementById('mp-support-email').value.trim();
      var subject = document.getElementById('mp-support-subject').value.trim();
      var message = document.getElementById('mp-support-message').value.trim();
      var status = document.getElementById('mp-support-status');

      if(!name || !email || !subject || !message){
        status.textContent = 'Please fill in all fields.';
        status.className = 'mp-support-status error';
        return;
      }

      var self = this;
      $.ajax({
        url: window.base_url + 'assist/support_request',
        type: 'POST',
        data: {
          name: name,
          email: email,
          subject: subject,
          message: message,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          if(res.success){
            status.textContent = 'Support request sent successfully!';
            status.className = 'mp-support-status success';
            document.getElementById('mp-support-name').value = '';
            document.getElementById('mp-support-email').value = '';
            document.getElementById('mp-support-subject').value = '';
            document.getElementById('mp-support-message').value = '';
            setTimeout(function(){ self.closeSupportModal(); }, 2000);
          } else {
            status.textContent = res.error || 'Failed to send request.';
            status.className = 'mp-support-status error';
          }
        },
        error: function(){
          status.textContent = 'Network error. Please try again.';
          status.className = 'mp-support-status error';
        }
      });
    },

    handleKey: function(e){
      var sug = document.getElementById('mp-assist-suggestions');
      if(sug && sug.classList.contains('active')){
        if(e.key === 'ArrowDown' || e.key === 'ArrowUp'){
          e.preventDefault();
          var items = sug.querySelectorAll('.mp-suggestion-item');
          var sel = sug.querySelector('.mp-suggestion-item.selected');
          var idx = -1;
          for(var i = 0; i < items.length; i++){
            if(items[i] === sel){ idx = i; break; }
          }
          if(sel) sel.classList.remove('selected');
          if(e.key === 'ArrowDown'){
            idx = (idx + 1) % items.length;
          } else {
            idx = (idx <= 0) ? items.length - 1 : idx - 1;
          }
          items[idx].classList.add('selected');
          items[idx].scrollIntoView({ block: 'nearest' });
          return;
        }
        if(e.key === 'Escape'){
          this._hideSuggestions();
          return;
        }
        if(e.key === 'Enter' && sug.querySelector('.mp-suggestion-item.selected')){
          e.preventDefault();
          sug.querySelector('.mp-suggestion-item.selected').click();
          return;
        }
      }
      if(e.key === 'Enter'){
        e.preventDefault();
        this.send();
      }
    },

    send: function(){
      var input = document.getElementById('mp-assist-input');
      var message = input.value.trim();
      if(!message) return;
      input.value = '';
      this._hideSuggestions();
      this._addUserMessage(message);
      this._showTyping();
      this._sendToServer(message);
    },

    quickAction: function(action){
      this._showTyping();
      var self = this;
      $.ajax({
        url: window.base_url + 'assist/quick_action',
        type: 'POST',
        data: {
          action: action,
          session_id: self.sessionId,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          self._hideTyping();
          self._renderResponse(res);
        },
        error: function(){
          self._hideTyping();
          self._addBotMessage('Sorry, something went wrong. Please try again.');
        }
      });
    },

    resolveChoice: function(intent, choice){
      this._showTyping();
      var self = this;
      $.ajax({
        url: window.base_url + 'assist/resolve',
        type: 'POST',
        data: {
          intent: intent,
          choice: choice,
          session_id: self.sessionId,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          self._hideTyping();
          self._renderResponse(res);
        },
        error: function(){
          self._hideTyping();
          self._addBotMessage('Sorry, could not process your selection.');
        }
      });
    },

    confirmDraft: function(){
      if(!this.draftId) return;
      this._showTyping();
      var self = this;
      $.ajax({
        url: window.base_url + 'assist/confirm',
        type: 'POST',
        data: {
          draft_id: self.draftId,
          session_id: self.sessionId,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          self._hideTyping();
          self.draftId = null;
          self._renderResponse(res);
        },
        error: function(){
          self._hideTyping();
          self._addBotMessage('Failed to execute. Please try again.');
        }
      });
    },

    cancelDraft: function(){
      if(!this.draftId) return;
      var self = this;
      $.ajax({
        url: window.base_url + 'assist/cancel',
        type: 'POST',
        data: {
          draft_id: self.draftId,
          session_id: self.sessionId,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          self.draftId = null;
          self._renderResponse(res);
        }
      });
    },

    // =================== INTERNAL ===================

    _sendToServer: function(message){
      var self = this;
      $.ajax({
        url: window.base_url + 'assist/process',
        type: 'POST',
        data: {
          message: message,
          session_id: self.sessionId,
          [window.csrfName]: window.csrfHash
        },
        dataType: 'json',
        success: function(res){
          self._hideTyping();
          self._renderResponse(res);
        },
        error: function(){
          self._hideTyping();
          self._addBotMessage('Sorry, I could not process that. Please try again.');
        }
      });
    },

    _renderResponse: function(res){
      switch(res.type){
        case 'text':
        case 'error':
          this._addBotMessage(res.text);
          if(res.quick_tasks && res.quick_tasks.length){
            this._addQuickTasks(res.quick_tasks);
          }
          break;
        case 'knowledge':
          var kbText = res.text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
          this._addBotHtml('', '<span class="mp-kb-badge">MartPoint Guide</span><div class="assist-card" style="max-width:100%;">' + kbText + '</div>');
          if(res.follow_up){
            this._addFollowUp(res.follow_up, res.quick_tasks || []);
          }
          break;
        case 'html':
        case 'success':
          this._addBotHtml(res.text, res.html || '');
          if(res.quick_tasks && res.quick_tasks.length){
            this._addQuickTasks(res.quick_tasks);
          }
          break;
        case 'conversational':
          this._addConversationalMessage(res);
          break;
        case 'choice':
          this._addChoiceMessage(res.text, res.options || [], res.context || '');
          break;
        case 'draft':
          this.draftId = res.draft_id || null;
          this._addDraftMessage(res.text, res.html || '', res.draft_type || '');
          break;
        case 'welcome':
          this._addWelcomeMessage(res.text, res.quick_tasks || []);
          break;
        case 'fallback':
          this._addBotHtml('', res.text);
          if(res.quick_tasks && res.quick_tasks.length){
            this._addQuickTasks(res.quick_tasks);
          }
          // Add guide/support inline actions for fallback
          var fbDiv = document.createElement('div');
          fbDiv.className = 'mp-msg mp-msg-bot';
          fbDiv.innerHTML = '<div class="mp-msg-bubble"><div class="mp-quick-actions">' +
            '<button class="mp-quick-btn" onclick="MPAssist.openSupportModal()"><i class="fa fa-envelope"></i> Contact Support</button>' +
            '<button class="mp-quick-btn" onclick="MPAssist.loadGuide(this)"><i class="fa fa-book"></i> Read Guide</button>' +
            '</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
          document.getElementById('mp-assist-messages').appendChild(fbDiv);
          this._scrollToBottom();
          this._saveHistory();
          break;
        case 'clear':
          this.clearChat();
          break;
        default:
          this._addBotMessage(res.text || 'Done.');
      }

      // Delayed follow-up after cancellation
      if(res.delayed_followup){
        var self = this;
        setTimeout(function(){
          self._addBotMessage(res.delayed_followup);
        }, 10000);
      }

      // Handle redirect action
      if(res.action === 'redirect' && res.url){
        setTimeout(function(){
          window.location.href = res.url;
        }, 1500);
      }
    },

    _addUserMessage: function(text){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-user';
      div.innerHTML = '<div class="mp-msg-bubble">'+this._escapeHtml(text)+'</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span><span class="mp-msg-check"><i class="fa fa-check"></i></span></div>';
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addBotMessage: function(text){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      div.innerHTML = '<div class="mp-msg-bubble">'+this._escapeHtml(text)+'</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addBotHtml: function(text, html){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      div.innerHTML = '<div class="mp-msg-bubble"><div>'+this._escapeHtml(text)+'</div>'+html+'</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addChoiceMessage: function(text, options, context){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var html = '<div class="mp-msg-bubble"><div>'+this._escapeHtml(text)+'</div><div class="mp-choice-list">';
      for(var i = 0; i < options.length; i++){
        html += '<button class="mp-choice-btn" onclick="MPAssist.resolveChoice(\''+this._escapeJs(options[i].value)+'\', \''+this._escapeJs(options[i].value)+'\')">'+this._escapeHtml(options[i].label)+'</button>';
      }
      html += '</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = html;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addDraftMessage: function(text, html, draftType){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var inner = '<div class="mp-msg-bubble"><div>'+this._escapeHtml(text)+'</div>'+html;
      inner += '<div class="mp-draft-actions">';
      inner += '<button class="mp-btn-confirm" onclick="MPAssist.confirmDraft()">Confirm</button>';
      inner += '<button class="mp-btn-cancel" onclick="MPAssist.cancelDraft()">Cancel</button>';
      inner += '</div></div>';
      inner += '<div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = inner;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addWelcomeMessage: function(text, quickTasks){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var html = '<div class="mp-msg-bubble"><div>'+text+'</div><div class="mp-task-label">Quick Tasks</div><div class="mp-quick-actions">';
      for(var i = 0; i < quickTasks.length; i++){
        var icon = quickTasks[i].icon ? '<i class="fa '+this._escapeHtml(quickTasks[i].icon)+'"></i> ' : '';
        html += '<button class="mp-quick-btn" onclick="MPAssist.quickAction(\''+this._escapeJs(quickTasks[i].action)+'\')">'+icon+this._escapeHtml(quickTasks[i].label)+'</button>';
      }
      html += '</div><div style="margin-top:10px;font-size:0.9em;color:#555;border-top:1px dashed #ddd;padding-top:6px;">Anything missing? Send me a request.</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = html;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addConversationalMessage: function(res){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var inner = '<div class="mp-msg-bubble">';
      inner += '<div>'+(res.text || '')+'</div>';
      if(res.html){
        inner += res.html;
      }
      if(res.options && res.options.length){
        inner += '<div class="mp-choice-list">';
        for(var i = 0; i < res.options.length; i++){
          inner += '<button class="mp-choice-btn" onclick="MPAssist._sendChoice(\''+this._escapeJs(res.options[i].value)+'\',\''+this._escapeJs(res.options[i].label)+'\')">'+this._escapeHtml(res.options[i].label)+'</button>';
        }
        inner += '</div>';
      }
      inner += '</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = inner;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addFollowUp: function(text, tasks){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var html = '<div class="mp-msg-bubble"><div class="mp-follow-up-text">' + this._escapeHtml(text) + '</div>';
      if(tasks && tasks.length){
        html += '<div class="mp-quick-actions mp-follow-up-actions">';
        for(var i = 0; i < tasks.length; i++){
          var icon = tasks[i].icon ? '<i class="fa '+tasks[i].icon+'"></i> ' : '';
          html += '<button class="mp-quick-btn mp-follow-up-btn" onclick="MPAssist.quickAction(\''+this._escapeJs(tasks[i].action)+'\')">'+icon+this._escapeHtml(tasks[i].label)+'</button>';
        }
        html += '</div>';
      }
      html += '</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = html;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addQuickTasks: function(tasks){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var html = '<div class="mp-msg-bubble"><div class="mp-task-label">Quick Tasks</div><div class="mp-quick-actions">';
      for(var i = 0; i < tasks.length; i++){
        var icon = tasks[i].icon ? '<i class="fa '+tasks[i].icon+'"></i> ' : '';
        html += '<button class="mp-quick-btn" onclick="MPAssist.quickAction(\''+this._escapeJs(tasks[i].action)+'\')">'+icon+this._escapeHtml(tasks[i].label)+'</button>';
      }
      html += '</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = html;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _saveHistory: function(){
      var container = document.getElementById('mp-assist-messages');
      if(container){
        localStorage.setItem(this._storageKey('history'), container.innerHTML);
        localStorage.setItem(this._storageKey('history_time'), Date.now().toString());
      }
    },

    _restoreHistory: function(){
      var container = document.getElementById('mp-assist-messages');
      if(!container) return;
      var saved = localStorage.getItem(this._storageKey('history'));
      var savedTime = localStorage.getItem(this._storageKey('history_time'));
      if(saved && savedTime){
        var age = Date.now() - parseInt(savedTime, 10);
        if(age < 7200000){ // 2 hours
          container.innerHTML = saved;
          this._scrollToBottom();
        } else {
          localStorage.removeItem(this._storageKey('history'));
          localStorage.removeItem(this._storageKey('history_time'));
        }
      }
    },

    _sendChoice: function(value, label){
      this._addUserMessage(label || value);
      this._showTyping();
      this._sendToServer(value);
    },

    _showTyping: function(){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.id = 'mp-assist-typing';
      div.className = 'mp-msg mp-msg-bot';
      div.innerHTML = '<div class="mp-msg-bubble"><div class="mp-typing"><div class="mp-typing-dot"></div><div class="mp-typing-dot"></div><div class="mp-typing-dot"></div></div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      container.appendChild(div);
      this._scrollToBottom();
    },

    _hideTyping: function(){
      var el = document.getElementById('mp-assist-typing');
      if(el) el.remove();
    },

    _scrollToBottom: function(){
      var container = document.getElementById('mp-assist-messages');
      container.scrollTop = container.scrollHeight;
    },

    _escapeHtml: function(text){
      if(!text) return '';
      var div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    },

    _escapeJs: function(text){
      if(!text) return '';
      return String(text)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '\\"')
        .replace(/\n/g, '\\n')
        .replace(/\r/g, '\\r');
    },

    _formatTime: function(){
      var now = new Date();
      return now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
    },

    _generateSessionId: function(){
      return 'assist_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    },

    _bindEvents: function(){
      var self = this;
      document.addEventListener('click', function(e){
        var menu = document.getElementById('mp-fab-menu');
        var fab = document.getElementById('mp-assist-fab');
        if(menu && menu.classList.contains('open')){
          if(fab && !fab.contains(e.target) && !menu.contains(e.target)){
            self._closeMenu();
          }
        }
      });

      // Tab visibility change - prompt only when switching to another tab
      document.addEventListener('visibilitychange', function(){
        if(document.hidden){
          // User left this tab - store time and current URL
          localStorage.setItem(self._storageKey('hidden_time'), Date.now().toString());
          localStorage.setItem(self._storageKey('hidden_url'), window.location.href);
        } else {
          // User came back - check if it's the same page (tab switch) or different (internal nav)
          var hiddenTime = localStorage.getItem(self._storageKey('hidden_time'));
          var hiddenUrl = localStorage.getItem(self._storageKey('hidden_url'));
          if(!hiddenTime || !hiddenUrl) return;

          var away = Date.now() - parseInt(hiddenTime, 10);
          var samePage = (window.location.href === hiddenUrl);

          // Clean up
          localStorage.removeItem(self._storageKey('hidden_time'));
          localStorage.removeItem(self._storageKey('hidden_url'));

          // Only show if: away >30s, same page (tab switch), chat closed, has history
          if(away > 30000 && samePage && !self.isOpen){
            var container = document.getElementById('mp-assist-messages');
            if(container && container.children.length > 0){
              self._showContinuePrompt();
            }
          }
        }
      });
    },

    _bindAutocomplete: function(){
      var self = this;
      var input = document.getElementById('mp-assist-input');
      if(!input) { console.warn('[Assist] Input element not found'); return; }
      var debounceTimer = null;
      input.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        var val = input.value.trim();
        if(val.length < 2){
          self._hideSuggestions();
          return;
        }
        debounceTimer = setTimeout(function(){ self._fetchSuggestions(val); }, 200);
      });
      // Hide suggestions when clicking outside
      document.addEventListener('click', function(e){
        var area = document.querySelector('.mp-assist-input-area');
        if(area && !area.contains(e.target)){
          self._hideSuggestions();
        }
      });
    },

    _fetchSuggestions: function(query){
      var self = this;
      console.log('[Assist] Fetching topics for:', query);
      $.ajax({
        url: window.base_url + 'assist/topics?q=' + encodeURIComponent(query),
        type: 'GET',
        dataType: 'json',
        success: function(res){
          console.log('[Assist] Topics response:', res);
          if(res.topics && res.topics.length){
            self._showSuggestions(res.topics);
          } else {
            self._hideSuggestions();
          }
        },
        error: function(xhr, status, err){
          console.error('[Assist] Topics fetch failed:', status, err);
          self._hideSuggestions();
        }
      });
    },

    _showSuggestions: function(topics){
      console.log('[Assist] Showing suggestions:', topics.length);
      var container = document.getElementById('mp-assist-suggestions');
      if(!container) { console.warn('[Assist] Suggestions container not found'); return; }
      var html = '<div class="mp-suggestion-hint">Try asking...</div>';
      for(var i = 0; i < topics.length; i++){
        var t = topics[i];
        var kw = t.keywords && t.keywords.length ? t.keywords[0] : t.preview;
        html += '<div class="mp-suggestion-item" onclick="MPAssist._selectSuggestion(this)" data-id="' + this._escapeHtml(t.id) + '" data-keyword="' + this._escapeHtml(kw) + '">' +
          '<span class="mp-suggestion-category">' + this._escapeHtml(t.category) + '</span>' +
          '<span class="mp-suggestion-preview">' + this._escapeHtml(t.preview) + '</span>' +
          '</div>';
      }
      container.innerHTML = html;
      container.classList.add('active');
    },

    _hideSuggestions: function(){
      var container = document.getElementById('mp-assist-suggestions');
      if(container){
        container.classList.remove('active');
        container.innerHTML = '';
      }
    },

    _selectSuggestion: function(el){
      var input = document.getElementById('mp-assist-input');
      var topicId = el ? el.dataset.id : '';
      if(!topicId) return;
      if(input) input.value = '';
      this._hideSuggestions();
      this._loadTopicById(topicId);
    },

    _loadTopicById: function(topicId){
      var self = this;
      console.log('[Assist] Loading topic by ID:', topicId);
      $.ajax({
        url: window.base_url + 'assist/topic?id=' + encodeURIComponent(topicId),
        type: 'GET',
        dataType: 'json',
        success: function(res){
          if(res.answer){
            var kbText = res.answer.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            var container = document.getElementById('mp-assist-messages');
            var div = document.createElement('div');
            div.className = 'mp-msg mp-msg-bot';
            div.innerHTML = '<div class="mp-msg-bubble"><span class="mp-kb-badge">MartPoint Guide</span><div class="assist-card" style="max-width:100%;">' + kbText + '</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+self._formatTime()+'</span></div>';
            container.appendChild(div);
            if(res.follow_up){
              self._addFollowUp(res.follow_up, res.quick_tasks || []);
            }
            self._scrollToBottom();
            self._saveHistory();
          } else {
            self._addBotMessage('Sorry, I could not find that topic.');
          }
        },
        error: function(xhr, status, err){
          console.error('[Assist] Topic load failed:', status, err);
          self._addBotMessage('Sorry, I could not load that guide right now.');
        }
      });
    },

    _showContinuePrompt: function(){
      // Disabled: prompt removed per user request
      return;
    },

    _dismissContinuePrompt: function(continueChat){
      var prompt = document.getElementById('mp-continue-prompt');
      if(prompt) prompt.remove();
      if(continueChat){
        this.toggle();
      }
    }
  };

  // Auto-init when DOM ready
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', function(){ MPAssist.init(); });
  } else {
    MPAssist.init();
  }
})();
