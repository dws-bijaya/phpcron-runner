<?php
//
require_once( CRON_CLASS_DIR . 'cron_runner_class.php' );
require_once( CRON_CLASS_DIR . 'cron_service_class.php' );
class exampleCronJob1CronService extends cronService implements iCronRunner
{
	public function onValidate(){
		return "";
	}
	public  function onFail($status,$msg) {
	}
	public  function onStart() {
		$this->writeToLog("Starting ....");
		$start  = time();

		// Write to logs all global variables
		$gbls =  array( '_FILES' => isset($_FILES) ? $_FILES : array(),
						'_GET'   => isset($_GET) ? $_GET : array(),
						'_POST'   => isset($_POST) ? $_POST : array(),
						'_SESSION'   => isset($_SESSION) ? $_SESSION : array(),
						'_COOKIE'   => isset($_COOKIE) ? $_COOKIE : array()

				);
		$this->writeToLog(print_r($gbls, true));
		$i = 1 ;
		do
		{
			$this->writeToStatus("Process $i .");
			// Do somestuff
			sleep(1);
		}while( time() - $start < 60 );

		$this->writeToLog("Process completed.");
		return ture; // Mean sucessfully cron job ended 
	}
	public function onStop($status, $msg ) {
		$err = $this->getOutputBuffer();
		$this->writeToLog($err);			
	}
	public function onReceive($command, $argv = array(), $params = array() ) {
	}		
}
?>