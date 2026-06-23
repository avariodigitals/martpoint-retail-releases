$(document).ready(function() {

  //Save
  $("#save").on("click", function() {
    var base_url = $("#base_url").val();
    if($("#name").val().trim() == '') {
      toastr["warning"]("Please enter payment mode name!");
      $("#name").focus();
      return;
    }
    if($("#code").val().trim() == '') {
      toastr["warning"]("Please enter payment mode code!");
      $("#code").focus();
      return;
    }

    if(confirm("Are you sure?")) {
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      var data = new FormData($("#payment-modes-form")[0]);
      $.ajax({
        type: 'POST',
        url: base_url + 'payment_modes/new_payment_mode',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $(".overlay").remove();
          if(result == "success") {
            toastr["success"]("Payment mode saved successfully!");
            success.currentTime = 0;
            success.play();
            window.setTimeout(function() { window.location = base_url + 'payment_modes'; }, 1000);
          } else {
            toastr["error"](result);
            failed.currentTime = 0;
            failed.play();
          }
        }
      });
    }
  });

  //Update
  $("#update").on("click", function() {
    var base_url = $("#base_url").val();
    if($("#name").val().trim() == '') {
      toastr["warning"]("Please enter payment mode name!");
      $("#name").focus();
      return;
    }
    if($("#code").val().trim() == '') {
      toastr["warning"]("Please enter payment mode code!");
      $("#code").focus();
      return;
    }

    if(confirm("Are you sure?")) {
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      var data = new FormData($("#payment-modes-form")[0]);
      $.ajax({
        type: 'POST',
        url: base_url + 'payment_modes/update_payment_mode',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $(".overlay").remove();
          if(result == "success") {
            toastr["success"]("Payment mode updated successfully!");
            success.currentTime = 0;
            success.play();
            window.setTimeout(function() { window.location = base_url + 'payment_modes'; }, 1000);
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
