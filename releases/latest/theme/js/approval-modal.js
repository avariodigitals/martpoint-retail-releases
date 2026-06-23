/**
 * MartPoint Approval Modal System
 * Include after jQuery and SweetAlert
 */
window.MPApproval = (function(){
  var pendingCallback = null;
  var currentLogId = null;
  var currentContext = {};
  var selectedApproverId = null;

  function getBaseUrl(){
    var url = window.base_url || '';
    return url.replace(/\/$/, '') + '/';
  }

  /**
   * Fetch users with can_approve permission
   */
  function fetchApprovers(callback){
    var url = getBaseUrl() + 'approvals/get_approvers';
    console.log('[Approval] Fetching approvers from:', url);
    $.ajax({
      url: url,
      dataType: 'json',
      success: function(res){
        console.log('[Approval] Approvers received:', res);
        var list = (res && res.approvers) ? res.approvers : [];
        if(list.length === 0){
          console.warn('[Approval] No approvers returned from server');
        }
        callback(list);
      },
      error: function(xhr, status, err){
        console.error('[Approval] Failed to fetch approvers:', status, err, xhr.status, xhr.responseText);
        callback([]);
      }
    });
  }

  /**
   * Show approver selection dropdown, then PIN prompt
   */
  function showApproverSelect(title, reason, method, logId, context, onApprove){
    currentLogId = logId;
    currentContext = context || {};
    pendingCallback = onApprove;
    selectedApproverId = null;

    console.log('[Approval] Method/allowed IDs from settings:', method);
    // method is now a comma-separated string of allowed approver user IDs
    // Legacy support: if method contains non-numeric values (e.g. 'manager_pin'), show all approvers
    var allowedIds = [];
    var isLegacy = false;
    if(method && method !== 'none'){
      var parts = String(method).split(',').map(function(s){ return s.trim(); });
      // Check if all parts are numeric user IDs
      allowedIds = parts.filter(function(p){ return /^\d+$/.test(p); });
      if(allowedIds.length === 0 || allowedIds.length !== parts.length){
        isLegacy = true; // Contains legacy method strings like 'manager_pin'
        console.log('[Approval] Legacy mode detected, showing all approvers');
      }
    }
    if(!isLegacy && allowedIds.length === 0){
      swal({ title: 'No Approvers Configured', text: 'No approvers assigned for this approval type. Please go to Settings > Security & Approvals and assign approvers.', icon: 'error' });
      if(typeof pendingCallback === 'function') pendingCallback(false, currentContext);
      return;
    }

    fetchApprovers(function(approvers){
      var filtered;
      if(isLegacy){
        // Legacy mode: show all users with approval rights
        filtered = approvers;
      } else {
        // Filter to only allowed IDs
        filtered = approvers.filter(function(a){ return allowedIds.indexOf(String(a.id)) !== -1; });
      }
      if(filtered.length === 0){
        var errMsg = 'No approvers found. Please ensure:\n' +
          '1. Users exist in the system\n' +
          '2. Users have roles like Admin, Manager, or Owner\n' +
          '3. OR the can_approve permission is assigned to their role\n' +
          '4. Go to Roles page and check "Can Approve Actions" permission';
        swal({ title: 'No Approvers Found', text: errMsg, icon: 'error' });
        if(typeof pendingCallback === 'function') pendingCallback(false, currentContext);
        return;
      }

      // Build dropdown HTML
      var currentUid = String(window.currentUserId || '');
      var options = '<option value="">-- Select Approver --</option>';
      for(var i = 0; i < filtered.length; i++){
        var sel = (String(filtered[i].id) === currentUid) ? ' selected' : '';
        options += '<option value="' + filtered[i].id + '"' + sel + '>' + filtered[i].name + ' (' + filtered[i].role + ')</option>';
      }

      var wrapper = document.createElement('div');
      wrapper.innerHTML = '<p style="margin-bottom:10px;">' + reason + '</p>' +
        '<select id="mp-approver-select" class="form-control" style="height:40px;font-size:16px;margin-bottom:15px;">' + options + '</select>';

      swal({
        title: title || 'Approval Required',
        text: 'Select an authorized approver',
        content: wrapper,
        icon: 'warning',
        closeOnClickOutside: false,
        closeOnEsc: false,
        buttons: {
          cancel: 'Cancel',
          confirm: {
            text: 'Next',
            closeModal: false
          }
        }
      }).then(function(value){
        if(value === null || value === false){
          if(typeof pendingCallback === 'function') pendingCallback(false, currentContext);
          pendingCallback = null;
          return;
        }
        var sel = document.getElementById('mp-approver-select');
        if(!sel || !sel.value){
          swal({ title: 'Required', text: 'Please select an approver', icon: 'warning', timer: 2000, buttons: false });
          return;
        }
        selectedApproverId = sel.value;
        showPinPrompt(method);
      });
    });
  }

  /**
   * Show PIN/Password prompt for selected approver
   */
  function showPinPrompt(method){
    var inputType = 'password';
    // If no approval_pin column exists in DB, backend falls back to checking login password
    var placeholder = 'Enter PIN or Password';
    var label = 'PIN or Password';

    swal({
      title: 'Enter ' + label,
      text: 'Enter your approval PIN. If you have not set one, enter your login password.',
      content: {
        element: 'input',
        attributes: {
          placeholder: placeholder,
          type: inputType
        }
      },
      icon: 'warning',
      closeOnClickOutside: false,
      closeOnEsc: false,
      buttons: {
        cancel: 'Cancel',
        confirm: {
          text: 'Approve',
          closeModal: false
        }
      }
    }).then(function(value){
      if(value === null || value === false){
        if(typeof pendingCallback === 'function') pendingCallback(false, currentContext);
        pendingCallback = null;
        return;
      }
      if(!value){
        swal({ title: 'Required', text: 'Please enter ' + label.toLowerCase(), icon: 'warning', timer: 2000, buttons: false });
        return;
      }
      validateApproval(value);
    });
  }

  function validateApproval(input){
    if(!currentLogId){
      swal({ title: 'Error', text: 'Approval session expired. Please try again.', icon: 'error' });
      return;
    }
    $.post(
      getBaseUrl() + 'approvals/validate',
      {
        log_id: currentLogId,
        input: input,
        approver_id: selectedApproverId,
        [window.csrfName || 'csrf_test_name']: window.csrfHash || ''
      },
      function(res){
        if(res.success){
          swal({ title: 'Approved', text: res.message, icon: 'success', timer: 1500, buttons: false });
          if(typeof pendingCallback === 'function'){
            pendingCallback(true, currentContext);
          }
        } else {
          swal({ title: 'Failed', text: res.message, icon: 'error' });
        }
      },
      'json'
    ).fail(function(xhr){
      var msg = 'Network error. Please try again.';
      if(xhr.status === 403) msg = 'Session expired. Please refresh the page.';
      swal({ title: 'Error', text: msg, icon: 'error' });
      if(typeof pendingCallback === 'function'){
        pendingCallback(false, currentContext);
        pendingCallback = null;
      }
    });
  }

  /**
   * Check approval and show modal if needed.
   * @param string type - approval type key
   * @param object context - {amount, limit, percentage, etc.}
   * @param function onResult - callback(approved, context)
   */
  function request(type, context, onResult){
    console.log('[Approval] Checking:', type, context);
    $.post(
      getBaseUrl() + 'approvals/check_ajax',
      {
        type: type,
        context: JSON.stringify(context || {}),
        [window.csrfName || 'csrf_test_name']: window.csrfHash || ''
      },
      function(res){
        console.log('[Approval] Response:', res);
        if(!res || !res.required){
          onResult(true, context);
          return;
        }
        showApproverSelect('Approval Required', res.reason, res.method, res.log_id, context, onResult);
      },
      'json'
    ).fail(function(xhr, status, err){
      console.error('[Approval] AJAX failed:', status, err, xhr.status, xhr.responseText);
      var msg = 'Approval check failed. Please try again or contact admin.';
      if(xhr.status === 403) msg = 'Session expired. Please refresh the page.';
      onResult(false, context);
      if(typeof swal !== 'undefined'){
        swal({ title: 'Approval Error', text: msg, icon: 'error' });
      } else {
        alert(msg);
      }
    });
  }

  return {
    request: request,
    showModal: showApproverSelect
  };
})();
