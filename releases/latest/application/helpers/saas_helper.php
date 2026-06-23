<?php

function store_module(){
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