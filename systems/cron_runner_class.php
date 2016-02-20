<?php
/*
  ######################################################################
  script file : cron runner
  version     : 1.0
  description : main cron runner class
  ######################################################################
 */
(!defined('STDIN') ? define('STDIN', !isset($_SERVER['REMOTE_ADDR'])) : '' );

class cronRunner {

	// Background
	const BG_RUN_COMMAND_WIN   = '%%PHP_EXE_PATH%% -q %%INI_FILE%% %%COMMAND%%  2>nul >nul';
	const BG_RUN_COMMAND_LINUX = '%%PHP_EXE_PATH%% -q %%INI_FILE%% %%COMMAND%%  > /dev/null 2> /dev/null & echo $!';

	// Kill 
	const KILL_COMMAND_LINUX  =  'kill [[PID]]' ;
	const KILL_COMMAND_WIN = 'taskkill /F /PID [[PID]]';
	
	/*
	*  
	*/
	static $_PARAMS = array();

	/*
	*  
	*/
	static $_CONFIGS = array();

	/*
	*  
	*/
	static $cService = null;

	/*
	*  
	*/
	static $_pidfile = '';

	/*
	*  
	*/
	static $_pidsubkey = '';

	/*
	*  
	*/
	static $_instid = '';

	/*
	*  
	*/
	static $_cronid = 0;

	/*
	* 
	*/
	static $_pid = 0;

	/*
	*  pid file
	*/
	static $_starton = 0;

	/*
	*  
	*/
	static $_garbage_output = '';

	/*
	*  
	*/
	static $_logfile = '';
	/*
	*  
	*/
	static $_outputfile = '' ; 
	/*
	*  
	*/
	static $_statusfile = '' ;

	/*
	*  
	*/
	static $_stopfile = '' ;


	/*
	*  
	*/
	static $_callee_tmp = false;

	/*
	*  
	*/
	protected static $_clearexit = array( false , 'fail');
	
	/*
	*
	*/
	static function read_command () {
		$cron = self::$cService;
		// read command		
		$cmd_file =  CRON_PID_DIR .  $cron->getPid() . '.cmd';
		$cmd_file =  CRON_PID_DIR .  "0" . '.cmd';
		if ( is_readable($cmd_file) ) {
			$cmds = array_filter( explode("\n", file_get_contents($cmd_file) ) ) ;
			foreach ($cmds as $cmd) {
				if ( preg_match("`^([a-z0-9_]+)(\s+)(.*)$`imUs", $cmd, $argv) ){
					$cmd  = $argv[1];
					$argv = json_decode(trim( $argv[3]),true);
					$argv = ( (is_null($argv) or $argv == false ) ? array() :(array) $argv );
					$param = func_get_args();
					$arr_param = array ($cmd, $argv, $param); 
					$ret = call_user_func_array( array($cron,  'onReceive') , $arr_param);
				}	
			}
			@unlink($cmd_file);
		}
		return true;
	}

	/**
	*
	*/
	static function get_err_msg () {
		static $messages;
		if ( is_null($messages) )
		{
			$obj = new stdClass();
			$messages[0] = &$obj;
			$messages[0]->msgs[0] ="Success";
			$messages[0]->msgs[1] = "Instance ID not found";
			$messages[0]->msgs[2] = "Instance already running";
			$messages[0]->msgs[3] = "Load avg exceeded";
			$messages[0]->msgs[4] = "exececute cron file successfully"; // for http request file as cron
			$messages[0]->msgs[5] = "Temp folder is not writable.";
			$messages[0]->msgs[6] = ""; // Custom error
			$messages[0]->msgs[7] = "Timeout";
			$messages[0]->msgs[8] = "";
			$messages[0]->msgs[9] = "";
			$messages[0]->msgs[10] = "PID file folder is not writable";
			$messages[0]->msgs[11] = "fails to write PID file";
			$messages[0]->msgs[12] = "PID file opening error for writable";
			$messages[0]->msgs[13] = "fails to lock the file for exclusive mode";
			$messages[0]->msgs[14] = "No Process running.";
			$messages[0]->msgs[15] = "Invalid Query Passed.";
			$messages[0]->msgs[16] = "Not compactable with this OS.";
			$messages[0]->msgs[17] = "No command found to start the process.";
			$messages[0]->msgs['query'][0][] = "Successfully Killed.";
			$messages[0]->msgs['query'][1] = "Invalid Query Passed.";

		}
		return  $messages[0];
	}


	

	/**
	*
	*/
	static function get_options ($v = null ) {
		global $argv;
		$options = array ();
		$options[0] = $argv[0];
		for($i=1; $i < count($argv); $i=$i+2 ) {
			$options[$argv[$i]]=(isset($argv[$i+1])? $argv[$i+1] : '' ) ;			
		}	
		return ( is_null($v) ? $options :  (isset($options["-" . $v]) ? $options["-" . $v] : false ) ) ; 
	}
	/**
	*
	*/
	static function check_instance() 
	{		
		global $argv;
		if (INSTANCES === FALSE )
	        return true;

	    $instance = self::get_options('INSTID');
	    if ( $instance !== FALSE )
	    	$instance = (int) $instance;


	    // we don't alowed other then ..
	    if ( !in_array($instance, array_filter( explode(",", INSTANCES))) ) {
	        return false;
	    }    
	    self::$_instid = $instance;
	    unset($instance);
	    return true;
	}

