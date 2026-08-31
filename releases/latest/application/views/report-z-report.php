<!DOCTYPE html>
<html>
   <head>
      <!-- TABLES CSS CODE -->
      <?php include"comman/code_css.php"; ?>
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
                           <h3 class="box-title">Filter Shifts</h3>
                        </div>
                        <form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <div class="box-body">
                              <div class="form-group">
                                 <?php if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>get_current_store_id(),'div_length'=>'col-sm-3','show_all'=>'true','form_group_remove' => 'true')); }else{
                                    echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                                    }?>
                                 <label for="cashier_id" class="col-sm-2 control-label">Cashier</label>
                                 <div class="col-sm-3">
                                    <select class="form-control select2" id="cashier_id" name="cashier_id">
                                       <option value="">-All Cashiers-</option>
                                       <?php foreach($cashiers as $c): ?>
                                       <option value="<?=$c->id;?>"><?=htmlspecialchars(($c->first_name?$c->first_name.' '.$c->last_name:$c->username));?></option>
                                       <?php endforeach; ?>
                                    </select>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="from_date" class="col-sm-2 control-label"><?= $this->lang->line('from_date'); ?></label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('d-m-Y', strtotime('-7 days')));?>" >
                                    </div>
                                    <span id="from_date_msg" style="display:none" class="text-danger"></span>
                                 </div>
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
                     <div class="box">
                        <div class="box-header">
                           <h3 class="box-title"><?= $this->lang->line('records_table'); ?></h3>
                           <?php $this->load->view('components/export_btn',array('tableId' => 'report-data'));?>
                        </div>
                        <div class="box-body table-responsive no-padding">
                           <table class="table table-bordered table-hover " id="report-data" >
                              <thead>
                                 <tr class="bg-blue">
                                    <th>#</th>
                                    <?php if(store_module() && is_admin()){ ?>
                                    <th><?= $this->lang->line('store_name'); ?></th>
                                    <?php } ?>
                                    <th>Shift Code</th>
                                    <th>Cashier</th>
                                    <th>Till</th>
                                    <th>Opened</th>
                                    <th>Closed</th>
                                    <th class='text-right'>Expected Cash</th>
                                    <th class='text-right'>Counted Cash</th>
                                    <th class='text-right'>Cash Variance</th>
                                    <th>Status</th>
                                    <th>View</th>
                                 </tr>
                              </thead>
                              <tbody id="tbodyid"></tbody>
                           </table>
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
         function load_records(){
            $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            data = new FormData($('#report-form')[0]);
            if(!xss_validation(data)){ return false; }
            $("#view").attr('disabled',true);
            $.ajax({
               type: 'POST',
               url: base_url+'cashier_shifts/show_z_report',
               data: data,
               cache: false,
               contentType: false,
               processData: false,
               success: function(result){
                  $("#tbodyid").empty().append(result);
                  $("#view").attr('disabled',false);
                  $(".overlay").remove();
               }
            });
         }
         $("#view").on("click",function(){
            check_field("from_date");
            check_field("to_date");
            load_records();
         });
         $(function(){ load_records(); });
         function check_field(id){
            if(!$("#"+id).val()){
               $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
               flag=false;
            } else {
               $('#'+id+'_msg').fadeOut(200).hide();
            }
         }
      </script>
      <script>$(".report-z-active-li").addClass("active");</script>
   </body>
</html>
