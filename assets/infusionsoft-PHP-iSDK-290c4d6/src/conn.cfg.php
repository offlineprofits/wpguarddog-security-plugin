<?php
require_once('../../../../wp-load.php');
$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."infusion_settings");
$connectionName = $result['inf_domain'];
$infkey = $result['inf_key'];
$connInfo = array('connectionName:oe152:i:af71be1019e8c01c8e61e5a0c9af5569:This is the connection for oe152.infusionsoft.com');
?>
