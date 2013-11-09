<?php
require_once ('PLE_Auto_Updater.php');
if(!class_exists('PLE_Client_Util'))
{
	Class PLE_Client_Util
	{
		private $WP_OPTION_PREFIX = NULL;
		private $PLUGIN_SOFTWARE_NAME = NULL;
		private $HIDE_RESET_LICENSE_INFO_LINK = false;
		
		const WP_OPTION_PLE_KEY_NAME = '_ple_key_name';
		const WP_OPTION_PLE_KEY_EMAIL = '_ple_key_email';
		const WP_OPTION_PLE_KEY_EXPIRETIME = '_ple_key_expiretime';
		const WP_OPTION_PLE_KEY_ACTIVATION_KEY = '_ple_key_activation_key';
		const WP_OPTION_PLE_KEY_VALIDATED_TODAY = '_ple_key_validated_today';
		const WP_OPTION_RESET_FIELD_NAME = 'frm_ple_reset_field_key';
		const WP_OPTION_PLE_KEY_UPDATE_INFO = '_ple_key_update_info';
		const WP_OPTION_PLE_KEY_LOCKKEY = '_ple_key_lockkey';
		const WP_OPTION_PLE_KEY_SUBSCRIPTION_NAME = '_ple_key_subscription_name';
		//do not delete on reset
		const WP_OPTION_PLUGINS_LIST_KEY = '_ple_key_plugin_list';
		
		public function activate($key,$software){
			$auth_data = $this->getAPIAuthData($key,$software);
			$json = json_encode($auth_data);
			$url = $this->getURLFor("activate");
			return $this->getHttpResult($url, $json);
		}
			
		public function getFeatures(){
			$plugin_data = json_decode(get_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLUGINS_LIST_KEY, ""), true);
			// even if s/w is locked get the feature list for first time
			if(!$this->isLocked() || $plugin_data == NULL){
				$auth_data = $this->getAPIAuthData($this->getActivationKey(), $this->PLUGIN_SOFTWARE_NAME);
				$json = json_encode($auth_data);
				$url = $this->getURLFor("features");
				$response = $this->getHttpResult($url, $json);
				if ($this->responseHasResult($response)) {
					if (!$this->isLocked()) {
						delete_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLUGINS_LIST_KEY);
					}
					add_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLUGINS_LIST_KEY, json_encode($response['RESULT'], true));
					$plugin_data = json_decode(get_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLUGINS_LIST_KEY, ""), true);
				}
			}
			
			return $plugin_data;
		}
		
		public function getStatus(){
			$auth_data = $this->getAPIAuthData($this->getActivationKey(), $this->PLUGIN_SOFTWARE_NAME);
			$json = json_encode($auth_data);
			$url = $this->getURLFor("status");
			return $this->getHttpResult($url, $json);
		}
		
		public function reset(){
			$auth_data = $this->getAPIAuthData($this->getActivationKey(), $this->PLUGIN_SOFTWARE_NAME);
			$json = json_encode($auth_data);
			$url = $this->getURLFor("reset");
			return $this->getHttpResult($url, $json);
		}
		
		public function saveFeatureForm($data,$key,$software) {
			if (!$this->isLocked()) {
				$auth_data = $this->getAPIAuthData($key,$software);
				$auth_data['features'] = $data;
				$json = json_encode($auth_data);
				$url = $this->getURLFor("savefeature");
				return $this->getHttpResult($url, $json);
			}
		}
				
		private function getAPIAuthData($key=null, $software=null) {
			$auth_data = array('host' => $this->getHost());
			$auth_data['key'] = $key;
			$auth_data['software'] = $software;
			return $auth_data;
		}
		
		private function getURLFor($name) {
			return "http://www.wpfrogs.com/plm/services/licensegateway/$name.json";
			//return "http://localhost/plm/services/licensegateway/$name.json";
		}
		
		private function getHttpHeaders() {
			return array('Content-Type' => 'application/json');
		}
		
		private function getHttpResult($url, $json) {
			$request = new WP_Http;
			$result = $request->request( $url , array( 'method' => 'POST', 'body' => $json, 'headers' => $this->getHttpHeaders()));
			$result_json = json_decode($result['body'], true);
			return $result_json;		
		}
		
		private function getHost() {
			if ($host = $_SERVER['HTTP_X_FORWARDED_HOST'])
			{
				$elements = explode(',', $host);

				$host = trim(end($elements));
			}
			else
			{
				if (!$host = $_SERVER['HTTP_HOST'])
				{
					if (!$host = $_SERVER['SERVER_NAME'])
					{
						$host = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
					}
				}
			}
			// Remove port number from host
			$host = preg_replace('/:\d+$/', '', $host);
			return trim($host);
		}
		
		//START Auto-update
		private function getUpdateInfoKey() {
			return $this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLE_KEY_UPDATE_INFO;
		}
		
		public function setSlug($plugin_slug){
			$update_info = array();
			if ($this->getActivationKey()) {
				$update_info["keyhash"] = MD5($this->getActivationKey());
				$update_info["slug"] =  $plugin_slug;
				$arr = get_plugins();
				$update_info["updateuri"] = $arr["$plugin_slug"]["PluginURI"] . $update_info["keyhash"];
				$update_info["version"] = $arr["$plugin_slug"]["Version"];
				delete_option($this->getUpdateInfoKey());
				add_option($this->getUpdateInfoKey(), $update_info);
			}
		}
		
		public function initUpdater() {
			if (!$this->isLocked()) {
				$update_info = get_option($this->getUpdateInfoKey());
				new PLE_Auto_Updater($update_info["version"], $update_info["updateuri"] , $update_info["slug"],$update_info["keyhash"]);
			}
		}
		
		public function isLocked() {
			if ($this->hasLicense() && MD5($this->getActivationKey(). "-paid") == get_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLE_KEY_LOCKKEY) && !$this->isExpired())
				return false;
			else
				return true;
		}
		
		private function isExpired() {
			$exp_date = get_option($this->WP_OPTION_PREFIX . PLE_Client_Util::WP_OPTION_PLE_KEY_EXPIRETIME); 
			$todays_date = date("Y-m-d"); 
			$today = strtotime($todays_date); 
			$expiration_date = strtotime($exp_date);
			if ($expiration_date < $today){
				return true;
			}
			return false;
		}
		
		//END Auto-update
		
		public function setPrefix($prefix_name){
			$this->WP_OPTION_PREFIX = $prefix_name;
		}
		
		public function setSoftwareName($software_name){
			$this->PLUGIN_SOFTWARE_NAME = $software_name;
		}
					
		public function init($prefix_name,$software_name){
			$this->WP_OPTION_PREFIX = $prefix_name;
			$this->PLUGIN_SOFTWARE_NAME = $software_name;
		}
		
		public function hideResetInfoLink($value){
			$this->HIDE_RESET_LICENSE_INFO_LINK = $value;
		}
		
		public function preCheckLicense(){
			if(isset($_POST[PLE_Client_Util::WP_OPTION_RESET_FIELD_NAME])){
				$this->reset();
				$this->removeActiovationDetails();
			}
			if(isset($_POST['submit_activationkey'])){
				if(isset($_POST['activation_key'])  && trim($_POST['activation_key']) != ''){
					$response = $this->activate($_POST['activation_key'],$this->PLUGIN_SOFTWARE_NAME);
					if ($this->responseHasResult($response)) {
						$this->saveActivationResult($response['RESULT']);
					} else {
						$this->parseAndSetError($response);
						$this->showActivationPage();
						return true;
					}
				}
				else{
					echo "<div class='error'><p>Please enter a valid key!</p></div>";
					$this->showActivationPage();
					return true;
				}
			}
			else{
				if($this->WP_OPTION_PREFIX){
					if(!$this->hasLicense()){
						$this->showActivationPage();
						return true;
					}
					elseif(!$this->isCheckedToday()){
						$response = $this->getStatus();							
						if($this->responseHasResult($response)){							
							$this->saveActivationResult($response['RESULT']);
							$this->setValidateDate();
						}
						else{
							$this->parseAndSetError($response);
							$this->showActivationPage();
							return true;
						}
					}
				}
			}
			echo $this->getSoftwareNotifications();
			if(!$this->HIDE_RESET_LICENSE_INFO_LINK){
				$this->setResetLink();
			}
			return false;
		}
		
		public function getLicenseStats() {
			$PREFIX = $this->WP_OPTION_PREFIX;
			return array( 	
				"name" => get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_NAME),
				"email" => get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EMAIL),
				"expiretime" => date('m-d-Y',strtotime(get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EXPIRETIME))),
				"key" => get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_ACTIVATION_KEY),
				"subscription" => get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_SUBSCRIPTION_NAME)
			);
		}
					
		private function getActivationKey() {
			$PREFIX = $this->WP_OPTION_PREFIX;
			return get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_ACTIVATION_KEY);
		}
		
		/*
		public function getKeyHash() {
			return md5($this->getActivationKey());
		}
		*/
		
		private function hasLicense(){
			return $this->getActivationKey();
		}
		
		private function isCheckedToday() {
			$PREFIX = $this->WP_OPTION_PREFIX;
			$validate_date = date(get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_VALIDATED_TODAY)); 
			$todays_date = date("Y-m-d"); 
			$todays_time = strtotime($todays_date); 
			$validate_time = strtotime($validate_date);
			if ($validate_time == $todays_time) {
				return true;
			}
			return false;
		}
		
		private function setValidateDate() {
			$PREFIX = $this->WP_OPTION_PREFIX;
			update_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_VALIDATED_TODAY, date("Y-m-d"));
		}
		
		private function showActivationPage(){
			$form = <<<ACTFORM
			<form name="activation_key_form" method="post" action="#">
				<table style="width: 50%; margin: 25px auto;" align="center" class="wp-list-table widefat plugins">
					<thead>
					<tr>
						<th colspan="2"><b>License Activation<b></th>
					</tr>
					</thead>
					<tr>
						<td width="25%">License Key</td>
						<td>
							<input style="width: 100%;font-size:1.5em" type="text" name="activation_key"/>
						</td>
					</tr>
					<tr>
						<td width="25%"></td>
						<td >
						<input style="width: 50%;height: 35px;" class="button" type="submit" name="submit_activationkey" value="Activate"/>
						</td>
					</tr>						
					
				</table>
			</form>
ACTFORM;
		echo $form;
		}
		
		private function getJavaScript() {
			$js = <<<JS
				<script type="text/javascript">
					function onLicenseInfo() {
						jQuery( "#dialog-message" ).dialog({
							modal: true,
							buttons: {
								Ok: function() {
								jQuery( this ).dialog( "close" );
								}
							}
						});
						jQuery("#dialog-message").dialog( "option", "width", "35%" );
						jQuery(".ui-dialog-titlebar").hide();
					}
					
					function onResetLicense() {
					document.getElementById("frm_ple_client_util_reset").submit();				
					}
					
				</script>			
JS;
		echo $js;
	}
		
		private function setResetLink() {
		$reset_input_name = PLE_Client_Util::WP_OPTION_RESET_FIELD_NAME;
		$PREFIX = $this->WP_OPTION_PREFIX;
		$name = get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_NAME);
		$email = get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EMAIL);
		$key = get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_ACTIVATION_KEY);
		$expireOn =  date('m-d-Y',strtotime(get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EXPIRETIME)));
		$subscription = get_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_SUBSCRIPTION_NAME);
		$upgrade = NULL;
		if($subscription == "Free" || $subscription == "Standard"){
		      $upgrade = "<div style='float:right'><a href='http://wpfrogs.com/product-category/plugins/'  target='_blank'>Upgrade Now!</a></div>";
		}
		wp_enqueue_script("myUi","http://code.jquery.com/ui/1.10.3/jquery-ui.js");
		wp_enqueue_style("myStyle","http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css");
		$form = <<<ACTFORM
				{$this->getJavaScript()}
				<div id="dialog-message" title="License Information" style="display: none;">
					<table class="wp-list-table widefat plugins" width="100%">
						<thead>
							<tr>
								<th colspan="3">License details<span style="float:right"><a href="#" onclick="onResetLicense()">Reset</a></span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Name</td><td>:</td><td>{$name}</td>
							</tr>
							<tr>
								<td>Email</td><td>:</td><td>{$email}</td>
							</tr>
							<tr>
								<td>Key</td><td>:</td><td>{$key}</td>
							</tr>
							<tr>
								<td>Expire on</td><td>:</td><td>{$expireOn}</td>
							</tr>
							<tr>
							<td>Subscription</td><td>:</td><td>{$subscription} {$upgrade}</td>
						</tr>
						</tbody>
					</table>
				</div>
				<form name="frm_ple_client_util_reset" id="frm_ple_client_util_reset" method="post">
					<input type="hidden" name="{$reset_input_name}" id="{$reset_input_name}" value="Reset"/>
				</form>
				<span style="float:right;padding-right:3em;"><a href='#' onclick="onLicenseInfo()">License Info</a> | <a href='#' onclick="onResetLicense()">Reset License</a></span>
