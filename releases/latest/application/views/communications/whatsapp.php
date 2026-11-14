<?php
$workspace_key='whatsapp';require __DIR__.'/_context.php';$workspace_name='WhatsApp';$workspace_symbol='W';$workspace_development=true;$workspace_inbox=true;
$workspace_menu=array('inbox'=>'Inbox','connection'=>'Connection','campaigns'=>'Campaigns');
$titles=array('inbox'=>array('WhatsApp inbox','Read and reply to customer conversations.'),'conversation'=>array('WhatsApp conversation','Your customer conversation and reply history.'),'connection'=>array('WhatsApp connection','Check whether your existing WhatsApp connection is configured.'),'campaigns'=>array('WhatsApp campaigns','Campaign activation is under development.'));
if(!isset($titles[$workspace_section]))$workspace_section='inbox';list($section_title,$section_description)=$titles[$workspace_section];$section_action=null;
$this->load->view('communications/_frame_start',get_defined_vars());
if($workspace_section==='campaigns'): ?><section class="comm-status-panel"><div><span class="comm-badge off">Under development</span><h2>WhatsApp campaigns</h2><p class="muted">Campaigns and new subscriptions will become available after your Administrator enables them. Your existing inbox and conversation history are available separately.</p></div></section><?php else:$this->load->view('communications/_inbox',get_defined_vars());endif;
$this->load->view('communications/_frame_end');
