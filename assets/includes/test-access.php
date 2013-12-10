<?php
if (isset ( $_REQUEST ['check'] ) && $_REQUEST ['check'] == "testaccess")
{
        if(!defined('sugarEntry') || !sugarEntry)
        {
		define('sugarEntry', TRUE);
		include_once('nusoap/nusoap.php');
        }

	$url = trim($_POST['url'], '/');
	$username = $_REQUEST['username'];
	$password = $_REQUEST['password'];
/*
        $url = trim($config['smack_host_address'], '/');
        $username = $config['smack_host_username'];
        $password = $config['smack_host_access_key'];
*/

        $client = new nusoapclient($url.'/soap.php?wsdl',true);
        $user_auth = array(
                'user_auth' => array(
                'user_name' => $username,
                'password' => md5($password),
                'version' => '0.1'
        ),
        'application_name' => 'wp-sugar-pro');


        $login = $client->call('login',$user_auth);
        $session_id = $login['id'];
	if(isset($login['id']))
	{
		$recordInfo = $client->call('get_module_fields', array('session' => $session_id, 'module_name' => 'Leads'));

		if(isset($recordInfo['error']['number']) && is_array($recordInfo['error']) )
		{
			die("Please check the user name or password");
		}
		else
		{
			die('Success');
		}
	}
	else
	{
		die('Please check the credentials');
	}
}
?>
