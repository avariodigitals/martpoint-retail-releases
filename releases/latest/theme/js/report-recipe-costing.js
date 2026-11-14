$("#view,#view_all").on("click",function(){
    var recipe_id=document.getElementById("recipe_id").value;
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post($("#base_url").val()+"reports/show_recipe_costing_report",{
        recipe_id:recipe_id,store_id:$("#store_id").val()
    },function(result){
        setTimeout(function(){ $("#tbodyid").empty().append(result); $(".overlay").remove(); }, 0);
    });
});
