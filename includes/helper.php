<?php
if(!function_exists('lipnamo_array_key_exists')){
	function lipnamo_array_key_exists($key, $atts, $default = ''){
		if($atts && is_array($atts)){
			if(array_key_exists($key, $atts)){
				if(!empty($atts[$key])){
					return $atts[$key];
				}
				
				return $default;
			}
		}
		
		return $default;
	}
}

if(!function_exists('lipnamo_get_option')){
	function lipnamo_get_option($key){
		return lipnamo_array_key_exists($key, get_option('lipsum-dynamo'));
	}
}