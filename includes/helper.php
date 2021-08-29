<?php
if(!function_exists('lipnamo_array_key_exists')){
	function lipnamo_array_key_exists($key, $atts, $default = ''){
		if($atts && is_array($atts)){
			if(array_key_exists($key, $atts)){
				return (!empty($atts[$key])) ? $atts[$key] : $default;
			}
		}
		
		return $default;
	}
}