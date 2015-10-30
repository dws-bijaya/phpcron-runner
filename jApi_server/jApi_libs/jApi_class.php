<?php
/*
* file: jAPI class
* @author Bijaya Kumar Behera <it.bijaya@gmail.com>
* @version 1.0
*/
(!defined('STDIN') ? define('STDIN', !isset($_SERVER['REMOTE_ADDR'])) : '' );

/*
* class: jApi_Class
*/
class jApi_class {
	//TODOS : will be implement later
}
/*
* class: jApi
*/
class jApi {
	static $err_debug = 0;
	static $err_no = 0;
	static $err_msg = '' ;
	static $jApiExec = '';
	static $jApiDebugOut = '';
	
	// config
	private static $jApi_rule = null;
	private static $output    =  'json';
	private static $cache    =  false;
	private static $content_disposition = 'jApi';
	private static $token = false;
	private static $refresh_token = '' ;
	private static $script_id = '' ;
	private static $method = '' ;
	private static $return = '' ;
	private static $security_salt = 'jAPI';
	private static $auth_url = '';
	private static $auth_ip = '';
	private static $cookie_name = 'jAPI_session'; // cookie

    /* 
    *	session var
    */
	private static $sess_ua = '' ;
	private static $sess_ref_host = '';
	private static $sess_ip = '';
	private static $sess_userid = '';
	private static $sess_timeout = '';
	private static $sess_params  = array();

	static function verify_response_output () {

    	// Not allowed to run on shell
    	if ( self::$err_no === 1001 )
     		die("Can't run from shell, reuire web call");
		
		#var_dump ( self::$err_no); die;// = 1007;
     	// anything go wrong ..
     	if ( self::$err_no == 4000 ) { // No Access 
       		header('HTTP/1.0 401 Unauthorized');
			echo "<h1>Unauthorized.";
       		exit ;
     	}

     	// callback set ?
     	$callback = (string) (  isset($_GET['callback']) ? $_GET['callback'] : '');
    	$callback = @urldecode($callback);
		$parse_url_callback  =  parse_url($callback);
		$qry =  array();
		if ( isset($parse_url_callback['query']))
			 parse_str($parse_url_callback['query'], $qry);
		//
		$qry['jApiAuthRefreshToken'] = 	self::$refresh_token ;
		if ( count($qry) ) {
				$qry ="?" . http_build_query($qry);
		} else 
				$qry = '';
	
		//
		setcookie(self::$cookie_name, self::$refresh_token, self::$sess_timeout );
		$nUrl = $parse_url_callback['scheme'] . "://" . $parse_url_callback['host'] . $parse_url_callback['path']  . $qry;
		@header("Location: $nUrl");
		exit ();
	}
	//
	static function verify_response_init (&$jApi_rules) {
		self::__init1 ($jApi_rules);

		//
		if (self::$err_no)
			return false ;
		// 
		$jApiAuthToken = (isset($_GET['jApiAuthToken']) ? $_GET['jApiAuthToken'] : ''); 
		$jApiAuthRefreshToken = (isset($_GET['jApiAuthRefreshToken']) ? $_GET['jApiAuthRefreshToken'] : ''); 
		$sess_file  = jApi_sess_path . "{$jApiAuthRefreshToken}.sess.data";
		#var_dump ($jApiAuthToken, $jApiAuthSessID, $sess_file); die;
		// No token passed  
		if ( $jApiAuthRefreshToken === "" or !file_exists($sess_file) or !is_readable($sess_file)  or !isset($jApi_rules[$jApiAuthToken]) or ( isset($jApi_rules[$jApiAuthToken]['status']) && $jApi_rules[$jApiAuthToken]['status'] ===0 ) ) {
		 	self::$err_no = 4000; // No jApiAuthToken passed or inactive
		 	return false;
		}


		 //A/c Token 
		 self::$token = $jApiAuthToken  ;

		 //
		 self::$jApi_rule = $jApi_rules[self::$token];


		 // set security salt 
		 if ( isset(self::$jApi_rule['cookie_name']) ) 
			self::$cookie_name = self::$jApi_rule['cookie_name']  ;


		//
		self::$refresh_token = $jApiAuthRefreshToken;	

		//
		$sess_data = file_get_contents($sess_file);
		$sess_data =  @unserialize($sess_data);
		$sess_data = is_array($sess_data) ? $sess_data : array();
		self::$sess_timeout = isset($sess_data['jApi_sess_timeout']) ? $sess_data['jApi_sess_timeout'] : 0 ;
		unset($sess_data);

		//
		self::$err_no = 0 ;
		return true;
	}

