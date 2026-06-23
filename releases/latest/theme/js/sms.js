
/*Email validation code end*/
$('#send').on("click",function (e) {
	e.preventDefault();
	var base_url=$("#base_url").val();
    var flag=true;

    function check_field(id)
    {
      if(!$("#"+id).val() )
        {
            $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
            $('#'+id).css({'background-color' : '#E8E2E9'});
            flag=false;
        }
        else
        {
             $('#'+id+'_msg').fadeOut(200).hide();
             $('#'+id).css({'background-color' : '#FFFFFF'});
        }
    }

    check_field("mobile");
	check_field("message");

    if(flag==false)
    {
		toastr["warning"]("You have Missed Something to Fillup!")
		return;
    }

    var this_id=this.id;

    swal({
		title: "Are you sure?",
		text: "You are about to send an SMS.",
		icon: "warning",
		buttons: true,
		dangerMode: true
	}).then(function(willSend){
		if(willSend) {
			$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
			$("#"+this_id).attr('disabled',true);
			data = new FormData($('#sms-form')[0]);
			$.ajax({
				type: 'POST',
				url: base_url+'sms/send_message',
				data: data,
				cache: false,
				contentType: false,
				processData: false,
				success: function(result){
					if(result=="success")
					{
						toastr["success"]("SMS Sent Successfully!");
						$("#mobile,#message").val('');
					}
					else if(result=="failed")
					{
					   toastr["error"]("Sorry! Failed to Send SMS.Try again!");
					}
					else
					{
						toastr["error"](result);
					}
					$("#"+this_id).attr('disabled',false);
					$(".overlay").remove();
			   }
		   });
		}
	});


   

});


//On Enter Move the cursor to desigtation Id
function shift_cursor(kevent,target){

    if(kevent.keyCode==13){
		$("#"+target).focus();
    }
	
}

$('#update').on("click",function (e) {
	e.preventDefault();
	var base_url=$("#base_url").val();
    var this_id=this.id;

    function doSmsUpdate(){
		$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
		$("#"+this_id).attr('disabled',true);
		data = new FormData($('#api-form')[0]);
		$.ajax({
			type: 'POST',
			url: base_url+'sms/api_update',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(result){
					if(result=="success")
					{
						location.reload();
					}
					else if(result=="failed")
					{
					   toastr['error']("Sorry! Failed to save Record.Try again");
					}
					else
					{
						swal(result);
					}
				$("#"+this_id).attr('disabled',false);
				$(".overlay").remove();
		   }
	   });
    }
	if(typeof swal === 'undefined'){
		if(!confirm("Are you sure ?")) return;
		doSmsUpdate();
	} else {
		swal({
			title: "Are you sure?",
			text: "You are about to update SMS API settings.",
			icon: "warning",
			buttons: true,
			dangerMode: true
		}).then(function(willUpdate){
			if(willUpdate) doSmsUpdate();
		});
	}
});