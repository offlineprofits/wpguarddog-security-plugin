<?php 
require_once('aweber_api/aweber_api.php');

class aweber {

	// Step 1: assign these values from https://labs.aweber.com/apps
	private $consumerKey;
	private $consumerSecret;
	
	
	private $accessKey;
	private $accessSecret;
	private $account_id;
	private $list_id; // list name:default3160788
	private $aweber;
	function __construct() {
		$this->consumerKey = 'Akfb7SmhI9ZjZnfApE2j3HWd';
		$this->consumerSecret = 'jaUt65dRFyKBFLii46Bng9ZfKK8GfD9aTtXgUA6d';
	
	
		$this->accessKey = 'Agy25MJTEbLFafDAyv1qZP35';
		$this->accessSecret = 'Os2QiFbdPEsKNsJDkV7ZTtaPa9ly7jsAikPhWSIc';
		$this->account_id =  "496277";
		//$this->list_id = '1572609'; // list name:apprentice-rms
		$this->aweber = new AWeberAPI($this->consumerKey, $this->consumerSecret);		
	}
	
	function add_subscriber($email, $ip, $name, $listid) {
		try {
			$this->list_id = $listid;
		    $account = $this->aweber->getAccount($this->accessKey, $this->accessSecret);
			
		    $listURL = "/accounts/{$this->account_id}/lists/{$this->list_id}";
		    $list = $account->loadFromUrl($listURL);
		
		    # create a subscriber
		    $params = array(
		        'email' => $email,
		        'ip_address' => $ip,
		        'name' => $name
		    );
		    $subscribers = $list->subscribers;
		    $new_subscriber = $subscribers->create($params);
			
		    # success!
		    return "success";
		    //print "A new subscriber was added to the $list->name list!";
		
		} catch(AWeberAPIException $exc) {
		    //print "<h3>AWeberAPIException:</h3>";
		   // print " <li> Type: $exc->type              <br>";
		    //print " <li> Msg : $exc->message           <br>";
		   // print " <li> Docs: $exc->documentation_url <br>";
		   // print "<hr>";
		   // exit(1);
		}
	}
}

?>