<?php
global $wpdb;
error_reporting(1);

$table = $wpdb->prefix . "jumpforms";	
$table_webinar = $wpdb->prefix . "jumpforms_webinar_data"; 
$flag = 2;
if(isset($_POST['submit'])) {
	$citrix = new CitrixAPI();
	$response = json_decode($citrix->directLogin($_POST['email'], 
								$_POST['password'], 
								$_POST['apikey']));	
	
	$data = array("apikey" => sanitize_text_field($_POST['apikey']),
				"email" => sanitize_email($_POST['email']),
				"password" => sanitize_text_field($_POST['password']),
				"access_token" => $response->access_token,
				"org_key" => $response->organizer_key );
	if($response->access_token) {
		$result = $wpdb->query("SELECT id FROM ".$wpdb->prefix. "jumpforms_webinar WHERE email='$_POST[email]'");
		
		if($wpdb->num_rows == 0) {	
			$wpdb->insert($wpdb->prefix. "jumpforms_webinar", 
					$data);
		}
		else {
			$wpdb->update($wpdb->prefix. "jumpforms_webinar", $data, 
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
$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix ."jumpforms_webinar");	

if(isset($_POST['inf_save'])) {
	$result1 = $wpdb->get_results("SELECT * FROM $table");
	foreach ($result1 as $res) {
		$wpdb->update($table, array("webinar" => 0), array("id" => $res->id));
	}
	if($_POST['addinf'])
		foreach($_POST['addinf'] as $a) {
			$wpdb->update($wpdb->prefix."jumpforms", array("webinar" => '1'), array("id" => $a));
			$i = $wpdb->get_var("SELECT id FROM $table_webinar WHERE id=$a");
			if(!$i) {
	 			$wpdb->query("INSERT INTO $table_webinar(formid) VALUES('$a')");
			}
		}
}

if(isset($_POST['save_feed'])) {
	
	$ids = $wpdb->get_results("SELECT id FROM $table_webinar");
	foreach($ids as $id) {
		$data = array(
					"email" => $_POST['email-'.$id->id],
					"first_name" => $_POST['firstname-'.$id->id],
					"last_name" => $_POST['lastname-'.$id->id],
					"webinar" => $_POST['sel-webinar-'.$id->id]
					);	
		
		$where = array("id" => $id->id);
		$wpdb->update($table_webinar, $data, $where);	
	}
}
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
			<li><a class="current"><?php _e('Webinar','jumpforms'); ?></a></li>
			
		</ul>
				
		<div id="tdmfw_content">
			<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;">
				<a id="settings"> <?php _e('Webinar Settings ','jumpforms');?></a>|
				<a id="feeds"><?php _e('Webinar Feeds','jumpforms'); ?></a>
				<a id="addinf" style="float: right;"><?php _e('Add Form','jumpforms'); ?></a>	
			</p>
			<?php
			$webinarForms = $wpdb->get_results("SELECT id,title FROM $table");
			?>
			<div class="tdmfw_box_content">
				<div id="settingsview" style="display: none;">
					
					<form method="post">
					
						<table>
							<tr>
							<td>API Key</td>
							<td><input type="text" name="apikey" value=<?php echo $result[0]->apikey; ?> ></td>
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
					<b>Forms Intergrated with Webinar</b>
					<?php 
					$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$table_webinar." ON ".$table.".id=".$table_webinar.".formid AND $table.webinar=1");
					//echo "SELECT * FROM $table INNER JOIN ".$table_webinar." ON ".$table.".id=".$table_webinar.".formid AND $table.webinar=1"; die();
					//print_r($results);die();
					echo "<table>";
					
					foreach($results as $r) {
						//echo $r."oooo";die();
						//echo "SELECT email,first_name,last_name FROM $table_infusionsoft WHERE formid='$r->id'";die("asdasd");
						$values = $wpdb->get_results("SELECT * FROM $table_webinar WHERE formid=$r->id");
						//print_r($values);//die();
						//print_r($r);die();
						$order  = $r->sortorder;
						$sortrows = explode(",", $order);
						?>
						<tr><td><a id="<?php $r->id ?>"><?php echo $r->title; ?></a></td></tr>
						<tr>
							<td>Select Webinar</td>
							<td>
							<select name="sel-webinar-<?php echo $r->id; ?>">
							<option value=""></option>
							<?php foreach ($sortrows as $counter) {
							//$val = $wpdb->get_var("SELECT email FROM $table_infusionsoft WHERE id=''");
							$type = 'f'.$counter.'_type';
							$label = 'f'.$counter.'_label';
							if($r->$type == "dropdown" ) {
							?>
								<option value="<?php echo $counter; ?>" <?php if($values[0]->webinar == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
							<?php 
							} 
							?>
				
							<?php } ?>
							</select>
							</td>
						</tr>
						<tr>
							<td>Email</td>
							<td>
							<select name="email-<?php echo $r->id; ?>">
							<option value=""></option>
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
							<option value=""></option>
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
							<option value=""></option>
							<?php foreach ($sortrows as $counter) {
								
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
						
						
					<?php
					} echo "</table>";?>
					<input type="submit" value="Save Changes" name="save_feed" />
					</form>
				</div>	
				
				<div id="addinfview" style="display: none;">
				
					<form method="post"> 
					
						<table>
						<b>Add Form Name to Webinar</b>
						<?php foreach ($webinarForms as $inf) {
								$i = $wpdb->get_var("SELECT webinar FROM $table WHERE id=$inf->id");
							  ?>
							<tr><td><?php echo $inf->title; ?></td><td><input type="checkbox" <?php if($i == "1") echo "checked='checked'" ?> name="addinf[]" id="addinf" value="<?php echo $inf->id; ?>" /></td></tr>
						<?php } ?>
						<tr>
						<td>
						<input type="submit" class="btn btn-primary" value="Save Changes" name="inf_save" />
						</td>
						</tr>
					</table>
						
					</form>
					
				</div>	
			</div>
		</div>	
	</div>
</div>



