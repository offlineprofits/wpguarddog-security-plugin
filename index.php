<?php
/*
Plugin Name: JumpForms
Plugin URI: http://wpfrogs.com
Description: JumpForms makes it easy to build forms for your WordPress site
Version: .2
Author: WPfrogs
Author URI: http://wpfrogs.com
*/
require_once 'plugin-updates/plugin-update-checker.php';
//require_once 'AWeber-API-PHP-Library-master/aweber_api/aweber_api.php';

$MyUpdateChecker = new PluginUpdateChecker(
    'http://localhost/~vishnurs/jumpupdate.json',
    __FILE__,
    'jumpform'
);
require_once('lib/PLE_Client_Util.php');
require_once("assets/PHP-iSDK-master/src/isdk.php");
require_once('assets/citrix.php');
/************************************************************
*License Integration
************************************************************/

$pleClient = new PLE_Client_Util();
function initformenginepleClient() {
	global $pleClient;
	$pleClient->setPrefix("fe_pfx");
	$pleClient->setSoftwareName("Jump Forms");
	$pleClient->hideResetInfoLink(false);
	$pleClient->setSlug(plugin_basename(__FILE__));
	$pleClient->initUpdater();
}




$plugin = plugin_basename(__FILE__); 
add_action('init','formengine_init');
add_action('admin_menu','formengine_menu');
register_activation_hook(__FILE__,'formengine_install');
add_filter("plugin_action_links_$plugin", 'formengine_dashboard_link' );
add_shortcode('formengine','formengine_display');
add_shortcode('formengine_modal','formengine_display_modal');
add_action('media_buttons_context',  'add_my_custom_button');
add_action( 'admin_enqueue_scripts', 'infusion_tabbing_script' );
add_action( 'admin_footer',  'add_popup_content' );
add_action('wp_ajax_formchange', 'formchange_callback');
add_action('wp_ajax_infselect', 'infselect_callback');
add_action('wp_ajax_infusion', 'infusion_callback');

function infusion_callback() {
	
}
function infselect_callback() {
	$infusion = new iSDK();
	$infusion->cfgCon("connectionName");
	$webForm = $infusion->getWebFormHtml($_POST['inffid']);
	preg_match_all("/name=[\"|']{1,1}[a-zA-Z0-9]\w+[\"|']{1,1}/i", $webForm, $matches);
	$var = array();
	
	foreach($matches[0] as $match) {
		$temp = explode("=",$match);
		$var[] = substr($temp[1],1,-1);
	}
	echo json_encode($var);
	die();	
}

function formchange_callback() {
	global $wpdb;
	$table = $wpdb->prefix . "formengine";
	$formdata = $wpdb->get_results("SELECT * FROM wp_formengine WHERE id=$_POST[fid]");
	$formarray = get_object_vars($formdata[0]); 
	$i = array_search('f1_label', array_keys($formarray));
	$total = count($formarray);
	$val = $total - $i;
	$newarray = range("f1_label","f".$total."_label");
	$options = array();
	$checkoptions = array("hidden","input","password","email","upload","textarea","checkbox","acceptance","dropdown","country","state","stateaus","statecan","county");
	for($j=1; $j<$val; $j++ ) {
		if(isset($formarray["f".$j."_type"]) && in_array($formarray["f".$j."_type"], $checkoptions)) {
			$options[] = $formarray["f".$j."_label"];  
		}
	}
	echo json_encode($options);
	die(); // this is required to return a proper result
}
function infusion_tabbing_script() {
	wp_register_script('infusiontabbing', plugins_url('/assets/js/backend/infusiontabbing.js',__FILE__ )); 
	wp_enqueue_script('infusiontabbing');
}
function add_my_custom_button($context) {
	$context .= "<input type='button' id='addform' class='button' title='Add Form' value='Add Form' />";
	return $context;
}
function add_popup_content() {
	global $wpdb;
	$table = $wpdb->prefix . "formengine";	
?>
<div class="small_message_box">
	<h1>Message</h1>
    <p class="small_message_box_p">
    <?php
    	$forms = $wpdb->get_results('SELECT id,title FROM '.$table);
		foreach($forms as $f) {
			echo "<a data-value='".$f->id."' style='color:white; cursor: pointer;' class='formadd'>".$f->title."</a><br />";
		}		
    ?>	
    </p>
    <a href="#" id="close_msg">x</a>
</div>
<?php
}

add_action( 'admin_enqueue_scripts', 'custom_popup_script' );
function custom_popup_script() {
	wp_register_script('popup', plugins_url('/assets/js/backend/popup.js',__FILE__ )); wp_enqueue_script('popup');
	wp_register_style('popup', plugins_url('/assets/css/popup.css',__FILE__ )); wp_enqueue_style('popup');
}

function formengine_init() {
	
	require('assets/includes/notifier.php');
	load_plugin_textdomain('formengine', false, dirname(plugin_basename(__FILE__)) . '/assets/lang/');
}

function formengine_dashboard_link($links) { 
	$settings_link = '<a href="admin.php?page=formengine_dashboard">Dashboard</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}


function formengine_version() {
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$formengine_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$formengine_file = basename( ( __FILE__ ) );
	return $formengine_folder[$formengine_file]['Version'];
}

function create_form_page($id,$title) {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$post = array(
	  'post_title'    => $title,
	  'post_content'  => '[formengine id='.$id.']',
	  'post_type'   => 'page',
	  'post_status'   => 'publish'
	);
	wp_insert_post( $post );

	$args = array(
	'sort_order' => 'DESC',
	'sort_column' => 'post_date',
	'number' => '1',
	); 	
	
	$pages = get_pages($args); 
		foreach ( $pages as $page ) {
		$id = $page->ID;
	}	

	$success = __('Success! A page was created for the form!<span style="float:right;"><a href="post.php?post='.$id.'&action=edit">View Page</a></span>','formengine');
}

function create_table($table, $sql){
	// Check that the table does not already exist
    global $wpdb;
    if($wpdb->get_var("show tables like '". $table . "'") != $table) {
        $wpdb->query($sql);
    }
}

function update_form($fid) {
	global $wpdb;
	$table = $wpdb->prefix . "formengine";	
	$webinar = $wpdb->get_var("SELECT webinar FROM $table WHERE id='$_GET[fid]'"); 
	$orgkey = $wpdb->get_var("SELECT organizerkey FROM $table WHERE id='$_GET[fid]'");
	$acctkn = $wpdb->get_var("SELECT accesstoken FROM $table WHERE id='$_GET[fid]'");
	
	if($webinar == "1")  {
		// to avoid sending request to citrix for every page load, only if the organizerkey or accesstoken is changed the request will be send.
		if($_POST['organizerkey'] != $orgkey || $_POST['accesstoken'] != $acctkn) {
			//require('assets/citrix.php');
			$citrix = new CitrixAPI($_POST["accesstoken"], $_POST["organizerkey"]);
			$upcomingwebs = $citrix -> getUpcomingWebinars();
			$test = json_decode($upcomingwebs);
			if(isset($test[0]->webinarKey)) {
				$upcomingwebinars = json_decode(preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $upcomingwebs));	
			}
			else {
				$upcomingwebinars = $test;
			}
			$i=0; 
			$optionString = "";
			if($upcomingwebinars) {
				foreach ($upcomingwebinars as $web) {
					$date = explode("T",$web->times[0]->startTime);
					$newDate = $date[0]." ".substr($date[1], 0, -1);
					$correctDate = date("Y-m-d H:i:s", strtotime($newDate ."- 4 hours"));
					$date = explode(" ",$correctDate);
					$time = substr($date[1], 0,2);
					if($time > 12 ) {
						$showtime = ($time - 12).substr($date[1], 2, strlen($date[1])-2)." PM EST";
					}
					else if($time == 0) {
						$showtime = "12".substr($date[1], 2, strlen($date[1])-2)." PM EST";
					}
					else {
						$showtime = $date[1]." AM EST";
					}
					
					$optionString = $optionString.$web->webinarKey."::".$date[0]." at ".$showtime.", ";
					if($i>1) {
						break;
					} else {
						$i++; 
					}
				} 
				$optionString = substr($optionString, 0, -2);
			}	
		}
		else {
			$optionString =  $wpdb->get_var("SELECT f2_value FROM $table WHERE id='$_GET[fid]'");
		}
		$table = $wpdb->prefix . "formengine";
		$inf = NULL;
		$formid = NULL;
		$formname = NULL;
		$formversion = NULL;
		$ipaddress = NULL;
		
	}

	global $wpdb;
	global $error;
	global $success;
	global $info;
		
	$wpdb->update( $table, array(
		'title' => stripslashes_deep($_POST["title"]),
		'notify' => $_POST["notify"],
		'notifytype' => $_POST["notifytype"],
		'notifysubject' => $_POST["notifysubject"],
		'notifymessage' => $_POST["notifymessage"],
		'email' => $_POST["email"],
		'formid' => $formid,
		'formversion' => $formversion,
		'formname' => $formname,
		'ipaddress' => $ipaddress,
		'redirect' => $_POST["redirect"],
		'errorredirect' => $_POST["errorredirect"],
		'sections' => $_POST["sections"],
		'fields' => $_POST["fields"],
		'sortorder' => $_POST["sortorder"],
		'captcha' => $_POST["captcha"],
		'modalbutton' => stripslashes_deep($_POST["modalbutton"]),
		'progress' => $_POST["progress"],
		'organizerkey' => $_POST["organizerkey"],
		'accesstoken' => $_POST["accesstoken"]
	), array( 'id' => $fid ) );
	
	$fields = $wpdb->get_var("SELECT fields FROM $table WHERE id = $fid");
		for($counter = 1; $counter<=$fields;$counter++) {
					
			$label = 'f'.$counter.'_label';
			$value = 'f'.$counter.'_value';
			$type = 'f'.$counter.'_type';
			$validation = 'f'.$counter.'_validation';
			if($_POST[$label] == "Select Webinar"){
				if($optionString)
					$_POST[$value] = $optionString;
				else
					$_POST[$value] = "No Webinars Available";
			}			
		$wpdb->update( $table, array(
			$label => stripslashes_deep($_POST[$label]),
			$value => stripslashes_deep($_POST[$value]),
			$type => stripslashes_deep($_POST[$type]),
			$validation => stripslashes_deep($_POST[$validation])
		), array( 'id' => $fid ) );
			
		}
			
	$success = __('Success! The form was updated!');
	
}

function delete_form($fid) {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$table2 = $wpdb->prefix . "formengine_data";
	$wpdb->query("DELETE FROM $table WHERE id=$fid");
	$wpdb->query("DELETE FROM $table2 WHERE fid=$fid");
}

function delete_response($id) {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table2 = $wpdb->prefix . "formengine_data";
	$wpdb->query("DELETE FROM $table2 WHERE id=$id");
}

function getformfromid($fid) {
	global $wpdb;
    $table = $wpdb->prefix . "formengine";
  	$form = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
	return '<a href="?page=formengine_form&fid='.$fid.'#start">'.$form->title.'</a>';
}

function formengine_wipedatabase() {
	global $wpdb;
	global $alert;
	$table = $wpdb->prefix . "formengine";
	$table2 = $wpdb->prefix . "formengine_data";
	$wpdb->query("DROP TABLE IF EXISTS $table");
	$wpdb->query("DROP TABLE IF EXISTS $table2");
	delete_option( 'formengine_max' );
}