	/*
	* 
	*/
	static function verify_response_sess_output () {

    	// Not allowed to run on shell
    	if ( self::$err_no === 1001 )
     		die("Can't run from shell, reuire web call");
		
		#var_dump ( self::$err_no); die;// = 1007;
     	// anything go wrong ..
     	if ( self::$err_no == 4000 ) { // No Access 
       		header('HTTP/1.0 401 Unauthorized');
			echo "<h1>Unauthorized.";
       		exit ;
     	}

     	// callback set ?
     	$callback = (string) (  isset($_GET['callback']) ? $_GET['callback'] : '');
    	$callback = @urldecode($callback);
		$parse_url_callback  =  parse_url($callback);
		$qry =  array();
		if ( isset($parse_url_callback['query']))
			 parse_str($parse_url_callback['query'], $qry);
		//
		$qry['jApiAuthRefreshToken'] = 	self::$refresh_token ;
		if ( count($qry) ) {
				$qry ="?" . http_build_query($qry);
		} else 
				$qry = '';
	
		//
		setcookie(self::$cookie_name, self::$refresh_token, self::$sess_timeout );
		$nUrl = $parse_url_callback['scheme'] . "://" . $parse_url_callback['host'] . $parse_url_callback['path']  . $qry;
		@header("Location: $nUrl");
		exit ();
	}
	
	//
	static function session_init (&$jApi_rules)
	{

		self::__init1 ($jApi_rules);

		//
		if (self::$err_no)
			return false ;

		// 
		$jApiAuthToken = (isset($_GET['jApiAuthToken']) ? $_GET['jApiAuthToken'] : ''); 
		$jApiAuthRefreshToken = (isset($_GET['jApiAuthRefreshToken']) ? $_GET['jApiAuthRefreshToken'] : ''); 
		#var_dump(empty($jApiAuthToken), $jApi_rules[$jApiAuthToken] == '' , !isset($jApi_rules[$jApiAuthToken]) , ( isset($jApi_rules[$jApiAuthToken]['status']) && $jApi_rules[$jApiAuthToken]['status'] ===0 ) ); die;
		if ( empty($jApiAuthToken) or $jApi_rules[$jApiAuthToken] == '' or !isset($jApi_rules[$jApiAuthToken]) or ( isset($jApi_rules[$jApiAuthToken]['status']) && $jApi_rules[$jApiAuthToken]['status'] ===0 ) ) {
		 	self::$err_no = 5000; // No jApiAuthToken passed or inactive
		 	return false;
		}		

		//A/c Token 
		self::$token = $jApiAuthToken  ;

		//
		self::$jApi_rule = $jApi_rules[self::$token];

		// set security salt 
		 if ( isset(self::$jApi_rule['security_salt']) ) 
			self::$security_salt = self::$jApi_rule['security_salt']  ;
		// set auth ip
		self::$auth_ip = self::$jApi_rule['auth_ip']  ;
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		#var_dump(self::$auth_ip, $ip);die;
		if ( is_array(self::$auth_ip) && !in_array($ip, self::$auth_ip)  ) {
			self::$err_no = 5000; // No jApiAuthToken passed or inactive
		 	return false;
		}
		//
		$sess_file  = jApi_sess_path . "{$jApiAuthRefreshToken}.sess.data";
		#var_dump ($jApiAuthToken, $jApiAuthRefreshToken, $sess_file); die;
		// if logout
		if ( isset($_GET['jApi_logout']) && file_exists($sess_file)) {
			@unlink($sess_file); echo $jApiAuthRefreshToken;
			exit ;
		}



		//
		$array = array ( 

						'jApi_sess_id' => (string) (isset($_GET['jApi_sess_id']) ? $_GET['jApi_sess_id'] :  '')  ,
						'jApi_sess_ua' => (string) (isset($_GET['jApi_ua']) ? $_GET['jApi_ua'] :  '')  ,
						'jApi_sess_ref_host' => (string) (isset($_GET['jApi_ref_host']) ? $_GET['jApi_ref_host'] :  '')  ,
						'jApi_sess_ip'  =>  (string) (isset($_GET['jApi_ip']) ? $_GET['jApi_ip'] :  '') ,
						'jApi_sess_userid' => (int) (isset($_GET['jApi_userid']) ? $_GET['jApi_userid'] :  0 ) ,
						'jApi_sess_timeout' => time() +  (int) (isset($_GET['jApi_timeout']) ? $_GET['jApi_timeout'] :  0 )  ,
						'jApi_sess_params' => ( isset($_GET['jApi_params'] ) ? unserialize(base64_decode($_GET['jApi_params']) ):  array() )
					) ;
		#print_r ($array); die;
		$jApiAuthRefreshToken_gen = md5( self::$token . $array['jApi_sess_id'] . $array['jApi_sess_userid'] . self::$security_salt );
		if ($jApiAuthRefreshToken !== $jApiAuthRefreshToken_gen ) {
			self::$err_no = 5000; // No jApiAuthToken passed or inactive
		 	return false;
		}

		$sess_file  = jApi_sess_path . "{$jApiAuthRefreshToken_gen}.sess.data";
		file_put_contents($sess_file, serialize($array));
		echo $jApiAuthRefreshToken_gen; 
		exit;
	}

