<?php
//
require_once( CRON_CLASS_DIR . 'cron_runner_class.php' );
require_once( CRON_CLASS_DIR . 'cron_service_class.php' );
class exampleCronJob4CronService extends cronService implements iCronRunner
{
	public function onValidate(){
		
		// Set custom LogFile
		# $this->setLogFile();

		// Set custom OutputFile
		# $this->setOutputFile();

		// Set custom StatusFile
		# $this->setStatusFile();

		// Set custom StopFile
		# $this->setStopFile();
		return "";
	}
	public  function onFail($status,$msg) {
		
	}
	public  function onStart() {
		
		$DumpVar  = array();
		// get command line arguments values 
		$DumpVar['params'] = $this->getParams();
		
		// get CronID
		$DumpVar['CronID'] = $this->getCronID();
		
		// get InstID
		$DumpVar['InstID'] = $this->getInstID();
		
		// get PidFile
		$DumpVar['PidFile'] = $this->getPidFile();

		// get getPid
		$DumpVar['Pid'] = $this->getPid();

		// get Name
		$DumpVar['Name'] = $this->getName();
	
		// get LogFile
		$DumpVar['LogFile'] = $this->getLogFile();

		// get OutputFile
		$DumpVar['OutputFile'] = $this->getOutputFile();

		// get StatusFile
		$DumpVar['StatusFile'] = $this->getStatusFile();

		// get StopFile
		$DumpVar['StopFile'] = $this->getStopFile();
		
		// Write to log file
		$this->writeToLog("Starting ....")		;

		//  Write to output file for dugging data
		$this->writeToOut(print_r($DumpVar, true));

		$t = 0;
		while ($t++ <= 10) {
			sleep(1);
			$this->writeToStatus("Completed $t of 10.");
			// read external command from tmp/<pid>.cmd
			// each commmand separated by newline
			// example :  <command> <json_encoded_string>
			// passed parameters too 
			$this->readCmd(1, 2);

			// exec php shell command
			$out=$this->execCmd();
			var_dump($out);
		}
		$this->writeToLog("Completed ....")		;
		var_dump($DumpVar) ;
		return true; // Succesfully finished

	}
	public function onStop($status, $msg ) {
		$out = $this->getOutputBuffer();
		print($out);
		$this->writeToStop("Stoped :  status=$status, msg=$msg");
	}
	public function onReceive($command, $argv = array(), $params = array() ) {
	    $this->writeToLog("Received new command: $command");
	    if ($command  == 'ping' ) {
	       	$this->pingPong(); // You can override 
	       	return ;
	    }
	    if ($command  == 'kill' ) {
	    	$this->killMe();
	    }

	    // write your own command managements


	}		
}
?>