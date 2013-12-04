<?php 

//error_reporting( E_ALL );
//ini_set( "display_errors", 1 );

//require_once("aweber_api/aweber_api.php");
include_once( plugin_dir_path( __FILE__ ) . '/aweber_api/aweber_api.php');
if(isset($_POST['account'])) {
	$url = explode("?",$_SERVER['REQUEST_URI']);
	$aurl = 'http://' . $_SERVER['HTTP_HOST'] . $url[0] . '?page=jumpforms_aweber';
	update_option("accesskey", '');
	update_option("accesssecret", '');
	?>
	<script>
		window.location = "<?php echo $aurl; ?>";
	</script>
	<?php
}

global $wpdb;
$table = $wpdb->prefix . "jumpforms";	
$table_aweber = $wpdb->prefix . "jumpforms_aweber"; 
// Step 1: assign these values from https://labs.aweber.com/apps
$consumerKey = 'Akfb7SmhI9ZjZnfApE2j3HWd';
$consumerSecret = 'jaUt65dRFyKBFLii46Bng9ZfKK8GfD9aTtXgUA6d'; 
update_option("consumerkey", $consumerKey);
update_option("consumersecret", $consumerSecret);
//$a = new AWeberAPI
$aweber = new AWeberAPI($consumerKey, $consumerSecret);
	
$accessKey = get_option("accesskey");
$accessSecret = get_option("accesssecret");

if(isset($_POST['inf_save'])) {
	$result = $wpdb->get_results("SELECT * FROM $table");
	foreach ($result as $res) {
		$wpdb->update($table, array("aweber" => 0), array("id" => $res->id));
	}
	if($_POST['addinf']) {
		foreach($_POST['addinf'] as $a) {
			$wpdb->update($wpdb->prefix."jumpforms", array("aweber" => 1), array("id" => $a));
			$i = $wpdb->get_var("SELECT id FROM $table_aweber WHERE id=$a");
			if(!$i) {
	 			$wpdb->query("INSERT INTO $table_aweber(formid) VALUES('$a')");
			}
		}
	}
}

if(isset($_POST['save_feed'])) {
	
	$ids = $wpdb->get_results("SELECT id FROM $table_aweber");
	foreach($ids as $id) {
		$data = array(
					"email" => $_POST['email-'.$id->id],
					"first_name" => $_POST['firstname-'.$id->id],
					"last_name" => $_POST['lastname-'.$id->id]
					);	
		
		$where = array("id" => $id->id);
		$wpdb->update($table_aweber, $data, $where);	
	}
}

function get_self(){
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
	
function display_available_lists($account){
    print "Please add one for the lines of PHP Code below to the top of your script for the proper list<br>" .
            "then click <a href='" . get_self() . "'>here</a> to continue<p>";

    $listURL ="/accounts/{$account->id}/lists/"; 
    $lists = $account->loadFromUrl($listURL);
    foreach($lists->data['entries'] as $list ){
        print "<pre>\$list_id = '{$list['id']}'; // list name:{$list['name']}\n</pre>";
    }
}
	
function display_access_tokens($aweber){
	
    if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
    	
    $aweber->user->requestToken = $_GET['oauth_token'];
    $aweber->user->verifier = $_GET['oauth_verifier'];
    $aweber->user->tokenSecret = get_option("aweber_secret");
	
    list($accessTokenKey, $accessTokenSecret) = $aweber->getAccessToken();
	update_option("accesskey", $accessTokenKey);
	update_option("accesssecret", $accessTokenSecret);
	$accessKey = get_option("accesskey");
	$accessSecret = get_option("accesssecret");
	return;
	}
	
	if(!isset($_SERVER['HTTP_USER_AGENT'])){
    	print "This request must be made from a web browser\n";
    	exit;
	}

	$callbackURL = get_self();
	//die("no pblm");
	list($key, $secret) = $aweber->getRequestToken($callbackURL);
	$authorizationURL = $aweber->getAuthorizeUrl();
	
	update_option("aweber_secret", $secret)
	
	?>
	<script>
		window.location = "<?php echo $authorizationURL; ?>";
	</script>
    <?php
	
}

if (!$accessKey || !$accessSecret){
	display_access_tokens($aweber);
}
?>