	static function initSession ()
	{

		$jApiAuthRefreshToken = (string) (isset($_GET['jApiAuthRefreshToken']) ? $_GET['jApiAuthRefreshToken'] :  '') ;
		$cookie_jApiAuthRefreshToken = (string) (isset($_COOKIE[self::$cookie_name]) ? $_COOKIE[self::$cookie_name] :  '') ;
		$sess_file  = jApi_sess_path . "{$jApiAuthRefreshToken}.sess.data";

		#var_dump(1,  ($cookie_jApiAuthRefreshToken) ); die;
		//
		if ( empty($jApiAuthRefreshToken) or empty($cookie_jApiAuthRefreshToken) or $cookie_jApiAuthRefreshToken!= $jApiAuthRefreshToken or !file_exists($sess_file) or !is_readable($sess_file)  ) {
			// Not logged
			self::$err_no = 1003; // Not logged
			return false;
		}

		//
		$sess_data = file_get_contents($sess_file);
		$sess_data =  @unserialize($sess_data);
		$sess_data = is_array($sess_data) ? $sess_data : array();

		//
		self::$sess_ua = isset($sess_data['jApi_sess_ua']) ? $sess_data['jApi_sess_ua'] : '';
		self::$sess_ref_host = isset($sess_data['jApi_sess_ref_host']) ? $sess_data['jApi_sess_ref_host'] : '';
		self::$sess_ip = isset($sess_data['jApi_sess_ip']) ? $sess_data['jApi_sess_ip'] : '';
		self::$sess_userid = isset($sess_data['jApi_sess_userid']) ? $sess_data['jApi_sess_userid'] : 0 ;
		self::$sess_timeout = isset($sess_data['jApi_sess_timeout']) ? $sess_data['jApi_sess_timeout'] : 0 ;
		self::$sess_params = isset($sess_data['jApi_sess_params']) ? $sess_data['jApi_sess_params'] : 0 ;



		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$ref_host = '';
		if ($ref) {
			$ref_host = parse_url($ref, PHP_URL_HOST);
			if ( is_null($ref_host) or $ref_host == false )
					$ref_host = '';
		}

		#var_dump (self::$sess_ua, self::$sess_ref_host ,  self::$sess_ip, self::$sess_userid, self::$sess_timeout ); die;
		#var_dump ( time() > self::$sess_timeout, (self::$sess_ua!=$ua && $ua != '' ),  (self::$sess_ip!=$ip && $ip != '' ) ); die;
		if (  (time() > self::$sess_timeout ) or (self::$sess_ua!=$ua && $ua != '' ) or (self::$sess_ip!=$ip && $ip != '' ) ) {
			@unlink($sess_file);
			self::$err_no = 1003; // Not logged
			return false;
		}

		//
		self::$refresh_token = $jApiAuthRefreshToken;
		//
		return true;
	}
	static function __init1($jApi_rules){
		static $init ;

		//
		if (STDIN) {
			self::$err_no = 1000; // Can't called from shell
			return false;
		}

		if ( ! is_null($init) ) {
			self::$err_no = 1001; // Multiple call not allowed
			return false;
		}

		// default behaviour
		if ( defined('jApi_output') ) 
			self::$output = jApi_output ;

		if ( defined('jApi_content_disposition') ) 
			self::$content_disposition = jApi_content_disposition ;

		if ( defined('jApi_cache') ) 
			self::$cache = jApi_cache ;

		if ( defined('jApi_security_salt') ) 
			self::$security_salt = jApi_security_salt ;

		if ( defined('jApi_cookie_name') ) 
			self::$security_salt = jApi_cookie_name ;
	}
	/*
	* 
	*/
	static function init (&$jApi_rules) {
		

		self::__init1 ($jApi_rules);

		//
		if (self::$err_no)
			return false ;

		//
		$jApiAuthToken = (string) (isset($_GET['jApiAuthToken']) ? $_GET['jApiAuthToken'] :  '') ;
		if ( !isset($jApi_rules[$jApiAuthToken]) or ( isset($jApi_rules[$jApiAuthToken]['status']) && $jApi_rules[$jApiAuthToken]['status'] ===0 ) ) {
		 	self::$err_no = 1002; // No jApiAuthToken passed or inactive
		 	return false;
		}

		 //A/c Token 
		 self::$token = $jApiAuthToken  ;

		 //
		 self::$jApi_rule = $jApi_rules[self::$token];
		
		 
		 // set content disposition
		 if ( isset(self::$jApi_rule['content_disposition']) ) 
			self::$content_disposition = self::$jApi_rule['content_disposition']  ;

		 // set output 
		 if ( isset(self::$jApi_rule['output']) ) 
			self::$output = self::$jApi_rule['output']  ;

		
		 // set security salt 
		 if ( isset(self::$jApi_rule['security_salt']) ) 
			self::$security_salt = self::$jApi_rule['security_salt']  ;
		

		 // set auth_url
		 self::$auth_url = self::$jApi_rule['auth_url']  ;

		 // set auth ip
		 self::$auth_ip = self::$jApi_rule['auth_ip']  ;
				
		 // get validate session
		 if ( !self::initSession() ) {
			return false;
		 }
		 
		//
    	self::$script_id = (isset($_GET['jApiScriptId']) ? $_GET['jApiScriptId'] : '');
		// Valid login .. 
		// collect the output buffer
		ob_start ();

    }
    static function _sendCacheHeader (){
    	// 
    	if ( self::$cache === FALSE ) {
			@header( 'Expires: Wed, 11 Jan 1970 05:00:00 GMT' );				
			header( 'Cache-Control: no-cache,no-store, must-revalidate, max-age=0 , must-revalidate, post-check=0, pre-check=0 ',false);
			header( 'Pragma: no-cache' );
		}
		else {
			@header('Cache-control: must-revalidate, public, cache');
			@header('Expires: ' . gmdate('D, d M Y H:i:s', self::$cache ) . ' GMT');
			@header('Pragma: cache');
		}

    }
    static function _sendContentTypeHeader ()
    {
    	//
    	@header('X-jApi-Powered-By: jApi 1.0');
	   	if ( self::$output == 'json') {
    		//
    		@header('Content-Type: application/json; charset=utf-8');
   		}

   		//
   		if ( self::$content_disposition !== false )
			@header('Content-Disposition: attachment; filename=' . self::$content_disposition ."." . self::$output);
    }
    static function call ()
    {

    	if ( self::$err_no >  0 ) 
    		return false  ;

    	// Find the method name
    	$method = explode(".", isset($_GET['jApiMethod']) ? $_GET['jApiMethod'] : '' );
    	if ( count($method) ==1 ) 
    		array_unshift($method, 'default');
    	$p_class = $method[0];
    	$p_method = $method[1];
   	 	$class_path = jApi_account_path . jApi_ds . self::$token . jApi_ds . "classes" . jApi_ds . "jApi_{$p_class}_class.php";
    	if ( !file_exists($class_path) or !is_readable($class_path) ) {
    		self::$err_no = 2001; // No Pugin file found
			return false;
    	}

    	require_once $class_path ;
    	$class = "{$p_class}_jApi_class";
	   	if ( ! class_exists($class)) {
    		self::$err_no = 2002; // No Pugin class found
			return false;
    	}

    	//
    	if ( !is_callable( array($class, $p_method) )) {
    		self::$err_no = 2003; // No Pugin class found
			return false;
    	}

    	//
    	self::$method = join(".", $method);

    	// UNSET 
    	if(isset($_GET['jApiMethod'])) {
    		unset($_GET['jApiMethod']);
    	}
    	// UNSET 
    	if(isset($_GET['jApiAuthToken'])) {
    		unset($_GET['jApiAuthToken']);
    	}
    	// UNSET 
    	if(isset($_GET['jApiScriptId'])) {
    		unset($_GET['jApiScriptId']);
    	}
    	// UNSET 
    	if(isset($_GET['jApiAuthRefreshToken'])) {
    		unset($_GET['jApiAuthRefreshToken']);
    	}
       	//
    	self::$return = call_user_func_array( array($class, $p_method) , array(self::$sess_userid, self::$sess_params));
    }
    
