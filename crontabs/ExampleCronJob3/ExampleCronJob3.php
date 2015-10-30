<?php
	#####################<SETUP>###############################
	# 
	define('DS', DIRECTORY_SEPARATOR);
	define("CRON_ROOT_DIR", dirname(__FILE__) . DS  ); 
	define("CRON_TMP_DIR", CRON_ROOT_DIR . 'tmp' . DS );
	define("CRON_BIN_DIR", realpath(CRON_ROOT_DIR  . '..' . DS . '..' . DS . 'bin') . DS);
	define("CRON_CLASS_DIR", realpath(CRON_ROOT_DIR . '../../systems') . DS );
	define("CRON_CHANGE_DIR", @chdir(CRON_ROOT_DIR));
	define("CRON_CUSTOM_PHPINI", realpath(CRON_ROOT_DIR . "../.configs"));

	# Linux: /usr/local/bin/php, Window: D:/xampp/php/php.exe
	define("CRON_PHP_PATH", 'D:/xampp/php/php.exe');
	# Linux : /usr/bin/nohup, Window: CRON_BIN_DIR . 'nohup.exe' or COM or start or any custom exe 
	define("BG_RUN_COMMAND", CRON_BIN_DIR . 'nohup.exe' );

	# Set name of the script
	define('CRON_SERVICE_NAME', 'example-cron-job3' ); 
	# Set log file name
	define('CRON_LOG', CRON_TMP_DIR . 'example-cron-job3.log');
	# Set output file name
	define('CRON_OUTPUT_FILE', CRON_TMP_DIR . 'example-cron-job3.out');
	# Set status file name
	define('CRON_STATUS_FILE', CRON_TMP_DIR . 'example-cron-job3.status');
	# Set stop file name
	define('CRON_STOP_FILE', CRON_TMP_DIR . 'example-cron-job3.stop');
	# Set pid file name
	define('CRON_PID_FILENAME', 'example-cron-job3');
	# Set pid dir path
	define('CRON_PID_DIR', CRON_TMP_DIR );
	# Set 1 for developing mode on , 0 for production mode on
	define('DEVELOPING_MODE', 0);
	# To run only one instance of this script set "1001" (any unique integer no);
	# To run only two instances of this script set "1001,1002,..." (any two unique integer no);
	# To run with no limits set as false
	define('INSTANCES',  "1001"); 
	# false for disable, any value between 1- 100
	define('LOAD_AVG_MAX',  false); 
	#####################</SETUP>###############################

	require_once( CRON_ROOT_DIR .  ".classes" . DS . 'example-cron-job3.class.php');

	// init
	cronRunner::init( new exampleCronJob3CronService() ) ;
	
	// run it
	cronRunner::run();