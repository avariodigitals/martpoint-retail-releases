/*Email validation code*/
function validateEmail(sEmail) {
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,9}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
}
/*Email validation code end*/
/* customers modal start*/
$(".add_customer").on("click",function(e){
	var base_url=$("#base_url").val();
    //Initially flag set true
    var flag=true;
    function check_field(id){
	  if(!$("#"+id).val() ) //Also check Others????
	    {

	        $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
	        $('#'+id).css({'background-color' : '#E8E2E9'});
	        flag= false;
	    }
	    else
	    {
	         $('#'+id+'_msg').fadeOut(200).hide();
	         $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
	    }
	}
    //Validate Input box or selection box should not be blank or empty
	check_field("customer_name");
	//check_field("state");
	
	
    if(flag==false)
    {
		toastr["warning"]("You have Missed Something to Fillup!");
		return;
    }
    var email=$("#email").val();
    if (email!='' && !validateEmail(email)) {
            $("#email_msg").html("Invalid Email!").show();
             //flag=false;
             toastr["warning"]("Please Enter valid Email ID.")
			return;
        }
        else{
        	$("#email_msg").html("Invalid Email!").hide();
        }
    var this_id=this.id;

    

			//if(confirm("Are you Sure ?")){
				e.preventDefault();
				data = new FormData($('#customer-form')[0]);//form name
				/*Check XSS Code*/
				if(!xss_validation(data)){ return false; }
				
				$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
				$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
				$.ajax({
				type: 'POST',
				url: base_url+'pos/newcustomer?js_store_id='+$("#store_id").val(),
				data: data,
				cache: false,
				contentType: false,
				processData: false,
				success: function(result){
      				//alert(result);//return;
      				var data = jQuery.parseJSON(result);
					if(data.result=="success")
					{   
						$('#customer-modal').modal('toggle');
					   	var newOption = '<option value='+data.id+' selected>'+data.customer_name+'</option>';
					    $('#customer_id').append(newOption).trigger('change');
					    //$("#amount").val(data.advance);

              set_the_previous_due(0,0);

					     $('#customer-form')[0].reset();
					     toastr["success"]("New Customer Added!!");
					    success.currentTime = 0;
						success.play();
					}
					else if(data.result=="failed")
					{
					   toastr["error"]("Sorry! Failed to save Record.Try again!");
					   failed.currentTime = 0;
						failed.play();
					}
					else
					{
					   toastr["error"](data.result);
					   failed.currentTime = 0;
						failed.play();
					}
					$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
					$(".overlay").remove();

			   }
			   });
		//} //confirmation sure
		

		//e.preventDefault
});
/* customers modal end */


$("#copy_address").on("ifChanged",function(event){
          if(event.target.checked){
           $("#shipping_country").val($("#country").val()).select2();
           $("#shipping_state").val($("#state").val()).select2();
           $("#shipping_postcode").val($("#postcode").val());
           $("#shipping_city").val($("#city").val());
           $("#shipping_address").val($("#address").val());
           $("#shipping_location_link").val($("#location_link").val());
          }
          else{
           $("#shipping_country").val('').select2();
           $("#shipping_state").val('').select2();
           $("#shipping_postcode").val('');
           $("#shipping_city").val('');
           $("#shipping_address").val('');
           $("#shipping_location_link").val('');
          }
        });


