<?php
//
require_once( CRON_CLASS_DIR . 'cron_runner_class.php' );
require_once( CRON_CLASS_DIR . 'cron_service_class.php' );
class exampleCronJob3CronService extends cronService implements iCronRunner
{
	public function onValidate(){
		return "Something went wrong. Please retry after some time";
	}
	public  function onFail($status,$msg) {
		$this->writeToLog("Failed status: $status , $msg: $msg");
	}
	public  function onStart() {
		/*
				You'll never reach to this body
		*/
		while (1) {
			# code...
		}
	}
	public function onStop($status, $msg ) {		
	}
	public function onReceive($command, $argv = array(), $params = array() ) {
	}		
}
?>