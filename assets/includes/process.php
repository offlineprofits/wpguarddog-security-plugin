<?php

if (isset($_POST)) {
	require_once("../../../../../wp-config.php");
	
    global $wpdb;
    $table = $wpdb->prefix . "jumpforms";
    $table2 = $wpdb->prefix . "jumpforms_data";
	$table3 = $wpdb->prefix . "jumpforms_infusion";
    $fid = $_POST['fid'];
    $row = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
	
    if($row->webinar) {
    	require_once("../citrix.php");
		$accesstoken = $row->accesstoken;
		$organizerkey = $row->organizerkey;
		$citrix = new CitrixAPI($accesstoken, $organizerkey);	
    }
	if($row->infusion) {
		$val = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jumpforms_infusion WHERE formid='$row->id'");
		$email = $_POST["f".$val[0]->email."_label"];
		$fname = $_POST["f".$val[0]->first_name."_label"];
		$lname = $_POST["f".$val[0]->last_name."_label"];
		$groupId = $val[0]->tagid;
		require_once("../PHP-iSDK-master/src/isdk.php");
		$isdk = new iSDK();
		$isdk->cfgCon("connectionName");
		$con_id = $isdk->findByEmail($email,array('Id'));
		if(!$con_id) {
			$conDat = array('FirstName' => $fname,
		            'LastName'  => $lname,
		            'Email'     => $email);
			$conID = $isdk->addCon($conDat);
			if($groupId) {
				$result = $isdk->grpAssign($conID, $groupId);
			}
		}
	} 
	if($row->aweber) {
		
		$val = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jumpforms_aw WHERE formid='$row->id'");
		$email = $_POST["f".$val[0]->email."_label"];
		$fname = $_POST["f".$val[0]->first_name."_label"];
		$lname = $_POST["f".$val[0]->last_name."_label"];
		$listid = $wpdb->get_var("SELECT aweber_list_id FROM $table WHERE id='$row->id'");
		echo $listid;
		require_once("add_subscriber.php");
		$aweber = new aweber();
		if($listid) {
			$aweber->add_subscriber($email, $_SERVER["REMOTE_ADDR"], $fname." ".$lname, $listid);
		} 
	}
		
	// SugarCRM
	if($row->sugarfree) {
		define(sugarEntry,true);
		//die("inside");
		$val = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jumpforms_sugarfree WHERE formid='$row->id'");
		//$contents = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jumpforms WHERE formid='$row->id'");
		
		//echo "<pre>";print_r($_POST);die();
		require_once('nusoap/nusoap.php');  
		$url = get_option("sugarfree_url");
		$username = get_option("sugarfree_username");
		$password = get_option("sugarfree_password");
		//print_r($url);echo "<br />";
		//print_r($username);echo "<br />";
		//print_r($password);echo "<br />";
		 
		$client = new nusoapclient($url.'/soap.php?wsdl',true);
		
		$user_auth = array(
		                'user_auth' => array(
		                'user_name' => $username,
		                'password' => md5($password),
		                'version' => '0.1'
		        ), 'application_name' => 'wp-sugar-pro');
		$login = $client->call('login',$user_auth);
		//print_r($login);echo "<br />";
		$session_id = $login['id'];
		//print_r($login);echo "<br  />";
		//echo "<pre>";
		//$recordInfo = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Opportunities'));
		//echo "recored info";print_r($recordInfo);
		//die();
		//$firstname = $val[0]->firstname; 
		//$lastname = $val[0]->lastname;
		//$email = $val[0]->email;
		$addas = $val[0]->addas;
		$module = ucfirst($addas);
		//echo $module."kkk";die();
		if($addas == "opportunity") {
			$params = unserialize($val[0]->value);
			$name = $_POST["f".$params['firstname']."_label"]." ".$_POST["f".$params['lastname']."_label"];
			//echo "<pre>";print_r($params);echo "<br />";
			$set_entry_params = array(  
	    		'session' => $session_id,  
			    'module_name' => "Opportunities",  
			    'name_value_list'=>array(  
			        array('name'=>'property_address_postalcode_c','value'=>$_POST["f".$params['zip']."_label"]),  
			        array('name'=>'reason_c','value'=>$_POST["f".$params['reason']."_label"]),  
			        array('name'=>'asking_price_c', 'value'=>$_POST["f".$params['aprice']."_label"]),
			        array('name'=>'property_address_street_c', 'value'=>$_POST["f".$params['address']."_label"]),
					array('name'=>'property_address_city_c', 'value'=>$_POST["f".$params['city']."_label"]),
					array('name'=>'seller_mobile_phone_c', 'value'=>$_POST["f".$params['phone']."_label"]),
					array('name'=>'seller_name_c', 'value'=>$name),
					//array('name'=>'lastname', 'value'=>$_POST["f".$params['lastname']."_label"]),
					//array('name'=>'entryid', 'value'=>$_POST["f".$params['entryid']."_label"]),
					array('name'=>'date_entered', 'value'=>$_POST["f".$params['entrydate']."_label"]),
					//array('name'=>'sourceurl', 'value'=>$_POST["f".$params['sourceurl']."_label"]),
					array('name'=>'ip_c', 'value'=>$_SERVER['REMOTE_ADDR'])
			        )
				); 			
			$result = $client->call('set_entry',$set_entry_params);  
			//print_r($set_entry_params);die();
			//print_r($result);die();	
		}
		
		//$recordInfo = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Opportunities'));
		/*$set_entry_params = array(  
	    'session' => $session_id,  
	    'module_name' => $module,  
	    'name_value_list'=>array(  
	        array('name'=>'first_name','value'=>$firstname),  
	        array('name'=>'last_name','value'=>$lastname),  
	        array('name'=>'email', 'value'=>$email)
	        )); */ 
	   
	}
	// End of SugarCRM
	
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
	
	$updateid = mysql_insert_id();
	$k = array_keys($_POST);	
	
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
					$value = plugins_url().'/jumpforms/assets/uploads/'.$trimname;
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
					$value = plugins_url().'/jumpforms/assets/uploads/'.$trimname;
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
					$value = plugins_url().'/jumpforms/assets/uploads/'.$trimname;
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