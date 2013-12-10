<?php


/* test */
//require_once('nusoap/nusoap.php');  
/*$url = get_option("sugarfree_url");
$username = get_option("sugarfree_username");
$password = get_option("sugarfree_password");
$client = new nusoapclient($url.'/soap.php?wsdl',true);*/
//print_r($client);die("shakl");
/*$user_auth = array(
                'user_auth' => array(
                'user_name' => $username,
                'password' => md5($password),
                'version' => '0.1'
        ), 'application_name' => 'wp-sugar-pro');
$login = $client->call('login',$user_auth);*/
//print_r($login);die("iumm");
/*        $session_id = $login['id'];
$recordInfo = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Opportunities'));
echo "<pre>";
print_r($recordInfo);die();*/


// values, then assign to the authenticated Sugar user...  
/*$set_entry_params = array(  
    'session' => $session_id,  
    'module_name' => 'Opportunities',  
    'name_value_list'=>array(  
        array('name'=>'first_name','value'=>'test'),  
        array('name'=>'last_name','value'=>'test'),  
        array('name'=>'status', 'value'=>'New'),  
        array('name'=>'phone_work', 'value'=>'5509898878'),  
        array('name'=>'account_name','value'=>''),  
        array('name'=>'lead_source','value'=>'Web Site'),  
        array('name'=>'primary_address_street ','value'=>'123 n nowhere'),  
        array('name'=>'primary_address_city','value'=>'nine'),  
        array('name'=>'primary_address_state','value'=>'WA'),  
        array('name'=>'primary_address_postalcode','value'=>'99026'),  
        array('name'=>'email','value'=>'nothere@email.com'),  
        array('name'=>'description','value'=>''),  
        array('name'=>'assigned_user_id', 'value'=>'')));  
   
    $result = $client->call('set_entry',$set_entry_params);  
die("Finish");*/
//END 




global $wpdb;
$table = $wpdb->prefix . "jumpforms";	
$table_sugarfree = $wpdb->prefix . "jumpforms_sugarfree";
$forms = $wpdb->get_results("SELECT id,title FROM $table");
$result = $wpdb->get_results("SELECT * FROM ".$table_sugarfree);	
 
if(isset($_POST['test_submit'])) {
	$url = $_POST['url'];
	//$url = "http://localhost/SugarCE";
	//$client = new nusoapclient($url.'/soap.php?wsdl',true);
	$username = $_POST['uname']; 
	$password = $_POST['password'];
	update_option("sugarfree_url", $url);
	update_option("sugarfree_username", $username);
	update_option("sugarfree_password", $password);
	/*$set_entry_params = array(  
    'session' => $session_id,  
    'module_name' => 'Leads',  
    'name_value_list'=>array(  
        array('name'=>'first_name','value'=>'Jon'),  
        array('name'=>'last_name','value'=>'Doe'),  
        array('name'=>'status', 'value'=>'New'),  
        array('name'=>'phone_work', 'value'=>'5555555555'),  
        array('name'=>'account_name','value'=>''),  
        array('name'=>'lead_source','value'=>'Web Site'),  
        array('name'=>'primary_address_street ','value'=>'123 n nowhere'),  
        array('name'=>'primary_address_city','value'=>'nine'),  
        array('name'=>'primary_address_state','value'=>'WA'),  
        array('name'=>'primary_address_postalcode','value'=>'99026'),  
        array('name'=>'email','value'=>'nothere@email.com'),  
        array('name'=>'description','value'=>''),  
        array('name'=>'assigned_user_id', 'value'=>'')));  
   
    $result = $soapclient->call('set_entry',$set_entry_params);  */
	//$user_auth = array(
		//			'user_auth' => array(
			//		'user_name' => $username,
				//	'password' => $password,
					//'version' => '0.1'
					//),
				//'application_name' => 'wp-sugar-free');
	//$login = $client->call('login',$user_auth);
	//$session_id = $login['id'];
	 						
}


if(isset($_POST['inf_save'])) {
	$result1 = $wpdb->get_results("SELECT * FROM $table");
	foreach ($result1 as $res) {
		$wpdb->update($table, array("sugarfree" => 0), array("id" => $res->id));
	}
	if($_POST['addinf']) 
		foreach($_POST['addinf'] as $a) {
			$wpdb->update($table, array("sugarfree" => '1'), array("id" => $a));
			$i = $wpdb->get_var("SELECT id FROM $table_sugarfree WHERE id=$a");
			if(!$i) {
	 			$wpdb->query("INSERT INTO $table_sugarfree(formid) VALUES('$a')");
			}
		}
}

