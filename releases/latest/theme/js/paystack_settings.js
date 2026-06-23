$(document).ready(function() {
  $("#save_settings").on("click", function() {
    var base_url = $("#base_url").val();
    var secret_key = $("#secret_key").val().trim();
    var public_key = $("#public_key").val().trim();

    if(secret_key == '') {
      toastr["warning"]("Please enter your Paystack Secret Key!");
      $("#secret_key").focus();
      return;
    }
    if(public_key == '') {
      toastr["warning"]("Please enter your Paystack Public Key!");
      $("#public_key").focus();
      return;
    }

    if(!secret_key.startsWith('sk_')) {
      toastr["warning"]("Secret Key should start with 'sk_'!");
      $("#secret_key").focus();
      return;
    }
    if(!public_key.startsWith('pk_')) {
      toastr["warning"]("Public Key should start with 'pk_'!");
      $("#public_key").focus();
      return;
    }

    if(confirm("Are you sure?")) {
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      var data = new FormData($("#paystack-settings-form")[0]);
      $.ajax({
        type: 'POST',
        url: base_url + 'paystack/save_settings',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $(".overlay").remove();
          if(result == "success") {
            toastr["success"]("Paystack settings saved successfully!");
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
