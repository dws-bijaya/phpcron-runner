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
	}
	public function onReceive($command, $argv = array(), $params = array() ) {
	}		
}
?>