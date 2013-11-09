<?php
/*
 * Author : Vishnu
 * Email : rsvishnuu@gmail.com
 * 
 * 
 **/
 


class CitrixAPI {

	public $_organizerKey;
	public $_accessToken;
	public $_webinarkey;
	public function __construct ($_accessToken = NULL, $_organizerKey = NULL, $_webinarkey = NULL) {
		$this->_accessToken = $_accessToken;
		$this->_organizerKey = $_organizerKey;
		$this->_webinarkey = $_webinarkey;
	}
	public function infusionRegister($postString, $purl) {
		$curl_connection = curl_init($purl);
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
		
		curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $postString);
		//perform our request
		$result = curl_exec($curl_connection);
		
		curl_close($curl_connection);
		return $result;
	}
	
	
	
	public function directLogin($email, $password, $apikey) {
		$url = "https://api.citrixonline.com/oauth/access_token?grant_type=password&user_id=".$email."&password=".$password."&client_id=".$apikey;
		/*$fields = array(
				"grant_type" => "password",
				"user_id" => $email,
				"password" => $password,
				"client_id" => $apikey
				);*/
		return $this->makeApiRequest($url,"GET",array(),$this->getJsonHeaders());
	}

	public function createRegistrant($firstname,$lastname,$email) {
		
				
		$url = "https://api.citrixonline.com/G2W/rest/organizers/".$this->_organizerKey."/webinars/".$this->_webinarkey."/registrants";
		
		$fields = array(
						'firstName' => $firstname,
						'lastName' => $lastname,
						'email' => $email
				  );
				 
		
		//url-ify the data for the POST
		/*foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&'; 
		}
		$fields_string = rtrim($fields_string, '&');*/
		//print_r($fields_string);die();
			
		return $this->makeApiRequest($url,"POST",$fields);
	}

	public function getOAuthToken ($_apiKey = null, $_callbackUrl = null) {
		//if (isset($_GET['authorize']) && (int)$_GET['authorize'] == 1) {
			//header('location:https://api.citrixonline.com/oauth/authorize?client_id='. $_apiKey .'&redirect_uri=' . $_callbackUrl);
			//exit();
		//}
		//die("here");
		//if (isset($_GET['code'])) {
			$url = 'https://api.citrixonline.com/oauth/access_token?grant_type=authorization_code&code=N2FjMWU0ZDM6MTQxMDIyYzJkYTY6LTQxOTQ=&client_id='. $_apiKey;
			return $this->makeApiRequest($url);
		//}
	}

	/**
	 * @name getAttendeesByOrganizer
	 * @desc GoToMeeting API
	 */

	public function getAttendeesByOrganizer () {
		$url  = 'https://api.citrixonline.com/G2M/rest/organizers/'. $this->_organizerKey .'/attendees';
		$url .= '?startDate='. date('c');
		$url .= '?endDate='. date('c', strtotime("-7 Days"));

		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getFutureMeetings
	 * @desc GoToMeeting API
	 */

	public function getFutureMeetings () {
		$url  = 'https://api.citrixonline.com/G2M/rest/meetings?scheduled=true';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getUpcomingWebinars
	 * @desc GoToWebinar API
	 */
	public function getUpcomingWebinars () {
		
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/upcomingWebinars';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getPastWebinars
	 * @desc GoToWebinar API
	 */
	public function getPastWebinars ($fromdate, $todate) {
		
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/historicalWebinars?fromTime='.$fromdate.'T00:00:00Z&toTime='.$todate.'T00:00:00Z';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getWebinarAttendees
	 * @desc GoToWebinar API
	 */

	public function getWebinarAttendees ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey .'/attendees';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	public function getWebinarRegistrants ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey .'/registrants';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	public function getWebinar ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey;
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}
	
	public function getOrganizerSession($fromdate, $todate) {
		
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'.$this->_organizerKey.'/sessions?toTime='.$todate.'T00:00:00Z&fromTime='.$fromdate.'T00:00:00Z';
		echo "pp";
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
		
	}
	
	public function getSession($sessionKey, $webinarKey) {
		
		$url = 'https://api.citrixonline.com/G2W/rest/organizers/'.$this->_organizerKey.'/webinars/'.$webinarKey.'/sessions/'.$sessionKey;
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}
	
	public function getAttendee($sessionKey, $webinarKey, $registrantKey) {
		$url = 'https://api.citrixonline.com/G2W/rest/organizers/'.$this->_organizerKey.'/webinars/'.$webinarKey.'/sessions/'.$sessionKey.'/attendees/'.$registrantKey;
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}
	
	public function getPerformance($sessionKey, $webinarKey) {
		$url = 'https://api.citrixonline.com/G2W/rest/organizers/'.$this->_organizerKey.'/webinars/'.$webinarKey.'/sessions/'.$sessionKey.'/performance';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());	
	}
	public function getQuestions($sessionKey, $webinarKey, $registrantKey) {
		$url = 'https://api.citrixonline.com/G2W/rest/organizers/'.$this->_organizerKey.'/webinars/'.$webinarKey.'/sessions/'.$sessionKey.'/attendees/'.$registrantKey.'/questions';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders()); 
	}
	
	
	/**
	 * @param String $url
	 * @param String $requestType
	 * @param Array $postData
	 * @param Array $headers
	 */

	public function makeApiRequest ($url = null, $requestType = 'GET', $postData = array(), $headers = array()) {
		$headers = $this->getJsonHeaders();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    if ($requestType == 'POST') {
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
	    }

	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    $data = curl_exec($ch);

	    $validResponseCodes = array(200, 201, 409);
	    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

	    if (curl_errno($ch)) {
	    	curl_close($ch);
	        return curl_error($ch);
	    }
	    elseif (!in_array($responseCode, $validResponseCodes)) {
	        if ($this->isJson($data)) {
	            $data = json_decode($data);
	        }
	    }

	    curl_close($ch);
	    return $data;
	}

	public function getJsonHeaders () {
		return array(
			"HTTP/1.1",
			"Content-type: application/json",
			//"Accept: application/vnd.citrix.g2wapi-v1.1+json",
			"Accept: application/json",
			"Authorization: OAuth oauth_token=". $this->_accessToken
		);
	}

	public function isJson ($string) {
	    $isJson = 0;
	    $decodedString = json_decode($string);

	    if (is_array($decodedString) || is_object($decodedString)) {
	        $isJson = 1;
	    }

	    return $isJson;
	}
}

//$citrix = new CitrixAPI();
//$oauth = $citrix->getOAuthToken("9f9b11e5561599fe8e081defc994f007");
//$r = $citrix->getWebinar("459139915932043264");
//print_r($r);
//var_dump($oauth);

