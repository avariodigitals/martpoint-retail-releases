<?php

function store_module(){
    if (function_exists('mp_feature_flag_raw')) {
        $flag = mp_feature_flag_raw('multi_store');
        if ($flag !== null) {
            return $flag;
        }
    }
    return false;
  }

function special_access(){
	if(is_admin()){//is saas admin
		return true;
	}
	else if(is_store_admin()){
		if(store_module()){//is saas activated ?
			return false;
		}
		return true;
	}
}