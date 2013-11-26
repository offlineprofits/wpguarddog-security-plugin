<?php
global $wpdb;
error_reporting(1);
$infusion = new iSDK();
//$oldkey = "f3f94c143d755752a2f64d1a53820f90";
$infusion->cfgCon("connectionName");
$webForm = $infusion->getWebFormMap();
global $wpdb;
$table = $wpdb->prefix . "formengine";	
$table_infusionsoft = $wpdb->prefix . "formengine_infusion"; 
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





<div id="tdmfw">
<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
<ul id="tdmfw_crumbs">
	<li><a href="?page=formengine_dashboard">JumpForms</a></li>
	<li><a class="current"><?php _e('Infusionsoft','formengine'); ?></a></li>
	
</ul>
		
<div id="tdmfw_content">
<div class="tdmfw_box" style="margin-top:0;">
<p class="tdmfw_box_title" style="margin-top:0;">
	<a id="settings"> <?php _e('Infusionsoft Settings ','formengine');?></a>|
	<a id="feeds"><?php _e('Infusionsoft Feeds','formengine'); ?></a>
	<a id="addinf" style="float: right;"><?php _e('Add Form','formengine'); ?></a>	
</p>
<?php
$infusionForms = $wpdb->get_results("SELECT id,title FROM $table");
?>
<?php
if(isset($_POST['settings_submit'])) {
	$settings = $wpdb->query("SELECT * FROM ".$wpdb->prefix."formengine_infusion_settings");
	$wpdb->query("DELETE FROM ".$wpdb->prefix."formengine_infusion_settings");
	$wpdb->insert($wpdb->prefix."formengine_infusion_settings", array(
				"inf_key" => $_POST['apikey'],
				"inf_domain" => $_POST['subdomain']));	
	
}
?>
<div class="tdmfw_box_content">
<div id="settingsview" style="display: none;">
	<?php
	$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."formengine_infusion_settings");
	?>
	<form method="post">
	
			<table>
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
	
</div>	

<div id="feedsview" style="display: none;">
	<form method="post">
	<b>Forms Intergrated with Infusionsoft</b>
	<?php 
	$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$wpdb->prefix."formengine_infusion  ON ".$table.".id=".$wpdb->prefix."formengine_infusion.formid");
	echo "<table>";
	
	foreach($results as $r) {
//echo $r."oooo";die();
	//echo "SELECT email,first_name,last_name FROM $table_infusionsoft WHERE formid='$r->id'";die("asdasd");
		$values = $wpdb->get_results("SELECT email,first_name,last_name FROM $table_infusionsoft WHERE formid=$r->id");
		//print_r($values);//die();
		//print_r($r);die();
		$order  = $r->sortorder;
		$sortrows = explode(",", $order);
	?>
	<tr><td><a id="<?php $r->id ?>"><?php echo $r->title; ?></a></td></tr>
	<tr>
		<td>Email</td>
		<td>
		<select name="email-<?php echo $r->id; ?>">
		<?php foreach ($sortrows as $counter) {
		//$val = $wpdb->get_var("SELECT email FROM $table_infusionsoft WHERE id=''");
		$type = 'f'.$counter.'_type';
		$label = 'f'.$counter.'_label';
		if($r->$type == "email" ) {
		?>
			<option value="<?php echo $counter; ?>" <?php if($values[0]->email == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
		<?php 
		} 
		?>
		
		<?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>First Name</td>
		<td>
		<select name="firstname-<?php echo $r->id; ?>">
		<?php foreach ($sortrows as $counter) {
			
		$type = 'f'.$counter.'_type';
		$label = 'f'.$counter.'_label';
		if($r->$type == "input" ) {
		?>
			<option value="<?php echo $counter; ?>" <?php if($values[0]->first_name == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
		<?php 
		} 
		?>
		
		<?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>Last Name</td>
		<td>
		<select name="lastname-<?php echo $r->id; ?>">
		<?php foreach ($sortrows as $counter) {
		//echo "<script>alert('Main   ".$values[0]->last_name."');</script>";	
		//echo "<script>alert('sub  ".$counter."');</script>";	
		$type = 'f'.$counter.'_type';
		$label = 'f'.$counter.'_label';
		if($r->$type == "input" ) {
		?>
			<option value="<?php echo $counter; ?>" <?php if($values[0]->last_name == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
		<?php 
		} 
		?>
		
		<?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>Tag Id</td>
		<td><input type="text" name="tagid-<?php echo $r->id; ?>" value="<?php echo $values[0]->tagid ?>" /></td>
	</tr>
	</tr>
	<?php
	} echo "</table>";?>
	<input type="submit" value="Save Changes" name="save_feed" />
</form>
</div>	

	<div id="addinfview" style="display: none;">
	
		<form method="post"> 
		
			<b>Add Form Name to InfusionSoft</b>
			
		</form>
		
	</div>	
</div>
</div>	
</div>
</div>









<!--