    static function _output(&$out_data, $nheader = 0 )
    {
    	//
    	self::_sendCacheHeader ();
    	//
    	self::_sendContentTypeHeader ();
    	//
    	$jApiErrNo = json_encode($out_data['jApiErrNo']);
    	$jApiErr = json_encode($out_data['jApiErr']);
    	$jApiExec = json_encode($out_data['jApiExec']);
    	$jApiParams = "[]";
    	$jApiDebugOut = json_encode($out_data['jApiDebugOut']);
    	$jApiExec_ref_update = '';
    	// 
    	$sid = json_encode(self::$script_id);
    	$method = json_encode(self::$method);
    	self::$return = json_encode((array) self::$return);
    	echo "/* <![CDATA[ */\n(function(_jApi_err_no,_jApi_err,_jApi_exec,_jApi_params,_jApi_debug_out,_jApi_err_debug,_jApi_script_id,_jApi_method,_jApi_auth_token,_jApi_refresh_token){";
    	echo "$jApiExec_ref_update\nif(typeof jApi.execute!='undefined'){jApi.execute(_jApi_err_no,_jApi_err,_jApi_exec,_jApi_params,_jApi_debug_out,_jApi_err_debug,_jApi_script_id,_jApi_method,_jApi_auth_token,_jApi_refresh_token);}";
    	echo "})($jApiErrNo,$jApiErr,$jApiExec," . self::$return .",$jApiDebugOut," . self::$err_debug . "," . ($sid) . "," . ($method) . "," . json_encode(self::$token) . "," .  json_encode(self::$refresh_token) . ");\n/* <![CDATA[ */";
    }
    
