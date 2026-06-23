<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<?php
  if(!isset($name)){
    $name=$code=$description=$enabled=$is_default=$sort_order=$requires_reference=$requires_confirmation=$affects_cash_in_hand=$icon_class=$q_id="";
  }
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Add/Update Record</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo $base_url; ?>payment_modes">Payment Modes</a></li>
      <li class="active">Payment Modes</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <?php include"comman/code_flashdata.php"; ?>
      <div class="col-md-12">
        <div class="box box-primary">
          <form class="form-horizontal" id="payment-modes-form" onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
            <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
            <div class="box-body">
              <input type='hidden' name='store_id' id='store_id' value='<?=get_current_store_id()?>'>

              <div class="form-group">
                <label for="name" class="col-sm-2 control-label">Name <label class="text-danger">*</label></label>
                <div class="col-sm-4">
                  <input type="text" class="form-control input-sm" id="name" name="name" value="<?=htmlspecialchars($name);?>" autofocus>
                  <span id="name_msg" style="display:none" class="text-danger"></span>
                </div>
              </div>

              <div class="form-group">
                <label for="code" class="col-sm-2 control-label">Code <label class="text-danger">*</label></label>
                <div class="col-sm-4">
                  <input type="text" class="form-control input-sm" id="code" name="code" value="<?=htmlspecialchars($code);?>" placeholder="e.g. cash, pos, bank_transfer">
                  <span id="code_msg" style="display:none" class="text-danger"></span>
                </div>
              </div>

              <div class="form-group">
                <label for="description" class="col-sm-2 control-label">Description</label>
                <div class="col-sm-4">
                  <textarea class="form-control input-sm" id="description" name="description" rows="2"><?=htmlspecialchars($description);?></textarea>
                </div>
              </div>

              <div class="form-group">
                <label for="icon_class" class="col-sm-2 control-label">Icon Class</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control input-sm" id="icon_class" name="icon_class" value="<?=htmlspecialchars($icon_class);?>" placeholder="e.g. fa-money">
                </div>
              </div>

              <div class="form-group">
                <label for="sort_order" class="col-sm-2 control-label">Sort Order</label>
                <div class="col-sm-4">
                  <input type="number" class="form-control input-sm" id="sort_order" name="sort_order" value="<?=$sort_order!=''?$sort_order:0;?>">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Enabled</label>
                <div class="col-sm-4">
                  <select class="form-control input-sm" id="enabled" name="enabled">
                    <option value="1" <?=($enabled==1 || $enabled==='')?'selected':'';?>>Yes</option>
                    <option value="0" <?=($enabled==='0')?'selected':'';?>>No</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Default Payment Mode</label>
                <div class="col-sm-4">
                  <select class="form-control input-sm" id="is_default" name="is_default">
                    <option value="0" <?=($is_default==0 || $is_default==='')?'selected':'';?>>No</option>
                    <option value="1" <?=($is_default==1)?'selected':'';?>>Yes</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Requires Reference</label>
                <div class="col-sm-4">
                  <select class="form-control input-sm" id="requires_reference" name="requires_reference">
                    <option value="0" <?=($requires_reference==0 || $requires_reference==='')?'selected':'';?>>No</option>
                    <option value="1" <?=($requires_reference==1)?'selected':'';?>>Yes</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Requires Confirmation</label>
                <div class="col-sm-4">
                  <select class="form-control input-sm" id="requires_confirmation" name="requires_confirmation">
                    <option value="0" <?=($requires_confirmation==0 || $requires_confirmation==='')?'selected':'';?>>No</option>
                    <option value="1" <?=($requires_confirmation==1)?'selected':'';?>>Yes</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Affects Cash In Hand</label>
                <div class="col-sm-4">
                  <select class="form-control input-sm" id="affects_cash_in_hand" name="affects_cash_in_hand">
                    <option value="0" <?=($affects_cash_in_hand==0 || $affects_cash_in_hand==='')?'selected':'';?>>No</option>
                    <option value="1" <?=($affects_cash_in_hand==1)?'selected':'';?>>Yes</option>
                  </select>
                </div>
              </div>

            </div>
            <div class="box-footer">
              <div class="col-sm-8 col-sm-offset-2 text-center">
                <?php if($name!=""){ $btn_name="Update"; $btn_id="update"; ?>
                  <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id;?>"/>
                <?php } else { $btn_name="Save"; $btn_id="save"; } ?>
                <div class="col-md-3 col-md-offset-3">
                  <button type="button" id="<?php echo $btn_id;?>" class="btn btn-block btn-success"><?php echo $btn_name;?></button>
                </div>
                <div class="col-sm-3">
                  <a href="<?=base_url('payment_modes');?>"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
                </div>
              </div>
            </div>
          </form>
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
<script src="<?php echo $theme_link; ?>js/payment_modes.js"></script>
<script>$(".payment_modes-active-li").addClass("active");</script>
</body>
</html>