<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
			<li><a class="current">Aweber</a></li>
	
		</ul>
		
	<div id="tdmfw_content">
		<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;">
				<a id="settings"> <?php _e('Aweber Settings ','jumpforms');?></a>|
				<a id="feeds"><?php _e('Aweber Feeds','jumpforms'); ?></a>
				<a id="addinf" style="float: right;"><?php _e('Add Form','jumpforms'); ?></a>	
			</p>
			<?php
			$aweberForms = $wpdb->get_results("SELECT id,title FROM $table");
			?>
			<?php 
			 /*
			if(isset($_POST['settings_submit'])) {
				
				$wpdb->query("DELETE FROM ".$wpdb->prefix."jumpforms_infusion_settings");
				$wpdb->insert($wpdb->prefix."jumpforms_infusion_settings", array(
							"inf_key" => $_POST['apikey'],
							"inf_domain" => $_POST['subdomain']));	
				
			}*/
			?>
			<div class="tdmfw_box_content">
				<div id="settingsview" style="display: none;">
					<?php if(get_option("accesskey")) {
						$flag = 1;
						$account = $aweber->getAccount($accessKey, $accessSecret);
						 
						?>
						An Aweber Account with an id <?php echo $account->data['id'] ?> is integrated <br/><br/>
					<?php } ?>
					<form method="post" action=""> 
						<input type="submit" value="Add <?php if($flag == 1) echo "Another" ?> Account" id="aweber_link" name="account" />
					</form>
				</div>	

				<div id="feedsview" style="display: none;">
					<form method="post">
					<b>Forms Intergrated with Aweber</b>
					<?php 
					$results = $wpdb->get_results("SELECT * FROM $table INNER JOIN ".$table_aweber." ON ".$table.".id=".$table_aweber.".formid AND aweber=1");
					//echo "SELECT * FROM $table INNER JOIN ".$table_aweber." ON ".$table.".id=".$table_aweber.".formid AND aweber=1"; die();
					echo "<table>";
					
					foreach($results as $r) {
				
						$values = $wpdb->get_results("SELECT email,first_name,last_name FROM $table_aweber WHERE formid=$r->id");
						//print_r($r);die();
						$order  = $r->sortorder;
						$sortrows = explode(",", $order);
					?>
					<tr><td><a id="<?php $r->id ?>"><?php echo $r->title; ?></a></td></tr>
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
					
					<?php
					} echo "</table>";?>
					<input type="submit" value="Save Changes" name="save_feed" />
					</form>
				</div>	
			
				<div id="addinfview" style="display: none;">
					<form method="post"> 
					<table>
						<b>Add Form Name to Aweber</b>
						<?php foreach ($aweberForms as $inf) {
								$i = $wpdb->get_var("SELECT aweber FROM $table WHERE id=$inf->id");
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







<!--
<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
			<li><a class="current"><?php _e('Aweber','jumpforms'); ?></a></li>
			
		</ul>
	
	<div id="tdmfw_content">
		<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;">	
			</p>
			<div class="tdmfw_box_content">
				<div id="settingsview" style="display: none;">
					<?php if(get_option("accesskey")) {
						$flag = 1;
						$account = $aweber->getAccount($accessKey, $accessSecret);
						 
						?>
						An Aweber Account with an id <?php echo $account->data['id'] ?> is integrated <br/><br/>
					<?php } ?>
					<form method="post" action=""> 
						<input type="submit" value="Add <?php if($flag == 1) echo "Another" ?> Account" id="aweber_link" name="account" />
					</form>
					<!--<form method="post">
						API Key    <input type="text" id="apikey" name="apikey" style="width: 300px;" value="<?php ?>" /><br /><br /><br />
						<input type="submit" class="btn btn-primary" value="Save Changes" name="aweber_save" />

						<!--Sub Domain <input type="text" id="subdomain" name="subdomain" value="<?php  ?>" /><br /><br />
						<input type="submit" value="Save Changes" name="settings_submit" class="btn btn-info" />-->
					<!--</form>--> 
				<!--</div>	
			</div>
			
		</div>
	</div>
</div>-->
<script>
	jQuery(function() {
		jQuery("#aweber_link").click(function() {
			var res = confirm("Alert!!! Your current account information will be resetted");
			if(!res) {
				return false;
			}
		})
	})
</script>