function formengine_menu() {
	
	add_menu_page('JumpForms', 'JumpForms', 'administrator', 'formengine_dashboard', 'formengine_dashboard', plugins_url('/assets/img/jumpform.png',__FILE__ )); 
	add_submenu_page(NULL, 'New Form', 'New Form', 'administrator', 'formengine_new', 'formengine_new');
	add_submenu_page(NULL, 'Export Form', 'Export Form', 'administrator', 'formengine_export', 'formengine_export');
	add_submenu_page(NULL, 'Import Data', 'Import Data', 'administrator', 'formengine_import', 'formengine_import');
	add_submenu_page(NULL, 'JumpForms Form', 'JumpForms Form', 'administrator', 'formengine_form', 'formengine_form');
	add_submenu_page(NULL, 'JumpForms Response', 'JumpForms Response', 'administrator', 'formengine_response', 'formengine_response');
	add_submenu_page(NULL, 'Custom CSS', 'Custom CSS', 'administrator', 'formengine_custom_css', 'formengine_custom_css_options');
	add_submenu_page('formengine_dashboard', 'Extensions', 'Extensions', 'administrator', 'formengine_extensions', 'formengine_extensions');
	add_submenu_page('formengine_dashboard', 'Documentation', 'Documentation', 'administrator', 'formengine_documentation', 'formengine_documentation');
	add_submenu_page('formengine_dashboard', 'InfusionSoft', 'InfusionSoft', 'administrator', 'formengine_infusionsoft', 'formengine_infusionsoft');
	add_submenu_page('formengine_dashboard', 'Webinar', 'Webinar', 'administrator', 'formengine_webinar', 'formengine_webinar');
	add_submenu_page(NULL, 'Wipe Database', 'Wipe Database', 'administrator', 'formengine_wipe', 'formengine_wipe');
	add_submenu_page('formengine_dashboard', "Aweber", "Aweber", "administrator", 'formengine_aweber','formengine_aweber');
	if(is_plugin_active('formengine_paypal/index.php')) { add_submenu_page(NULL, 'Formengine - PayPal', 'Formengine - PayPal', 'administrator', 'formengine_paypal', 'formengine_paypal'); }
	
}


function formengine_webinar() {
	initformenginepleClient();
	global $pleClient;
	$activation_form= $pleClient->preCheckLicense();
	if($activation_form) 
		return;
	/****************************************************/
	//wp_register_script('ajax', plugins_url('/assets/js/backend/ajax.js',__FILE__ )); wp_enqueue_script('ajax');
	//wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	require('assets/includes/webinar.php');
}

function formengine_install() {
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$option_name = 'formengine_max' ;
	$new_value = '50' ;
	
	if (get_option($option_name) != $new_value) {
		update_option($option_name, $new_value);
	} else {
	    $deprecated = ' ';
	    $autoload = 'no';
	    add_option( $option_name, $new_value, $deprecated, $autoload );
	}
	// SETUP FORMENGINE TABLE
	//$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix."formengine");
	//$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix."formengine_data");
	//$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix."formengine_infusion");
	//$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix."formengine_infusion_settings");
	$table = $wpdb->prefix . "formengine";
	$sql = "CREATE TABLE $table (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  title text NOT NULL,
	  notify text NOT NULL,
	  notifytype text NOT NULL,
	  notifysubject text NOT NULL,
	  notifymessage text NOT NULL,  
	  email text NOT NULL,
	  redirect text NOT NULL,
	  errorredirect text NOT NULL,
	  sections text NOT NULL,
	  fields text NOT NULL,
	  sortorder text NOT NULL,
	  captcha text NOT NULL,
	  modalbutton text NOT NULL,
	  progress text NOT NULL,
	  webinar text,
	  infusion text,
	  aweber text,
	  formid text,
	  formname text,
	  formversion text,
	  ipaddress text,
	  accesstoken text,
	  organizerkey text,
	  views text NOT NULL,
	  UNIQUE KEY id (id)
	);";
	
	// SETUP FORMENGINE_DATA TABLE	
	$table2 = $wpdb->prefix . "formengine_data";
	$sql2 = "CREATE TABLE $table2 (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  date timestamp NOT NULL,
	  fid text NOT NULL,
	  UNIQUE KEY id (id)
	);";
	
	/*$table3 = $wpdb->prefix . "formengine_rest";
	$sql3 = "CREATE TABLE $table2 (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  date timestamp NOT NULL,
	  fid text NOT NULL,
	  UNIQUE KEY id (id)
	);";*/
	$table3 = $wpdb->prefix . "formengine_infusion";
	$sql3 = "CREATE TABLE $table3 (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  formid mediumint(9),
	  val text,
	  links text,
	  UNIQUE KEY id (id)
	);"; 
	
	$table4 = $wpdb->prefix . "formengine_infusion_settings";
	$sql4 = "CREATE TABLE $table4 (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  inf_key text,
	  inf_domain text,
	  UNIQUE KEY id (id)
	 );";
	 
	$table5 = $wpdb->prefix. "formengine_webinar";
	$sql5 = "CREATE TABLE $table5 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		apikey text,
		email text,
		password text,
		access_token text,
		org_key text,
		UNIQUE KEY id (id)
	);";	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql);
	dbDelta($sql2);
	dbDelta($sql3);
	dbDelta($sql4);
	dbDelta($sql5);
	//create_table($table, $sql);
	//create_table($table2, $sql2);
	//create_table($table3, $sql3);
	//create_table($table4, $sql4);
	for($counter = 1; $counter<=get_option('formengine_max');$counter++) {
	
		$label = 'f'.$counter.'_label';
		$value = 'f'.$counter.'_value';
		$type = 'f'.$counter.'_type';
		$validation = 'f'.$counter.'_validation';
		$datalabel = 'f'.$counter;
		$datavalue = 'f'.$counter.'_value';
					
		$sql = "ALTER TABLE $table ADD ($label text, $value text, $type text, $validation text)";
		$wpdb->query($sql);
		
		$sql = "ALTER TABLE $table2 ADD ($datalabel text, $datavalue text)";
		$wpdb->query($sql);	

	}
	
}

function formengine_dashboard() {
	initformenginepleClient();
	global $pleClient;
	$activation_form = $pleClient->preCheckLicense();
	if($activation_form) 
		return;
	if(wp_script_is('jquery')) { } else { wp_enqueue_script('jquery'); }
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	if(isset($_GET['delete_form'])) { delete_form($_GET['delete_form']); $success = __('Success! The form was deleted!<span style="float:right;"><a href="?page=formengine_dashboard">Refresh Data</a></span>'); }
?>
<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><a class="current"><?php _e('Dashboard','formengine'); ?></a></li>
	</ul>
	
	<?php
		if (function_exists('simplexml_load_string')) {
			global $wp_admin_bar, $wpdb;
			if (!is_super_admin() || !is_admin_bar_showing())
				return;
				$xml = xxx_get_latest_plugin_version(XXX_PLUGIN_NOTIFIER_CACHE_INTERVAL);
			if (is_admin())
				$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . XXX_NOTIFIER_PLUGIN_FOLDER_NAME . '/' .XXX_NOTIFIER_PLUGIN_FILE_NAME);
			if((string)$xml->latest > (string)$plugin_data['Version']) { ?>
				<div class="tdmfw_error"><?php _e('There is a new version of JumpForms available to download','formengine'); ?>.<a style="float:right;" href="?page=formengine_update"><?php _e('Update to version','formengine'); ?> <?php echo $xml->latest; ?></a>.</div>
			<?php }
		}
	?>
	
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">

		<?php
			$tablecheck = $wpdb->get_var("show tables like '". $wpdb->prefix . "formengine'");
			if($tablecheck) {
		?>	

		
		<?php
			global $wpdb;
			$table = $wpdb->prefix . "formengine_data";
			$forms = $wpdb->get_var("SELECT count(*) FROM $table");
			$subs = $wpdb->get_results("SELECT * FROM $table ORDER BY date DESC");
			if($forms > 0) { ?>
		
		<div class="tdmfw_box">
			<p class="tdmfw_box_title"><?php _e('Latest Responses','formengine'); ?></p>
			<div class="tdmfw_box_content">


				<table class="tdmfw_table"> 
					<thead>
						<tr valign="top">
							<th><?php _e('Date/Time','formengine'); ?></th>
							<th><?php _e('Form','formengine'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php $i = 0; foreach ($subs as $sub) { ?>
						<tr valign="top">
						<td width="50%"><a href="?page=formengine_response&fid=<?php echo $sub->fid; ?>&id=<?php echo $sub->id; ?>"><?php echo date("j F Y", strtotime($sub->date)); ?> at <?php echo date("H:ia", strtotime($sub->date)); ?></a></td>
						<td width="50%"><?php echo getformfromid($sub->fid);?></td>
						</tr>
					<?php if (++$i == 3) break; } ?>
					</tbody>
				</table>


			</div>
		</div>
		
		<?php } ?>
		
		<?php
			global $wpdb;
			$table = $wpdb->prefix . "formengine";
			$table2 = $wpdb->prefix . "formengine_data";
			$forms = $wpdb->get_var("SELECT count(*) FROM $table");
			if($forms > 0) { ?>
		
			<div class="tdmfw_box">
				<p class="tdmfw_box_title"><?php _e('My Forms','formengine'); ?></p>
				<div class="tdmfw_box_content">
					<table class="tdmfw_table">
						<thead>
							<tr>
								<th style="width:47%"><?php _e('Form','formengine'); ?></th>
								<th style="width:10%;text-align:center;"><?php _e('Views','formengine'); ?></th>
								<th style="width:10%;text-align:center;"><?php _e('Responses','formengine'); ?></th>
								<th style="width:13%;text-align:center;"><?php _e('Action','formengine'); ?></th>
							</tr>
						</thead>
						<tbody>
						
							<?php
								$table = $wpdb->prefix . "formengine";
								$forms = $wpdb->get_results("SELECT * FROM $table");
								foreach ($forms as $form) {
							?>

							<tr valign="top">
							<td><?php echo '<a href="?page=formengine_form&fid='.$form->id.'">'.$form->title.'</a>';?></td>
							<td style="text-align:center;"><?php if($form->views) { $views = $form->views; } else { $views = '0';} echo $views;?></td>
							<td style="text-align:center;"><?php echo $wpdb->get_var("SELECT count(*) FROM $table2 WHERE fid = $form->id");?></td>
							<td style="text-align:center;">
								<a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=formengine_dashboard&amp;delete_form=<?php echo $form->id;?>" onclick="return confirm('<?php _e('Are you sure you want to delete this form?','formengine'); ?>')"><?php _e('Delete Form','formengine'); ?></a></td>
							</tr>
							
							<?php } ?>
							
							
						</tbody>
					</table>
				</div>
			</div>
			
			<a style="margin-top:20px;" class="button-primary" href="?page=formengine_new"><?php _e('Create New Form','formengine'); ?></a>
			<a style="margin-top:20px;"class="button-secondary" href="?page=formengine_custom_css"><?php _e('Manage Custom CSS','formengine'); ?></a>
			<a style="margin-top:20px;float:right;text-transform:uppercase;" class="button-secondary" href="?page=formengine_wipe"><?php _e('Remove all data','formengine'); ?></a>
			
			<?php } else { echo '<div class="tdmfw_inline_error">'.__('You do not have any forms','formengine').'. <a href="?page=formengine_new">'.__('Create New Form','formengine').'</a></div>';  } ?>

		<?php
			} else { echo '<div class="tdmfw_inline_error" style="margin-top:0;">'.__('All JumpForms data has been wiped. Please reactivate the plugin','formengine'); } 
		?>	

	</div><!-- /tdmfw_content -->
</div><!-- /tdmfw -->

<?php }

