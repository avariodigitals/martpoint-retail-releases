$("#view,#view_all").on("click",function(){
    var from_date=document.getElementById("from_date").value;
    var to_date=document.getElementById("to_date").value;
    var status=document.getElementById("status").value;
    if(from_date == ""){ toastr["warning"]("Select From Date!"); document.getElementById("from_date").focus(); return; }
    if(to_date == ""){ toastr["warning"]("Select To Date!"); document.getElementById("to_date").focus(); return; }
    var view_all = (this.id=="view_all") ? 'yes' : 'no';
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post($("#base_url").val()+"reports/show_production_summary_report",{
        from_date:from_date,to_date:to_date,status:status,store_id:$("#store_id").val(),view_all:view_all
    },function(result){
        setTimeout(function(){ $("#tbodyid").empty().append(result); $(".overlay").remove(); }, 0);
    });
});
