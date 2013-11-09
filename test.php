<?php
require_once('aweber_api/aweber_api.php');

$consumerKey = '';
$consumerSecret = '';

// Step 2: load this PHP file in a web browser, and follow the instructions to set
// the following variables:
$accessKey = '';
$accessSecret = '';
$list_id = '';

if (!$consumerKey || !$consumerSecret){
    print "You need to assign \$consumerKey and \$consumerSecret at the top of this script and reload.<br><br>" .
        "These are listed on <a href='https://labs.aweber.com/apps' target=_blank>https://labs.aweber.com/apps</a><br>\n";
    exit;
}

$aweber = new AWeberAPI($consumerKey, $consumerSecret);
if (!$accessKey || !$accessSecret){
    display_access_tokens($aweber);
}

try { 
    $account = $aweber->getAccount($accessKey, $accessSecret);
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

} catch(AWeberAPIException $exc) { 
    print "<h3>AWeberAPIException:</h3>"; 
    print " <li> Type: $exc->type <br>"; 
    print " <li> Msg : $exc->message <br>"; 
    print " <li> Docs: $exc->documentation_url <br>"; 
    print "<hr>"; 
    exit(1); 
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
        $aweber->user->tokenSecret = $_COOKIE['secret'];

        list($accessTokenKey, $accessTokenSecret) = $aweber->getAccessToken();

        print "Please add these lines of code to the top of your script:<br>" .
                "<pre>" .
                "\$accessKey = '{$accessTokenKey}';\n" . 
                "\$accessSecret = '{$accessTokenSecret}';\n" .
                "</pre>" . "<br><br>" .
                "Then click <a href='" . get_self() . "'>here</a> to continue";
        exit;
    }

    if(!isset($_SERVER['HTTP_USER_AGENT'])){
        print "This request must be made from a web browser\n";
        exit;
    }

    $callbackURL = get_self();
    list($key, $secret) = $aweber->getRequestToken($callbackURL);
    $authorizationURL = $aweber->getAuthorizeUrl();

    setcookie('secret', $secret);

    header("Location: $authorizationURL");
    exit();
}
?>