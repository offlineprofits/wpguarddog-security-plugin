<?php

global $wpdb;
require_once("../../../../../wp-config.php");	
$fid = $_GET['fid'];
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$db = DB_NAME;
$table = $wpdb->prefix . "jumpforms_data";
$file = 'export_fid'.$fid;

$link = mysql_connect($host, $user, $pass) or die("Can not connect." . mysql_error());
mysql_select_db($db) or die("Can not connect.");

$result = mysql_query("SHOW COLUMNS FROM ".$table."");
$i = 0;
if (mysql_num_rows($result) > 0) {
while ($row = mysql_fetch_assoc($result)) {
if(isset($csv_output)) { } else { $csv_output = ''; }
$csv_output .= $row['Field'].", ";
$i++;
}
}
$csv_output .= "\n";

$values = mysql_query("SELECT * FROM ".$table." WHERE fid=".$fid."");
while ($rowr = mysql_fetch_row($values)) {
for ($j=0;$j<$i;$j++) {

	if($j==0) {
		$csv_output .= $rowr[$j].", ";
	} else {
		$csv_output .= $rowr[$j].", ";	
	}

}
$csv_output .= "\n";
}

$filename = $file."_".date("Y-m-d_H-i",time());
header("Content-type: application/vnd.ms-excel");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header( "Content-disposition: filename=".$filename.".csv");
print $csv_output;
exit;

?>