function formengine_new() {
	
	if(wp_script_is('jquery')) { } else { wp_enqueue_script('jquery'); }
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	
	if(isset($_POST['new_form_build'])){
		if($_POST['new_form'] == 'blank') { formengine_blank_form(); }
		elseif ($_POST['new_form'] == 'application') { formengine_application_form(); }
		elseif ($_POST['new_form'] == 'booking') { formengine_booking_form(); }
		elseif ($_POST['new_form'] == 'competition') { formengine_competition_form(); }
		elseif ($_POST['new_form'] == 'contact') { formengine_contact_form(); }
		elseif ($_POST['new_form'] == 'delivery') { formengine_delivery_form(); }
		elseif ($_POST['new_form'] == 'feedback') { formengine_feedback_form(); }
		elseif ($_POST['new_form'] == 'upload') { formengine_upload_form(); }
		elseif ($_POST['new_form'] == 'import') {
			
			$sql = stripslashes_deep($_POST['sql']);		
			$result = $wpdb->query(stripslashes_deep($sql));
	
			if (!$result) {
				$error = __('Error! The form could not be imported.','formengine');
			} else {
				$success = __('Success! The form was imported.','formengine');
			}		
		}
		elseif ($_POST['new_form'] == 'webinar') {
			formengine_webinar_form();
		}
		
	}
	
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><a class="current"><?php _e('New Form','formengine'); ?></a></li>
	</ul>
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">
	<script>
		function checkcustom() {
			if(document.getElementById("import").checked) {
				document.getElementById("toggle").style.display="block";
			} else {
				document.getElementById("toggle").style.display="none";
			}
		}
	</script>
		
		<?php _e('Choose a template from the list below','formengine');echo': '; ?>
		
			<div class="tdmfw_box">
				<form method="post" action="">
				<p class="tdmfw_box_title"><?php _e('Form Templates','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=4"><?php _e('Help?','formengine'); ?></a></p>
				<div class="tdmfw_box_content" style="margin-bottom:20px;">
					<table class="tdmfw_table">
						<thead>
							<tr>
								<th>Form</th>
							</tr>
						</thead>
						<tbody>
							
							<tr><td><input type="radio" name="new_form" id="blank" value="blank" checked="checked" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Blank','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="application" value="application" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Application','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="booking" value="booking" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Booking','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="competition" value="competition" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Competition','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="contact" value="contact" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Contact','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="delivery" value="delivery" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Delivery','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="feedback" value="feedback" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Feedback','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="upload" value="upload" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Upload','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="import" value="import" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Import','formengine'); ?></span></td></tr>
							<tr><td><input type="radio" name="new_form" id="webinar" value="webinar" onclick="checkcustom()"><span style="padding:0 0 0 8px;"><?php _e('Webinar','formengine'); ?></span></td></tr>
														
							
							<tr id="toggle" style="display:none;">
								<td><span style="float:left;padding:5px 0 8px 0;"><?php _e('Paste your import code below:','formengine'); ?></span><br/>
								<textarea name="sql" id="sql" style="float:left;width:528px;height:100px;"></textarea>		
							</td></tr>
						</tbody>
					</table>
				</div>
				
				<input class="button-primary" type="submit" name="new_form_build" id="new_form_build" value="<?php _e('Build Form','formengine'); ?>" />
				<a class="button-secondary" href="?page=formengine_dashboard"><?php _e('Go Back','formengine'); ?></a>
				
				</form>
	
	</div><!-- /tdmfw_content -->
</div><!-- /tdmfw -->

<?php }

function formengine_blank_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed',
		'notifymessage' => 'Thanks for completing our form',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '1',
		'sortorder' => '1',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => '',
		'f1_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
}

function formengine_application_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for applying for this position. We will contact you shortly.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '2',
		'fields' => '10',
		'sortorder' => '1,2,3,4,5,6,7,8,9,10',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => 'Personal Information',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Title',
		'f2_value' => 'Mr, Mrs, Miss, Ms, Dr, Prof, Rev, Other',
		'f2_type' => 'dropdown',
		'f2_validation' => 'on',
		'f3_label' => 'Forename',
		'f3_value' => '',
		'f3_type' => 'input',
		'f3_validation' => 'on',
		'f4_label' => 'Surname',
		'f4_value' => '',
		'f4_type' => 'input',
		'f4_validation' => 'on',
		'f5_label' => 'Address',
		'f5_value' => '',
		'f5_type' => 'textarea',
		'f5_validation' => 'on',
		'f6_label' => 'Email Address',
		'f6_value' => '',
		'f6_type' => 'email',
		'f6_validation' => 'off',
		'f7_label' => '',
		'f7_value' => '',
		'f7_type' => 'sectionend',
		'f7_validation' => 'off',
		'f8_label' => 'Your Application',
		'f8_value' => '',
		'f8_type' => 'sectionstart',
		'f8_validation' => 'off',
		'f9_label' => 'Why are you applying for this role?',
		'f9_value' => '',
		'f9_type' => 'textarea',
		'f9_validation' => 'off',
		'f10_label' => '',
		'f10_value' => '',
		'f10_type' => 'sectionend',
		'f10_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_booking_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for completing our form.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '7',
		'sortorder' => '1,2,3,4,5,6,7',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Your Name',
		'f2_value' => '',
		'f2_type' => 'input',
		'f2_validation' => 'on',
		'f3_label' => 'Your Email Address',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'on',
		'f4_label' => 'Booking Date',
		'f4_value' => '',
		'f4_type' => 'date',
		'f4_validation' => 'on',
		'f5_label' => 'Booking Time',
		'f5_value' => '',
		'f5_type' => 'time',
		'f5_validation' => 'on',
		'f6_label' => 'Special Requests',
		'f6_value' => '',
		'f6_type' => 'textarea',
		'f6_validation' => 'off',
		'f7_label' => '',
		'f7_value' => '',
		'f7_type' => 'sectionend',
		'f7_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_competition_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for entering the competition. We will contact you shortly.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '5',
		'sortorder' => '1,2,3,4,5',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Your Name',
		'f2_value' => '',
		'f2_type' => 'input',
		'f2_validation' => 'on',
		'f3_label' => 'Your Email Address',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'on',
		'f4_label' => 'What colour is the sky?',
		'f4_value' => 'Red, Blue, Yellow, Green',
		'f4_type' => 'radio',
		'f4_validation' => 'on',
		'f5_label' => '',
		'f5_value' => '',
		'f5_type' => 'sectionend',
		'f5_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_contact_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for completing our form.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '5',
		'sortorder' => '1,2,3,4,5',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Your Name',
		'f2_value' => '',
		'f2_type' => 'input',
		'f2_validation' => 'on',
		'f3_label' => 'Your Email Address',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'on',
		'f4_label' => 'Your Question',
		'f4_value' => '',
		'f4_type' => 'textarea',
		'f4_validation' => 'on',
		'f5_label' => '',
		'f5_value' => '',
		'f5_type' => 'sectionend',
		'f5_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_delivery_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thank you for your order.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '5',
		'sortorder' => '1,2,3,4,5',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Your Name',
		'f2_value' => '',
		'f2_type' => 'input',
		'f2_validation' => 'on',
		'f3_label' => 'Your Email Address',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'on',
		'f4_label' => 'Your Address',
		'f4_value' => '',
		'f4_type' => 'textarea',
		'f4_validation' => 'on',
		'f5_label' => '',
		'f5_value' => '',
		'f5_type' => 'sectionend',
		'f5_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_feedback_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for leaving your feedback.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '5',
		'sortorder' => '1,2,3,4,5',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Your Name',
		'f2_value' => '',
		'f2_type' => 'input',
		'f2_validation' => 'on',
		'f3_label' => 'Your Email Address',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'off',
		'f4_label' => 'How would you rate this website?',
		'f4_value' => 'Excellent, Good, OK, Poor, Awful',
		'f4_type' => 'radio',
		'f4_validation' => 'on',
		'f5_label' => '',
		'f5_value' => '',
		'f5_type' => 'sectionend',
		'f5_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_upload_form () {		

	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for completing our form.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '1',
		'fields' => '4',
		'sortorder' => '1,2,3,4',
		'captcha' => 'off',
		'modalbutton' => 'Launch Form',
		'progress' => 'off',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Upload File',
		'f2_value' => '',
		'f2_type' => 'upload',
		'f2_validation' => 'on',
		'f3_label' => 'Comments',
		'f3_value' => '',
		'f3_type' => 'textarea',
		'f3_validation' => 'on',
		'f4_label' => '',
		'f4_value' => '',
		'f4_type' => 'sectionend',
		'f4_validation' => 'off',
	));
	
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_webinar_form() {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for completing our form.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '2',
		'fields' => '7',
		'sortorder' => '1,2,3,4,5,6,7',
		'modalbutton' => 'Launch Form',
		'progress' => 'on',
		'webinar' => '1',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Select Webinar',
		'f2_value' => '',
		'f2_type' => 'dropdown',
		'f2_validation' => 'on',
		'f3_label' => 'Email',
		'f3_value' => '',
		'f3_type' => 'email',
		'f3_validation' => 'on',
		'f4_label' => '',
		'f4_value' => '',
		'f4_type' => 'sectionend',
		'f4_validation' => 'off',
		'f5_label' => '',
		'f5_value' => '',
		'f5_type' => 'sectionstart',
		'f5_validation' => 'off',
		'f6_label' => 'First Name',
		'f6_value' => '',
		'f6_type' => 'input',
		'f6_validation' => 'on',
		'f7_label' => 'Last Name',
		'f7_value' => '',
		'f7_type' => 'input',
		'f7_validation' => 'on',
		'f8_label' => '',
		'f8_value' => '',
		'f8_type' => 'sectionend',
		'f8_validation' => 'off'
	));
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_infusion_form() {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	
	$wpdb->insert($table, array(
		'title' => 'Form',
		'notify' => 'off',
		'notifytype' => 'basic',
		'notifysubject' => 'Form Completed!',
		'notifymessage' => 'Thanks for completing our form.',
		'email' => get_option('admin_email'),
		'redirect' => get_bloginfo('url'),
		'errorredirect' => get_bloginfo('url'),
		'sections' => '2',
		'fields' => '11',
		'sortorder' => '1,2,3,4,5,6,7,8,9,10,11',
		'modalbutton' => 'Launch Form',
		'progress' => 'on',
		'infusion' => '1',
		'views' => '',
		'f1_label' => '',
		'f1_value' => '',
		'f1_type' => 'sectionstart',
		'f1_validation' => 'off',
		'f2_label' => 'Form ID',
		'f2_value' => '',
		'f2_type' => 'hidden',
		'f2_validation' => 'off',
		'f3_label' => 'Form Name',
		'f3_value' => '',
		'f3_type' => 'hidden',
		'f3_validation' => 'off',
		'f4_label' => 'Form Version',
		'f4_value' => '',
		'f4_type' => 'hidden',
		'f4_validation' => 'off',
		'f5_label' => 'First Name',
		'f5_value' => '',
		'f5_type' => 'input',
		'f5_validation' => 'on',
		'f6_label' => 'Last Name',
		'f6_value' => '',
		'f6_type' => 'input',
		'f6_validation' => 'on',
		'f7_label' => '',
		'f7_value' => '',
		'f7_type' => 'sectionend',
		'f7_validation' => 'off',
		'f8_label' => '',
		'f8_value' => '',
		'f8_type' => 'sectionstart',
		'f8_validation' => 'off',
		'f9_label' => 'Email',
		'f9_value' => '',
		'f9_type' => 'email',
		'f9_validation' => 'on',
		'f10_label' => 'IP Address',
		'f10_value' => '',
		'f10_type' => 'hidden',
		'f10_validation' => 'off',
		'f11_label' => '',
		'f11_value' => '',
		'f11_type' => 'sectionend',
		'f11_validation' => 'off'
	));
	$fid = $wpdb->get_var("SELECT id FROM $table order by id desc");
	
	$wpdb->update( $table, array(
		'title' => 'Form '.$fid,
	), array( 'id' => $fid ) );
	
	$success = __('Success! The form was created!','formengine').'<span style="float:right;"><a href="?page=formengine_dashboard">'.__('Go Back','formengine').'</a></span>';
	
}

function formengine_wipe() { 
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	
	if(isset($_POST['wipe'])) { formengine_wipedatabase(); 	$error = __('All JumpForms data removed!','formengine'); }
	
	?>
	
	<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><a class="current"><?php _e('Remove All Data','formengine'); ?></a></li>
	</ul>
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>

	<div id="tdmfw_content">

		<?php
			$tablecheck = $wpdb->get_var("show tables like '". $wpdb->prefix . "formengine'");
			if($tablecheck) {
		?>

	<form method="post" action="">
	<?php _e('This action will remove all JumpForms data from the WordPress database.','formengine'); ?><br/><br/>
	<?php _e('To continue using JumpForms after data has been removed, please reactivate the plugin.','formengine'); ?><br/><br/>
	<?php _e('Are you sure you want to continue?','formengine'); ?><br/>
	<input type="submit" name="wipe" class="button-primary" style="margin-top:20px;" value="<?php _e('Yes - REMOVE ALL DATA','formengine'); ?>">
	<a class="button-secondary" href="?page=formengine_dashboard"><?php _e('No - Cancel','formengine'); ?></a>
	</form>
	
		<?php
			} else { echo '<div class="tdmfw_inline_error" style="margin-top:0;">'.__('All JumpForms data has been wiped. Please reactivate the plugin','formengine'); }  
		?>	

	</div>
	</div>
	</div>

	<?php }
	