/* Suppliers modals start*/
$(".add_supplier").on("click",function(e){
	var base_url=$("#base_url").val();
    //Initially flag set true
    var flag=true;
    function check_field(id){
	  if(!$("#"+id).val() ) //Also check Others????
	    {

	        $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
	        $('#'+id).css({'background-color' : '#E8E2E9'});
	        flag= false;
	    }
	    else
	    {
	         $('#'+id+'_msg').fadeOut(200).hide();
	         $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
	    }
	}
    //Validate Input box or selection box should not be blank or empty
	check_field("supplier_name");
	//check_field("state");
	
	
    if(flag==false)
    {
		toastr["warning"]("You have Missed Something to Fillup!");
		return;
    }
    var email=$("#email").val();
    if (email!='' && !validateEmail(email)) {
            $("#email_msg").html("Invalid Email!").show();
             //flag=false;
             toastr["warning"]("Please Enter valid Email ID.")
			return;
        }
        else{
        	$("#email_msg").html("Invalid Email!").hide();
        }
    var this_id=this.id;

    
    

			//if(confirm("Are you Sure ?")){
				
				e.preventDefault();
				data = new FormData($('#supplier-form')[0]);//form name
				/*Check XSS Code*/
				if(!xss_validation(data)){ return false; }
				
				$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
				$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
				$.ajax({
				type: 'POST',
				url: base_url+'purchase/newsupplier?js_store_id='+$("#store_id").val(),
				data: data,
				cache: false,
				contentType: false,
				processData: false,
				success: function(result){
      				//alert(result);//return;
      				var data = jQuery.parseJSON(result);
					if(data.result=="success")
					{   
						$('#supplier-modal').modal('toggle');
					   	var newOption = '<option value='+data.id+' selected>'+data.supplier_name+'</option>';
					    $('#supplier_id').append(newOption).trigger('change');
					    //$("#amount").val(data.advance);
					     $('#supplier-form')[0].reset();
					     toastr["success"]("New supplier Added!!");
					    success.currentTime = 0;
						success.play();
					}
					else if(data.result=="failed")
					{
					   toastr["error"]("Sorry! Failed to save Record.Try again!");
					   failed.currentTime = 0;
						failed.play();
					}
					else
					{
					   toastr["error"](data.result);
					   failed.currentTime = 0;
						failed.play();
					}
					$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
					$(".overlay").remove();

			   }
			   });
		//} //confirmation sure
		

		//e.preventDefault
});
/* Suppliers modal end*/

/* customers modal start*/
$(".add_variant").on("click",function(e){
	var base_url=$("#base_url").val();
    //Initially flag set true
    var flag=true;
    function check_field(id){
	  if(!$("#"+id).val() ) //Also check Others????
	    {

	        $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
	        $('#'+id).css({'background-color' : '#E8E2E9'});
	        flag= false;
	    }
	    else
	    {
	         $('#'+id+'_msg').fadeOut(200).hide();
	         $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
	    }
	}
    //Validate Input box or selection box should not be blank or empty
	check_field("variant");
	//check_field("state");
	
	
    if(flag==false)
    {
		toastr["warning"]("You have Missed Something to Fillup!");
		return;
    }
   
    var this_id=this.id;
					//if(confirm("Do You Wants to Save Record ?")){
						e.preventDefault();
						data = new FormData($('#variant-form')[0]);//form name
						/*Check XSS Code*/
						if(!xss_validation(data)){ return false; }
						
						$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
						$("#"+this_id).attr('disabled',true);  //Enable Save or Update button
						$.ajax({
						type: 'POST',
						url: base_url+'variants/newvariant',
						data: data,
						cache: false,
						contentType: false,
						processData: false,
						success: function(result){
             	
							if(result=="success")
							{   
								$('#variant-modal').modal('toggle');
							     toastr["success"]("New Variant Added!!");
							   	 success.currentTime = 0;
								 success.play();
								 $("#variant-form")[0].reset();
							}
							else if(result=="failed")
							{
							   toastr["error"]("Sorry! Failed to Save Record.Try again!");
							   failed.currentTime = 0;
							   failed.play();
							}
							else
							{
							   toastr["error"](result);
							   failed.currentTime = 0;
								failed.play();
							}

							$("#"+this_id).attr('disabled',false);  //Enable Save or Update button
							$(".overlay").remove();
					   }
					   });
				//}

				//e.preventDefault


   
		

		//e.preventDefault
});
/* customers modal end */

