<!DOCTYPE html>
<html>
   <head><?php include"comman/code_css.php"; ?></head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php include"sidebar.php"; ?>
         <div class="content-wrapper">
            <section class="content-header">
               <h1><?=$page_title;?> <small></small></h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li class="active"><?=$page_title;?></li>
               </ol>
            </section>
            <section class="content">
               <div class="row">
                  <div class="col-md-12">
                     <div class="box box-primary">
                        <div class="box-header with-border"><h3 class="box-title">Filter</h3></div>
                        <form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                           <div class="box-body">
                              <div class="form-group">
                                 <?php if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>get_current_store_id(),'div_length'=>'col-sm-3','show_all'=>'true','form_group_remove' => 'true')); }else{
                                    echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                                 }?>
                              </div>
                              <div class="form-group">
                                 <label for="from_date" class="col-sm-2 control-label">From Date</label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('d-m-Y', strtotime('-30 days')));?>" >
                                    </div>
                                 </div>
                                 <label for="to_date" class="col-sm-2 control-label">To Date</label>
                                 <div class="col-sm-3">
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y'));?>" >
                                    </div>
                                 </div>
                              </div>
                              <div class="form-group">
                                 <label for="recipe_id" class="col-sm-2 control-label">Recipe</label>
                                 <div class="col-sm-3">
                                    <select class="form-control select2" id="recipe_id" name="recipe_id">
                                       <option value="">All Recipes</option>
                                       <?php
                                       $recipes = $this->db->where('store_id', get_current_store_id())->where('status',1)->order_by('name','ASC')->get('db_recipes')->result();
                                       foreach($recipes as $r){ echo '<option value="'.$r->id.'">'.htmlspecialchars($r->name).'</option>'; }
                                       ?>
                                    </select>
                                 </div>
                              </div>
                           </div>
                           <div class="box-footer">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="view" class="btn btn-block btn-success">Show</button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
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
                        <div class="box-header"><h3 class="box-title">Records</h3><?php $this->load->view('components/export_btn',array('tableId' => 'report-data'));?></div>
                        <div class="box-body table-responsive no-padding">
                           <table class="table table-bordered table-hover" id="report-data">
                              <thead>
                                 <tr class="bg-blue">
                                    <th>#</th>
                                    <?php if(store_module() && is_admin()){ ?><th>Store</th><?php } ?>
                                    <th>Run Date</th>
                                    <th>Recipe</th>
                                    <th class="text-right">Planned</th>
                                    <th class="text-right">Actual Yield</th>
                                    <th class="text-right">Actual Cost</th>
                                    <th>Staff</th>
                                    <th>Notes</th>
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
      <?php include"comman/code_js_sound.php"; ?><?php include"comman/code_js.php"; ?><?php include"comman/code_js_export.php"; ?>
      <script src="<?php echo $theme_link; ?>js/sheetjs.js"></script>
      <script src="<?php echo $theme_link; ?>js/report-production-runs.js"></script>
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
   </body>
</html>
