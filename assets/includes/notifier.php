<?php
/**************************************************************
 *                                                            *
 *   Provides a notification to the user everytime            *
 *   your WordPress plugin is updated                         *
 *															  *
 *	 Based on the script by Unisphere:						  *
 *   https://github.com/unisphere/unisphere_notifier          *
 *                                                            *
 *   Author: Pippin Williamson                                *
 *   Profile: http://codecanyon.net/user/mordauk              *
 *   Follow me: http://twitter.com/pippinsplugins             *
 *                                                            *
 **************************************************************/
 
/*
	Replace XXX and xxx by your plugin prefix to prevent conflicts between plugins using this script.
*/

// Constants for the plugin name, folder and remote XML url
define( 'XXX_NOTIFIER_PLUGIN_NAME', 'FormEngine' ); // The plugin name
define( 'XXX_NOTIFIER_PLUGIN_SHORT_NAME', 'FE' ); // The plugin short name, only if needed to make the menu item fit. Remove this if not needed
define( 'XXX_NOTIFIER_PLUGIN_FOLDER_NAME', 'formengine' ); // The plugin folder name
define( 'XXX_NOTIFIER_PLUGIN_FILE_NAME', 'index.php' ); // The plugin folder name
define( 'XXX_NOTIFIER_PLUGIN_XML_FILE', 'http://2dmonkey.com/api/formengine.xml' ); // The remote notifier XML file containing the latest version of the plugin and changelog
define( 'XXX_PLUGIN_NOTIFIER_CACHE_INTERVAL', 3600 ); // The time interval for the remote XML cache in the database (21600 seconds = 6 hours)
define( 'XXX_PLUGIN_NOTIFIER_CODECANYON_USERNAME', '2DMonkey' ); // Your Codecanyon username


// Adds an update notification to the WordPress Dashboard menu
function xxx_update_plugin_notifier_menu() {  
	if (function_exists('simplexml_load_string')) { // Stop if simplexml_load_string funtion isn't available
	    $xml 			= xxx_get_latest_plugin_version(XXX_PLUGIN_NOTIFIER_CACHE_INTERVAL); // Get the latest remote XML file on our server
		$plugin_data 	= get_plugin_data(WP_PLUGIN_DIR . '/' . XXX_NOTIFIER_PLUGIN_FOLDER_NAME . '/' . XXX_NOTIFIER_PLUGIN_FILE_NAME); // Read plugin current version from the style.css

		if( (string)$xml->latest > (string)$plugin_data['Version']) { // Compare current plugin version with the remote XML version
			if(defined('XXX_NOTIFIER_PLUGIN_SHORT_NAME')) {
				$menu_name = XXX_NOTIFIER_PLUGIN_SHORT_NAME;
			} else {
				$menu_name = XXX_NOTIFIER_PLUGIN_NAME;
			}
			add_dashboard_page( XXX_NOTIFIER_PLUGIN_NAME . ' Plugin Updates', $menu_name . ' <span class="update-plugins count-1"><span class="update-count">New Updates</span></span>', 'administrator', 'formengine_update', 'xxx_update_notifier');
		}
	}	
}
add_action('admin_menu', 'xxx_update_plugin_notifier_menu');  