	/**
	*
	*/
	static function clear_exit($status = true , $msg = 'success') 
	{
		self::$_clearexit = array ( $status , $msg) ;
	}
	/**
	*
	*/
	static function get_instance_name() 
	{
		static $instance_name ;
		if ( !$instance_name ) {	
			$p =  ( empty( $_SERVER['PHP_SELF'] ) ? $_SERVER['argv'][0] :  $_SERVER['PHP_SELF']  ); 
			$p =  pathinfo( $p );
			$instance_name = strtolower(preg_replace("/[^a-z0-9_]/i","_",   $p['filename'] ) ) ;
			unset ( $p ); 
		}
		return $instance_name;
	}
	

	/**
	*
	*/
	static function instance_running($inst_cron_id) 
	{ 
	  	if (INSTANCES === FALSE )
        	return false;
    	$pid_file_pattern = CRON_PID_DIR . CRON_PID_FILENAME . "_" . $inst_cron_id . "_[0-9]*.pid";
    	$cur_pid_files = glob($pid_file_pattern);
   		$pid = false;
		foreach ($cur_pid_files as $cur_pid_file) {
			$basename = basename($cur_pid_file);
			$files   =  array ();
			// invalid file , just skip
        		if (!preg_match("~.*_(\d+)\.pid$~ims", $basename, $files) or !file_exists($cur_pid_file)  )
            			continue;
			$fptr = @fopen($cur_pid_file, 'r+');
			// no more locked
			if ( !$fptr || ! flock($fptr, LOCK_EX | LOCK_NB) ) {
				$return = array( (int) $files[1], self::$_instid, 0 );
				if($fptr) {
					fclose($fptr);
					$pids =   file_get_contents($cur_pid_file);
					#echo "dd" . $pids; die;
					$return = explode("|", $pids); 
				} 				
				return $return;
			}
			if($fptr)
					fclose($fptr);
			@unlink($cur_pid_file);	
    	} 
		return ( $pid === TRUE  );
	}
	/**
	*
	*/
	static function suppress_err () {
		static $int = 0 ;
		if ( $int ==0 ) {
			set_error_handler(array('self','suppress_error_handler'));
			$int = 1;
			return ;
		}
		restore_error_handler();
		$int = 0;
	}
	/**
	*
	*/
	static function suppress_error_handler() 
	{
		return;
	}
	/**
	*
	*/
	static function get_output_type (){
		static $output_type = array(); 
		if ( count($output_type))
			return $output_type;
		$ctype  = 'text';
		$cmode = '|';
		if ( !STDIN ) {
			@list($ctype, $cmode) = @explode(";", isset($_GET['3GCRON']['OUTPUT']) ? $_GET['3GCRON']['OUTPUT'] : "$ctype;$cmode" );
		} 	
		$output_type = array ( $ctype, $cmode); 
		return $output_type;
	}

	/**
	*
	*/
	static function send_nocache_headers () 
	{
  		header("Expires: Tue, 03 Jul 1997 06:00:00 GMT");
    	header("Last-Modified: " . gmdate("D, d M Y H:i(worry)") . " GMT");
    	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    	header("Cache-Control: post-check=0, pre-check=0", false);
    	header("Pragma: no-cache"); 
	}

	/*
	*
	*/
	static function send_ctype_header ( $ctype, $test = 0)
	{
		switch( true) {
 			case $ctype  == 'json':
 				header ('Content-Type: application/json; charset=UTF-8');
 				if ( ! $test) 
  					header ('Content-Disposition: attachment');
  				break;
  			case $ctype  == 'xml':
 				header ('Content-Type: text/xml; charset=UTF-8');
 				break;
 			case $ctype  == 'text':
 			default 			  : 
 				header ('Content-Type: text/plain; charset=UTF-8');
 		} 
 	}
 	/*
 	*
 	*/
 	static function send_output ($ret, $return = false )
 	{

 		list($ctype, $cmode) = self::get_output_type();
 		self::send_nocache_headers ();
 		self::send_ctype_header ($ctype, 1) ; 

 		$jsout = '';
 		switch( true) {
 			case $ctype  == 'json':
 				if ( is_string($cmode) && $cmode !=="") {
 					$jsout  = $cmode . "(" . json_encode($ret) . ")";
 					break;
 				} 
 				$jsout = json_encode($ret); break;
 			case $ctype  == 'text':

 				if ( is_string($cmode) && $cmode == "json") {
 					$jsout =json_encode($ret); break ;
 				}
 				$jsout = implode($cmode, $ret);
 				break; 
 			case $ctype  == 'xml':	
 				$jsout =  '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?><data>';
 				foreach ($ret as $key => $value) {
 					$jsout .= "<$key>$value</$key>";
 				}	
 				$jsout .="</data>";
 		}
 		if ( $return ) return $jsout;
 		echo $jsout;
 	}

