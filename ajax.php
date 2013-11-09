<?php


require_once("assets/citrix.php");


$accessToken = "b5c1af29e94b7759907b3d8c9265c2d1";
$organizerkey = "5494652480797950726";
	
//$WebinarKeyID = "4636901471458403072"; // change this later, fetch it from DB
	
$apiKey = "9f9b11e5561599fe8e081defc994f007";
$callback = "http://forexflowtrading.com";

$citrix = new CitrixAPI($accessToken, $organizerkey);



switch($_POST['action']) {
	case "fetchwebinar" : fetchwebinar($_POST['webinarid']); break;
	case "create" : create($_POST['id'],$_POST['firstname'],$_POST['lastname'],$_POST['email']);break;
	case "webinfusion" : webinfusion($_POST['id'],$_POST['firstname'],$_POST['lastname'],$_POST['email']);break;
}

//DB operation

function create($id, $firstname, $lastname, $email) {
	global $citrix;
	$citrix->_webinarkey = $id;
	$response = $citrix->createRegistrant(strip_tags(pg_escape_string(($firstname))), 
				 strip_tags(pg_escape_string(($lastname))), 
				 strip_tags(pg_escape_string(($email))));
	echo $response;
}

function fetchwebinar($webinarid) {
	
	global $citrix;
	echo $citrix->getWebinar($webinarid);
}

function webinfusion($id, $firstname, $lastname, $email) {
	global $citrix;
	$citrix -> _webinarkey = $id;
	$response = $citrix->createRegistrant(strip_tags(pg_escape_string(($firstname))), 
				 strip_tags(pg_escape_string(($lastname))), 
				 strip_tags(pg_escape_string(($email))));
	echo $response;
}
