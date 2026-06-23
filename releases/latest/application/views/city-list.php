<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include"sidebar.php"; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $this->lang->line('cities_list'); ?> <small>View/Search Cities</small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $this->lang->line('cities_list'); ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <?php include"comman/code_flashdata.php"; ?>
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $this->lang->line('cities_list'); ?></h3>
            </div>
            <div class="box-body">
              <table id="example2" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray">
                <tr>
                  <th>City</th>
                  <th>State</th>
                  <th>Country</th>
                  <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $q = $this->db->select('c.id, c.city, s.state, s.country, c.status')
                              ->from('db_cities c')
                              ->join('db_states s','s.id = c.state_id','left')
                              ->get();
                foreach($q->result() as $row){
                  $status = ($row->status==1)
                    ? '<span class="label label-success">Active</span>'
                    : '<span class="label label-danger">Inactive</span>';
                  echo '<tr>';
                  echo '<td>'.html_escape($row->city).'</td>';
                  echo '<td>'.html_escape($row->state).'</td>';
                  echo '<td>'.html_escape($row->country).'</td>';
                  echo '<td>'.$status.'</td>';
                  echo '</tr>';
                }
                ?>
                </tbody>
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
<?php include"comman/code_js.php"; ?>
<script>
$(document).ready(function () {
  $('#example2').DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false
  });
});
</script>
<script>$(".city-list-active-li").addClass("active");</script>
</body>
</html>