 	/**
	*
	*/
	static function get_load_avg() {
    	$load = false;
    	if (stristr(PHP_OS, 'win')) {       
            $wmi = new COM("Winmgmts://");
            $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
            $cpu_num = 0;
            $load_total = 0;
            foreach ($server as $cpu){
                $cpu_num++;
                $load_total += $cpu->loadpercentage;
            }  
            $load = round($load_total/$cpu_num);           
    	} else {
      		if ( !function_exists('sys_getloadavg') ) 
        	{
           		$str = substr(strrchr(shell_exec("uptime"),":"),1);
        		$load = array_map("trim",explode(",",$str));
       		} else {
            		$sys_load = sys_getloadavg();
            		$load = $sys_load[0];
        	}
    	}
    	return is_bool($load) ?  $load : (int) $load;
 	}
 	/**
	*
	*/
	static function start ($exeScript, $instid , $params, $confirm = 2 , $out = 'text' ) {
		$dirname =  pathinfo($exeScript);
		if ( $dirname['dirname'] == '.' ) {
			$exeScript = CRON_ROOT_DIR . $dirname['basename'] ; 
		}
		$BACKUP = isset($_GET['3GCRON']) ? $_GET['3GCRON'] : NULL ;
		if( $BACKUP === NULL  )
		{
			unset($_GET['3GCRON']) ;			
		}

		

		if (  $instid !== FALSE )
			$_GET['3GCRON']['SHELL'] = $instid ;

		$_GET['3GCRON']['FILE'] = $exeScript ;
		$_GET['3GCRON']['PARAMS'] = array ();
		foreach ($params as $key => $value) {
			$_GET['3GCRON']['PARAMS'][$key] = $value;
		}

		// 
		$_GET['3GCRON']['CONFIRM'] = $confirm ;
		$_GET['3GCRON']['OUTPUT']  = $out;
		
		$ret =  self::__start_cron(1);

		unset($_GET['3GCRON']) ;
		if ( $BACKUP !== NULL )
			$_GET['3GCRON'] = $BACKUP;

		return $ret;
	}

 	/**
	*
	*/
	static function __start_cron( $is_external =0 )
	{
		global $argv;
		$resp = array ( 'status' => 0, 'msg' => 'Success', 'cronid' =>0, 'pid' => getmypid()  );
		if ( !is_writable(CRON_TMP_DIR) && ! $is_external  )
		{
			$resp['status'] = 5;
			$resp['msg'] = self::get_err_msg()->msgs[5];
			return $resp;
		}

		$pathinfo = pathinfo($_SERVER['PHP_SELF']);
		$shell_file = $pathinfo['basename'];
		if ( isset($_GET['3GCRON']['FILE']) )
			$shell_file = $_GET['3GCRON']['FILE'];
		else
			$shell_file = CRON_ROOT_DIR . $shell_file;

		$shell_file = escapeshellarg($shell_file);
		if ( isset($_GET['3GCRON']['SHELL'])  ) 
			$shell_file .= " -INSTID " . escapeshellarg($_GET['3GCRON']['SHELL']) ;


		$argvs =  array();
		if ( isset($_GET['3GCRON']['PARAMS']) )
		{
			foreach ($_GET['3GCRON']['PARAMS'] as $PKEY => $PVAL)
			{
				if ( in_array( $PKEY, array("INSTID", 'GPCSFS', 'QUERY' ) ) )
				 continue ;
				$argvs[$PKEY] = $PVAL;
			}
		}

		$confirm = isset($_GET['3GCRON']['CONFIRM'])  ? $_GET['3GCRON']['CONFIRM'] : "0";
		$cronid =  md5(uniqid()) ;
		$confirm = (int) $confirm ;
		$output = isset($_GET['3GCRON']['OUTPUT'])  ? $_GET['3GCRON']['OUTPUT'] : "json";


		$temp_GET =  $_GET['3GCRON'];
		// removed
		if ( isset($_GET['3GCRON']) )
			unset( $_GET['3GCRON'] ) ;


		// 
		$the_request = array ( 
						'_GET'  => ( isset($_GET) ? $_GET : array() ) ,
						'_POST' => ( isset($_POST) ? $_POST : array() ) ,
						'_COOKIE' => ( isset($_COOKIE) ? $_COOKIE : array() ) ,
						'_SESSION' => ( isset($_SESSION) ? $_SESSION : array() ) ,
						'_FILES' => ( isset($_FILES) ? $_FILES : array() ) ,	
						'_ARGV' => $argvs ,	
						'_CRONID'  => $cronid						
					);
		$_GET['3GCRON'] = $temp_GET ;// restore
		unset($temp_GET); 
		$the_request = serialize($the_request);
		
		$gpcsf_path = CRON_TMP_DIR . ".{$cronid}.0";
		file_put_contents($gpcsf_path , self::__gzcompress($the_request) );
		$shell_file .= " -GPCSF " . escapeshellarg($gpcsf_path)  ;
				
		$status = self::nohup_php ($shell_file);
		if ( $status ) {
			$resp['status'] = $status;
			$resp['msg'] = self::get_err_msg()->msgs[$status];
			return $resp;
		}
		$resp['status'] = 0;
		$resp['msg'] = 'Success';
		$resp['cronid'] = $cronid;
		if ($confirm) {
			$f_out = CRON_TMP_DIR . ".{$cronid}.1"  ;
			file_put_contents($f_out,""); // write the file
			$next = time() + $confirm;
			while ( file_exists($f_out)  && ($c=file_get_contents($f_out)) == '' ) {
				if ( time()>= $next) {
					$confirm = -1;
					$resp['status'] = 7;
					$resp['msg'] = self::get_err_msg()->msgs[7] ;
					$resp['cronid'] = '';
					$resp['pid'] = 0 ;
					$resp['instid'] = 0 ;
					$resp['time'] = 0 ;
					break;
				}
				usleep(400);
			}
			@unlink($f_out); // remove the file
			if ( file_exists($gpcsf_path))
				@unlink(CRON_TMP_DIR . ".{$cronid}.0");

			if ($confirm && $c) {
				list($status, $msg, $cronid, $pid, $instid, $time)  = explode("|", $c);
				$resp['status'] = $status;
				$resp['msg'] = $msg ;
				$resp['cronid'] =$cronid;
				$resp['pid'] = $pid ;
				$resp['instid'] = $instid ;
				$resp['time'] = $time ;
			}

		}		
		return ($resp);		
	}

