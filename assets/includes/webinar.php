<?php
global $wpdb;
$flag = 2;
if(isset($_POST['submit'])) {
	$citrix = new CitrixAPI();
	$response = json_decode($citrix->directLogin($_POST['email'], 
								$_POST['password'], 
								$_POST['apikey']));	
	//print_r($response);die();
	$data = array("apikey" => sanitize_text_field($_POST['apikey']),
				"email" => sanitize_email($_POST['email']),
				"password" => sanitize_text_field($_POST['password']),
				"access_token" => $response->access_token,
				"org_key" => $response->organizer_key );
	if($response->access_token) {
		$result = $wpdb->query("SELECT id FROM ".$wpdb->prefix. "formengine_webinar WHERE email='$_POST[email]'");
		
		if($wpdb->num_rows == 0) {	
			$wpdb->insert($wpdb->prefix. "formengine_webinar", 
					$data);
		}
		else {
			$wpdb->update($wpdb->prefix. "formengine_webinar", $data, 
						array("email" => $_POST['email']));
		}
		$flag = 1;
	}				
	else {
		$flag = 0;
		
	}
						
}
if($flag == 1) {
	echo "<div class='updated'>You have successfully logged in, the access token is saved in the 
	database</div>";
}
elseif($flag == 0) {
	echo "<div class='updated'>An error occured, please check the login credentials</div>";
}
$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."formengine_webinar");	
?>
<form method="post">
<table style="margin-left: 200px; margin-top: 50px;">
	<tr>
		<td>API Key</td>
		<td><input type="text" name="apikey" value=<?php echo $result[0]->apikey; ?> /></td>
	</tr>
	<tr>
		<td>Email</td>
	<td><input type="text" name="email" value="<?php echo $result[0]->email; ?>" /></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="text" name="password" value="<?php echo $result[0]->password; ?>" /></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Get Token" name="submit" /></td>
	</tr>
</table>
</form>