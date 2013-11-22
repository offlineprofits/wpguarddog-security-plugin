<?php 

error_reporting( E_ALL );
ini_set( "display_errors", 1 );

require_once("aweber_api/aweber_api.php");
	
if(isset($_POST['account'])) {
	$url = explode("?",$_SERVER['REQUEST_URI']);
	$aurl = 'http://' . $_SERVER['HTTP_HOST'] . $url[0] . '?page=formengine_aweber';
	update_option("accesskey", '');
	update_option("accesssecret", '');
	?>
	<script>
		window.location = "<?php echo $aurl; ?>";
	</script>
	<?php
}


// Step 1: assign these values from https://labs.aweber.com/apps
$consumerKey = 'Akfb7SmhI9ZjZnfApE2j3HWd';
$consumerSecret = 'jaUt65dRFyKBFLii46Bng9ZfKK8GfD9aTtXgUA6d'; 
update_option("consumerkey", $consumerKey);
update_option("consumersecret", $consumerSecret);	
$aweber = new AWeberAPI($consumerKey, $consumerSecret);
	
$accessKey = get_option("accesskey");
$accessSecret = get_option("accesssecret");

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
	//die("here");
	    display_access_tokens($aweber);
}
	
//try { 
    /*$account = $aweber->getAccount($accessKey, $accessSecret);
    $account_id = $account->id;

    if (!$list_id){
        display_available_lists($account);
        exit;
    }

    print "You script is configured properly! " . 
        "You can now start to develop your API calls, see the example in this script.<br><br>" .
        "Be sure to set \$test_email if you are going to use the example<p>";

    //example: create a subscriber
    /*
    $test_email = '';
    if (!$test_email){
    print "Assign a valid email address to \$test_email and retry";
    exit;
    }
    $listURL = "/accounts/{$account_id}/lists/{$list_id}"; 
    $list = $account->loadFromUrl($listURL);
    $params = array( 
        'email' => $test_email,
        'ip_address' => '127.0.0.1',
        'ad_tracking' => 'client_lib_example', 
        'misc_notes' => 'my cool app', 
        'name' => 'John Doe' 
    ); 
    $subscribers = $list->subscribers; 
    $new_subscriber = $subscribers->create($params);
    print "{$test_email} was added to the {$list->name} list!";
    */
/*
} catch(AWeberAPIException $exc) { 
    print "<h3>AWeberAPIException:</h3>"; 
    print " <li> Type: $exc->type <br>"; 
    print " <li> Msg : $exc->message <br>"; 
    print " <li> Docs: $exc->documentation_url <br>"; 
    print "<hr>"; 
    exit(1); 
}*/
?>
<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=formengine_dashboard">JumpForms</a></li>
			<li><a class="current"><?php _e('Aweber','formengine'); ?></a></li>
			
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
				</div>	
			</div>
			
		</div>
	</div>
</div>
<script>
	jQuery(function() {
		jQuery("#aweber_link").click(function() {
			confirm("Alert!!! Your current account information will resetted");
		})
	})
</script>