	/*
	*
	*/
	static function process_query_request ($query){

		$resp = array ( 'status' => 15, 'msg' => self::get_err_msg()->msgs[$ret], 'cronid' => 0 , 'pid' => 0 , 'instid' => 0 , 'time' => 0 );
		
		$commands = array();
		if ( preg_match("/^([a-z]+)\[@(instid|cronid)(=|=>|<=|>|<|!=)(.*)\]/im", $query, $commands) ) {
			$command = $commands[1];
			$instid = $commands[2] == 'instid' ? $commands[2] : FALSE ;
			$cronid = $commands[2] == 'cronid' ? $commands[2] : FALSE ;
			$op = $commands[3] == '=' ? "$commands[3]=" : $commands[3]	;
			$val = $commands[4]  ;
			$pid_name =  CRON_PID_DIR . CRON_PID_FILENAME . "_" . $val ."_[0-9]*.pid";
			$pidfiles =  glob($pid_name);
			if ( $command  === 'kill') {
				$killed = false ;
				foreach ($pidfiles as  $pidfile) {
					$mpid = array ();
					$ptr   =  @fopen($pidfile, "r+");
					if ( $ptr && !flock($ptr, LOCK_EX | LOCK_NB ) && preg_match("~.*_([a-z0-9]+)_(\d+)\.pid$~ims", $pidfile, $mpid )) {
						fclose($ptr);
						$pidinfos = file_get_contents($pidfile);
						$pidinfos = explode("|",$pidinfos );
						$pid = (int) $pidinfos[0];
						$instid = (int) $pidinfos[1];
						$f =  eval("return $instid$op$val ? true : false;");
						if ( $f  ) {
							self::kill_pid( $pid);
							$cronid = $pidinfos[2];


							// clean if present
							if ( self::$_callee_tmp !== FALSE ) {
								$add_files[] = self::$_callee_tmp . ".{$cronid}.0";
								$add_files[] = self::$_callee_tmp . ".{$cronid}.1";
							}
							foreach ($add_files as  $add_file) {
								if (  file_exists($add_file) ) 
									@unlink(add_file);
							}

							
							@unlink($pidfile);	
							$resp['status'] = 0 ;
							$resp['msg'] = self::get_err_msg()->msgs['query'][0][0] ;
							$killed = true ;					
						}
					}
				}
				if ( !$killed ) {
					$resp['status'] = 14 ;
					$resp['msg'] = self::get_err_msg()->msgs[$ret];
				}

			}
			else if ( $command  === 'status' ) {
				foreach ($pidfiles as  $pidfile) {
				$mpid = array ();
				$ptr   =  @fopen($pidfile, "r+");
				if ( $ptr && flock($ptr, LOCK_EX | LOCK_NB ) && preg_match("~.*_([a-z0-9]+)_(\d+)\.pid$~ims", $pidfile, $mpid )) {
					fclose($ptr);
					$pidinfos = file_get_contents($pidfile);
					$pidinfos = explode("|",$pidinfos );
					$pid = (int) $mpid[1];
					$f =  eval("return $pid$op$val ? true : false;");
					if ( $f  ) {
						self::kill_pid( (int) $pid);
						$cronid = $pidinfos[2];

						// clean if present
						if ( self::$_callee_tmp !== FALSE ) {
							$add_files[] = self::$_callee_tmp . ".{$cronid}.0";
							$add_files[] = self::$_callee_tmp . ".{$cronid}.1";
						}
						foreach ($add_files as  $add_file) {
							if (  file_exists($add_file) ) 
								@unlink(add_file);
						}
						@unlink($pidfile);	
						$resp['status'] = 0 ;
						$resp['msg'] = self::get_err_msg()->msgs['query'][0][0] ;					
					}
				}
				}
			}

		}
		
		self::send_output ($resp);
				
	}