/* brand modal start*/
$(".add_brand").on("click",function(e){
  var base_url=$("#base_url").val().trim();
    //Initially flag set true
    var flag=true;
    function check_field(id){
    if(!$("#"+id).val().trim() ) //Also check Others????
      {

          $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
          $('#'+id).css({'background-color' : '#E8E2E9'});
          flag= false;
      }
      else
      {
           $('#'+id+'_msg').fadeOut(200).hide();
           $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
      }
  }
    //Validate Input box or selection box should not be blank or empty
  check_field("brand");
  
  
    if(flag==false)
    {
    toastr["warning"]("You have Missed Something to Fillup!");
    return;
    }
   
    var this_id=this.id;

      e.preventDefault();
      swal({
        title: "Are you sure?",
        text: "You want to save this brand?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
      }).then(function(willSave){
        if(willSave){
          data = new FormData($('#brand_form')[0]);//form name
          /*Check XSS Code*/
          if(!xss_validation(data)){ return false; }
          
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $("#"+this_id).attr('disabled',true);  //Enable Save or Update button
          $.ajax({
          type: 'POST',
          url: base_url+'brands/add_brand_modal',
          data: data,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
                //alert(result);//return;
                var data = jQuery.parseJSON(result);
            if(data.result=="success")
            {   
              $('#brand_modal').modal('toggle');
                var newOption = '<option value='+data.id+' selected>'+data.brand+'</option>';
                $('#brand_id').append(newOption).trigger('change');
                //$("#amount").val(data.advance);
                 $('#brand_form')[0].reset();
                 toastr["success"]("New brand Added!!");
                success.currentTime = 0;
              success.play();
            }
            else if(data.result=="failed")
            {
               toastr["error"]("Sorry! Failed to save Record.Try again!");
               failed.currentTime = 0;
              failed.play();
            }
            else
            {
               toastr["error"](data.result);
               failed.currentTime = 0;
              failed.play();
            }
            $("#"+this_id).attr('disabled',false);  //Enable Save or Update button
            $(".overlay").remove();

           }
           });
        }
      });
});
/* brands modal end */

/* category modal start*/
$(".add_category").on("click",function(e){
  var base_url=$("#base_url").val().trim();
    //Initially flag set true
    var flag=true;
    function check_field(id){
    if(!$("#"+id).val().trim() ) //Also check Others????
      {

          $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
          $('#'+id).css({'background-color' : '#E8E2E9'});
          flag= false;
      }
      else
      {
           $('#'+id+'_msg').fadeOut(200).hide();
           $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
      }
  }
    //Validate Input box or selection box should not be blank or empty
  check_field("category");
  
  
    if(flag==false)
    {
    toastr["warning"]("You have Missed Something to Fillup!");
    return;
    }
   
    var this_id=this.id;

      e.preventDefault();
      swal({
        title: "Are you sure?",
        text: "You want to save this category?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
      }).then(function(willSave){
        if(willSave){
          data = new FormData($('#category_form')[0]);//form name
          /*Check XSS Code*/
          if(!xss_validation(data)){ return false; }
          
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $("#"+this_id).attr('disabled',true);  //Enable Save or Update button
          $.ajax({
          type: 'POST',
          url: base_url+'category/add_category_modal',
          data: data,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
                //alert(result);//return;
                var data = jQuery.parseJSON(result);
            if(data.result=="success")
            {   
              $('#category_modal').modal('toggle');
                var newOption = '<option value='+data.id+' selected>'+data.category+'</option>';
                $('#category_id').append(newOption).trigger('change');
                //$("#amount").val(data.advance);
                 $('#category_form')[0].reset();
                 toastr["success"]("New category Added!!");
                success.currentTime = 0;
              success.play();
            }
            else if(data.result=="failed")
            {
               toastr["error"]("Sorry! Failed to save Record.Try again!");
               failed.currentTime = 0;
              failed.play();
            }
            else
            {
               toastr["error"](data.result);
               failed.currentTime = 0;
              failed.play();
            }
            $("#"+this_id).attr('disabled',false);  //Enable Save or Update button
            $(".overlay").remove();

           }
           });
        }
      });
});
/* categorys modal end */
/* unit modal start*/
$(".add_unit").on("click",function(e){
  var base_url=$("#base_url").val().trim();
    //Initially flag set true
    var flag=true;
    function check_field(id){
    if(!$("#"+id).val().trim() ) //Also check Others????
      {

          $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
          $('#'+id).css({'background-color' : '#E8E2E9'});
          flag= false;
      }
      else
      {
           $('#'+id+'_msg').fadeOut(200).hide();
           $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
      }
  }
    //Validate Input box or selection box should not be blank or empty
  check_field("unit_name");
  
  
    if(flag==false)
    {
    toastr["warning"]("You have Missed Something to Fillup!");
    return;
    }
   
    var this_id=this.id;

      e.preventDefault();
      swal({
        title: "Are you sure?",
        text: "You want to save this unit?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
      }).then(function(willSave){
        if(willSave){
          data = new FormData($('#unit_form')[0]);//form name
          /*Check XSS Code*/
          if(!xss_validation(data)){ return false; }
          
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $("#"+this_id).attr('disabled',true);  //Enable Save or Update button
          $.ajax({
          type: 'POST',
          url: base_url+'units/add_unit_modal',
          data: data,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
                //alert(result);//return;
                var data = jQuery.parseJSON(result);
            if(data.result=="success")
            {   
              $('#unit_modal').modal('toggle');
                var newOption = '<option value='+data.id+' selected>'+data.unit+'</option>';
                $('#unit_id').append(newOption).trigger('change');
                //$("#amount").val(data.advance);
                 $('#unit_form')[0].reset();
                 toastr["success"]("New unit Added!!");
                success.currentTime = 0;
              success.play();
            }
            else if(data.result=="failed")
            {
               toastr["error"]("Sorry! Failed to save Record.Try again!");
               failed.currentTime = 0;
              failed.play();
            }
            else
            {
               toastr["error"](data.result);
               failed.currentTime = 0;
              failed.play();
            }
            $("#"+this_id).attr('disabled',false);  //Enable Save or Update button
            $(".overlay").remove();

           }
           });
        }
      });
});
/* units modal end */

