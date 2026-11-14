<?php
$workspace_key='socials';require __DIR__.'/_context.php';$workspace_name='Socials';$workspace_symbol='#';$workspace_development=true;$workspace_inbox=true;
$workspace_menu=array('inbox'=>'Inbox','connection'=>'Connection','dashboard'=>'Channels');$section_action=null;
$section_title=$workspace_section==='conversation'?'Social conversation':($workspace_section==='connection'?'Social connections':($workspace_section==='dashboard'?'Social channels':'Social inbox'));
$section_description='Instagram and Facebook conversations stay separate from WhatsApp.';
$this->load->view('communications/_frame_start',get_defined_vars());
if($workspace_section==='dashboard'): ?><div class="comm-grid"><?php foreach(array('Instagram','Facebook Messenger','TikTok') as $platform): ?><article class="comm-card"><h2><?= $h($platform); ?></h2><p class="muted"><?= $platform==='TikTok'?'Under development.':'Existing customer conversations are available in the inbox. Additional campaign features are under development.'; ?></p></article><?php endforeach; ?></div><?php else:$this->load->view('communications/_inbox',get_defined_vars());endif;
$this->load->view('communications/_frame_end');