function formengine_export() { 
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	global $wpdb;
	$table = $wpdb->prefix . "formengine";
	$fid = $_GET['fid'];
	$form = $wpdb->get_row("SELECT * FROM $table WHERE id = '$fid'");
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><?php echo '<a href="?page=formengine_form&fid='.$form->id.'">'.$form->title.'</a>';?></li>
	    <li><a class="current"><?php _e('Export Form','formengine'); ?></a></li>
	</ul>
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">

	<?php _e('In order to re-import this form, please make a copy of the code below','formengine'); ?>.<br/><br/>

	<form id="formengine" action="" method="POST" enctype="multipart/form-data">
	
		<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Export Form','formengine'); ?></p>
			
			<div class="tdmfw_box_content">			

<textarea name="export_code" disabled="disabled" style="float:left;width:538px;height:300px;font-family:courier;"><?php
		if(isset($q)) { } else { $q = ''; }
		$q .= "INSERT INTO `wp_formengine` VALUES(";
		$q .= "'', ";
		$q .= "'".$form->title."', ";
		$q .= "'".$form->notify."', ";
		$q .= "'".$form->notifytype."', ";
		$q .= "'".$form->notifysubject."', ";
		$q .= "'".$form->notifymessage."', ";
		$q .= "'".$form->email."', ";
		$q .= "'".$form->redirect."', ";
		$q .= "'".$form->errorredirect."', ";
		$q .= "'".$form->sections."', ";
		$q .= "'".$form->fields."', ";
		$q .= "'".$form->sortorder."', ";
		$q .= "'".$form->captcha."', ";
		$q .= "'".$form->modalbutton."', ";
		$q .= "'".$form->progress."', ";
		$q .= "'".$form->views."', ";

		$fields = get_option('formengine_max');
		for($counter = 1; $counter<=$fields;$counter++) {
			
			$label = 'f'.$counter.'_label';
			$value = 'f'.$counter.'_value';
			$type = 'f'.$counter.'_type';
			$validation = 'f'.$counter.'_validation';
			$q .= "'".$form->$label."', ";
			$q .= "'".$form->$value."', ";
			$q .= "'".$form->$type."', ";
			
			if($counter < $fields) {
				$q .= "'".$form->$validation."', ";
			} else {
				$q .= "'".$form->$validation."'); ";
			}
			
		}	

		echo $q;
		
	?></textarea>

			</div>
		</div>
	
	<a style="margin-top:20px;" class="button-secondary" href="<?php echo '?page=formengine_form&fid='.$form->id;?>"><?php _e('Go Back','formengine'); ?></a>
	
	</form>
	
	</form>
	</div>
	</div>

<?php }

function formengine_import() { 
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	global $wpdb;
	global $error;
	global $success;
	global $info;
	$table = $wpdb->prefix . "formengine";
	$table2 = $wpdb->prefix . "formengine_data";
	$fid = $_GET['fid'];
	$form = $wpdb->get_row("SELECT * FROM $table WHERE id = '$fid'");
	
	if(isset($_POST['import_data'])){
		if ($_FILES["file"]["error"] > 0) {
		} else {
		
		$fields = get_option('formengine_max');
		for($counter = 1; $counter<=$fields;$counter++) {
			
			$label = 'f'.$counter;
			$value = 'f'.$counter;
			
			if(isset($q)) { } else { $q = ''; }
			$q .= $label.', ';
			
			if($counter < $fields) {
				$q .= 'f'.$counter.'_value, ';
			} else {
				$q .= 'f'.$counter.'_value';
			}
			
		}					
			$result = mysql_query("LOAD DATA LOCAL INFILE '".$_FILES["file"]["tmp_name"]."' INTO TABLE wp_formengine_data Fields terminated by ',' ENCLOSED BY '\"' LINES terminated by '\n' IGNORE 1 LINES(fid, $q)");
			if (!$result) {
			    $error = __('Error! Data could not be imported!');
			} else {
				$success = __('Success! Data successfully imported!');
			}
		
		}
	}
	
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><?php echo '<a href="?page=formengine_form&fid='.$form->id.'">'.$form->title.'</a>';?></li>
	    <li><a class="current"><?php _e('Import Data','formengine'); ?></a></li>
	</ul>
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">
	
	<?php _e('To import data into this form please upload a CSV file using the form below','formengine'); ?>.<br/><br/>

	<form id="formengine" action="" method="POST" enctype="multipart/form-data">
	
		<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Import Data','formengine'); ?></p>
			
			<div class="tdmfw_box_content">			
				<input type="file" name="file">
		</div>
	
	<input type="submit" name="import_data" class="button-primary" style="margin-top:20px;" value="<?php _e('Import','formengine'); ?>">
	<a class="button-secondary" href="<?php echo '?page=formengine_form&fid='.$form->id;?>"><?php _e('Go Back','formengine'); ?></a>
	
	</form>
	
	</div>
	</div>

<?php }