ACTFORM;
		echo $form;
			
		}
		
		private function getSoftwareNotifications(){
			$auth_data = $this->getAPIAuthData($this->getActivationKey(),$this->PLUGIN_SOFTWARE_NAME);
			$json = json_encode($auth_data);
			$url = $this->getURLFor("softwarenotification");
			$response = $this->getHttpResult($url, $json);
			if($this->responseHasResult($response))
				return "<div class='update-nag'>".$response['RESULT']."</div>";
		}
		
		private function responseHasResult($response) {
			if ($response != null && isset($response['STATUS'])
				&& $response['STATUS'] == 'success'
				&& isset($response['RESULT'])
				&& !empty($response['RESULT']))
				return true;
			else
				return false;
		}
		
		private function parseAndSetError($response) {
			if ($response != null && isset($response['STATUS'])) {
				if ($response['STATUS'] == 'error') {
					echo "<div class='error'><p>".$response['RESULT']."</p></div>";
				} else if ($response['STATUS'] == 'warning') {
					echo "<div class='updated'><p>".$response['RESULT']."</p></div>";
				} else {
					echo "<div class='error'><p>Request failed!</p></div>";
				}
			} else {
				echo "<div class='error'><p>Failed to connect license server!</p></div>";
			}
		}
		
		private function saveActivationResult($results) {
			$PREFIX = $this->WP_OPTION_PREFIX;
			$this->removeActiovationDetails();
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_NAME, $results['name']);
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EMAIL, $results['email']);
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EXPIRETIME, $results['expiretime']);
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_ACTIVATION_KEY, $results['key']);
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_VALIDATED_TODAY, date("Y-m-d"));
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_LOCKKEY, $results['lockkey']);
			add_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_SUBSCRIPTION_NAME, $results['subscriptionname']);
		}
		
		public function removeActiovationDetails() {
			$PREFIX = $this->WP_OPTION_PREFIX;
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_NAME);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EMAIL);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_EXPIRETIME);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_ACTIVATION_KEY);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_VALIDATED_TODAY);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_UPDATE_INFO);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_LOCKKEY);
			delete_option($PREFIX.PLE_Client_Util::WP_OPTION_PLE_KEY_SUBSCRIPTION_NAME);		
		}
		
	}
}
?>