if(isset($_POST['save_feed'])) {
	
	$ids = $wpdb->get_results("SELECT id FROM $table_sugarfree");
	foreach($ids as $id) {
		$data = array(
					"email" => $_POST['email-'.$id->id],
					"first_name" => $_POST['firstname-'.$id->id],
					"last_name" => $_POST['lastname-'.$id->id],
					"addas" => $_POST["addas"]
					);	
		
		$where = array("id" => $id->id);
		$wpdb->update($table_sugarfree, $data, $where);	
	}
}
?>
<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
			<li><a class="current"><?php _e('SugarFree CRM','jumpforms'); ?></a></li>
			
		</ul>
				
		<div id="tdmfw_content">
			<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;">
				<a id="settings"> <?php _e('SugarFree Settings ','jumpforms');?></a>|
				<a id="feeds"><?php _e('SugarFree Feeds','jumpforms'); ?></a>
				<a id="addinf" style="float: right;"><?php _e('Add Form','jumpforms'); ?></a>	
			</p>
			<?php
		
			?>
			<div class="tdmfw_box_content">
				<div id="settingsview" style="display: none;">
					
					<form method="post">
					
						<table>
							<tr>
							<td>SugarCrm URL</td>
							
							<td><input type="text" name="url" id="url" value="<?php echo get_option('sugarfree_url')? get_option('sugarfree_url') : ''; ?>"></td>
							</tr>
							<tr>
							<td>User Name</td>
							<td><input type="text" name="uname" value="<?php echo get_option('sugarfree_username')? get_option('sugarfree_username') : ''; ?>" id="username" /></td>
							</tr>
							<tr>
								<td>Password</td>
								<td><input type="password" name="password" value="<?php echo get_option('sugarfree_password')? get_option('sugarfree_password') : ''; ?>" id="password" /></td>
							</tr>
							<tr>
								<td><input type="button" value="Test connection" name="test" id="testcon" /></td>
								<td><div id="test_result"></div></td>
							</tr>
							
							<tr>
								<td><input type="submit" class="button-primary" value="Save settings" name="test_submit" /></td>
							</tr>
						</table>
					</form>
		
				</div>	
		
				<div id="feedsview" style="display: none;">
					
					<?php 
					$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$table_sugarfree." ON ".$table.".id=".$table_sugarfree.".formid AND sugarfree=1");
					echo "<form method='post'><table>";
					foreach($results as $r) {
						$values = $wpdb->get_results("SELECT * FROM $table_sugarfree WHERE formid=$r->id");
						$order  = $r->sortorder;
						$sortrows = explode(",", $order);
					?>
					<tr><td><a id="<?php $r->id ?>"><?php echo $r->title; ?></a></td>
						<td>
							<select name="addas">
								<option <?php if($values[0]->addas == "") echo "selected='selected'" ?> value="">Add As</option>
								<option <?php if($values[0]->addas == "lead") echo "selected='selected'" ?> value="lead">Lead</option>
								<option <?php if($values[0]->addas == "contact") echo "selected='selected'" ?> value="contact">Contact</option>
								<option <?php if($values[0]->addas == "opportunity") echo "selected='selected'" ?> value="opportunity">Opportunity</option>
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
						<tr><td><hr /></td><td><hr /></td></tr>
					<?php } ?>
					<tr><td></td><td><input type="submit" class="button-primary" value="Save Changes" name="save_feed" /></td></tr>
					</table>
					
					</form>
				</div>	
				
				<div id="addinfview" style="display: none;">
					<form method="post"> 
					
						<table>
							<b>Add Forms to Sugarfree CRM</b>
							<?php foreach ($forms as $f) {
								$i = $wpdb->get_var("SELECT sugarfree FROM $table WHERE id=$f->id");
							?>
								<tr><td><?php echo $f->title; ?></td><td><input type="checkbox" <?php if($i == "1") echo "checked='checked'" ?> name="addinf[]" id="addinf" value="<?php echo $f->id; ?>" /></td></tr>
							<?php } ?>
							<tr>
							<td>
							<input type="submit" class="button-primary" value="Save Changes" name="inf_save" />
							</td>
							</tr>
						</table>
						
					</form>
					
				</div>	
			</div>
		</div>	
	</div>
</div>
