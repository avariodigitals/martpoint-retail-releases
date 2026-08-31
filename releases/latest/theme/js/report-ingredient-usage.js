$("#view,#view_all").on("click",function(){
    var from_date=document.getElementById("from_date").value;
    var to_date=document.getElementById("to_date").value;
    var item_id=document.getElementById("item_id").value;
    if(from_date == ""){ toastr["warning"]("Select From Date!"); document.getElementById("from_date").focus(); return; }
    if(to_date == ""){ toastr["warning"]("Select To Date!"); document.getElementById("to_date").focus(); return; }
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post($("#base_url").val()+"reports/show_ingredient_usage_report",{
        from_date:from_date,to_date:to_date,item_id:item_id,store_id:$("#store_id").val()
    },function(result){
        setTimeout(function(){ $("#tbodyid").empty().append(result); $(".overlay").remove(); }, 0);
    });
});
