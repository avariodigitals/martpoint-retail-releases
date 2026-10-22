(function(){
  'use strict';

  window.MPAssist = {
    sessionId: null,
    isOpen: false,
    draftId: null,
    pendingConfirm: false,

    voice: {
      supported: false,
      listening: false,
      recognition: null,
      SR: null,
      speak: true,
      voices: null,
      voice: null
    },

    _storageKey: function(key){
      var uid = (typeof mpUserId !== 'undefined' && mpUserId) ? mpUserId : '0';
      return 'mp_assist_' + uid + '_' + key;
    },

    init: function(){
      this.sessionId = this._generateSessionId();
      this._bindEvents();
      this._bindAutocomplete();
      this._restoreHistory();
      this._initVoice();

      // Hard reset: never start with the panel open, even if cached markup had the class
      var panel = document.getElementById('mp-assist-panel');
      var overlay = document.getElementById('mp-assist-overlay');
      var fab = document.getElementById('mp-assist-fab');
      if(panel) panel.classList.remove('open');
      if(overlay) overlay.classList.remove('active');
      if(fab) fab.style.display = 'flex';
      this.isOpen = false;
      this._closeMenu();
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
        this._stopVoice();
        this._cancelSpeech();
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

    closeMenu: function(){
      this._closeMenu();
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
      input.style.height = 'auto';
      this._hideSuggestions();
      this._voiceStatus('', false);
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

    // =================== VOICE ===================
    //
    // Push-to-talk plus spoken replies. Voice only ever *fills the composer* —
    // it never sends and never confirms on its own, so every message (and every
    // data change behind it) still needs a deliberate tap from the user.

    _initVoice: function(){
      var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      this.voice.SR = SR || null;
      this.voice.supported = !!SR;
      var saved = null;
      try{ saved = localStorage.getItem(this._storageKey('speak')); }catch(e){}
      this.voice.speak = (saved === null) ? true : (saved === '1');
      this._loadVoices();
      if(typeof window.speechSynthesis !== 'undefined' && window.speechSynthesis && window.speechSynthesis.addEventListener){
        window.speechSynthesis.addEventListener('voiceschanged', function(){ MPAssist._loadVoices(); });
      }
      this._syncVoiceButtons();
    },

    // Prefer an African English voice for spoken replies, so Assist sounds
    // local instead of the browser default (usually US/UK). Falls back to the
    // browser default when none is installed.
    _loadVoices: function(){
      if(typeof window.speechSynthesis === 'undefined' || !window.speechSynthesis || !window.speechSynthesis.getVoices) return;
      var voices = window.speechSynthesis.getVoices() || [];
      if(!voices.length) return;
      this.voice.voices = voices;
      this.voice.voice = this._pickAfricanVoice(voices);
      this._syncVoiceButtons();
    },

    _pickAfricanVoice: function(voices){
      var matchers = [
        /en[-_]?(NG|GH|KE|ZA|TZ|UG|CM)/i,
        /(nigeri|ghana|kenya|south\s*africa|tanzania|uganda|africa)/i
      ];
      for(var m = 0; m < matchers.length; m++){
        for(var i = 0; i < voices.length; i++){
          var v = voices[i];
          if(!v) continue;
          if(matchers[m].test(v.lang || '') || matchers[m].test(v.name || '')) return v;
        }
      }
      return null;
    },

    toggleMic: function(){
      if(this.voice.listening){
        this._stopVoice();
      } else {
        this._startVoice();
      }
    },

    _startVoice: function(){
      var v = this.voice;
      if(v.listening) return;
      if(!v.supported){
        this._voiceStatus('Voice input is not available in this browser. You can type instead.', true);
        return;
      }
      if(window.isSecureContext === false){
        this._voiceStatus('Voice needs a secure (HTTPS) connection. You can type instead.', true);
        return;
      }
      var input = document.getElementById('mp-assist-input');
      if(!input) return;

      var recognition = new v.SR();
      v.recognition = recognition;
      var inputLen = (input.value || '').length;
      var maxLen = (input.maxLength && input.maxLength > 0) ? input.maxLength : 4000;
      var base = (input.value || '').replace(/\s+$/, '');
      if(base) base += ' ';
      var lang = document.documentElement.lang || 'en-NG';
      var errored = false;

      recognition.lang = (lang === 'en' ? 'en-NG' : lang);
      recognition.interimResults = true;
      recognition.continuous = false;

      recognition.onstart = function(){
        v.listening = true;
        MPAssist._syncVoiceButtons();
        MPAssist._voiceStatus('Listening… tap the mic again to stop.', false);
      };
      recognition.onresult = function(event){
        var text = '';
        for(var i = 0; i < event.results.length; i++){
          text += event.results[i][0].transcript;
        }
        var next = (base + text).slice(0, maxLen);
        if(next !== input.value){
          input.value = next;
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      };
      recognition.onerror = function(event){
        errored = true;
        var code = (event && event.error) ? event.error : '';
        var message = (code === 'not-allowed' || code === 'service-not-allowed')
          ? 'Microphone access was not granted. You can type instead.'
          : (code === 'no-speech'
            ? 'I did not catch that. Tap the mic and try again.'
            : 'Voice could not finish. You can type instead.');
        MPAssist._voiceStatus(message, true);
      };
      recognition.onend = function(){
        v.listening = false;
        v.recognition = null;
        MPAssist._syncVoiceButtons();
        var typed = (input.value || '').slice(inputLen).trim();
        if(typed){
          // While a "Did you mean...?" confirmation is waiting, a spoken yes/no
          // answers it straight away — no extra tap needed.
          var answer = MPAssist.pendingConfirm ? MPAssist._normalizeYesNo(typed) : null;
          if(answer){
            input.value = '';
            MPAssist._voiceStatus(answer === 'yes' ? 'Sending yes…' : 'Sending no…', false);
            MPAssist._sendChoice(answer, typed);
            return;
          }
          // Talk back: read the request aloud and ask if it is right before it
          // runs, so the reply is voice input only until the user sends it.
          MPAssist._voiceStatus('Is this what you mean? Tap send to run it.', false);
          var readback = typed.length > 140 ? typed.slice(0, 140) + '…' : typed;
          MPAssist._speak('You said: ' + readback + '. Is this what you mean?');
        } else if(!errored){
          MPAssist._voiceStatus('No speech captured. Tap the mic and try again.', true);
        }
        if(input.focus) input.focus();
      };

      try{
        recognition.start();
      }catch(error){
        v.recognition = null;
        v.listening = false;
        this._syncVoiceButtons();
        this._voiceStatus('Unable to start the microphone. You can type instead.', true);
      }
    },

    _stopVoice: function(){
      var v = this.voice;
      if(v.recognition){
        var rec = v.recognition;
        try{ rec.stop(); }
        catch(e){ try{ rec.abort(); }catch(e2){} }
        // Some webviews don't fire onend after stop(). If this session is still
        // active shortly after, abort it so the mic never gets stuck listening.
        setTimeout(function(){
          if(v.recognition === rec){
            try{ rec.abort(); }catch(e){}
            v.recognition = null;
            v.listening = false;
            MPAssist._syncVoiceButtons();
          }
        }, 1200);
      }
      v.listening = false;
      this._syncVoiceButtons();
    },

    toggleSpeak: function(){
      this.voice.speak = !this.voice.speak;
      try{ localStorage.setItem(this._storageKey('speak'), this.voice.speak ? '1' : '0'); }catch(e){}
      if(!this.voice.speak) this._cancelSpeech();
      this._syncVoiceButtons();
    },

    _syncVoiceButtons: function(){
      var v = this.voice;
      var mic = document.getElementById('mp-assist-mic');
      if(mic){
        var field = mic.parentElement;
        if(!v.supported){
          if(field && field.classList) field.classList.add('is-mic-hidden');
          mic.style.display = 'none';
        } else {
          if(field && field.classList) field.classList.remove('is-mic-hidden');
          mic.style.display = '';
          mic.classList.toggle('listening', !!v.listening);
          mic.setAttribute('aria-pressed', v.listening ? 'true' : 'false');
          mic.title = v.listening ? 'Stop listening' : 'Speak your request';
        }
      }
      var spk = document.getElementById('mp-assist-speak');
      if(spk){
        spk.classList.toggle('muted', !v.speak);
        spk.setAttribute('aria-pressed', v.speak ? 'true' : 'false');
        var voiceName = (v.voice && v.voice.name) ? ' (' + v.voice.name + ')' : '';
        spk.title = (v.speak ? 'Spoken replies: on' : 'Spoken replies: off') + voiceName;
        var icon = spk.querySelector ? spk.querySelector('i') : null;
        if(icon) icon.className = 'fa ' + (v.speak ? 'fa-volume-up' : 'fa-volume-off');
      }
    },

    _voiceStatus: function(text, isError){
      var el = document.getElementById('mp-assist-voice-status');
      if(!el) return;
      el.textContent = text || '';
      el.classList.toggle('active', !!text);
      el.classList.toggle('error', !!isError);
    },

    _speak: function(text){
      if(!this.voice.speak || !text) return;
      if(typeof window.speechSynthesis === 'undefined' || typeof window.SpeechSynthesisUtterance === 'undefined') return;
      var plain = String(text)
        .replace(/<[^>]*>/g, ' ')
        .replace(/&nbsp;/gi, ' ')
        .replace(/&amp;/gi, '&')
        .replace(/&#39;|&apos;/gi, "'")
        .replace(/&quot;/gi, '"')
        .replace(/&[a-z]+;/gi, ' ')
        .replace(/\s+/g, ' ')
        .replace(/\s+([?!.,;:])/g, '$1')
        .trim();
      if(!plain) return;
      try{
        window.speechSynthesis.cancel();
        var utterance = new window.SpeechSynthesisUtterance(plain.slice(0, 400));
        var lang = document.documentElement.lang || 'en-NG';
        utterance.lang = (lang === 'en' ? 'en-NG' : lang);
        if(this.voice.voice){
          utterance.voice = this.voice.voice;
          utterance.lang = this.voice.voice.lang || utterance.lang;
        }
        utterance.rate = 1.02;
        utterance.pitch = 1;
        window.speechSynthesis.speak(utterance);
      }catch(e){}
    },

    _cancelSpeech: function(){
      if(typeof window.speechSynthesis !== 'undefined' && window.speechSynthesis){
        try{ window.speechSynthesis.cancel(); }catch(e){}
      }
    },

    // Map a spoken/typed reply to a yes/no answer for a pending confirmation.
    _normalizeYesNo: function(text){
      var m = String(text == null ? '' : text).toLowerCase().replace(/[^a-z ]/g, '').replace(/\s+/g, ' ').trim();
      if(!m) return null;
      var first = m.split(' ')[0];
      if(['yes','yeah','yep','yup','y','sure','ok','okay','correct','right','affirmative','confirm','proceed'].indexOf(first) !== -1) return 'yes';
      if(['no','nope','nah','n','cancel','stop','abort','quit','wrong','incorrect','dont'].indexOf(first) !== -1) return 'no';
      return null;
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
      this.pendingConfirm = (res.type === 'confirm');
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
        case 'confirm':
          this._addConfirmMessage(res.text, res.options || []);
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

      // Spoken reply — the other half of "voice instruction". The welcome/help
      // message is skipped so opening the panel never starts talking on its own.
      if(res.type !== 'welcome' && res.type !== 'clear'){
        var spoken = res.text || '';
        if(res.type === 'draft'){
          spoken = (spoken ? spoken + ' ' : '') + 'Tap Confirm to save, or Cancel.';
        }
        this._speak(spoken);
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
      div.innerHTML = '<div class="mp-msg-bubble">'+this._safeHtml(text)+'</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addBotHtml: function(text, html){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      div.innerHTML = '<div class="mp-msg-bubble"><div>'+this._safeHtml(text)+'</div>'+html+'</div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    _addChoiceMessage: function(text, options, context){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var html = '<div class="mp-msg-bubble"><div>'+this._safeHtml(text)+'</div><div class="mp-choice-list">';
      for(var i = 0; i < options.length; i++){
        html += '<button class="mp-choice-btn" onclick="MPAssist.resolveChoice(\''+this._escapeJs(options[i].value)+'\', \''+this._escapeJs(options[i].value)+'\')">'+this._escapeHtml(options[i].label)+'</button>';
      }
      html += '</div></div><div class="mp-msg-meta"><span class="mp-msg-time">'+this._formatTime()+'</span></div>';
      div.innerHTML = html;
      container.appendChild(div);
      this._scrollToBottom();
      this._saveHistory();
    },

    // "Did you mean I should ...?" — spoken aloud, answered by voice ("yes"/"no")
    // or by tapping a button. Nothing runs until the user confirms.
    _addConfirmMessage: function(text, options){
      var container = document.getElementById('mp-assist-messages');
      var div = document.createElement('div');
      div.className = 'mp-msg mp-msg-bot';
      var opts = (options && options.length) ? options : [
        { label: 'Yes, do it', value: 'yes' },
        { label: 'No, cancel', value: 'no' }
      ];
      var html = '<div class="mp-msg-bubble"><div>'+this._safeHtml(text)+'</div><div class="mp-choice-list mp-confirm-actions">';
      for(var i = 0; i < opts.length; i++){
        var cls = String(opts[i].value) === 'no' ? 'mp-choice-btn mp-confirm-no' : 'mp-choice-btn mp-confirm-yes';
        html += '<button class="'+cls+'" onclick="MPAssist._sendChoice(\''+this._escapeJs(opts[i].value)+'\',\''+this._escapeJs(opts[i].label)+'\')">'+this._escapeHtml(opts[i].label)+'</button>';
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
      var inner = '<div class="mp-msg-bubble"><div>'+this._safeHtml(text)+'</div>'+html;
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
      var html = '<div class="mp-msg-bubble"><div>'+this._safeHtml(text)+'</div><div class="mp-task-label">Quick Tasks</div><div class="mp-quick-actions">';
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
      inner += '<div>'+this._safeHtml(res.text || '')+'</div>';
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
      var html = '<div class="mp-msg-bubble"><div class="mp-follow-up-text">' + this._safeHtml(text) + '</div>';
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

    // Server-generated bot text may contain safe formatting markup
    // (<strong>, <br>, <span style>, tables). Sanitize so it renders as
    // formatted output instead of leaking raw tags — while stripping
    // scripts, event handlers and unsafe attributes.
    _safeHtml: function(html){
      if(!html) return '';
      var allowedTags = {
        A:1, BR:1, P:1, DIV:1, SPAN:1, STRONG:1, B:1, EM:1, I:1, U:1, S:1,
        SMALL:1, CODE:1, PRE:1, UL:1, OL:1, LI:1, HR:1, BLOCKQUOTE:1,
        TABLE:1, THEAD:1, TBODY:1, TR:1, TH:1, TD:1,
        H1:1, H2:1, H3:1, H4:1, H5:1, H6:1
      };
      var dropTags = {
        SCRIPT:1, STYLE:1, IFRAME:1, OBJECT:1, EMBED:1, SVG:1, MATH:1,
        FORM:1, INPUT:1, TEXTAREA:1, SELECT:1, BUTTON:1, LINK:1, META:1, BASE:1
      };
      try {
        var doc = new DOMParser().parseFromString(String(html), 'text/html');
        var walk = function(node){
          var children = Array.prototype.slice.call(node.childNodes);
          for(var i = 0; i < children.length; i++){
            var el = children[i];
            if(el.nodeType === 8){ node.removeChild(el); continue; }
            if(el.nodeType !== 1) continue;
            var tag = el.tagName;
            if(dropTags[tag]){ node.removeChild(el); continue; }
            if(!allowedTags[tag]){
              // Unwrap: keep the element's sanitized children
              var parent = el.parentNode;
              walk(el);
              while(el.firstChild){ parent.insertBefore(el.firstChild, el); }
              parent.removeChild(el);
              continue;
            }
            for(var j = el.attributes.length - 1; j >= 0; j--){
              var attr = el.attributes[j].name.toLowerCase();
              var keep = (attr === 'style' || attr === 'class' || attr === 'title' || attr === 'colspan' || attr === 'rowspan');
              if(tag === 'A' && (attr === 'href' || attr === 'target')) keep = true;
              if(!keep){ el.removeAttribute(el.attributes[j].name); continue; }
              if(attr === 'href' && !/^\s*(https?:|mailto:|\/|#)/i.test(el.getAttribute('href') || '')){
                el.removeAttribute('href');
              }
            }
            if(tag === 'A' && el.getAttribute('target')){
              el.setAttribute('target', '_blank');
              el.setAttribute('rel', 'noopener');
            }
            walk(el);
          }
        };
        walk(doc.body);
        return doc.body.innerHTML;
      } catch(e) {
        return this._escapeHtml(html);
      }
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
        // Auto-grow for pasted multi-line lists (bulk entry)
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
        var val = input.value.trim();
        // Never pop suggestions over the voice status while dictating.
        if(val.length < 2 || self.voice.listening){
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
