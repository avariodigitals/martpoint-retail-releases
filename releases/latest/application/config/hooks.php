<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

function mp_set_sql_mode() {
    $CI =& get_instance();
    if (isset($CI->db) && $CI->db->conn_id) {
        $CI->db->query("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
    }
}

$hook['post_controller_constructor'] = [
    'class'    => '',
    'function' => 'mp_set_sql_mode',
    'filename' => '',
    'filepath' => '',
    'params'   => []
];