/* tax modal start*/
$(".add_tax").on("click",function(e){
  var base_url=$("#base_url").val().trim();
    //Initially flag set true
    var flag=true;
    function check_field(id){
    if(!$("#"+id).val().trim() ) //Also check Others????
      {

          $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
          $('#'+id).css({'background-color' : '#E8E2E9'});
          flag= false;
      }
      else
      {
           $('#'+id+'_msg').fadeOut(200).hide();
           $('#'+id).css({'background-color' : '#FFFFFF'});    //White color
      }
  }
    //Validate Input box or selection box should not be blank or empty
  check_field("tax_name");
  check_field("tax");
  
  
    if(flag==false)
    {
    toastr["warning"]("You have Missed Something to Fillup!");
    return;
    }
   
    var this_id=this.id;

      e.preventDefault();
      swal({
        title: "Are you sure?",
        text: "You want to save this tax?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
      }).then(function(willSave){
        if(willSave){
          data = new FormData($('#tax_form')[0]);//form name
          /*Check XSS Code*/
          if(!xss_validation(data)){ return false; }
          
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $("#"+this_id).attr('disabled',true);  //Enable Save or Update button
          $.ajax({
          type: 'POST',
          url: base_url+'tax/add_tax_modal',
          data: data,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
                //alert(result);//return;
                var data = jQuery.parseJSON(result);
            if(data.result=="success")
            {   
              $('#tax_modal').modal('toggle');
                var newOption = '<option data-tax='+data.tax+' value='+data.id+' selected>'+data.tax_name+'</option>';
                $('#tax_id').append(newOption).trigger('change');
                //$("#amount").val(data.advance);
                 $('#tax_form')[0].reset();
                 toastr["success"]("New tax Added!!");
                success.currentTime = 0;
              success.play();
            }
            else if(data.result=="failed")
            {
               toastr["error"]("Sorry! Failed to save Record.Try again!");
               failed.currentTime = 0;
              failed.play();
            }
            else
            {
               toastr["error"](data.result);
               failed.currentTime = 0;
              failed.play();
            }
            $("#"+this_id).attr('disabled',false);  //Enable Save or Update button
            $(".overlay").remove();

           }
           });
        }
      });
});
/* taxs modal end */