$(document).ready(function() {
  $("#save_settings").on("click", function() {
    var base_url = $("#base_url").val();
    var api_key = $("#api_key").val().trim();
    var secret_key = $("#secret_key").val().trim();
    var contract_code = $("#contract_code").val().trim();

    if(api_key == '') {
      toastr["warning"]("Please enter your Monnify API Key!");
      $("#api_key").focus();
      return;
    }
    if(secret_key == '') {
      toastr["warning"]("Please enter your Monnify Secret Key!");
      $("#secret_key").focus();
      return;
    }
    if(contract_code == '') {
      toastr["warning"]("Please enter your Monnify Contract Code!");
      $("#contract_code").focus();
      return;
    }

    if(!api_key.startsWith('MK_')) {
      toastr["warning"]("API Key should start with 'MK_'!");
      $("#api_key").focus();
      return;
    }

    if(confirm("Are you sure?")) {
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      var data = new FormData($("#monnify-settings-form")[0]);
      $.ajax({
        type: 'POST',
        url: base_url + 'monnify/save_settings',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $(".overlay").remove();
          if(result == "success") {
            toastr["success"]("Monnify settings saved successfully!");
            success.currentTime = 0;
            success.play();
          } else {
            toastr["error"](result);
            failed.currentTime = 0;
            failed.play();
          }
        }
      });
    }
  });
});
