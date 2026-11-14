<!DOCTYPE html>
<html>
   <head>
      <!-- TABLES CSS CODE -->
      <?php include"comman/code_css.php"; ?>
      <style>
         .cf-summary-card { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
         .cf-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:20px; }
         .cf-kpi { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:18px; }
         .cf-kpi .lbl { font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
         .cf-kpi .val { font-size:22px; font-weight:700; color:#1E293B; }
         .cf-kpi .val.pos { color:#10B981; }
         .cf-kpi .val.neg { color:#EF4444; }
      </style>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php include"sidebar.php"; ?>
         <div class="content-wrapper">
            <section class="content-header">
               <h1><?=$page_title;?><small></small></h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li class="active"><?=$page_title;?></li>
               </ol>
            </section>
            <section class="content">
               <div class="row">
                  <div class="col-md-12">
                     <div class="box box-primary ">
                        <div class="box-header with-border">
                           <h3 class="box-title">Please Enter Valid Information</h3>
                        </div>
                        <form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <div class="box-body">
                              <div class="form-group">
                                 <?php if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>get_current_store_id(),'div_length'=>'col-sm-3','show_all'=>'true','form_group_remove' => 'true')); }else{
                                    echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                                    }?>
                                 <label for="from_date" class="col-sm-2 control-label"><?= $this->lang->line('from_date'); ?></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('01-m-Y'));?>" >
                                    </div>
                                    <span id="from_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="to_date" class="col-sm-2 control-label"><?= $this->lang->line('to_date'); ?></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y'))?>" >
                                    </div>
                                    <span id="to_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                           </div>
                           <div class="box-footer">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="view" class=" btn btn-block btn-success" title="Show">Show</button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button></a>
                                 </div>
                              </div>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </section>
            <section class="content">
               <div class="row">
                  <div class="col-md-12">
                     <div id="cf-results" style="display:none;">
                        <div class="cf-kpi-grid">
                           <div class="cf-kpi"><div class="lbl">Total Cash In</div><div class="val pos" id="cf-in">0</div></div>
                           <div class="cf-kpi"><div class="lbl">Total Cash Out</div><div class="val neg" id="cf-out">0</div></div>
                           <div class="cf-kpi"><div class="lbl">Net Cash Movement</div><div class="val" id="cf-net">0</div></div>
                        </div>
                        <div class="cf-summary-card">
                           <div class="box-header"><h3 class="box-title">Cash Movement Breakdown</h3></div>
                           <div class="box-body table-responsive no-padding">
                              <table class="table table-bordered table-hover" id="report-data">
                                 <thead>
                                    <tr class="bg-blue">
                                       <th>#</th>
                                       <th>Source</th>
                                       <th>Direction</th>
                                       <th class='text-right'>Amount</th>
                                    </tr>
                                 </thead>
                                 <tbody id="tbodyid"></tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                     <div id="cf-empty" class="box">
                        <div class="box-body text-center text-muted" style="padding:40px;">
                           <i class="fa fa-tachometer" style="font-size:36px;"></i>
                           <p style="margin-top:10px;">Select a date range and click <strong>Show</strong> to view the cash flow statement.</p>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         </div>
         <?php include"footer.php"; ?>
         <div class="control-sidebar-bg"></div>
      </div>
      <?php include"comman/code_js_sound.php"; ?>
      <?php include"comman/code_js.php"; ?>
      <?php include"comman/code_js_export.php"; ?>
      <script src="<?php echo $theme_link; ?>js/sheetjs.js" type="text/javascript"></script>
      <script type="text/javascript">
         var base_url=$("#base_url").val();
         function fmt(n){ return (n*1).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
         function load_records(){
            $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            data = new FormData($('#report-form')[0]);
            if(!xss_validation(data)){ return false; }
            $("#view").attr('disabled',true);
            $.ajax({
               type: 'POST',
               url: base_url+'reports/show_cash_flow_report',
               data: data,
               cache: false,
               contentType: false,
               processData: false,
               dataType: 'json',
               success: function(r){
                  $("#cf-empty").hide();
                  $("#cf-results").show();
                  $("#cf-in").text(fmt(r.in_total));
                  $("#cf-out").text(fmt(r.out_total));
                  var netEl=$("#cf-net"); netEl.text(fmt(r.net));
                  netEl.removeClass('pos neg').addClass(r.net>=0?'pos':'neg');
                  var rows='';
                  $.each(r.lines,function(i,l){
                     rows+='<tr>';
                     rows+='<td>'+(i+1)+'</td>';
                     rows+='<td>'+l.label+'</td>';
                     rows+='<td>'+(l.direction==='in'?'<span class="label label-success">In</span>':'<span class="label label-danger">Out</span>')+'</td>';
                     rows+='<td class="text-right">'+fmt(l.amount)+'</td>';
                     rows+='</tr>';
                  });
                  rows+='<tr class="bg-gray-active"><td colspan="3" class="text-right text-bold">Net Cash Movement</td><td class="text-right text-bold '+(r.net>=0?'text-success':'text-danger')+'">'+fmt(r.net)+'</td></tr>';
                  $("#tbodyid").empty().append(rows);
                  $("#view").attr('disabled',false);
                  $(".overlay").remove();
               },
               error: function(){
                  $("#view").attr('disabled',false);
                  $(".overlay").remove();
                  toastr.error('Could not load cash flow data.');
               }
            });
         }
         $("#view").on("click",function(){
            check_field("from_date");
            check_field("to_date");
            load_records();
         });
         function check_field(id){
            if(!$("#"+id).val()){
               $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
               flag=false;
            } else {
               $('#'+id+'_msg').fadeOut(200).hide();
            }
         }
      </script>
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
   </body>
</html>
