<?php
$destination=in_array($channel??'',array('sms','email','whatsapp','socials','automation'),true)?$channel:'overview';
$this->load->view('communications/'.$destination,get_defined_vars());
