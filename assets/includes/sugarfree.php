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
	$username = $_POST['uname']; 
	$password = $_POST['password'];
	update_option("sugarfree_url", $url);
	update_option("sugarfree_username", $username);
	update_option("sugarfree_password", $password);
		 
	$client = new nusoapclient($url.'/soap.php?wsdl',true);
	$user_auth = array(
	                'user_auth' => array(
	                'user_name' => $username,
	                'password' => md5($password),
	                'version' => '0.1'
	        ), 'application_name' => 'jumpforms');
	$login = $client->call('login',$user_auth);
	$session_id = $login['id'];
	
	$opportunity_fields = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Opportunities'));
	$lead_fields = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Leads'));
	$contact_fields = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Contacts'));				
	update_option("opportunity_fields", serialize($opportunity_fields));
	update_option("lead_fields", serialize($lead_fields));
	update_option("contact_fields", serialize($contact_fields));
		
}
if(isset($_POST["save_opportunity"])) {
	

	$ids = $wpdb->get_results("SELECT id FROM $table_sugarfree");
	foreach($ids as $id) {
		if($_POST['addas-'.$id->id] == "opportunity") {
			$where = array("id" => $id->id);
			$wpdb->update($table_sugarfree, array("addas" => "opportunity"), $where);	
			$serialize_array["zip"] = $_POST["zip-".$id->id];
			$serialize_array["reason"] = $_POST["reason-".$id->id];
			$serialize_array["aprice"] = $_POST["aprice-".$id->id];
			$serialize_array["address"] = $_POST["address-".$id->id];
			$serialize_array["city"] = $_POST["city-".$id->id];
			$serialize_array["email"] = $_POST["email-".$id->id];
			$serialize_array["phone"] = $_POST["phone-".$id->id];
			$serialize_array["firstname"] = $_POST["firstname-".$id->id];
			$serialize_array["lastname"] = $_POST["lastname-".$id->id];
			$serialize_array["entryid"] = $_POST["entryid-".$id->id];
			$serialize_array["entrydate"] = $_POST["entrydate-".$id->id];
			$serialize_array["sourceurl"] = $_POST["sourceurl-".$id->id];
			$serialize_array["ip"] = $_POST["ip-".$id->id]; 
			$tot_val = serialize($serialize_array);
			$data = array(
					"value" => $tot_val
					);	
		
			$wpdb->update($table_sugarfree, $data, $where);	
		}
	}
	
	/*foreach($_POST as $key => $sa) {
		if($key != "save_opportunity"){
			$serialize_array[$$key] = $a; 
		}
	}
	//$serialize_array = 
	foreach($ids as $id) {
		$data = array(
					"email" => $_POST['email-'.$id->id],
					"first_name" => $_POST['firstname-'.$id->id],
					"last_name" => $_POST['lastname-'.$id->id],
					"addas" => $_POST["addas"]
					);	
		
		$where = array("id" => $id->id);
		$wpdb->update($table_sugarfree, $data, $where);	
	}*/
//	print_r($_POST);die();
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
					
					<form method="post" action="?page=jumpforms_sugarfree&plcsm=sts">
					
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
					
					$opportunity_fields = unserialize(get_option("opportunity_fields"));
					$lead_fields = unserialize(get_option("lead_fields"));
					$contact_fields = unserialize(get_option("contact_fields"));
					//echo "<pre>";print_r($lead_fields);die();
					foreach($opportunity_fields['module_fields'] as $of) {
						$opportunity_options[$of['name']] = $of['label'];  
					}
					//echo "<pre>";
					//print_r($opportunity_options);
					foreach($lead_fields['module_fields'] as $lf) {
						$lead_options[$lf['name']] = $of['label'];
					}
					//echo "<br /><br /><br /><br />";
					//print_r($lead_options);
					foreach($contact_fields['module_fields'] as $cf) {
						$contact_options[$cf['name']] = $of['label'];
					}
					//echo "<br /><br /><br /><br />";
					//print_r($contact_options);die();
					$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$table_sugarfree." ON ".$table.".id=".$table_sugarfree.".formid AND sugarfree=1");
					echo "<form method='post' action='?page=jumpforms_sugarfree&plcsm=fds' ><table id='main_table'>";
					
					foreach($results as $r) {
						$values = $wpdb->get_results("SELECT * FROM $table_sugarfree WHERE formid=$r->id");
						$order  = $r->sortorder;
						$sortrows = explode(",", $order);
						?>
						<tr><td style="width: 147px;"><a id="<?php echo $r->id ?>"><?php echo $r->title; ?></a></td>
							<td>
								<select name="addas-<?php echo $r->id ?>" id="addas" class="addas">
									<option <?php if($values[0]->addas == "") echo "selected='selected'" ?> value="">Add As</option>
									<option <?php if($values[0]->addas == "lead") echo "selected='selected'" ?> value="lead">Lead</option>
									<option <?php if($values[0]->addas == "contact") echo "selected='selected'" ?> value="contact">Contact</option>
									<option <?php if($values[0]->addas == "opportunity") echo "selected='selected'" ?> value="opportunity">Opportunity</option>
								</select>
								<input type="hidden" value="<?php echo $r->id ?>" name="" id="fid" />
							</td>
							<td>
								<img src="<?php echo WP_PLUGIN_URL . '/jumpforms/assets/img/add.jpg' ?>" width="28px" height="28px" style="cursor: pointer" id="add_row" />
							</td>
						</tr>
						</table>
						<!--<table id="lead_contact-<?php echo $r->id ?>"  <?php if($values[0]->addas == 'opportunity') { ?>style="display: none;" <?php } ?> >
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
								if($r->$type != "email" ) {
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
								if($r->$type != "email" ) {
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
						
							<!--<tr><td></td><td><input type="submit" class="button-primary" value="Save Changes" name="save_feed" /></td></tr>-->
						<!--</table>-->
				
					
						<table id="opportunities-<?php echo $r->id ?>" <?php if($values[0]->addas != 'opportunity') { ?>style="display: none;" <?php } ?> >
						<?php $upval = unserialize($values[0]->value); 
						$menu = unserialize($values[0]->menu);
						$fff = 1;
						foreach($upval as $u) {							
						?>
							<tr>
							<td></td>
							<td>
									<select name="zip-<?php echo $r->id; ?>">
									<option value=""></option>
									<?php foreach ($sortrows as $counter) {
										
										$type = 'f'.$counter.'_type';
										$label = 'f'.$counter.'_label';
										if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
										?>
											<option value="<?php echo $counter; ?>" <?php if($upval["zip"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
										<?php 
										} 
										?>
						
									<?php } ?>
									</select>
							</td>
							</tr>
						<?php
						$fff++;
						}
						?>
							<!--<tr>
								<td>Zip Code</td>
								<td>
								<select name="zip-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["zip"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Reason for Selling</td>
								<td>
								<select name="reason-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["reason"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Asking Price</td>
								<td>
								<select name="aprice-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["aprice"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Address</td>
								<td>
								<select name="address-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["address"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>City</td>
								<td>
								<select name="city-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["city"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
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
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type =="email") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["email"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Phone Number</td>
								<td>
								<select name="phone-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["phone"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
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
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["firstname"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
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
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["lastname"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Entry Id</td>
								<td>
								<select name="entryid-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["entryid"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Entry Date</td>
								<td>
								<select name="entrydate-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["entrydate"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
				
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>Source URL</td>
								<td>
								<select name="sourceurl-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["sourceurl"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>
							<tr>
								<td>User IP</td>
								<td>
								<select name="ip-<?php echo $r->id; ?>">
								<option value=""></option>
								<?php foreach ($sortrows as $counter) {
									
								$type = 'f'.$counter.'_type';
								$label = 'f'.$counter.'_label';
								if($r->$type != "email" && $r->$type !="sectionstart" && $r->$type !="sectionend") {
								?>
									<option value="<?php echo $counter; ?>" <?php if($upval["ip"] == $counter) echo "selected='selected'" ?> ><?php echo $r->$label; ?></option>
								<?php 
								} 
								?>
					
								<?php } ?>
								</select>
								</td>
							</tr>-->
							<tr><td><hr /></td><td><hr /></td></tr>
							
						</table>
					
					<?php } ?>
					<table>
						<tr>
							<td></td>
							<td><input type="submit" class="button-primary" value="Save Changes" name="save_opportunity" /></td>
						</tr>
					</table>
					</form>
				</div>	
				
				<div id="addinfview" style="display: none;">
					<form method="post" action="?page=jumpforms_sugarfree&plcsm=ads"> 
					
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
<script>
	jQuery(function() {
		jQuery(".addas").change(function() {
			id = jQuery(this).next().val();
			val = jQuery(this).val();
			if(val == "lead" || val == "contact") {
				jQuery("#opportunities-"+id).hide();
				jQuery("#lead_contact-"+id).show();
			}
			if(val == "opportunity") {
				jQuery("#lead_contact-"+id).hide();
				jQuery("#opportunities-"+id).show();
			}
		})
	})
</script>
<?php

if(isset($_GET['plcsm']) && $_GET['plcsm'] == 'sts') {
?>
<script>
	jQuery(function() {
		jQuery("#settings").css("color","black");
		jQuery("#feeds").css("color","#21759B");
		jQuery("#addinf").css("color","#21759B");
		jQuery("#settingsview").css("display","block");
		jQuery("#feedsview").css("display","none");
		jQuery("#addinfview").css("display","none");
	})
</script>
<?php	
}
if(isset($_GET['plcsm']) && $_GET['plcsm'] == 'fds') {
?>
<script>
	jQuery(function() {
		jQuery("#settings").css("color","#21759B");
		jQuery("#feeds").css("color","black");
		jQuery("#addinf").css("color","#21759B");
		jQuery("#settingsview").css("display","none");
		jQuery("#feedsview").css("display","block");
		jQuery("#addinfview").css("display","none");
	})
</script>
<?php	
}
if(isset($_GET['plcsm']) && $_GET['plcsm'] == 'ads') {
?>
<script>
	jQuery(function() {
		jQuery("#settings").css("color","#21759B");
		jQuery("#feeds").css("color","#21759B");
		jQuery("#addinf").css("color","black");
		jQuery("#settingsview").css("display","none");
		jQuery("#feedsview").css("display","none");
		jQuery("#addinfview").css("display","block");
	})
</script>
<?php	
}
?>

<script>
	<?php
	$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$table_sugarfree." ON ".$table.".id=".$table_sugarfree.".formid AND sugarfree=1");
	//print_r($results);
	foreach($results as $r) {
		$values = $wpdb->get_results("SELECT * FROM $table_sugarfree WHERE formid=$r->id");
		$order  = $r->sortorder;
		$sortrows = explode(",", $order);
		foreach ($sortrows as $counter) {
			$type = 'f'.$counter.'_type';
			$label = 'f'.$counter.'_label';
			//echo "<script>alert('".$type."')</script>";
			if($r->$type != "sectionstart" && $r->$type != "sectionend") {
				$drop_val[$r->id][$counter] = $r->$label;	
			}
		}	
	}		
	//echo "<pre>"; print_r($drop_val);die();		
	echo "var opportunity_array = ". json_encode($opportunity_options).";";
	echo "var lead_array = ". json_encode($lead_options).";";
	echo "var contact_array = ". json_encode($contact_options).";";
	echo "var drop_val = ". json_encode($drop_val).";";
	?>
	
	op_select = "<select>";
	for(k in opportunity_array) {
		op_select = op_select + "<option value='"+k+"' >"+opportunity_array[k]+"</option>";
	}
	op_select = op_select + "</select>";
	
	ld_select = "<select>";
	for(l in lead_array) {
		ld_select = ld_select + "<option value='"+l+"' >"+lead_array[l]+"</option>";
	}
	ld_select = ld_select + "</select>";
	
	cn_select = "<select>";
	for(c in contact_array) {
		cn_select = cn_select + "<option value='"+c+"' >"+contact_array[c]+"</option>";
	}
	cn_select = cn_select + "</select>";
	
	
	 
	jQuery("#add_row").click(function() {
		id = jQuery("#addas").next().val();
		drop_select = "<select>";
		for(d in drop_val[id]) {
			drop_select = drop_select + "<option value='"+d+"' >"+drop_val[id][d]+"</option>";
		}
		drop_select = drop_select + "</select>";
		jQuery("#main_table").append("<tr><td>"+op_select+"</td><td>"+drop_select+"</td></tr>");
	})
</script>