<?php
/**
 * Sample PHP API Client
 * @author		Andrew Waage
 * @created		2011-05-26
 * @version		1.0
 * @copyright		Copyright (c) 2011, Andrew Waage. All rights reserved.
 * @license		Apache License V.2.0
 */
class Incentibox {
	// internal constant to enable/disable debugging
	const DEBUG = false;
	const INCENTIBOX_API_URL = 'http://incentibox.dev/api'; //'http://api.incentibox.com';
	const INCENTIBOX_API_PORT = 80;
	const API_VERSION = '1';
	
	private $password;
	private $time_out = 60;
	private $user_agent;
	private $username;

	// class methods
	public function __construct($username = null, $password = null) {
		if($username !== null) $this->set_username($username);
		if($password !== null) $this->set_password($password);
	}

	private function perform_call($url, $params = array(), $authenticate = false, $use_post = true) {
		// redefine
		$url = (string) $url;
		$aParameters = (array) $params;
		$authenticate = (bool) $authenticate;
		$use_post = (bool) $use_post;

		// build url
		$url = self::INCENTIBOX_API_URL .'/v'. self::API_VERSION .  $url;

		// validate needed authentication
		if($authenticate && ($this->getUsername() == '' || $this->get_password() == '')) {
			throw new IncentiboxException('No username or password was set.');
		}

		// build GET URL if not using post
		if(!empty($params) && !$use_post){
			$queryString = '';
			foreach($aParameters as $key => $value) $queryString .= '&'. $key .'='. urlencode(utf8_encode($value));
			$queryString = trim($queryString, '&');
			$url .= '?'. $queryString;
		}

		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PORT] = self::INCENTIBOX_API_PORT;
		$options[CURLOPT_USERAGENT] = $this->get_useragent();
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->get_time_out();

		// HTTP basic auth
		if($authenticate) {
			$options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			$options[CURLOPT_USERPWD] = $this->getUsername() .':'. $this->get_password();
		}

		// build post params if $use_post
		if(!empty($params) && $use_post) {
			$var = '';
			foreach($params as $key => $value) $var .= '&'. $key .'='. urlencode($value);
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = trim($var, '&');
		}

		// init
		$curl = curl_init();
		// set options
		curl_setopt_array($curl, $options);
		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);
		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);
		// close
		curl_close($curl);

		return $response;
	}


	// Getters
	private function get_password(){
		return (string) $this->password;
	}
	public function get_time_out(){
		return (int) $this->time_out;
	}
	public function get_useragent(){
		return (string) 'PHP IncentiBox API Client/'. self::API_VERSION .' '. $this->user_agent;
	}
	private function getUsername(){
		return (string) $this->username;
	}
	 
	// Setters
	private function set_password($password){
		$this->password = (string) $password;
	}
	public function set_time_out($seconds){
		$this->time_out = (int) $seconds;
	}
	public function set_user_agent($user_agent){
		$this->user_agent = (string) $user_agent;
	}
	private function set_username($username){
		$this->username = (string) $username;
	}


	/**
	 * Returns all redeemed_rewards for the given program_id. 
	 * If param @last_redeemed_reward_id is given, returns all redeemed_rewards where id > @last_redeemed_reward_id
	 *
	 * @return	array
	 * @param	$program_id
	 * @param 	$last_redeemed_reward_id (optional)
	 */
	public function get_redeemed_rewards($program_id, $last_redeemed_reward_id = null) {
		// build url
		$url = 'programs/'.urlencode($program_id).'/redeemed_rewards';
		$url = ($last_redeemed_reward_id != null) ?  $url . '/' . urlencode($last_redeemed_reward_id) : $url;

		$response = $this->perform_call($url, array(), true, false);

		// parse the returned json
		return json_parse($response);
	}
}


// IncentiboxException
class IncentiboxException extends Exception { }

?>
