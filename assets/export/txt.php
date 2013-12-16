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
$csv_output .= $row['Field']."\t";
$i++;
}
}
$csv_output .= "\n";

$values = mysql_query("SELECT * FROM ".$table." WHERE fid=".$fid."");
while ($rowr = mysql_fetch_row($values)) {
for ($j=0;$j<$i;$j++) {
$csv_output .= $rowr[$j]."\t";
}
$csv_output .= "\n";
}

$filename = $file."_".date("Y-m-d_H-i",time());
header("Content-type: plain/text");
header("Content-disposition: txt" . date("Y-m-d") . ".txt");
header( "Content-disposition: filename=".$filename.".txt");
print $csv_output;
exit;

?>