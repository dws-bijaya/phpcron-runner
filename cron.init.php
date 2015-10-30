<?php
	define('DS', DIRECTORY_SEPARATOR);
	define("CRON_ROOT_DIR", dirname(__FILE__) . DS ); 
	define("CRON_TMP_DIR", CRON_ROOT_DIR . 'tmp' . DS );
	define("CRON_CLASS_DIR", CRON_ROOT_DIR . DS.  'systems' . DS);
	define("CRON_CHANGE_DIR", @chdir(CRON_ROOT_DIR));
	define("CRON_CUSTOM_PHPINI", CRON_ROOT_DIR . ".configs");
	

	// php path
	define("CRON_PHP_PATH_WIN", 'D:/xampp/php/php.exe');
	define("CRON_PHP_PATH_LINUX", '/usr/bin/nohup /usr/local/bin/php');
	
	//
	define('CRON_PID_DIR', CRON_TMP_DIR );