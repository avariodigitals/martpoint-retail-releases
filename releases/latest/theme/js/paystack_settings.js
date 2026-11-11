$(document).ready(function() {
  function loadPaystackBanks(showMessage) {
    var sel = $("#payout_bank_code");
    if(!sel.length) return;
    var currentBank = sel.val() || "";
    sel.prop("disabled", true).html('<option value="">Loading banks…</option>');
    $.getJSON($("#base_url").val().replace(/\/$/, '') + "/paystack/banks")
      .done(function(res) {
        if(!res || !res.status || !Array.isArray(res.banks)) {
          sel.html('<option value="">Banks unavailable</option>');
          if(showMessage) toastr["warning"]((res && res.message) || "Credentials were saved, but Paystack did not return a bank list.");
          return;
        }
        sel.empty().append('<option value="">Select bank</option>');
        res.banks.forEach(function(bank) {
          var code = bank.code || bank.bank_code;
          var name = bank.name || bank.bank_name;
          if(code) sel.append($('<option>', {value: code, text: name}).attr('data-name', name));
        });
        sel.val(currentBank).prop("disabled", false);
        if(sel.hasClass("select2-hidden-accessible")) sel.trigger("change.select2");
        else sel.select2({width: '100%', placeholder: 'Search or select a bank', allowClear: true});
        if(showMessage) toastr["success"]("Credentials are saved and Paystack bank lookup is responding.");
      })
      .fail(function(xhr) {
        sel.prop("disabled", false).html('<option value="">Banks unavailable</option>');
        var detail = "Credentials were saved, but bank lookup failed. Check the Live keys and Paystack account permissions.";
        if(xhr.responseJSON && xhr.responseJSON.message) detail = "Credentials were saved, but bank lookup failed: " + xhr.responseJSON.message;
        if(showMessage) toastr["warning"](detail);
      });
  }

  $("#save_settings").on("click", function() {
    var baseUrl = $("#base_url").val();
    var secretKey = $("#secret_key").val().trim();
    var publicKey = $("#public_key").val().trim();

    if(secretKey === '') {
      toastr["warning"]("Please enter the MartPoint Pay secret key.");
      $("#secret_key").focus();
      return;
    }
    if(publicKey === '') {
      toastr["warning"]("Please enter the MartPoint Pay public key.");
      $("#public_key").focus();
      return;
    }
    if(!secretKey.startsWith('sk_')) {
      toastr["warning"]("Secret Key should start with 'sk_'!");
      $("#secret_key").focus();
      return;
    }
    if(!publicKey.startsWith('pk_')) {
      toastr["warning"]("Public Key should start with 'pk_'!");
      $("#public_key").focus();
      return;
    }

    if(!baseUrl) {
      toastr["error"]("MartPoint Pay could not determine the Central settings URL.");
      return;
    }

    if(confirm("Save MartPoint Pay settings?")) {
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      var data = new FormData($("#paystack-settings-form")[0]);
      $.ajax({
        type: 'POST',
        url: baseUrl.replace(/\/$/, '') + '/paystack/save_settings',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $(".overlay").remove();
          if($.trim(result) === "success") {
            toastr["success"]("Paystack credentials saved and confirmed in the Central database.");
            loadPaystackBanks(true);
            if(typeof success !== 'undefined') {
              success.currentTime = 0;
              success.play();
            }
          } else if($.trim(result) === "credentials_saved_fee_schema_missing") {
            toastr["warning"]("Credentials were saved, but the platform fee could not be saved. Apply the latest Central database update, then save the fee again.");
            loadPaystackBanks(true);
          } else {
            toastr["error"]($.trim(result) || "MartPoint Pay settings could not be saved.");
            if(typeof failed !== 'undefined') {
              failed.currentTime = 0;
              failed.play();
            }
          }
        },
        error: function(xhr) {
          $(".overlay").remove();
          var message = $.trim(xhr.responseText || "");
          toastr["error"](message || "Paystack credentials could not be saved. Check the connection and Central database migration status.");
        }
      });
    }
  });
});
