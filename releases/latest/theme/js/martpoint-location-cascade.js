/**
 * MartPoint Location Cascade
 * Handles country -> state -> city cascading dropdowns
 * for customer, supplier and store forms.
 */
(function($) {
  'use strict';

  var base_url = (window.base_url) ? window.base_url : ($('#base_url').val() || '');

  function loadStates(country, stateSelect, citySelect, selectedStateId) {
    if (!country) {
      $(stateSelect).html('<option value="">Select State</option>');
      $(citySelect).html('<option value="">Select City</option>');
      return;
    }

    var url = base_url + 'site/get_states_by_country';
    console.log('[LocationCascade] Loading states for country:', country, '| URL:', url);

    $.ajax({
      url: url,
      type: 'POST',
      dataType: 'json',
      data: { country: country },
      success: function(data) {
        console.log('[LocationCascade] States received:', data);
        var options = '<option value="">Select State</option>';
        $.each(data, function(i, item) {
          var selected = (selectedStateId && String(selectedStateId) === String(item.id)) ? ' selected' : '';
          options += '<option value="' + item.id + '"' + selected + '>' + item.state + '</option>';
        });
        $(stateSelect).html(options).trigger('change');

        if (selectedStateId) {
          $(stateSelect).val(selectedStateId).trigger('change');
        }
      },
      error: function(xhr, status, err) {
        console.error('[LocationCascade] States AJAX error:', status, err, 'URL:', url);
        $(stateSelect).html('<option value="">No Records Found</option>');
      }
    });
  }

  function loadCities(stateId, citySelect, selectedCityName) {
    if (!stateId) {
      $(citySelect).html('<option value="">Select City</option>');
      return;
    }

    var url = base_url + 'site/get_cities_by_state';
    console.log('[LocationCascade] Loading cities for stateId:', stateId, '| URL:', url);

    $.ajax({
      url: url,
      type: 'POST',
      dataType: 'json',
      data: { state_id: stateId },
      success: function(data) {
        console.log('[LocationCascade] Cities received:', data);
        var options = '<option value="">Select City</option>';
        $.each(data, function(i, item) {
          var selected = (selectedCityName && String(selectedCityName) === String(item.city)) ? ' selected' : '';
          options += '<option value="' + item.city + '"' + selected + '>' + item.city + '</option>';
        });
        $(citySelect).html(options);

        if (selectedCityName) {
          $(citySelect).val(selectedCityName).trigger('change');
        }
      },
      error: function(xhr, status, err) {
        console.error('[LocationCascade] Cities AJAX error:', status, err, 'URL:', url);
        $(citySelect).html('<option value="">No Records Found</option>');
      }
    });
  }

  /* ---------- Customer Modal (#customer-modal) ---------- */
  $(document).on('change', '#customer-modal #country', function() {
    var country = $(this).find('option:selected').text();
    loadStates(country, '#customer-modal #state', '#customer-modal #city');
  });

  $(document).on('change', '#customer-modal #state', function() {
    var stateId = $(this).val();
    loadCities(stateId, '#customer-modal #city');
  });

  $(document).on('change', '#customer-modal #shipping_country', function() {
    var country = $(this).find('option:selected').text();
    loadStates(country, '#customer-modal #shipping_state', '#customer-modal #shipping_city');
  });

  $(document).on('change', '#customer-modal #shipping_state', function() {
    var stateId = $(this).val();
    loadCities(stateId, '#customer-modal #shipping_city');
  });

  /* ---------- Supplier Modal (#supplier-modal) ---------- */
  $(document).on('change', '#supplier-modal #country', function() {
    var country = $(this).find('option:selected').text();
    loadStates(country, '#supplier-modal #state', '#supplier-modal #city');
  });

  $(document).on('change', '#supplier-modal #state', function() {
    var stateId = $(this).val();
    loadCities(stateId, '#supplier-modal #city');
  });

  /* ---------- Main Customer Form (#customers-form) ---------- */
  $(document).on('change', '#customers-form #country', function() {
    var country = $(this).find('option:selected').text();
    loadStates(country, '#customers-form #state', '#customers-form #city');
  });

  $(document).on('change', '#customers-form #state', function() {
    var stateId = $(this).val();
    loadCities(stateId, '#customers-form #city');
  });

  /* ---------- Main Supplier Form (#suppliers-form) ---------- */
  $(document).on('change', '#suppliers-form #country', function() {
    var country = $(this).find('option:selected').text();
    loadStates(country, '#suppliers-form #state', '#suppliers-form #city');
  });

  $(document).on('change', '#suppliers-form #state', function() {
    var stateId = $(this).val();
    loadCities(stateId, '#suppliers-form #city');
  });

  /* ---------- Store Form (#store-form) ---------- */
  // Store uses country NAME as value, so we need name-based state/city options
  function loadStoreStates(country, stateSelect, citySelect, selectedStateName) {
    if (!country) {
      $(stateSelect).html('<option value="">Select State</option>');
      $(citySelect).html('<option value="">Select City</option>');
      return;
    }
    $.ajax({
      url: base_url + 'site/get_states_by_country',
      type: 'POST',
      dataType: 'json',
      data: { country: country },
      success: function(data) {
        var options = '<option value="">Select State</option>';
        $.each(data, function(i, item) {
          var selected = (selectedStateName && String(selectedStateName) === String(item.state)) ? ' selected' : '';
          options += '<option value="' + item.state + '"' + selected + '>' + item.state + '</option>';
        });
        $(stateSelect).html(options).trigger('change');
      },
      error: function() {
        $(stateSelect).html('<option value="">No Records Found</option>');
      }
    });
  }

  function loadStoreCities(stateName, citySelect, selectedCityName) {
    if (!stateName) {
      $(citySelect).html('<option value="">Select City</option>');
      return;
    }
    // First find state ID by name, then load cities
    $.ajax({
      url: base_url + 'site/get_states_by_country',
      type: 'POST',
      dataType: 'json',
      data: { country: $('#store-form #country').val() },
      success: function(states) {
        var stateId = '';
        $.each(states, function(i, s) {
          if (String(s.state) === String(stateName)) { stateId = s.id; }
        });
        if (!stateId) {
          $(citySelect).html('<option value="">No Records Found</option>');
          return;
        }
        $.ajax({
          url: base_url + 'site/get_cities_by_state',
          type: 'POST',
          dataType: 'json',
          data: { state_id: stateId },
          success: function(data) {
            var options = '<option value="">Select City</option>';
            $.each(data, function(i, item) {
              var selected = (selectedCityName && String(selectedCityName) === String(item.city)) ? ' selected' : '';
              options += '<option value="' + item.city + '"' + selected + '>' + item.city + '</option>';
            });
            $(citySelect).html(options);
            if (selectedCityName) {
              $(citySelect).val(selectedCityName).trigger('change');
            }
          },
          error: function() {
            $(citySelect).html('<option value="">No Records Found</option>');
          }
        });
      },
      error: function() {
        $(citySelect).html('<option value="">No Records Found</option>');
      }
    });
  }

  $(document).on('change', '#store-form #country', function() {
    var country = $(this).val();
    loadStoreStates(country, '#store-form #state', '#store-form #city');
  });

  $(document).on('change', '#store-form #state', function() {
    var stateName = $(this).val();
    loadStoreCities(stateName, '#store-form #city');
  });

})(jQuery);
