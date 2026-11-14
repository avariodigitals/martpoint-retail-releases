
/*Email validation code end*/
$('.send').on("click",function (e) {
	var base_url=$("#base_url").val();

    /*Initially flag set true*/
    var flag=true;

    function check_field(id)
    {

      if(!$("#"+id).val() ) //Also check Others????
        {

            $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
            $('#'+id).css({'background-color' : '#E8E2E9'});
            flag=false;
        }
        else
        {
             $('#'+id+'_msg').fadeOut(200).hide();
             $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
        }
    }


    //Validate Input box or selection box should not be blank or empty
	check_field("email_to");
	check_field("email_subject");
	

    
	if(flag==false)
    {
		toastr["warning"]("You have Missed Something to Fillup!")
		return;
    }


    if($("#compose-textarea").val()==''){
    	toastr["warning"]("Email Content Should not be empty!!")
		return;
    }

    var this_id=this.id;
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$("#"+this_id).attr('disabled',true);
    

	$("#email-form").submit();

   

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
    
    function doEmailUpdate(){
		$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
		$("#"+this_id).attr('disabled',true);
		data = new FormData($('#api-form')[0]);
		$.ajax({
			type: 'POST',
			url: base_url+'email/api_update',
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
		doEmailUpdate();
	} else {
		swal({
			title: "Are you sure?",
			text: "You are about to update email settings.",
			icon: "warning",
			buttons: true,
			dangerMode: true
		}).then(function(willUpdate){
			if(willUpdate) doEmailUpdate();
		});
	}
});