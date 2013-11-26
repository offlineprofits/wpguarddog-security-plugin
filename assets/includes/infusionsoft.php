<?php

error_reporting(1);
$infusion = new iSDK();
//$oldkey = "f3f94c143d755752a2f64d1a53820f90";
$infusion->cfgCon("connectionName");
$webForm = $infusion->getWebFormMap();
global $wpdb;
$table = $wpdb->prefix . "formengine";	
$table_infusionsoft = $wpdb->prefix . "formengine_infusion"; 
if(isset($_POST['inf_save'])) {
	$result = $wpdb->get_results("SELECT * FROM $table");
	foreach ($result as $res) {
		$wpdb->update($table, array("infusion" => 0), array("id" => $res->id));
	}
	if($_POST['addinf'])
	foreach($_POST['addinf'] as $a) {
		$wpdb->update($wpdb->prefix."formengine", array("infusion" => 1), array("id" => $a));
		//echo "INSERT INTO $table_infusionsoft(formid) VALUES('$a')"; die();
		$i = $wpdb->get_var("SELECT id FROM $table_infusionsoft WHERE id=$a");
		if(!$i) {
 			$wpdb->query("INSERT INTO $table_infusionsoft(formid) VALUES('$a')");
		}
	}
	
}

if(isset($_POST['save_feed'])) {
	
	$ids = $wpdb->get_results("SELECT id FROM $table_infusionsoft");
	
	foreach($ids as $id) {
		$data = array(
					"email" => $_POST['email-'.$id->id],
					"first_name" => $_POST['firstname-'.$id->id],
					"last_name" => $_POST['lastname-'.$id->id],
					'tagid' => $_POST['tagid-'.$id->id]
					);	
		
		$where = array("id" => $id->id);
		$wpdb->update($table_infusionsoft, $data, $where);	
	}
	
	
}

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
	API Key    <input type="text" id="apikey" name="apikey" style="width: 300px;" value="<?php if($settings) echo $settings[0]->inf_key; ?>" /><br /><br /><br />
	Sub Domain <input type="text" id="subdomain" name="subdomain" value="<?php if($settings) echo $settings[0]->inf_domain; ?>" /><br /><br />
	<input type="submit" value="Save Changes" name="settings_submit" class="btn btn-info" />
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
		 print_r($values);//die();
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
<!--	1. Select the form to tap into.  	
	<form method="post">
		<input type="hidden" name="hiddenvname" id="hiddenvname" />
	<select id="formlist" name="formid">
		<option>Select a Form</option>
		<?php foreach ($infusionForms as $inf) {  ?>
		<option value="<?php echo $inf->id ?>"><?php echo $inf->title ?></option>
		<?php } ?>
	</select><img src="<?php echo plugins_url() ?>/formengine/assets/img/ajax-loader-large.gif" id="formlist_load" width="30px" height="30px" style="display: none;" />
	<hr />
	<div id="showinfform">
	<input type="hidden" id="optHtml" />
	2. Select a WebForm from Infusionsoft.
	<select id="infform" name="infid">
		<option>Select a Form</option>
		<?php foreach ($webForm as $key => $name) {  ?>
		<option value="<?php echo $key; ?>"><?php echo $name ?></option>
		<?php } ?>
	</select><img src="<?php echo plugins_url() ?>/formengine/assets/img/ajax-loader-large.gif" id="inflist_load" width="30px" height="30px" style="display: none;" /><br />
	</div>
	<div id="listings">
		
	</div>
	<input type="submit" name="save" id="addengine" value="Save Changes" class="btn btn-primary" />
	</form>-->
	<form method="post"> 
	<table>
		<b>Add Form Name to InfusionSoft</b>
		<?php foreach ($infusionForms as $inf) {
				$i = $wpdb->get_var("SELECT infusion FROM $table WHERE id=$inf->id");
			  ?>
			<tr><td><?php echo $inf->title; ?></td><td><input type="checkbox" <?php if($i==1) echo "checked" ?> name="addinf[]" id="addinf" value="<?php echo $inf->id; ?>" /></td></tr>
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


<?php
if(isset($_POST['save'])) {
	$table_new = $wpdb->prefix . "formengine_infusion";	
	$values = explode(",", $_POST['hiddenvname']);
	$i = 0;
	$fvalue = "";
	foreach ($_POST as $key=>$val) {
		if($key != "hiddenvname" && $key != "formid" && $key != "infid" && $key != "save") {
			$fvalue = $fvalue.$values[$i].":".$val.","; 
		}
	}
	$fvalue = substr($fvalue, 0, -1);
	$result = $wpdb->query("SELECT id FROM $table_new WHERE formid=$_POST[formid]");
	print_r($result);//die("polo");
	
	if($result) {
		$wpdb->update($table_new, array(
				"formid" => $_POST['formid'],
				"val" => $fvalue,
				"infid" => $_POST['infid'],
				"links" => $_POST['link']
				), array( 'id' => $result ));
				
	}
	else {
		die("here");
		$wpdb->insert($table_new, array(
				"formid" => $_POST['formid'],
				"val" => $fvalue,
				"infid" => $_POST['infid'],
				"links" => $_POST['link']
				));
		$wpdb->update($table, array(
				"infusion" => $wpdb->insert_id
				), array( 'id' => $_POST['formid'] ));		
	}
}

?>

		