	/**
	*
	*/
	static function init ($cService)
	{	
		static $passed = FALSE ;
		if ( is_null($cService)) return $passed;
		global $argv;
		if ( !is_object($cService) or !is_subclass_of($cService, 'cronService'))
			throw new Exception("Objecddd not found.");

		// callable
		$callable = is_callable(array($cService, 'onValidate')) 
				&& is_callable(array($cService, 'onFail')) 
				&& is_callable(array($cService, 'onStart')) 
				&& is_callable(array($cService, 'onStop'))
				&& is_callable(array($cService, 'onReceive'));
		if ( !$callable)
			throw new Exception("Method does not exits");


		$TESTMODE = (!STDIN && isset($_GET['3GCRON']['TESTMODE']) ) ? (int) $_GET['3GCRON']['TESTMODE'] : 0 ;


		//
		if ( !STDIN ) {
			$argv = $argv == null ? array() : (array) $argv;
			if ( empty($argv)) 
				$argv[] = '';
			if ( isset($_GET['3GCRON']['PARAMS']) )
			{
				foreach ($_GET['3GCRON']['PARAMS'] as $PKEY => $PVAL)
				{
					if ( in_array( $PKEY, array("INSTID", 'GPCSFS', 'QUERY') ) )
			 			continue ;
					$argv[] ="-$PKEY";
					$argv[] = $PVAL;
				}
			}
			if ( isset($_GET['3GCRON']['SHELL']) )
			{
				$argv[] = '-INSTID';
				$argv[] = (int) $_GET['3GCRON']['SHELL'];		
			}

			if ( isset($_GET['3GCRON']['QUERY']) )
			{
				$argv[] = '-QUERY';
				$argv[] = $_GET['3GCRON']['QUERY'];		
			}

			// 
			if ( !isset($_GET['3GCRON']['QUERY'])) {
				if ( $TESTMODE === 0 ) {
					$ret =  self::__start_cron();
					self::send_output ($ret);
					exit ;
				}
			}
		} 


		// web and shell access code here
		$QUERY = self::get_options('QUERY') ;
		if ( $QUERY !== FALSE ) {
			self::process_query_request($QUERY);
			exit;		
		}



		//
		self::$cService = &$cService;

		if ( defined('CRON_CHANGE_DIR') && CRON_CHANGE_DIR === FALSE ){
			// change current exec script dir
			@chdir(CRON_ROOT_DIR);
		}
		
		// New cron ID for this cron service
		$cronid =  md5(uniqid());
		
		// 
		$gpcsf_path = self::get_options('GPCSF') ;
		if ( $gpcsf_path!==FALSE && file_exists($gpcsf_path)) 
		{

			self::suppress_err();
			if ( is_readable($gpcsf_path) ){
				$contents = @unserialize( @self::__gzuncompress( @file_get_contents($gpcsf_path)));
			}		

			$_SESSION =  array();
			if ( is_array($contents)){
				foreach ($contents as $key => $value) {
					if ( $key ==  '_GET' )
						$_GET = array_merge($_GET, $value);
					if ( $key ==  '_POST' )
						$_POST = array_merge($_POST, $value);
					if ( $key ==  '_COOKIE' )
						$_COOKIE = array_merge($_COOKIE, $value);
					if ( $key ==  '_SESSION' )
						$_SESSION = array_merge($_SESSION, $value);
					if ( $key ==  '_FILES' )
						$_FILES = array_merge($_FILES, $value);
					if ( $key == '_ARGV') {
						foreach ($value as $key => $value) {
							$argv[] = "-$key";
							$argv[] = "$value";
						}

					}
					if ( $key == '_CRONID') {
						$cronid = $value;
					}
				}			
			}
			
			// UNLINK 
			@unlink($gpcsf_path);

			// set caller temp path
			self::$_callee_tmp = dirname ($gpcsf_path) . DS ;

			//
			self::suppress_err();
		} else {
			$gpcsf_path = false ;
		}

		

		// store it  
		self::$_cronid = $cronid;

		
		$ret = 0 ;


		// validate instance
		$ret = ( ! self::check_instance () ? 1 : 0  ) ;
		

		// SET default log file
		self::$_logfile    = CRON_LOG;
		self::$_outputfile = CRON_OUTPUT_FILE ; 
		self::$_statusfile = CRON_STATUS_FILE ;
		self::$_stopfile = CRON_STOP_FILE ;
		if ( !$ret && INSTANCES !== FALSE ) {

			$p =  pathinfo(CRON_LOG);
			$lfilename = $p['filename'] . '_' . self::$_instid . "." . ( isset($p['extension']) ? $p['extension'] : 'log' ) ;
			self::$_logfile = $p['dirname'] . DS . $lfilename;


			$p =  pathinfo(CRON_OUTPUT_FILE);
			$lfilename = $p['filename'] . '_' . self::$_instid . "." . ( isset($p['extension']) ? $p['extension'] : 'out' ) ;
			self::$_outputfile = $p['dirname'] . DS . $lfilename;

			$p =  pathinfo(CRON_STATUS_FILE);
			$lfilename = $p['filename'] . '_' . self::$_instid . "." . ( isset($p['extension']) ? $p['extension'] : 'status' ) ;
			self::$_statusfile = $p['dirname'] . DS . $lfilename;


			$p =  pathinfo(CRON_STOP_FILE);
			$lfilename = $p['filename'] . '_' . self::$_instid . "." . ( isset($p['extension']) ? $p['extension'] : 'stop' ) ;
			self::$_stopfile = $p['dirname'] . DS . $lfilename;
			unset($p);
		}
		#var_dump (self::$_outputfile ,self::$_statusfile, self::$_stopfile ); die;


		$running_pid = 0 ;
		// is process running  
		( !$ret ?  ( ( $running_pid = self::instance_running(self::$_instid)) !== FALSE ? ($ret = 2) : '' ) : '' );
		
		// load avg
		( !$ret ?  ( is_int(LOAD_AVG_MAX) && ( $avg = self::	get_load_avg()) >= LOAD_AVG_MAX  ? ( $ret = 3) : '' ) : '' );
		

		// 
		if ( ! $ret  ) {
			$inst_cronid = self::$cService->getInstID();
			if ( INSTANCES === FALSE  )
				$inst_cronid = self::$_cronid;
			$pid = getmypid();
    		$pid_file = CRON_PID_DIR . CRON_PID_FILENAME  . "_" . $inst_cronid . "_{$pid}.pid";
    		if (!is_writable(dirname($pid_file))) 
				$ret = 10;
			else {
				self::$_starton = time();
				$contents = "$pid|" .  self::$cService->getInstID() . "|" . $cronid . "|" . self::$_starton;
				$return = @file_put_contents($pid_file, "$contents" , LOCK_EX);
				if ( !$return ) {
					$ret = 11;
				} else {
					self::$_pidsubkey = @fopen($pid_file,"r+") ;
					if ( ! self::$_pidsubkey  ){
						self::$_pidsubkey  = null;
						$ret = 12;
					} else {
						if (!flock(self::$_pidsubkey , LOCK_SH | LOCK_NB) ) 
						 $ret = 13;
						else {						 
						 cronRunner::$_pidfile = $pid_file;
						 cronRunner::$_pid = getmypid();
						}
					}
				}
			}	
		}


		$resp = array ( 'status' => $ret, 'msg' => self::get_err_msg()->msgs[$ret], 'cronid' => 0 , 'pid' => 0 , 'instid' => 0 , 'time' => 0 );
		if ( !$ret ) 
		{
			// validate
			$msg = self::$cService->onValidate();
			$ret = ( (!is_string($msg) or $msg == '') ? 0 : 6 ); 
			if ( $ret ) {
				self::get_err_msg()->msgs[$ret] = $msg;
			}
		}
		
		$resp['status'] = $ret;
		$resp['msg'] = self::get_err_msg()->msgs[$ret];
		// error ?
		if ( $ret ) 
		{
			

			if ( $ret == 2 ) {
				$resp['cronid'] = $running_pid[2];
				$resp['pid'] = $running_pid[0];
				$resp['instid'] = $running_pid[1];
				$resp['time'] = isset($running_pid[3])?$running_pid[3]:0;					
			} else {

			}
			$out_data = self::send_output ($resp, true);			
			if ( $gpcsf_path !== FALSE ) {
				$f_out = self::$_callee_tmp . ".{$cronid}.1" ;
				if (  file_exists($f_out) )
					@file_put_contents($f_out, $out_data);
				exit ;
			}
			// onFailed
			self::$cService->onFail($ret, self::get_err_msg()->msgs[$ret] );
			echo $out_data;		
			exit ;

		}
		$passed = TRUE;
	}