// The notifier page
function xxx_update_notifier() { 
	$xml 			= xxx_get_latest_plugin_version(XXX_PLUGIN_NOTIFIER_CACHE_INTERVAL); // Get the latest remote XML file on our server
	$plugin_data 	= get_plugin_data(WP_PLUGIN_DIR . '/' . XXX_NOTIFIER_PLUGIN_FOLDER_NAME . '/' .XXX_NOTIFIER_PLUGIN_FILE_NAME); // Read plugin current version from the main plugin file
	wp_register_style('formengine', '/wp-content/plugins/'.XXX_NOTIFIER_PLUGIN_FOLDER_NAME.'/assets/css/framework.css'); wp_enqueue_style('formengine');
	?>
	
<div id="tdmfw">
	<div id="tdmfw_header"><h1>FormEngine<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
	<ul id="tdmfw_crumbs">
	    <li><a href="?page=formengine_dashboard">FormEngine</a></li>
	    <li><a class="current"><?php _e('Update'); ?></a></li>
	</ul>
	
	<div id="tdmfw_content">
	
			<div class="tdmfw_box">
				<p class="tdmfw_box_title" style="margin-top:0;"><?php _e('Release Notes'); ?></p>
				<div class="tdmfw_box_content">
				
				<?php echo $xml->changelog; ?>
				
				</div>
			</div
			
			<div class="tdmfw_box">
				<p class="tdmfw_box_title"><?php _e('Update Instructions'); ?></p>
				<div class="tdmfw_box_content">
				
				<table class="tdmfw_table"> 
					<tbody>
						<tr><td><strong>1. Backup</strong> &mdash; We always recommend that you <a target="_blank" href="http://codex.wordpress.org/Backing_Up_Your_Database">make a backup of your data</a> before updating</td></tr>
						<tr><td><strong>2. Release Notes</strong> &mdash; Find out what is new by reading through the <a target="_blank" href="http://www.2dmonkey.com/formengine-changelog">latest release notes</a></td></tr>
						<tr><td><strong>3. Download </strong>  &mdash; Login to CodeCanyon and re-download FormEngine from your downloads page</td></tr>
						<tr><td><strong>4. Support </strong>  &mdash; If you have any questions or need help before updating please <a target="_blank" href="http://www.2dmonkey.com/support">contact us</a></td></tr>
						<tr><td><strong>5. Rate </strong>  &mdash; Don't forget to rate FormEngine. If you are not giving five stars, please <a target="_blank" href="http://www.2dmonkey.com/support">tell us why</a></td></tr>
					</tbody>
				</table>
				</div>
			</div>
			
			<a style="margin-top:20px;" class="button-primary" target="_blank" href="http://codecanyon.net/item/formengine-wordpress-contact-form-wizard/2510594?ref=2DMonkey"><?php _e('Update to v'.$xml->latest); ?></a>
			<a class="button-secondary" href="?page=formengine_dashboard"><?php _e('Go Back'); ?></a>

	</div>
    
<?php } 



// Get the remote XML file contents and return its data (Version and Changelog)
// Uses the cached version if available and inside the time interval defined
function xxx_get_latest_plugin_version($interval) {
	$notifier_file_url = XXX_NOTIFIER_PLUGIN_XML_FILE;	
	$db_cache_field = 'notifier-cache';
	$db_cache_field_last_updated = 'notifier-cache-last-updated';
	$last = get_option( $db_cache_field_last_updated );
	$now = time();
	// check the cache
	if ( !$last || (( $now - $last ) > $interval) ) {
		// cache doesn't exist, or is old, so refresh it
		if( function_exists('curl_init') ) { // if cURL is available, use it...
			$ch = curl_init($notifier_file_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$cache = curl_exec($ch);
			curl_close($ch);
		} else {
			$cache = file_get_contents($notifier_file_url); // ...if not, use the common file_get_contents()
		}

		if ($cache) {			
			// we got good results	
			update_option( $db_cache_field, $cache );
			update_option( $db_cache_field_last_updated, time() );
		} 
		// read from the cache file
		$notifier_data = get_option( $db_cache_field );
	}
	else {
		// cache file is fresh enough, so read from it
		$notifier_data = get_option( $db_cache_field );
	}

	// Let's see if the $xml data was returned as we expected it to.
	// If it didn't, use the default 1.0 as the latest version so that we don't have problems when the remote server hosting the XML file is down
	if( strpos((string)$notifier_data, '<notifier>') === false ) {
		$notifier_data = '<?xml version="1.0" encoding="UTF-8"?><notifier><latest>1.0</latest><changelog></changelog></notifier>';
	}

	// Load the remote XML data into a variable and return it
	$xml = simplexml_load_string($notifier_data); 

	return $xml;
}

?>