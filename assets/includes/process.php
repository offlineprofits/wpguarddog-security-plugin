<?php

if (isset($_POST)) {
	//print_r($_POST);die();
	require_once("../../../../../wp-config.php");
	
    global $wpdb;
    $table = $wpdb->prefix . "formengine";
    $table2 = $wpdb->prefix . "formengine_data";
	$table3 = $wpdb->prefix . "formengine_infusion";
    $fid = $_POST['fid'];
    $row = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
    if($row->webinar) {
    	require_once("../citrix.php");
		$accesstoken = $row->accesstoken;
		$organizerkey = $row->organizerkey;
		$citrix = new CitrixAPI($accesstoken, $organizerkey);	
    }
	if($row->infusion) {
		require_once("../PHP-iSDK-master/src/isdk.php");
		$isdk = new iSDK();
		$infusion->cfgCon("connectionName");
		//$cMap = array(emai,firstname,lastname);
		//$infusion->addCon($cMap);
		
	}
	if($row->aweber) {
		$listid = $fid = $wpdb->get_var("SELECT aweber_list_id FROM $table WHERE id='$row->id'");
		require_once("../add_subscriber.php");
		$aweber = new aweber();
		$val = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."formengine_aweber WHERE formid='$row->id'");
		$email = $row["f".$val[0]->email."_value"];
		$fname = $row["f".$val[0]->first_name."_value"];
		$lname = $row["f".$val[0]->last_name."_value"];
		$aweber->add_subscriber($email, $_SERVER["REMOTE_ADDR"], $fname." ".$lname, $listid); 
	}
	
    if($row->captcha == "on") {
		session_start();
				
		if(isset($_POST['security_code']) && !empty($_SESSION['security_code'])) {
			if(($_SESSION['security_code'] == $_POST['security_code'])) {
				unset($_SESSION['security_code']);
			} else { wp_redirect($row->errorredirect, 301 ); exit; }
		}
	}	
			
	$go = $wpdb->insert( 
	$table2, 
	array( 
		'fid' => $fid 
	));
	//print_r($row);die();
	$updateid = mysql_insert_id();
	$k = array_keys($_POST);	
	if($row->infusion) {
		//$infusion = new iSDK();
		//$infusion->cfgCon("connectionName");
		//$infusion->addCon($cMap);
	}
	/*$purl = "https://pti.infusionsoft.com/app/form/process/".$_POST[$k[1]][0];
	
	if($row->infusion) {
		$results = $wpdb->get_results("SELECT * FROM $table3 WHERE formid=$fid");
		$workArray = explode(",", $results[0]->values);
		$postString = "";
 		foreach($workArray as $wa) {
 			echo "<br />";
			$new = explode(":",$wa);
			if($new[1] != "Don\'t Map") {
				$postString = $postString.$new[0]."=".$new[1]."&";
			}
		}  
		$postString = substr($postString, 0, -1);
		$purl = $results[0]->links;
		$citrix->infusionRegister($postString, $purl);
	}*/
	
	
	
	
	if($row->webinar == "1") {
		
		$webinarkey = $_POST['f2_label'];
		$email = $_POST['f3_label'];
		$fname = $_POST['f6_label'];
		$lname = $_POST['f7_label'];
		
		$citrix->_webinarkey = $webinarkey;
		
		$response = $citrix->createRegistrant($fname, $lname, $email);
				
		$r = json_decode($response);
		
		if (array_key_exists('joinUrl', $r)) {
			header('Location: '.$row->redirect);
			die(); // just in case
		}
		else {
			header('Location: '.$row->errorredirect);
			die(); // just in case
		}
	}
	
	
	
	/*---------------------------------------------------------------*/
	$order  = $row->sortorder;
	$sortrows = explode(",", $order);
	
	foreach ($sortrows as $counter) {	
		$label = 'f'.$counter.'_label';
		$type = 'f'.$counter.'_type';
		$datalabel = 'f'.$counter;
		$datavalue = 'f'.$counter.'_value';
		
		if(isset($_POST[$datalabel])) { $question = $_POST[$datalabel]; } else { $question = ''; }
		if(isset($_POST[$label])) { $value = $_POST[$label]; } else { $value = ''; }

		// CHECKBOXES
 		if(is_array($value)) { 

			$selected_cb = "";
			foreach ($value as $cb) {
			$selected_cb .= $cb . ", ";
			}
			$value = substr($selected_cb, 0, -2);
		}
		
		// UPLOADS
		
		if(isset($_FILES[$label])) { 
			if ($_FILES[$label]["error"] > 0) {
				header('Location: '.$row->errorredirect);
			} else {
				if (is_uploaded_file($_FILES[$label]["tmp_name"])) {
					$trimname = str_replace(' ','',$_FILES[$label]["name"]);
					move_uploaded_file($_FILES[$label]["tmp_name"],'../uploads/'.$trimname);
					$value = plugins_url().'/formengine/assets/uploads/'.$trimname;
				}
			}
		}
		
		$go = $wpdb->update( 
		$table2, 
		array( 
			$datalabel => stripslashes_deep($question),
			$datavalue => stripslashes_deep($value) 
		), array( 'ID' => $updateid )); 
		
	}
	
	// EMAIL
	
	if(isset($headers)) { } else { $headers = ''; }
	$headers . "From: " . $row->email . "\r\n";
	
	$subject = $row->notifysubject;

	// EMAIL BODY W/ DATA
	if($row->notifytype == "full") {
	
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	$body .= '<html><body>';
	$body .= '<p>'.$row->notifymessage.'</p>';
	
	$body .= '<table style="width:100%;border:1px solid #F5F5F5;" cellspacing="0" cellpadding="10" border="0">';
	$body .= '<tr style="background:#F5F5F5;font-weight:bold;"><td colspan="2" style="width:100%;">'.$row->title.'</td></tr>';
	$body .= '<tr style="background:#F5F5F5;"><td style="width:50%;">Question</td><td style="width:50%;">Response</td></tr>';
	
	$order  = $row->sortorder;
	$sortrows = explode(",", $order);

	foreach ($sortrows as $counter) {
		$label = 'f'.$counter.'_label';
		$datalabel = 'f'.$counter;
		$question = stripslashes_deep($_POST[$datalabel]);
		$value = stripslashes_deep($_POST[$label]);
				
		// CHECKBOXES
 		if(is_array($value)) { 

			$selected_cb = "";
			foreach ($value as $cb) {
			$selected_cb .= $cb . ", ";
			}
			$value = substr($selected_cb, 0, -2);
		}
		
		// UPLOADS
		if ($_FILES[$label]["error"] > 0) {
			header('Location: '.$row->errorredirect);
		} else {
			if (is_uploaded_file($_FILES[$label]["tmp_name"])) {
					$trimname = str_replace(' ','',$_FILES[$label]["name"]);
					move_uploaded_file($_FILES[$label]["tmp_name"],'../uploads/'.$trimname);
					$value = plugins_url().'/formengine/assets/uploads/'.$trimname;
			}
		}
		
		// EMAIL ADDRESS
		if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$userto = $value;
		}
		
		if($value != '') {
			//$body .= $question." - ".$value." \n";
			$body .= "<tr><td style='width:50%;'>".stripslashes_deep($question)."</td><td style='width:50%;'>".stripslashes_deep($value)."</td></tr>";
		}
		
	}
	
	$body .= '</table>';
	
	}
	
	if($row->notifytype == "basic") {
	
	$order  = $row->sortorder;
	$sortrows = explode(",", $order);

	foreach ($sortrows as $counter) {
		$label = 'f'.$counter.'_label';
		$datalabel = 'f'.$counter;
		
		if(isset($_POST[$datalabel])) { $question = stripslashes_deep($_POST[$datalabel]); } else { $question = ''; }
		if(isset($_POST[$label])) { $value = stripslashes_deep($_POST[$label]); } else { $value = ''; }
						
		// CHECKBOXES
 		if(is_array($value)) { 

			$selected_cb = "";
			foreach ($value as $cb) {
			$selected_cb .= $cb . ", ";
			}
			$value = substr($selected_cb, 0, -2);
		}
		
		// UPLOADS
		if(isset($_FILES[$label])) { 
			if ($_FILES[$label]["error"] > 0) {
				header('Location: '.$row->errorredirect);
			} else {
				if (is_uploaded_file($_FILES[$label]["tmp_name"])) {
					$trimname = str_replace(' ','',$_FILES[$label]["name"]);
					move_uploaded_file($_FILES[$label]["tmp_name"],'../uploads/'.$trimname);
					$value = plugins_url().'/formengine/assets/uploads/'.$trimname;
				}
			}
		}
		
		// EMAIL ADDRESS
		if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$userto = $value;
		}
	}
		
		// EMAIL BODY W/O DATA
		$body = "New form completed! You need to login to view the submission: ".get_bloginfo('url')."/wp-admin";
		
	}
		
	// EMAIL OPTIONS
	if($row->notify == "admin") {
		$to = $row->email;
		mail($to, $subject, $body, $headers);
	}
	
	if($row->notify == "user") {
		mail($userto, $subject, $body, $headers);
	}
	
	if($row->notify == "adminuser") {
		$to = $row->email;
		mail($to, $subject, $body, $headers);
		mail($userto, $subject, $body, $headers);
	}
	
	// FORM SENT
	header('Location: '.$row->redirect);
	
}

?>	