	/**
	*
	*/
	static function run() {

		$TESTMODE = (!STDIN && isset($_GET['3GCRON']['TESTMODE']) ) ? (int) $_GET['3GCRON']['TESTMODE'] : false ;

		if ( !self::init(NULL))
			throw new Exception("initialisation Failed.");

	

		// 
		while ( ob_get_length() > 0 ) {
			self::$_garbage_output .= ob_get_contents();
			ob_end_clean() ;
		}


		@ob_start();

		//
		@set_time_limit(0);

		if ( ! $TESTMODE )
		  @ignore_user_abort(true);


		//
    	register_shutdown_function(array('cronRunner', '__instance_shutdown') );
	
    	//
    	$gpcsf_path = self::get_options('GPCSF') ;
    	if ( $gpcsf_path !== FALSE && self::$_callee_tmp !== FALSE ) {
    		$resp = array ( 'status' => 0, 'msg' => self::get_err_msg()->msgs[0], 'cronid' =>self::$_cronid, 'pid' => self::$_pid, 'instid' => self::$_instid , 'time' => self::$_starton   );
			$out_data = self::send_output ($resp, true);
			$f_out = self::$_callee_tmp . "." . self::$_cronid . ".1" ;
			if (  file_exists($f_out) )
				@file_put_contents($f_out, $out_data);
    	}


    	global $argv;
    	$temp_argv = $argv;
    	$params = array();
    	if ( is_array($temp_argv) && ($N= count($temp_argv)) ) {
    		array_shift($temp_argv);
    		for( $i=0; $i<=$N-1; $i = $i + 2 ){
    			if ( isset($temp_argv[$i]) &&  substr($temp_argv[$i],0,1) == '-' && !in_array($temp_argv[$i] , array('-INSTID','-GPCSF','QUERY'))  ) {
    				if ( isset($temp_argv[$i+1]) ) {
    					$params[ltrim($temp_argv[$i],'-')] = $temp_argv[$i+1];
    				}
    			}
    		}
    	}
       	unset($temp_argv);   

       	// 
       	self::$_PARAMS  = $params;
       	$params  = array_values($params);


       	// clean 
       	$cmd_file =  CRON_PID_DIR .  self::$cService->getPid() . '.cmd';
       	if ( file_exists($cmd_file))
       		@unlink ($cmd_file);
		
    	// on Start
    	$ret = call_user_func_array( array(self::$cService,  'onStart') , $params);
		$staus = $ret ;
		$msg = '' ;
		if ( is_int($ret) ) 
			$msg = $ret ==0 ? 'Success' : 'Failed';
		else if (is_bool($ret)) {
			$msg = $ret == true ? 'Success' : 'Failed';
		}
		else if (is_null($ret) or !is_array($ret) or count($ret) == 0 ) {

			$staus = 2 ; // return nothing or not expected
			$msg = 'Unknown Error';
		} else if ( count($ret) == 1 )
			$staus = current($ret) ;
		else {
			$staus = current($ret) ;
			$msg = current($ret) ;
		}
		self::clear_exit ( $staus,  $msg);
		exit ;
	}


