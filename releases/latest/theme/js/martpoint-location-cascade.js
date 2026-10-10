/** Shared country -> state -> city cascade for customer, supplier and store forms. */
(function ($) {
  'use strict';
  function request(route, data, target, done) {
    var base = window.base_url || $('#base_url').val() || '';
    var name = window.csrfName;
    if (name) data[name] = window.csrfHash || $('input[name="' + name + '"]').first().val();
    var $target = $(target), previous = $target.data('locationRequest');
    if (previous) previous.abort();
    var req = $.ajax({url: base + route, type: 'POST', dataType: 'json', data: data,
      success: function (rows) { done(Array.isArray(rows) ? rows : []); },
      error: function (xhr, status) {
        if (status === 'abort') return;
        options($target, [], '', 'Could not load — select again');
        if (window.toastr) toastr.error('Location list could not be loaded. Please select again.');
      }
    });
    $target.data('locationRequest', req);
  }
  function options(target, rows, chosen, prompt, field) {
    var $target = $(target).empty();
    $target.append(new Option(prompt, ''));
    rows.forEach(function (row) {
      var value = field === 'stateName' ? row.state : (field === 'city' ? row.city : row.id);
      var label = field === 'city' ? row.city : row.state;
      $target.append(new Option(label, value, false, String(value) === String(chosen)));
    });
    $target.trigger('change.select2').trigger('mpupdate');
  }
  function cancel(target) {
    var old = $(target).data('locationRequest');
    if (old) old.abort();
  }
  function cities(stateId, target, chosen) {
    cancel(target);
    options(target, [], '', 'Select City');
    if (!stateId) return;
    request('site/get_cities_by_state', {state_id: stateId}, target, function (rows) {
      options(target, rows, chosen, 'Select City', 'city');
    });
  }
  function wire(form, shipping, store) {
    var prefix = shipping ? 'shipping_' : '';
    var c = form + ' #' + prefix + 'country', s = form + ' #' + prefix + 'state', t = form + ' #' + prefix + 'city';
    $(document).on('change', c, function () {
      cancel(s); cancel(t);
      options(s, [], '', 'Select State'); options(t, [], '', 'Select City');
      if (!this.value) return;
      var country = store ? this.value : $(this).find('option:selected').text().trim();
      request('site/get_states_by_country', {country: country}, s, function (rows) {
        $(s).data('locationStates', rows);
        options(s, rows, '', 'Select State', store ? 'stateName' : 'state');
      });
    });
    $(document).on('change', s, function () {
      var value = this.value;
      if (!store) { cities(value, t); return; }
      if (!value) { cities('', t); return; }
      function load(rows) {
        var state = rows.filter(function (r) { return r.state === value; })[0];
        cities(state ? state.id : '', t);
      }
      var cached = $(s).data('locationStates');
      if (cached) load(cached);
      else request('site/get_states_by_country', {country: $(c).val()}, t, load);
    });
  }
  ['#customers-form', '#customer-modal', '#suppliers-form', '#supplier-modal'].forEach(function (form) {
    wire(form, false, false); wire(form, true, false);
  });
  wire('#store-form', false, true);
})(jQuery);
