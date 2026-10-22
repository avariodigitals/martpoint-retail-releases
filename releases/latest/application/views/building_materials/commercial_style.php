<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Share Garden's editor structure; Building Materials supplies neutral tokens.
if(!isset($building_editor_style) || $building_editor_style)$this->load->view('garden/document_editor_style');
?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/building_commercial.css?v='.filemtime(FCPATH.'theme/dist/css/building_commercial.css')); ?>">
<?php
$commercial_id=(int)$this->uri->segment(3);$commercial_controller=strtolower($this->router->fetch_class());$commercial_method=$this->router->fetch_method();
$commercial_mobile_routes=[
    'sales'=>['index'=>'mobile/sales_list','add'=>'sales/add?mobile=1','update'=>'sales/update/'.$commercial_id.'?mobile=1','quotation'=>'sales/quotation/'.$commercial_id.'?mobile=1','invoice'=>'mobile/sales_invoice/'.$commercial_id],
    'quotation'=>['index'=>'mobile/quotations','add'=>'mobile/quotation_form','update'=>'mobile/quotation_form/'.$commercial_id,'invoice'=>'mobile/quotation_view/'.$commercial_id],
];
$commercial_mobile_target=$commercial_mobile_routes[$commercial_controller][$commercial_method] ?? null;
if($commercial_mobile_target): ?>
<script>(function(){function commercialScreen(){if(document.activeElement&&document.activeElement.matches('input,textarea,[contenteditable=true]'))return;if(matchMedia('(max-width:768px), (max-width:1024px) and (orientation:portrait)').matches)location.replace(<?= json_encode(base_url($commercial_mobile_target)); ?>);}commercialScreen();window.addEventListener('resize',commercialScreen);})();</script>
<?php endif; ?>