	/**
	*
	*/
	static function __instance_shutdown () {

		// kill the pid also
		self::suppress_err ();
		if( is_resource( self::$_pidsubkey )   ) {
			@fclose(self::$_pidsubkey, LOCK_UN);
			@fclose(self::$_pidsubkey);
			@unlink(self::$_pidfile) ;
		}
		self::suppress_err ();


		// get output 
		while ( ob_get_length() > 0 ) {
			self::$_garbage_output .= ob_get_contents();
			ob_end_clean() ;
		}

		$TESTMODE = (!STDIN && isset($_GET['3GCRON']['TESTMODE']) ) ? (int) $_GET['3GCRON']['TESTMODE'] : false ;
		if ($TESTMODE )
			echo self::$_garbage_output;


		cronRunner::write_to_stop( ( self::$_clearexit[0] === FALSE ? 1: (self::$_clearexit[0]===1 ? 0:self::$_clearexit[0] )) . "|" . self::$_clearexit[1]);

		//$stop_params =  array () ;
		self::$cService->onStop( self::$_clearexit[0] , self::$_clearexit[1] );

		
		$cmd_file =  CRON_PID_DIR .  self::$cService->getPid() . '.cmd';
       	if ( file_exists($cmd_file))
       		@unlink ($cmd_file);
		return ;	
	}
	
	/**
	*
	*/
	static function nohup_php( $command ) {
		static $WshShell;
		$PHP_OS  = strtolower(PHP_OS) ;	
		$phpini = "";
		if ( defined('CRON_CUSTOM_PHPINI')  ) {
			$phpini  = " -n -c " . escapeshellarg(CRON_CUSTOM_PHPINI);			
		}

		$bgexec  = false;
		if ($PHP_OS == 'linux' ) {
			$command = str_replace('%%COMMAND%%',$command , self::BG_RUN_COMMAND_LINUX );
			$command = str_replace('%%PHP_EXE_PATH%%', defined('CRON_PHP_PATH_LINUX') ? CRON_PHP_PATH_LINUX : CRON_PHP_PATH , $command);
			$bgexec  = 'nohup';
		} else if ($PHP_OS == 'winnt' ) {
			$command = str_replace('%%COMMAND%%',$command , self::BG_RUN_COMMAND_WIN );
			$command = str_replace('%%PHP_EXE_PATH%%', defined('CRON_PHP_PATH_WIN') ? CRON_PHP_PATH_WIN : CRON_PHP_PATH , $command);
			$bgexec  = 'COM';			
		} else {
			return 16;
		}
		$command = str_replace("%%INI_FILE%%", $phpini, $command);
		
		if ( defined('BG_RUN_COMMAND') )
			$bgexec  =  BG_RUN_COMMAND ;
		    
		if ( $bgexec === 'COM' && !class_exists('COM') )
			$bgexec  = 'start';

		if ( $bgexec === 'start'){
			$bgexec = 'start /B';
		}
		
		// 
		if ( $bgexec === 'COM' ) {
			try{
				$WshShell = new COM("WScript.Shell");
				$oExec = $WshShell->Run($command, 0, false);
				return $oExec == 0 ? true : false;
			}catch(Exception $e) {
				$bgexec = 'start /B';
			}				  
		}


		if ( function_exists('popen') &&  function_exists('pclose') ) 
		{
			// Remove trim "& echo $!" becuse error in linux
			$c = "{$bgexec} {$command}";
			$pos = strripos($c, "& echo $!");
			$c   = $pos !==FALSE ? (substr($c, 0, $pos)) : $c;
			$pHandler = @popen($c, "r");
			if ( $pHandler ) {
				pclose($pHandler);
				return 0;
			}
		}
	

		// Generic shell_exec
		if ( function_exists('shell_exec') && is_callable('shell_exec')) {
			$ret = shell_exec("{$bgexec} {$command}");	
			return 0;
		}

		// Generic exec
		if ( function_exists('exec') && is_callable('exec')) {
			$ret = exec("{$bgexec} {$command}");	
			return 0;
		}

		return 17; 
	
	}

