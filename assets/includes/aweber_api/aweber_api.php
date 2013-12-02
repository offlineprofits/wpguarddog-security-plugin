<?php

if (class_exists('AWeberAPI')) {
    trigger_error("Duplicate: Another AWeberAPI client library is already in scope.", E_USER_WARNING);
}
else {
	include_once( plugin_dir_path( __FILE__ ) . '/aweber.php');
    //require_once('aweber.php');
}
