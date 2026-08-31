<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('mp_migrate_status_badge')){
	function mp_migrate_status_badge($status){
		$map = [
			'ok'                  => 'label label-success',
			'old table not found' => 'label label-warning',
			'no matching columns' => 'label label-warning',
			'passwords reset'     => 'label label-info',
		];
		$cls = isset($map[$status]) ? $map[$status] : 'label label-default';
		return '<span class="'.$cls.'">'.htmlspecialchars($status).'</span>';
	}
}
