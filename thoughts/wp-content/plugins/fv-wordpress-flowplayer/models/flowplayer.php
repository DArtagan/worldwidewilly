<?php

class flowplayer {
	private $count = 0;
	
	/**
	 * Relative URL path
	 */
	const RELATIVE_PATH = '';
	/**
	 * Where videos should be stored
	 */
	const VIDEO_PATH = '';
	/**
	 * Where the config file should be
	 */
	private $conf_path = '';
	
	/**
	 * Configuration variables array
	 */
	public $conf = array();
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		//set conf path
		$this->conf_path = realpath(dirname(__FILE__)).'/../wpfp.conf';
		//load conf data into stack
		$this->_get_conf();
	}
	/**
	 * Gets configuration from cfg file.
	 * 
	 * @return bool Returns false on failiure, true on success.
	 */
	private function _get_conf() {
		//check file exists
		if(file_exists($this->conf_path)) {
			//open file for reading
			$fp = fopen($this->conf_path,'r');
			//check if failed to open
			if(!$fp) {
				error_log('Could not open '.$this->conf_path);
				$return = false;
			} else {
				//read data
				$data = fread($fp,filesize($this->conf_path));
				//get each line
				$tmp = explode("\n",$data);
				//get each var
				foreach($tmp as $key => $dat) {
					//split from var:val
					$data = explode(':', $dat);
					//build into conf stack
					$this->conf[$data[0]] = $data[1];
					$return = true;
				}
			}
			fclose($fp);
		} else {
			error_log("File does not exist: $this->conf_path, attempting to create");
			//attempt to create file
			if(touch($this->conf_path)) {
				//everything is ok!
				error_log($this->conf_path.' Created');
				//read the data
				$this->_get_conf();
			} else {
				//failed
				error_log($this->conf_path.' Creation failed');
				$return = false;
			}
		}
		
		return $return;
	}
	/**
	 * Writes configuration into file.
	 */
	public function _set_conf() {
		//attempt to open file
		$fp = fopen($this->conf_path,'w');
		
		if(!$fp) {
			error_log('Could not open '.$this->conf_path.' for writing');
		} else {
			//file is opened for editing!
			$str = ''; //setup holder var
			//loop post data
			foreach($_POST as $key => $data) {

				//do not want to record the submit value in the config file
				if($key != "submit") {
					// if we have a colour in value, add a #
					if (strlen($data) == 6) $data = '#'.$data;
					$str .= $key.':'.strtolower($data)."\n";
				}
			}
			//comit data
			$len = strlen($str);
			//check lenght
			if($len > 0) { 
				//attempt write
				$write = fwrite($fp, $str, $len);
				//report if failed to error_log
				if(!$write) {
					error_log('Could not write to '.$this->conf_path);
				}
			} else {
				//report 0 length write attempt
				error_log('Caught attempt to write 0 length to config file, aborted');
			}
			fclose($fp);
		}
	}
	/**
	 * Salt function - returns pseudorandom string hash.
	 * @return Pseudorandom string hash.
	 */
	public function _salt() {
        $salt = substr(md5(uniqid(rand(), true)), 0, 10);    
        return $salt;
	}

	
}

/**
 * Defines some needed constants and loads the right flowplayer_head() function.
 */
function flowplayer_head() {
	// define needed constants
	if (!defined('RELATIVE_PATH')) {
		define('RELATIVE_PATH', get_option('siteurl').'/wp-content/plugins/fv-wordpress-flowplayer');
		define('PLAYER', RELATIVE_PATH.'/flowplayer/flowplayer.swf');
		$vid = 'http://'.$_SERVER['SERVER_NAME'];
		if (dirname($_SERVER['PHP_SELF']) != '/') $vid .= dirname($_SERVER['PHP_SELF']);
		define('VIDEO_PATH', $vid.'/videos/');
	}
	// call the right function for displaying CSS and JS links
	if (class_exists('flowplayer_frontend')) {
		flowplayer_frontend::flowplayer_head();
	} else {
		flowplayer_backend::flowplayer_head();
	}
}

?>