	/**
	*
	*/
	static function kill_pid($pid) {
		static $WshShell;
		if ( !is_int($pid) )
			return 1;		
		$PHP_OS  = strtolower(PHP_OS) ;	
		if ($PHP_OS == 'linux' ) {
			$command = str_replace('[[PID]]',$pid ,  self::KILL_COMMAND_LINUX);
			$ret = shell_exec($command );	
			return $ret;	
		}	
		if ($PHP_OS == 'winnt' ) {
			$command = str_replace('[[PID]]',$pid  ,  self::KILL_COMMAND_WIN);
			if ( is_null($WshShell) ) {
				try {
					$WshShell = new COM("WScript.Shell");
					$oExec = @$WshShell->Run($command, 0,false);
					return $oExec == 0 ? true : false;  
				}
				catch(Exception $e) { 			
					if ( function_exists('popen') &&  function_exists('pclose') ) {
						$pHandler = @popen("$command ", "r");
						if ( $pHandler ) {
							pclose($pHandler);
							return true;
						}
						return 1;
					}				
				}			
			}
		}	
	}


	
	/**
	*
	*/
	static function write_to_log($logOutput= '', $logType ='i') {
		static $first = false ;
		$logOutput = "[".date('Y-m-d H:i:s') ."][$logType] : " . str_replace( array("\r","\n")," ", $logOutput);
		$logOutput = ( $first === true ? chr(13) : '') . $logOutput;
		$first = true ;
		@file_put_contents( self::$_logfile , $logOutput , FILE_APPEND | LOCK_EX);	
	}

	/**
	*
	*/
	static function write_to_status($status) {
		$out  =  time() . "|" . str_replace(array("\r","\n"),"",$status);
		file_put_contents( cronRunner::$_statusfile , $out, LOCK_EX);				
	}

	/**
	*
	*/
	static function write_to_out($out) {
		static $first = false ;
		$out = print_r ($out, true);
		$out = ( $first === true ? chr(13) : '') . $out;
		$first = true ;
		file_put_contents( cronRunner::$_outputfile , $out ,FILE_APPEND | LOCK_EX);			
	}

	/**
	*
	*/
	static function write_to_stop($out) {
		$out  =  time() . "|" . str_replace(array("\r","\n"),"",$out);
		$out = self::$_pid . "|" . self::$_instid . "|" . self::$_cronid . "|" . $out;
		file_put_contents( cronRunner::$_stopfile , $out , LOCK_EX);			
	}
	/*
	*
	*/
	static function ping_pong ($argv) {
		if ( ! isset($argv['file']) )
			return ;
		//
		$pongfile = CRON_TMP_DIR . $argv['file'];
		if ( strlen($argv['file']) > 1 && substr($argv['file'], 0,2) == '//' )
			$pongfile = substr($argv['file'], 2);
		
		$pong =  "pong" ;
		if ( file_exists(cronRunner::$_statusfile)) {
			$status = file_get_contents(cronRunner::$_statusfile);
			if ( $status == '')
				$pong .="||";
			else
				$pong .="|" . $status ;	
		} else
			$pong .="||";

		$pidfile = cronRunner::$_pidfile;
		if ( is_readable($pidfile) ) {
			$pong .="|" .  file_get_contents($pidfile);
		}
		echo $pongfile; die;
		file_put_contents( $pongfile , $pong ,LOCK_EX);	
	}

	/**
	*
	*/
	static function require_class($libs_file, $ret = false) 
	{
  		$libs_file = (array) $libs_file;
  		$libs_file[0] == "//"  && $is_abs = true ? array_shift($libs_file) : $libs_file;
		$libs_file = ( !$is_abs ? "./classes/" : "" ) . join("/", $libs_file );
		if (file_exists($libs_file) && is_readable($libs_file)) {
				if ($ret)
		    	return require $libs_file;
		     require_once($libs_file);
		     return true;
		}
		return false;
	}
	/*
	*
	*/
	static function __gzuncompress($str) {
		return function_exists('gzuncompress') ? gzuncompress($str) : $str;
	}

	/*
	*
	*/
	static function __gzcompress($str) {
		return function_exists('gzcompress') ? gzcompress($str) : $str;
	}
	
	
}