function formengine_form() {
	if(wp_script_is('jquery')) { } else { wp_enqueue_script('jquery'); }
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	wp_register_script('sortorder', plugins_url('/assets/js/backend/sortorder.js',__FILE__ )); wp_enqueue_script('sortorder');
	wp_register_script('formengine', plugins_url('/assets/js/backend/formengine.js',__FILE__ )); wp_enqueue_script('formengine');
	wp_register_script('update-validation', plugins_url('/assets/js/backend/update-validation.js',__FILE__ )); wp_enqueue_script('update-validation');
	global $wpdb;
	global $error;
	global $success;
	global $info;
	if(isset($_GET['fid'])) { $fid = $_GET['fid']; }
	if(isset($_POST['update_form'])){update_form($fid);}
	if(isset($_POST['create_form_page'])){create_form_page($fid,$_POST['title']);}
	$table = $wpdb->prefix . "formengine";
	$form = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
	$forms = $wpdb->get_results("SELECT * FROM $table WHERE id = '$fid'");
	if(isset($_GET['delete_response'])) { delete_response($_GET['delete_response']); $success = __('Success! The response was deleted!','formengine'); }
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><a class="current"><?php echo $form->title;?></a></li>
	</ul>
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">
	<form method="post" action="">
	
	
		<div class="tdmfw_box_half" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Form Title','formengine'); ?></p>
			<div class="tdmfw_box_content">
				<input name="title" class="tdmfw_input" type="text" value="<?php echo $form->title;?>">
			</div>
		</div>
		
		<?php
		    global $wpdb;
		    $table = $wpdb->prefix . "formengine";
			$table2 = $wpdb->prefix . "formengine_data";
		    $forms = $wpdb->get_var("SELECT count(*) FROM $table");
		    $row = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
			$rows = $wpdb->get_var("SELECT count(*) FROM $table2 WHERE fid = $fid ");
			$webval = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."formengine_webinar");
		?>

		<?php
			if(isset($stats)) { } else { $stats = ''; }
			if(isset($conversion)) { } else { $conversion = ''; }
			if($row->views) { $views = $row->views; } else { $views = '0';}
			$stats .= 'Views: '.$views.' | ';
			$stats .= 'Completed: '.$rows.' | ';
			if($row->views) { $conversion = ($rows/$row->views)*100; }
			$stats .= 'Conversion: '.round($conversion).'%';
		?>
		
		<div class="tdmfw_box_half tdmfw_box_half_end" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Statistics','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=10"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">
				<input name="title" style="border:1px solid #fff;padding-left:0;padding-right:0;" disabled="disabled" class="tdmfw_input" type="text" value="<?php echo $stats; ?>">
			</div>
		</div>
		
		<div class="tdmfw_box">
			<p class="tdmfw_box_title"><?php _e('Configuration','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=5"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">

					<table class="tdmfw_table">
						<tbody>
						
							<?php 
							$webinar = $wpdb->get_var("SELECT webinar FROM $table WHERE id='$_GET[fid]'"); 
							
							if($webinar == "1") :	
							?>
							<tr>
								<td>Access Token</td>
								<td><input type="text" name="accesstoken" style="width:269px;" value="<?php if($webval->access_token) echo $webval->access_token ?>" /></td>
							</tr>
							<tr>
								<td>Organizer Key</td>
								<td><input type="text" name="organizerkey" style="width:269px;" value="<?php if($webval->org_key) echo $webval->org_key ?>" /></td>
							</tr>
							
							<tr class="rowinfusion" style="display: none;">
								<td>Form ID</td>
								<td><input type="text" name="formid" style="width:269px;" value="<?php if($form->formid) echo $form->formid ?>" /></td>	
							</tr>
							<tr class="rowinfusion" style="display: none;">
								<td>Form Name</td>
								<td><input type="text" name="formname" style="width:269px;" value="<?php if($form->formname) echo $form->formname ?>" /></td>
							</tr>
							<tr class="rowinfusion" style="display: none;">
								<td>Form Version</td>
								<td><input type="text" name="formversion" style="width:269px;" value="<?php if($form->formversion) echo $form->formversion ?>" /></td>
							</tr>
							<tr class="rowinfusion" style="display: none;">
								<td>IP Address</td>
								<td><input type="text" name="ipaddress" style="width:269px;" value="<?php if($form->ipaddress) echo $form->ipaddress ?>" /></td>
							</tr>
							
							<?php endif; ?>
							<tr>
							<td style="width:50%;"><?php _e('Progress Bars','formengine'); ?></td>
							<td style="width:50%;">
								<select name="progress" style="width:269px;">
									<option value="off"<?php if($form->progress == 'off') {echo " selected='selected'";} ?>><?php _e('Off','formengine'); ?></option>
									<option value="progress"<?php if($form->progress == 'progress') {echo " selected='selected'";} ?>><?php _e('Basic','formengine'); ?></option>
									<option value="progress progress-striped"<?php if($form->progress == 'progress progress-striped') {echo " selected='selected'";} ?>><?php _e('Striped','formengine'); ?></option>
									<option value="progress progress-striped active"<?php if($form->progress == 'progress progress-striped active') {echo " selected='selected'";} ?>><?php _e('Animated','formengine'); ?></option>
								</select>
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Thank You Page','formengine'); ?></td>
							<td style="width:50%;">
								<select name="redirect" style="width:269px;">
								<option value="<?php echo get_bloginfo('url');?>"<?php if($form->redirect == get_bloginfo('url')) {echo " selected='selected'";} ?>><?php _e('Home','formengine'); ?></option>
								<?php 
								$pages = get_pages(); 
								foreach ( $pages as $page ) { ?>
								<option value="<?php echo get_page_link( $page->ID );?>"<?php if($form->redirect == get_page_link( $page->ID )) {echo " selected='selected'";} ?>><?php echo $page->post_title; ?></option>
								<?php }
								?>
								</select>
							</td>
							</tr>

							<tr>
							<td style="width:50%;"><?php _e('Error Page','formengine'); ?></td>
							<td style="width:50%;">
								<select name="errorredirect" style="width:269px;">
								<option value="<?php echo get_bloginfo('url');?>"<?php if($form->errorredirect == get_bloginfo('url')) {echo " selected='selected'";} ?>><?php _e('Home','formengine'); ?></option>
								<?php 
								$pages = get_pages(); 
								foreach ( $pages as $page ) { ?>
								<option value="<?php echo get_page_link( $page->ID );?>"<?php if($form->errorredirect == get_page_link( $page->ID )) {echo " selected='selected'";} ?>><?php echo $page->post_title; ?></option>
								<?php }
								?>
								</select>
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('CAPTCHA','formengine'); ?></td>
							<td style="width:50%;">
								<select name="captcha" style="width:269px;">
								<option value="off"<?php if($form->captcha == 'off') {echo " selected='selected'";} ?>><?php _e('Disabled','formengine'); ?></option>
								<option value="on"<?php if($form->captcha == 'on') {echo " selected='selected'";} ?>><?php _e('Enabled','formengine'); ?></option>
								</select>	
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Modal Text','formengine'); ?> &mdash; <a href="?page=formengine_documentation&did=5"><?php _e('Help?','formengine'); ?></a></td>
							<td style="width:50%;">
								<input name="modalbutton" class="tdmfw_input" type="text" style="width:269px;" value="<?php echo $form->modalbutton;?>">
							</td>
							</tr>
							
						</tbody>
					</table>

			</div>
		</div>
		
		<div class="tdmfw_box">
			<p class="tdmfw_box_title"><?php _e('Notifications','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=6"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">
				<table class="tdmfw_table">
			
			
			
							<tr>
							<td style="width:50%;"><?php _e('Notifications','formengine'); ?></td>
							<td style="width:50%;">
								<select name="notify" style="width:269px;">
									<option value="off"<?php if($form->notify == 'off') {echo " selected='selected'";} ?>><?php _e('Off','formengine'); ?></option>
									<option value="admin"<?php if($form->notify == 'admin') {echo " selected='selected'";} ?>><?php _e('Admin Only','formengine'); ?></option>
									<option value="user"<?php if($form->notify == 'user') {echo " selected='selected'";} ?>><?php _e('User Only','formengine'); ?></option>
									<option value="adminuser"<?php if($form->notify == 'adminuser') {echo " selected='selected'";} ?>><?php _e('Admin And User','formengine'); ?></option>
								</select>
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Notifications Type','formengine'); ?></td>
							<td style="width:50%;">
								<select name="notifytype" style="width:269px;">
									<option value="basic"<?php if($form->notifytype == 'basic') {echo " selected='selected'";} ?>><?php _e('Basic','formengine'); ?></option>
									<option value="full"<?php if($form->notifytype == 'full') {echo " selected='selected'";} ?>><?php _e('Full','formengine'); ?></option>
								</select>
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Email Address','formengine'); ?></td>
							<td style="width:50%;">
								<input name="email" class="tdmfw_input" type="text" style="width:269px;" value="<?php echo $form->email;?>">
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Email Subject','formengine'); ?></td>
							<td style="width:50%;">
								<input name="notifysubject" class="tdmfw_input" type="text" style="width:269px;" value="<?php echo $form->notifysubject;?>">
							</td>
							</tr>
							<tr>
							<td style="width:50%;"><?php _e('Email Message','formengine'); ?></td>
							<td style="width:50%;">
								<input name="notifymessage" class="tdmfw_input" type="text" style="width:269px;" value="<?php echo $form->notifymessage;?>">
							</td>
							</tr>
				
				
				
				</table>
			</div>
		</div>
		
				<div class="tdmfw_box">
			<p class="tdmfw_box_title"><?php _e('Form Builder','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=7"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">

				<table class="tdmfw_table" style="margin-bottom:20px;">
			
			
			
							<tr>
							<td style="width:50%;"><?php _e('Form Sections','formengine'); ?></td>
							<td style="width:50%;">
								<input type="text" style="width:269px;" class="tdmfw_input" name="sections" value="<?php echo $form->sections;?>" />
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Form Fields','formengine'); ?></td>
							<td style="width:50%;">
								<input type="text" style="width:269px;" class="tdmfw_input" name="fields" value="<?php echo $form->fields;?>" />
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;background:transparent !important;"></td>
							<td style="width:50%;background:transparent !important;">
								<input class="button-secondary" type="submit" name="update_form" id="update_form" value="<?php _e('Save Changes','formengine'); ?>" />
							</td>
							</tr>
														
				</table>
				
				<table class="tdmfw_table table_white drag" id="formengine">
							
						<thead>
						<tr valign="top">
						<th>&nbsp;</th>
						<th><?php _e('Label','formengine'); ?></th>
						<th><?php _e('Type','formengine'); ?></th>
						<th><?php _e('Value/Options','formengine'); ?></th>
						<th style="text-align:center;"><?php _e('Required','formengine'); ?></th>
						</tr>
						</thead>
						
						<tbody>
					
					
					<?php 
				    	$fields = $wpdb->get_var("SELECT fields FROM $table WHERE id = $fid");
				    	
				    	$order = $form->sortorder;
						$sortrows = explode(",", $order);
				 		
				 		// FIELDS SAME AS ITEMS IN SORT ORDER - ADD TO SORT ORDER
				 		if(max($sortrows) == $fields) {
							echo '<input type="hidden" name="sortorder" class="sortorder" value="'.$form->sortorder.'">';
						}
				
				 		// MORE FIELDS THAN ITEMS IN SORT ORDER - ADD TO SORT ORDER
				 		if(max($sortrows) < $fields) {
							for ($i = max($sortrows)+1; $i <= $fields; $i++) {
								array_push($sortrows, $i);
							}
							$neworder = implode(",", $sortrows);
							echo '<input type="hidden" name="sortorder" class="sortorder" value="'.$neworder.'">';
						}
						
						// LESS FIELDS THAN ITEMS IN SORT ORDER - TRIM SORT ORDER
				 		if(max($sortrows) > $fields) {
				 			$diff = 0;
							for ($i = max($sortrows); $i >= $fields+1; $i--) {
								array_pop($sortrows);
							}	
							$neworder = implode(",", $sortrows);
							echo '<input type="hidden" name="sortorder" class="sortorder" value="'.$neworder.'">';
						}
				    	
						foreach ($sortrows as $counter) {
							
							$label = 'f'.$counter.'_label';
							$value = 'f'.$counter.'_value';
							$type = 'f'.$counter.'_type';
							$validation = 'f'.$counter.'_validation';
							
						?>
							<tr valign="top" id="<?php echo $counter;?>">
								<td class="dragme">&nbsp;&nbsp;&nbsp;</td>
								<td width="30%"><input class="tdmfw_input" name="<?php echo $label;?>" type="text" value="<?php echo $form->$label;?>"></td>
								<td width="30%"><select style="width:100%;" name="<?php echo $type;?>">
									<optgroup label="Section">
										<option value="sectionstart"<?php if($form->$type == 'sectionstart') {echo " selected='selected'";} ?>><?php _e('Section Start','formengine'); ?></option>
										<option value="sectionend"<?php if($form->$type == 'sectionend') {echo " selected='selected'";} ?>><?php _e('Section End','formengine'); ?></option>
									</optgroup>
									<optgroup label="Custom">
										<option value="input"<?php if($form->$type == 'input' || $form->$type == '') { echo " selected='selected'";} ?>><?php _e('Single Line Text','formengine'); ?></option>
										<option value="textarea"<?php if($form->$type == 'textarea') {echo " selected='selected'";} ?>><?php _e('Paragraph Text','formengine'); ?></option>
										<option value="email"<?php if($form->$type == 'email') { echo " selected='selected'";} ?>><?php _e('Email Address','formengine'); ?></option>
										<option value="password"<?php if($form->$type == 'password') { echo " selected='selected'";} ?>><?php _e('Password','formengine'); ?></option>
										<option value="date"<?php if($form->$type == 'date') {echo " selected='selected'";} ?>><?php _e('Date Picker','formengine'); ?></option>
										<option value="time"<?php if($form->$type == 'time') {echo " selected='selected'";} ?>><?php _e('Time Picker','formengine'); ?></option>
										<option value="checkbox"<?php if($form->$type == 'checkbox') {echo " selected='selected'";} ?>><?php _e('Checkboxes','formengine'); ?></option>
										<option value="dropdown"<?php if($form->$type == 'dropdown') {echo " selected='selected'";} ?>><?php _e('Dropdown Menu','formengine'); ?></option>
										<option value="radio"<?php if($form->$type == 'radio') {echo " selected='selected'";} ?>><?php _e('Multiple Choice','formengine'); ?></option>
										<option value="inlineradio"<?php if($form->$type == 'inlineradio') {echo " selected='selected'";} ?>><?php _e('Inline Multiple Choice','formengine'); ?></option>
										<option value="upload"<?php if($form->$type == 'upload') {echo " selected='selected'";} ?>><?php _e('File Upload','formengine'); ?></option>
										<option value="divider"<?php if($form->$type == 'divider') {echo " selected='selected'";} ?>><?php _e('Text','formengine'); ?></option>
										<option value="acceptance"<?php if($form->$type == 'acceptance') {echo " selected='selected'";} ?>><?php _e('Acceptance','formengine'); ?></option>
										<option value="hidden"<?php if($form->$type == 'hidden') {echo " selected='selected'";} ?>><?php _e('Hidden','formengine'); ?></option>
									</optgroup>
									<optgroup label="Special">
										<option value="country"<?php if($form->$type == 'country') {echo " selected='selected'";} ?>><?php _e('Countries','formengine'); ?></option>
										<option value="county"<?php if($form->$type == 'county') {echo " selected='selected'";} ?>><?php _e('UK Counties','formengine'); ?></option>
										<option value="state"<?php if($form->$type == 'state') {echo " selected='selected'";} ?>><?php _e('States - USA','formengine'); ?></option>
										<option value="statecan"<?php if($form->$type == 'statecan') {echo " selected='selected'";} ?>><?php _e('States - Canada','formengine'); ?></option>
										<option value="stateaus"<?php if($form->$type == 'stateaus') {echo " selected='selected'";} ?>><?php _e('States - Australia','formengine'); ?></option>
									</optgroup>
								</select></td>
								<td width="30%"><input class="tdmfw_input" name="<?php echo $value;?>" type="text" value="<?php echo $form->$value;?>"></td>
								<td width="10%" style="text-align:center;padding-top:8px;">
									<input type="hidden" value="0" name="<?php echo $validation;?>">
									<input name="<?php echo $validation;?>" type="checkbox" <?php if($form->$validation == 'on') { echo "checked='yes'";} ?> /></td>
							</tr>
						
						<?php } 	
				
				?>
				
				</tbody>
				</table>			
				
			</div>
		</div>	
		
		<div class="tdmfw_box">
			<p class="tdmfw_box_title"><?php _e('Responses','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=8"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">

<?php if($rows > 0) { ?>

<table class="tdmfw_table"> 
		
		<thead>
		<tr valign="top">
		<th><?php _e('Date/Time','formengine'); ?></th>
		<th><?php _e('Action','formengine'); ?></th>
		</tr>
		</thead>
		<tbody>	

<?php

   	$rows = $wpdb->get_results("SELECT * FROM $table2 WHERE fid = $fid ORDER BY id DESC");
	foreach ($rows as $row) { ?>
	
	<tr valign="top">
		<td width="93%"><a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=formengine_response&amp;fid=<?php echo $row->fid;?>&amp;id=<?php echo $row->id;?>"><?php echo date("j F Y", strtotime($row->date)); ?> at <?php echo date("H:iA", strtotime($row->date)); ?></a></td>
		<td width="7%"><a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=formengine_form&amp;fid=<?php echo $row->fid;?>&amp;delete_response=<?php echo $row->id;?>" onclick="return confirm('<?php _e('Are you sure you want to delete this response?','formengine'); ?>')">Delete</a></td>
	</tr>
	
	<?php } ?>
	
		</tbody>
		</table>
		
	<?php } else { _e('You do not have any responses for this form.','formengine');}

?>

			</div>
		</div>
		
		<div class="tdmfw_box" style="margin-bottom:20px;">
			<p class="tdmfw_box_title"><?php _e('Import/Export','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=9"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">
			
				<table class="tdmfw_table"> 
					<tbody>
						<tr><td><?php echo "<a href='?page=formengine_import&amp;fid=".$form->id."'>";?><?php _e('Import data into','formengine'); ?> <?php echo $form->title;?></a></td></tr>
						<tr><td><?php echo "<a href='?page=formengine_export&amp;fid=".$form->id."'>";?><?php _e('Export','formengine'); ?> <?php echo $form->title;?></a></td></tr>
						<tr><td><a href="<?php echo plugins_url('assets/export/csv.php',__FILE__ );?>?fid=<?php echo $form->id;?>"><?php _e('Export','formengine'); ?> <?php echo $form->title;?> <?php _e('data to .CSV','formengine'); ?></a></td></tr>
						<tr><td><a href="<?php echo plugins_url('assets/export/txt.php',__FILE__ );?>?fid=<?php echo $form->id;?>"><?php _e('Export','formengine'); ?> <?php echo $form->title;?> <?php _e('data to .TXT','formengine'); ?></a></td></tr>
					</tbody>
				</table>
			
			</div>
		</div>
		
		<div style="clear:both;"></div>

				<input class="button-primary" type="submit" name="update_form" id="update_form" value="<?php _e('Save Changes','formengine'); ?>" />
				<input class="button-secondary" type="submit" name="create_form_page" id="create_form_page" value="<?php _e('Create Form Page','formengine'); ?>" />
				<a class="button-secondary" href="?page=formengine_custom_css"><?php _e('Manage Custom CSS','formengine'); ?></a>
				<a class="button-secondary" href="?page=formengine_dashboard"><?php _e('Go Back','formengine'); ?></a>
				
	</form>
	</div>

<?php }

function formengine_response() {
	global $wpdb;
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	if(isset($_GET['fid'])) { $fid = $_GET['fid']; }
	if(isset($_GET['id'])) { $id = $_GET['id']; }
	$table = $wpdb->prefix . "formengine";
	$table2 = $wpdb->prefix . "formengine_data";
	$forms = $wpdb->get_results("SELECT * FROM $table");
	$structure = $wpdb->get_row("SELECT * FROM $table WHERE id = '$fid'");
	$row = $wpdb->get_row("SELECT * FROM $table2 WHERE fid = '$fid'");
	$getrow = $wpdb->get_row("SELECT * FROM $table2 WHERE id = '$id'");
?>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><?php echo '<a href="?page=formengine_form&fid='.$structure->id.'#start">'.$structure->title.'</a>';?></li>
	    <li><a class="current"><?php _e('Response','formengine'); ?> <?php echo '#'. $_GET['id'];?></a></li>
	</ul>

	<div id="tdmfw_content">	
	
		<div class="tdmfw_box" style="margin-top:0;margin-bottom:20px;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Form Details','formengine'); ?></p>
			<div class="tdmfw_box_content">

				<table class="tdmfw_table">

							<tr>
							<td style="width:50%;"><?php _e('Form','formengine'); ?></td>
							<td style="width:50%;">
								<?php echo '<a href="?page=formengine_form&fid='.$structure->id.'#start">'.$structure->title.'</a>';?>
							</td>
							</tr>
							
							<tr>
							<td style="width:50%;"><?php _e('Date/Time','formengine'); ?></td>
							<td style="width:50%;">
								<?php echo date("j F Y", strtotime($row->date)); ?>  at <?php echo date("H:iA", strtotime($row->date)); ?>
							</td>
							</tr>
							
				</table>

			</div>
		</div>
	
		<div class="tdmfw_box" style="margin-top:0;margin-bottom:20px;">
			<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Response Details','formengine'); ?></p>
			<div class="tdmfw_box_content">
			
				<table class="tdmfw_table">

					<?php
					
						// LOOP THROUGH FORM
						
						$order  = $structure->sortorder;
						$sortrows = explode(",", $order);
						
						foreach ($sortrows as $counter) {	
							
							$label = 'f'.$counter.'_label';
							$type = 'f'.$counter.'_type';
							$datalabel = 'f'.$counter;
							$datavalue = 'f'.$counter.'_value';
					
							if($row->$datavalue != '') { ?>
							
								<tr valign="top">
								<td width="50%"><?php echo $getrow->$datalabel; ?></td>
								<td width="50%"><?php if(filter_var($getrow->$datavalue, FILTER_VALIDATE_EMAIL)) { echo '<a href="mailto:'.$getrow->$datavalue.'">'.$getrow->$datavalue.'</a>'; } elseif(filter_var($getrow->$datavalue, FILTER_VALIDATE_URL)) { echo '<a target="_blank" href="'.$getrow->$datavalue.'">'.$getrow->$datavalue.'</a>'; } else { echo $getrow->$datavalue; } ?></td>
								</tr>
							
							<?php }
							
						} ?>

				</table>
	
		</div>
		</div>
	<div id="content">

<a class="button-secondary" href="?page=formengine_form&fid=<?php echo $structure->id;?>"><?php _e('Go Back','formengine'); ?></a>

	</div>
	</div>

<?php }

function formengine_display($atts, $content = null) {	
	if(isset($id)) { } else { $id = ''; } 
	extract(shortcode_atts(array(
		"id" => $id
	), $atts));
	ob_start();	
	formengine_show_form($id);
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

function formengine_display_modal($atts, $content = null) {
	if(isset($id)) { } else { $id = ''; } 
	extract(shortcode_atts(array(
		"id" => $id
	), $atts));

	global $wpdb;
	$table = $wpdb->prefix . "formengine";
	$row = $wpdb->get_row("SELECT * FROM $table WHERE id = $id");
	echo "<a class='btn' data-toggle='modal' href='#pop' >".$row->modalbutton."</a>"; ?>
	
		<div class="modal fade" id="pop" style="z-index:9999;">
		<div class="modal-body">
		
		<?php 
		
		ob_start();	
		formengine_show_form($id);
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
		
		?>
		
		</div>
		</div>
	
	<?php 

}

function formengine_show_form($fid) { 

	if(wp_script_is('jquery')) { } else { wp_enqueue_script('jquery'); }
	wp_register_script('formengine', plugins_url('/assets/js/frontend/formengine.js',__FILE__ )); wp_enqueue_script('formengine');
	wp_register_script('bootstrap', plugins_url('/assets/js/frontend/bootstrap.js',__FILE__ )); wp_enqueue_script('bootstrap');
	wp_register_script('datepicker', plugins_url('/assets/js/frontend/datepicker.js',__FILE__ )); wp_enqueue_script('datepicker');
	wp_register_script('timepicker', plugins_url('/assets/js/frontend/timepicker.js',__FILE__ )); wp_enqueue_script('timepicker');
	wp_register_script('validation', plugins_url('/assets/js/frontend/validation.js',__FILE__ )); wp_enqueue_script('validation');
	wp_register_style('bootstrap', plugins_url('/assets/css/bootstrap.css',__FILE__ )); wp_enqueue_style('bootstrap');
	wp_register_style('formengine', plugins_url('/assets/css/formengine.css',__FILE__ )); wp_enqueue_style('formengine');

	global $wpdb;
	$table = $wpdb->prefix . "formengine";
	$fields = $wpdb->get_var("SELECT fields FROM $table WHERE id = $fid");
	$row = $wpdb->get_row("SELECT * FROM $table WHERE id = $fid");
	$sectioncount = $row->sections;
	if($row->webinar == "1") {
		wp_register_script('ajaxcall', plugins_url('/assets/js/frontend/ajaxcall.js',__FILE__ )); wp_enqueue_script('ajaxcall');
	}
	
	if(!current_user_can('level_10')) {
		$count = $row->views;
		$newcount = $count + 1;
		$wpdb->update( $table, array(
			'views' => $newcount
		), array( 'id' => $fid ) );
	}
	
?>

	<div id="formengine" class="formengine_form_<?php echo $fid;?>">
	<ul id="myTab" class="nav nav-tabs span8">
		<?php
			global $wpdb;
			$sections = $row->sections;
			for($counter = 1;$counter<=$sections;$counter++) { ?>
			<li <?php if($counter == '1') {echo 'class="active"';} ?>><a href="#step<?php echo $counter;?>" data-toggle="tab"><?php echo $counter;?></a></li>
		<?php } ?>
	</ul>


	<form id="formengine" action="<?php echo plugins_url('/assets/includes/process.php',__FILE__ );?>" method="POST" style="margin-bottom:0;" enctype="multipart/form-data">
		<div id="myTabContent" class="tab-content">	
	
	<?php
	$sec =1;
	$per = 1;
	
	$order  = $row->sortorder;
	$sortrows = explode(",", $order);
	foreach ($sortrows as $counter) {
		
		$datalabel = 'f'.$counter;
		$label = 'f'.$counter.'_label';
		$value = 'f'.$counter.'_value';
		$type = 'f'.$counter.'_type';
		$validation = 'f'.$counter.'_validation';
				
	if($row->$label || $row->$type == "sectionstart" || $row->$type == "sectionend") {
		
		if($row->$validation == "on") { $row->$validation = "validate[required]"; $valmsg = "<span style='color:red;'>&#042;</span>"; } else { $valmsg = ""; }
			
		if($row->$type == "sectionstart") {
		} elseif ($row->$type == "acceptance") {
		} elseif ($row->$type == "divider") {
		} elseif ($row->$type == "sectionend") {
		}elseif ($row->$type == "hidden") {
			echo "<input type='hidden' name='".$datalabel."' value='".$row->$label."'>";
		}else { echo "<label>".$row->$label." ".$valmsg."</label><input type='hidden' name='".$datalabel."' value='".$row->$label."'>"; }
		
		if($row->$validation == "on") { $row->$validation = "validate[required]";}
				
		// WORK OUT THE FIELD TYPE AND DISPLAY
		if($row->$type == "input") {
			echo "<fieldset><input type='text' name='".$label."' class='".$row->$validation."' value='".$row->$value."'></fieldset>";
		} elseif($row->$type == "password") {
			echo "<fieldset><input type='password' name='".$label."' class='".$row->$validation."' value='".$row->$value."'></fieldset>";
		} elseif($row->$type == "email") {
			echo "<fieldset><input type='email' name='".$label."' class='validate[custom[email]]".$row->$validation."' value='".$row->$value."'></fieldset>";
		} elseif($row->$type == "upload") {
			echo "<fieldset><input type='file' id='".$label."' name='".$label."' class='".$row->$validation."'></fieldset>";
		} elseif ($row->$type == "textarea") {
			echo "<fieldset><textarea rows='3' name='".$label."' class='".$row->$validation."'></textarea></fieldset>";
		} elseif ($row->$type == "checkbox") {
			$array = explode(", ",$row->$value);
				echo "<fieldset>";	
				foreach ($array as $key => $value) {
					echo "<input type='checkbox' name='".$label."[]' class='".$row->$validation."' value='".$value."' /> ".$value."<br/>";
				}	
				echo "</fieldset>";
		} elseif ($row->$type == "acceptance") {
			$array = explode(", ",$row->$value);
				echo "<fieldset>";	
				foreach ($array as $key => $value) {
					echo "<input type='checkbox' name='".$label."[]' class='validate[required]' value='".$value."' /> ".$row->$label." ".$valmsg."<br/>";
				}	
				echo "</fieldset>";
		}elseif($row->$type == "hidden") {
			
			$array = explode(", ",$row->$value);
					
				foreach ($array as $key => $value) {
					echo "<input type='hidden' name='".$label."[]' class='validate[required]' value='".$value."' /> ";
				}	
				
		} elseif ($row->$type == "dropdown") {
			$array = explode(", ",$row->$value);
			
			echo "<fieldset><select name='".$label."'>";
				
				if($row->webinar == "1") {
				?>
					<option>Select a Webinar</option>
				<?php
				}
				if($array[0] != "No Webinars Available") {
					foreach ($array as $key => $value) {
						if(strpos($value,'::')) {
							$v = explode("::",$value);
							echo '<option value="'.$v[0].'">'.$v[1].'</option>';
						}
						else {
							echo '<option value="'.$value.'">'.$value.'</option>';
						}
					}
				}
			echo "</select>";
			if($row->webinar == "1") {
				echo '<img src="'.plugins_url( '/assets/img/loading.gif' , __FILE__ ).'" width="100px" height="100px" id="loading" style="display:none" />';
			}
			echo "</fieldset>";	
			
		} elseif ($row->$type == "country") {			
			echo "<fieldset><select name='".$label."'>"; ?>
			
				<option value="Afganistan">Afghanistan</option>
				<option value="Albania">Albania</option>
				<option value="Algeria">Algeria</option>
				<option value="American Samoa">American Samoa</option>
				<option value="Andorra">Andorra</option>
				<option value="Angola">Angola</option>
				<option value="Anguilla">Anguilla</option>
				<option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option>
				<option value="Argentina">Argentina</option>
				<option value="Armenia">Armenia</option>
				<option value="Aruba">Aruba</option>
				<option value="Australia">Australia</option>
				<option value="Austria">Austria</option>
				<option value="Azerbaijan">Azerbaijan</option>
				<option value="Bahamas">Bahamas</option>
				<option value="Bahrain">Bahrain</option>
				<option value="Bangladesh">Bangladesh</option>
				<option value="Barbados">Barbados</option>
				<option value="Belarus">Belarus</option>
				<option value="Belgium">Belgium</option>
				<option value="Belize">Belize</option>
				<option value="Benin">Benin</option>
				<option value="Bermuda">Bermuda</option>
				<option value="Bhutan">Bhutan</option>
				<option value="Bolivia">Bolivia</option>
				<option value="Bonaire">Bonaire</option>
				<option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option>
				<option value="Botswana">Botswana</option>
				<option value="Brazil">Brazil</option>
				<option value="British Indian Ocean Ter">British Indian Ocean Ter</option>
				<option value="Brunei">Brunei</option>
				<option value="Bulgaria">Bulgaria</option>
				<option value="Burkina Faso">Burkina Faso</option>
				<option value="Burundi">Burundi</option>
				<option value="Cambodia">Cambodia</option>
				<option value="Cameroon">Cameroon</option>
				<option value="Canada">Canada</option>
				<option value="Canary Islands">Canary Islands</option>
				<option value="Cape Verde">Cape Verde</option>
				<option value="Cayman Islands">Cayman Islands</option>
				<option value="Central African Republic">Central African Republic</option>
				<option value="Chad">Chad</option>
				<option value="Channel Islands">Channel Islands</option>
				<option value="Chile">Chile</option>
				<option value="China">China</option>
				<option value="Christmas Island">Christmas Island</option>
				<option value="Cocos Island">Cocos Island</option>
				<option value="Colombia">Colombia</option>
				<option value="Comoros">Comoros</option>
				<option value="Congo">Congo</option>
				<option value="Cook Islands">Cook Islands</option>
				<option value="Costa Rica">Costa Rica</option>
				<option value="Cote DIvoire">Cote D'Ivoire</option>
				<option value="Croatia">Croatia</option>
				<option value="Cuba">Cuba</option>
				<option value="Curaco">Curacao</option>
				<option value="Cyprus">Cyprus</option>
				<option value="Czech Republic">Czech Republic</option>
				<option value="Denmark">Denmark</option>
				<option value="Djibouti">Djibouti</option>
				<option value="Dominica">Dominica</option>
				<option value="Dominican Republic">Dominican Republic</option>
				<option value="East Timor">East Timor</option>
				<option value="Ecuador">Ecuador</option>
				<option value="Egypt">Egypt</option>
				<option value="El Salvador">El Salvador</option>
				<option value="Equatorial Guinea">Equatorial Guinea</option>
				<option value="Eritrea">Eritrea</option>
				<option value="Estonia">Estonia</option>
				<option value="Ethiopia">Ethiopia</option>
				<option value="Falkland Islands">Falkland Islands</option>
				<option value="Faroe Islands">Faroe Islands</option>
				<option value="Fiji">Fiji</option>
				<option value="Finland">Finland</option>
				<option value="France">France</option>
				<option value="French Guiana">French Guiana</option>
				<option value="French Polynesia">French Polynesia</option>
				<option value="French Southern Ter">French Southern Ter</option>
				<option value="Gabon">Gabon</option>
				<option value="Gambia">Gambia</option>
				<option value="Georgia">Georgia</option>
				<option value="Germany">Germany</option>
				<option value="Ghana">Ghana</option>
				<option value="Gibraltar">Gibraltar</option>
				<option value="Great Britain">Great Britain</option>
				<option value="Greece">Greece</option>
				<option value="Greenland">Greenland</option>
				<option value="Grenada">Grenada</option>
				<option value="Guadeloupe">Guadeloupe</option>
				<option value="Guam">Guam</option>
				<option value="Guatemala">Guatemala</option>
				<option value="Guinea">Guinea</option>
				<option value="Guyana">Guyana</option>
				<option value="Haiti">Haiti</option>
				<option value="Hawaii">Hawaii</option>
				<option value="Honduras">Honduras</option>
				<option value="Hong Kong">Hong Kong</option>
				<option value="Hungary">Hungary</option>
				<option value="Iceland">Iceland</option>
				<option value="India">India</option>
				<option value="Indonesia">Indonesia</option>
				<option value="Iran">Iran</option>
				<option value="Iraq">Iraq</option>
				<option value="Ireland">Ireland</option>
				<option value="Isle of Man">Isle of Man</option>
				<option value="Israel">Israel</option>
				<option value="Italy">Italy</option>
				<option value="Jamaica">Jamaica</option>
				<option value="Japan">Japan</option>
				<option value="Jordan">Jordan</option>
				<option value="Kazakhstan">Kazakhstan</option>
				<option value="Kenya">Kenya</option>
				<option value="Kiribati">Kiribati</option>
				<option value="Korea North">Korea North</option>
				<option value="Korea Sout">Korea South</option>
				<option value="Kuwait">Kuwait</option>
				<option value="Kyrgyzstan">Kyrgyzstan</option>
				<option value="Laos">Laos</option>
				<option value="Latvia">Latvia</option>
				<option value="Lebanon">Lebanon</option>
				<option value="Lesotho">Lesotho</option>
				<option value="Liberia">Liberia</option>
				<option value="Libya">Libya</option>
				<option value="Liechtenstein">Liechtenstein</option>
				<option value="Lithuania">Lithuania</option>
				<option value="Luxembourg">Luxembourg</option>
				<option value="Macau">Macau</option>
				<option value="Macedonia">Macedonia</option>
				<option value="Madagascar">Madagascar</option>
				<option value="Malaysia">Malaysia</option>
				<option value="Malawi">Malawi</option>
				<option value="Maldives">Maldives</option>
				<option value="Mali">Mali</option>
				<option value="Malta">Malta</option>
				<option value="Marshall Islands">Marshall Islands</option>
				<option value="Martinique">Martinique</option>
				<option value="Mauritania">Mauritania</option>
				<option value="Mauritius">Mauritius</option>
				<option value="Mayotte">Mayotte</option>
				<option value="Mexico">Mexico</option>
				<option value="Midway Islands">Midway Islands</option>
				<option value="Moldova">Moldova</option>
				<option value="Monaco">Monaco</option>
				<option value="Mongolia">Mongolia</option>
				<option value="Montserrat">Montserrat</option>
				<option value="Morocco">Morocco</option>
				<option value="Mozambique">Mozambique</option>
				<option value="Myanmar">Myanmar</option>
				<option value="Nambia">Nambia</option>
				<option value="Nauru">Nauru</option>
				<option value="Nepal">Nepal</option>
				<option value="Netherland Antilles">Netherland Antilles</option>
				<option value="Netherlands">Netherlands (Holland, Europe)</option>
				<option value="Nevis">Nevis</option>
				<option value="New Caledonia">New Caledonia</option>
				<option value="New Zealand">New Zealand</option>
				<option value="Nicaragua">Nicaragua</option>
				<option value="Niger">Niger</option>
				<option value="Nigeria">Nigeria</option>
				<option value="Niue">Niue</option>
				<option value="Norfolk Island">Norfolk Island</option>
				<option value="Norway">Norway</option>
				<option value="Oman">Oman</option>
				<option value="Pakistan">Pakistan</option>
				<option value="Palau Island">Palau Island</option>
				<option value="Palestine">Palestine</option>
				<option value="Panama">Panama</option>
				<option value="Papua New Guinea">Papua New Guinea</option>
				<option value="Paraguay">Paraguay</option>
				<option value="Peru">Peru</option>
				<option value="Phillipines">Philippines</option>
				<option value="Pitcairn Island">Pitcairn Island</option>
				<option value="Poland">Poland</option>
				<option value="Portugal">Portugal</option>
				<option value="Puerto Rico">Puerto Rico</option>
				<option value="Qatar">Qatar</option>
				<option value="Republic of Montenegro">Republic of Montenegro</option>
				<option value="Republic of Serbia">Republic of Serbia</option>
				<option value="Reunion">Reunion</option>
				<option value="Romania">Romania</option>
				<option value="Russia">Russia</option>
				<option value="Rwanda">Rwanda</option>
				<option value="St Barthelemy">St Barthelemy</option>
				<option value="St Eustatius">St Eustatius</option>
				<option value="St Helena">St Helena</option>
				<option value="St Kitts-Nevis">St Kitts-Nevis</option>
				<option value="St Lucia">St Lucia</option>
				<option value="St Maarten">St Maarten</option>
				<option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option>
				<option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option>
				<option value="Saipan">Saipan</option>
				<option value="Samoa">Samoa</option>
				<option value="Samoa American">Samoa American</option>
				<option value="San Marino">San Marino</option>
				<option value="Sao Tome & Principe">Sao Tome &amp; Principe</option>
				<option value="Saudi Arabia">Saudi Arabia</option>
				<option value="Senegal">Senegal</option>
				<option value="Seychelles">Seychelles</option>
				<option value="Sierra Leone">Sierra Leone</option>
				<option value="Singapore">Singapore</option>
				<option value="Slovakia">Slovakia</option>
				<option value="Slovenia">Slovenia</option>
				<option value="Solomon Islands">Solomon Islands</option>
				<option value="Somalia">Somalia</option>
				<option value="South Africa">South Africa</option>
				<option value="Spain">Spain</option>
				<option value="Sri Lanka">Sri Lanka</option>
				<option value="Sudan">Sudan</option>
				<option value="Suriname">Suriname</option>
				<option value="Swaziland">Swaziland</option>
				<option value="Sweden">Sweden</option>
				<option value="Switzerland">Switzerland</option>
				<option value="Syria">Syria</option>
				<option value="Tahiti">Tahiti</option>
				<option value="Taiwan">Taiwan</option>
				<option value="Tajikistan">Tajikistan</option>
				<option value="Tanzania">Tanzania</option>
				<option value="Thailand">Thailand</option>
				<option value="Togo">Togo</option>
				<option value="Tokelau">Tokelau</option>
				<option value="Tonga">Tonga</option>
				<option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option>
				<option value="Tunisia">Tunisia</option>
				<option value="Turkey">Turkey</option>
				<option value="Turkmenistan">Turkmenistan</option>
				<option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option>
				<option value="Tuvalu">Tuvalu</option>
				<option value="Uganda">Uganda</option>
				<option value="Ukraine">Ukraine</option>
				<option value="United Arab Erimates">United Arab Emirates</option>
				<option value="United Kingdom" selected="selected">United Kingdom</option>
				<option value="United States of America">United States of America</option>
				<option value="Uraguay">Uruguay</option>
				<option value="Uzbekistan">Uzbekistan</option>
				<option value="Vanuatu">Vanuatu</option>
				<option value="Vatican City State">Vatican City State</option>
				<option value="Venezuela">Venezuela</option>
				<option value="Vietnam">Vietnam</option>
				<option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option>
				<option value="Virgin Islands (USA)">Virgin Islands (USA)</option>
				<option value="Wake Island">Wake Island</option>
				<option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option>
				<option value="Yemen">Yemen</option>
				<option value="Zaire">Zaire</option>
				<option value="Zambia">Zambia</option>
				<option value="Zimbabwe">Zimbabwe</option>
				
				<?php echo "</select></fieldset>";
			
		} elseif ($row->$type == "state") {			
			echo "<fieldset><select name='".$label."'>"; ?>
			
				<option value="Alabama">Alabama</option>
				<option value="Alaska">Alaska</option>
				<option value="Arizona">Arizona</option>
				<option value="Arkansas">Arkansas</option>
				<option value="California">California</option>
				<option value="Colorado">Colorado</option>
				<option value="Connecticut">Connecticut</option>
				<option value="Delaware">Delaware</option>
				<option value="Florida">Florida</option>
				<option value="Georgia">Georgia</option>
				<option value="Hawaii">Hawaii</option>
				<option value="Idaho">Idaho</option>
				<option value="Illinois">Illinois</option>
				<option value="Indiana">Indiana</option>
				<option value="Iowa">Iowa</option>
				<option value="Kansas">Kansas</option>
				<option value="Kentucky">Kentucky</option>
				<option value="Louisiana">Louisiana</option>
				<option value="Maine">Maine</option>
				<option value="Maryland">Maryland</option>
				<option value="Massachusetts">Massachusetts</option>
				<option value="Michigan">Michigan</option>
				<option value="Minnesota">Minnesota</option>
				<option value="Mississippi">Mississippi</option>
				<option value="Missouri">Missouri</option>
				<option value="Montana">Montana</option>
				<option value="Nebraska">Nebraska</option>
				<option value="Nevada">Nevada</option>
				<option value="New Hampshire">New Hampshire</option>
				<option value="New Jersey">New Jersey</option>
				<option value="New Mexico">New Mexico</option>
				<option value="New York">New York</option>
				<option value="North Carolina">North Carolina</option>
				<option value="North Dakota">North Dakota</option>
				<option value="Ohio">Ohio</option>
				<option value="Oklahoma">Oklahoma</option>
				<option value="Oregon">Oregon</option>
				<option value="Pennsylvania">Pennsylvania</option>
				<option value="Rhode Island">Rhode Island</option>
				<option value="South Carolina">South Carolina</option>
				<option value="South Dakota">South Dakota</option>
				<option value="Tennessee">Tennessee</option>
				<option value="Texas">Texas</option>
				<option value="Utah">Utah</option>
				<option value="Vermont">Vermont</option>
				<option value="Virginia">Virginia</option>
				<option value="Washington">Washington</option>
				<option value="West Virginia">West Virginia</option>
				<option value="Wisconsin">Wisconsin</option>
				<option value="Wyoming">Wyoming</option>
				
				<?php echo "</select></fieldset>";
				
		} elseif ($row->$type == "stateaus") {			
			echo "<fieldset><select name='".$label."'>"; ?>
			
				<option value="Australian Capital Territory">Australian Capital Territory</option>
				<option value="New South Wales">New South Wales</option>
				<option value="Northern Territory">Northern Territory</option>
				<option value="Queensland">Queensland</option>
				<option value="South Australia">South Australia</option>
				<option value="Tasmania">Tasmania</option>
				<option value="Victoria">Victoria</option>
				<option value="Western Australia">Western Australia</option>
				
				<?php echo "</select></fieldset>";
				
		} elseif ($row->$type == "statecan") {			
			echo "<fieldset><select name='".$label."'>"; ?>
							
				<option value="Alberta">Alberta</option>
				<option value="British Columbia">British Columbia</option>
				<option value="Manitoba">Manitoba</option>
				<option value="New Brunswick">New Brunswick</option>
				<option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
				<option value="Northwest Territories">Northwest Territories</option>
				<option value="Nova Scotia">Nova Scotia</option>
				<option value="Nunavut">Nunavut</option>
				<option value="Ontario">Ontario</option>
				<option value="Prince Edward Island">Prince Edward Island</option>
				<option value="Quebec">Quebec</option>
				<option value="Saskatchewan">Saskatchewan</option>
				<option value="Yukon Territory">Yukon Territory</option>
				
				<?php echo "</select></fieldset>";
				
		} elseif ($row->$type == "county") {			
			echo "<fieldset><select name='".$label."'>"; ?>
			
				<optgroup label="England">
					<option>Bedfordshire</option>
					<option>Berkshire</option>
					<option>Bristol</option>
					<option>Buckinghamshire</option>
					<option>Cambridgeshire</option>
					<option>Cheshire</option>
					<option>City of London</option>
					<option>Cornwall</option>
					<option>Cumbria</option>
					<option>Derbyshire</option>
					<option>Devon</option>
					<option>Dorset</option>
					<option>Durham</option>
					<option>East Riding of Yorkshire</option>
					<option>East Sussex</option>
					<option>Essex</option>
					<option>Gloucestershire</option>
					<option>Greater London</option>
					<option>Greater Manchester</option>
					<option>Hampshire</option>
					<option>Herefordshire</option>
					<option>Hertfordshire</option>
					<option>Isle of Wight</option>
					<option>Kent</option>
					<option>Lancashire</option>
					<option>Leicestershire</option>
					<option>Lincolnshire</option>
					<option>Merseyside</option>
					<option>Norfolk</option>
					<option>North Yorkshire</option>
					<option>Northamptonshire</option>
					<option>Northumberland</option>
					<option>Nottinghamshire</option>
					<option>Oxfordshire</option>
					<option>Rutland</option>
					<option>Shropshire</option>
					<option>Somerset</option>
					<option>South Yorkshire</option>
					<option>Staffordshire</option>
					<option>Suffolk</option>
					<option>Surrey</option>
					<option>Tyne and Wear</option>
					<option>Warwickshire</option>
					<option>West Midlands</option>
					<option>West Sussex</option>
					<option>West Yorkshire</option>
					<option>Wiltshire</option>
					<option>Worcestershire</option>
				</optgroup>
				<optgroup label="Scotland">
					<option>Aberdeenshire</option>
					<option>Angus</option>
					<option>Argyllshire</option>
					<option>Ayrshire</option>
					<option>Banffshire</option>
					<option>Berwickshire</option>
					<option>Buteshire</option>
					<option>Cromartyshire</option>
					<option>Caithness</option>
					<option>Clackmannanshire</option>
					<option>Dumfriesshire</option>
					<option>Dunbartonshire</option>
					<option>East Lothian</option>
					<option>Fife</option>
					<option>Inverness-shire</option>
					<option>Kincardineshire</option>
					<option>Kinross</option>
					<option>Kirkcudbrightshire</option>
					<option>Lanarkshire</option>
					<option>Midlothian</option>
					<option>Morayshire</option>
					<option>Nairnshire</option>
					<option>Orkney</option>
					<option>Peeblesshire</option>
					<option>Perthshire</option>
					<option>Renfrewshire</option>
					<option>Ross-shire</option>
					<option>Roxburghshire</option>
					<option>Selkirkshire</option>
					<option>Shetland</option>
					<option>Stirlingshire</option>
					<option>Sutherland</option>
					<option>West Lothian</option>
					<option>Wigtownshire</option>
				</optgroup>
				<optgroup label="Wales">
					<option>Anglesey</option>
					<option>Brecknockshire</option>
					<option>Caernarfonshire</option>
					<option>Carmarthenshire</option>
					<option>Cardiganshire</option>
					<option>Denbighshire</option>
					<option>Flintshire</option>
					<option>Glamorgan</option>
					<option>Merioneth</option>
					<option>Monmouthshire</option>
					<option>Montgomeryshire</option>
					<option>Pembrokeshire</option>
					<option>Radnorshire</option>
				</optgroup>
				<optgroup label="Northern Ireland">
					<option>Antrim</option>
					<option>Armagh</option>
					<option>Down</option>
					<option>Fermanagh</option>
					<option>Londonderry</option>
					<option>Tyrone</option>
				</optgroup>
				
				<?php echo "</select></fieldset>";
			
		} elseif ($row->$type == "date") {
			echo "<fieldset><input type='text' name='".$label."' class='datepick ".$row->$validation."' value='".date('d/m/Y')."'></fieldset>";
		} elseif ($row->$type == "time") {
			echo "<fieldset><input type='text' name='".$label."' class='timepick ".$row->$validation."' value='".$row->$value."'></fieldset>";
		} elseif ($row->$type == "radio") {
			$array = explode(", ",$row->$value);
			echo "<fieldset>";	
			foreach ($array as $key => $value) {
				echo "<input type='radio' style='margin-left:1px;' name='".$label."' class='".$row->$validation."' value='".$value."' /> ".$value."<br/>";
			}
		} elseif ($row->$type == "inlineradio") {
			$array = explode(", ",$row->$value);
			echo "<fieldset>";	
			foreach ($array as $key => $value) {
				echo "<input type='radio' style='margin-left:1px;' name='".$label."' class='".$row->$validation."' value='".$value."' /> ".$value." ";
			}
			echo "</fieldset>";
		} elseif ($row->$type == "sectionstart") {
		?>
		
		
		
			<div class="tab-pane <?php if($sec == '1') {echo 'active';} ?>" id="step<?php echo $sec++;?>">
					
			<?php if($row->progress != "off") { 
				global $wpdb;
				$sections = $row->sections;					
				$percentage = ($per++/$sections*100)."%";		
			?>
				<div class="<?php echo $row->progress;?>">
					<div class="bar" style="width: <?php echo $percentage ;?>"></div>
				</div>
			<?php } ?>
			
			<h2><?php echo $row->$label;?></h2>
		
		<?php } elseif ($row->$type == "divider") {
			echo '<fieldset><p class="divider">'.$row->$label.'</p></fieldset>';
		} elseif ($row->$type == "sectionend") {
			echo "</div>";
		}	
		
	}	

	}

?>

</div>
	
		<?php if($row->captcha == "on") { ?>
			<div class="captcha" style="display:none;">
			<label><?php _e('Security Code','formengine'); ?>: <span style='color:red;'>&#042;</span></label><br/>
			<img src="<?php echo plugins_url('/assets/includes/captcha.php',__FILE__ ); ?>" alt="" /><br/>
			<fieldset><input id="security_code" class="validate[required]" name="security_code" type="text" /></fieldset>
			</div>
		<?php } ?>
	
		<input type="hidden" class="text" name="fid" value="<?php echo $fid;?>" />
		
		<div class="form-actions" style="margin-top:15px;">
		<a class="btnPrev btn" style="display:none;float:left;margin-right:10px;"><?php _e('Back','formengine'); ?></a>
		<a class="btnNext btn" style="float:left;margin-right:10px;"><?php _e('Next','formengine'); ?></a>
		</div>
	
	</form>
</div>

<?php

}

function formengine_custom_css() {
	$formengine_customcss = get_option('formengine_custom_css');
	if (!empty($formengine_customcss)) {
		echo "\n<!-- JumpForms Custom CSS Start -->\n<style type=\"text/css\">\n".$formengine_customcss."\n</style>\n<!-- JumpForms Custom CSS End -->\n\n";
	}
}

function formengine_css_admin() {
	add_action( 'admin_init', 'register_settings_formengine_css' );
}

// register settings
function register_settings_formengine_css(){
	register_setting('formengine_mccss_settings','formengine_custom_css');
}
function formengine_custom_css_options() {
	global $wpdb;
	global $error;
	global $success;
	global $info;
	if(wp_script_is('jquery')) { } else { wp_enqueue_script('jquery'); }
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
?>

<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/assets/css/syntax/codemirror.css"></link>
<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/assets/css/syntax/default.css"></link>
<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/assets/css/syntax/codemirror.js"></script>
<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/assets/css/syntax/css.js"></script>

<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">JumpForms</a></li>
	    <li><a class="current"><?php _e('Custom CSS','formengine'); ?></a></li>
	</ul>
	
	<?php if(isset($_GET['settings-updated'])) { $success = __('Success! Custom CSS saved!','formengine'); } ?>
	
	<?php if(isset($error)) { echo '<div class="tdmfw_error">'.$error.'</div>'; } ?>
	<?php if(isset($success)) { echo '<div class="tdmfw_success">'.$success.'</div>'; } ?>
	<?php if(isset($info)) { echo '<div class="tdmfw_info">'.$info.'</div>'; } ?>
	<div id="tdmfw_content">

		<div class="tdmfw_box">
			<?php _e('Customise the design of your JumpForms forms without ever having to touch the underlying code.','formengine'); ?>
			<p class="tdmfw_box_title"><?php _e('Custom CSS','formengine'); ?><a style="float:right;" href="?page=formengine_documentation&did=11"><?php _e('Help?','formengine'); ?></a></p>
			<div class="tdmfw_box_content">

	<form method="post" action="options.php">
	<?php settings_fields( 'formengine_mccss_settings' ); ?>
	<textarea name="formengine_custom_css" id="formengine_custom_css" dir="ltr" style="width:100%;height:250px;" class="css"><?php echo get_option('formengine_custom_css');?></textarea>
	<script language="javascript">var editor = CodeMirror.fromTextArea(document.getElementById("formengine_custom_css"), { lineNumbers: true });</script>

    	<input type="submit" style="margin-top:20px;" class="button-primary" value="<?php _e('Save Changes','formengine'); ?>" />
		<a class="button-secondary" href="?page=formengine_dashboard"><?php _e('Go Back','formengine'); ?></a>

	</form>

			</div>
		</div>

	</div>
	
<?php 
}

add_action('admin_menu', 'formengine_css_admin');
add_action('wp_head', 'formengine_custom_css');

function formengine_documentation() {
	initformenginepleClient();
	global $pleClient;
	$activation_form = $pleClient->preCheckLicense();
	if($activation_form) 
		return;

		wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
		require('assets/includes/documentation.php');
	
}

function formengine_extensions() {
	initformenginepleClient();
	global $pleClient;
	$activation_form= $pleClient->preCheckLicense();
	if($activation_form) 
		return;

		wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
		require('assets/includes/extensions.php');
	
}


function formengine_infusionsoft() {
	initformenginepleClient();
	global $pleClient;
	$activation_form= $pleClient->preCheckLicense();
	if($activation_form) 
		return;
	/****************************************************/
	wp_register_script('ajax', plugins_url('/assets/js/backend/ajax.js',__FILE__ )); wp_enqueue_script('ajax');
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	require('assets/includes/infusionsoft.php');
	
} 

function formengine_aweber() {
	initformenginepleClient();
	global $pleClient;
	$activation_form= $pleClient->preCheckLicense();
	if($activation_form) 
		return;
	wp_register_style('formengine', plugins_url('/assets/css/framework.css',__FILE__ )); wp_enqueue_style('formengine');
	require('assets/includes/aweber.php');
	
}


?>