    static function getClientReqUrl()
    {
    	$nlocation = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://');
       	$nlocation .= $_SERVER['HTTP_HOST'] ; //  . $_SERVER['REQUEST_URI'];
		if ( isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") 
			 $nlocation .= ":". $_SERVER["SERVER_PORT"];
		return $nlocation .= $_SERVER['REQUEST_URI'];		
    }
	static function session_output(){
    	echo  self::$err_no;
    	exit ;
    }
	
	static function output ()
	{
    	$out_data =  array () ;
      	$out_buf =  "";
     	while ( ob_get_level() >0 ) {
     		$out_buf .= ob_get_clean();
       	}

       	// default
       	$out_data['jApiErrNo'] = self::$err_no;
     	$out_data['jApiErr'] = self::$err_msg;

		$out_data['jApiExec'] = self::$jApiExec;
       	$out_data['jApiDebugOut'] = $out_buf ;

	   	if ( self::$err_no === 1001 )
     		die("Can't run from shell, reuire web call");
     	if( headers_sent($filename, $linenum) ) {
     		$out_data['jApiErrNo'] = 2001 ; // Already started session
     		$out_data['jApiErr'] = "Can't send header to browser.";
     		self::_output($out_data, 1);
     		exit ;
     	}
		#var_dump ( self::$err_no); die;// = 1007;
		if ( self::$err_no == 1003 ) { // No login 
       		$nlocation = self::$auth_url;
       		$callback = urlencode( self::getClientReqUrl());
       		$nlocation = self::$auth_url . "?callback={$callback}";
       		header("Location: $nlocation");
       		exit ;
     	} 
     	ob_start();
     	self::_output($out_data, 1);
     	$contents = ob_get_clean();
		if ( 1 && ! ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
			header('Vary: Accept-Encoding'); // Handle proxies
			if ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('gzencode') ) {
				header('Content-Encoding: gzip');
				$contents = gzencode( $contents, 3 );
			} 
			else if ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') && function_exists('gzdeflate') ) {
				header('Content-Encoding: deflate');
				$contents = gzdeflate( $contents, 3 );
			} 
		}

		//
		header('Content-Length: ' . strlen($contents) );	
		echo $contents;
		$contents =  null ; unset($contents);
		@ob_end_flush();     // Strange behaviour, will not work
		flush(); 
     	exit ;
	}
}

