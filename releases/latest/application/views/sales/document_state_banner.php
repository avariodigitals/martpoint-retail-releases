<?php
$ci =& get_instance();
$stateQuery=$ci->db->query('SELECT sales_status FROM db_sales WHERE id=?',array((int)($sales_id ?? 0)));
$stateRow=$stateQuery?$stateQuery->row():null;
if($stateRow && $stateRow->sales_status==='Draft'):
?>
<div style="border:2px solid #b45309;padding:12px;margin:10px 0;color:#92400e;font-weight:bold;text-align:center;">DRAFT — NOT ISSUED. This is not a request for payment.</div>
<?php endif; ?>
