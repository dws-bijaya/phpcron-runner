<?php
/*
  ######################################################################
  script file : cron service
  version     : 1.0
  description : cron service class
  ######################################################################
 */
interface iCronRunner {

	/* Helper Function */
	public function killMe($status = 0 , $msg = 'Success'); 
	public function execCmd($cmd);
	public function clearExit($status = true , $msg = 'success') ;
	public function readCmd () ;
	public function pingPong ($argv) ;


	/* Write Log Function */
	public function writeToLog($logOutput= '', $logType ='i');
	public function writeToStatus($status) ;
	public function writeToOut($out) ;
	public function writeToStop($out) ;

	
}

abstract class cronService implements iCronRunner {

	/* */
	public function clearExit($status = true , $msg = 'success') {
		return cronRunner::clear_exit( $status, $msg )  ;						
	}

	/*   */		
	public function execCmd($cmd) {
		static $WshShell;
		$PHP_OS  = strtolower(PHP_OS) ;	
		$ret = shell_exec($cmd);
		return $ret;	
	}
	public function killMe($status = 0 , $msg = 'Success') {
		$this->clearExit ($status, $msg);
		exit($status);
	}
	public function readCmd () {
		// 
		cronRunner::write_to_status("Listening for new command.");
		
		$params =func_get_args();
		$ret = call_user_func_array( array('cronRunner', 'read_command'), $params );

		cronRunner::write_to_status("Listening for new command finshed.");	
		return $ret;	
	}
	/*   */

	/*   */
	function pingPong ($argv) {
		cronRunner::ping_pong($argv);		
	}

	function writeToLog($logOutput= '', $logType ='i') {
		cronRunner::write_to_log ( $logOutput, $logType);		
	}	
	function writeToStatus($status) {
		cronRunner::write_to_status ($status );		
	}
	function writeToOut($out) {
		cronRunner::write_to_out($out);	
	}
	function writeToStop($out) {
		cronRunner::write_to_stop($out);	
	}
	/* */


	public final function getParams ($key = NULL ) {
		return $key == null ? cronRunner::$_PARAMS : ( isset(cronRunner::$_PARAMS[$key])? cronRunner::$_PARAMS[$key] : NULL ) ;
	}
	public final function getCronID () {
		return cronRunner::$_cronid ;
	}
	public final function getInstID () {
		return cronRunner::$_instid ;
	}
	public final function getPidFile () {
		return cronRunner::$_pidfile ;
	}
	public final function getPid () {
		return cronRunner::$_pid ;						
	}

	public final function getOutputBuffer() {
		return cronRunner::$_garbage_output;
	}
	
	public final function getName () {
		return CRON_SERVICE_NAME ;						
	}

	/*   */
	public final function getLogFile () {
		return cronRunner::$_logfile ;						
	}
	public final function getOutputFile () {
		return cronRunner::$_outputfile ;						
	}
	public final function getStatusFile () {
		return cronRunner::$_statusfile ;						
	}
	public final function getStopFile () {
		return cronRunner::$_stopfile ;						
	}
	/*   */



	/*   */
	public final function setLogFile($newLogFile) {
		return cronRunner::$_logfile = $newLogFile;						
	}
	public final function setOutputFile($newOutputFile) {
		return cronRunner::$_outputfile = $newOutputFile;						
	}
	public final function setStatusFile($newStatusFile) {
		return cronRunner::$_statusfile = $newStatusFile;						
	}
	public final function setStopFile($newStopFile) {
		return cronRunner::$_stopfile = $newStopFile;						
	}
	/*   */

	/* implement function */
	public abstract function onValidate();
	public abstract function onFail($status,$msg);
	public abstract function onStart();
	public abstract function onStop($status, $msg);
	public abstract function onReceive($command, $argv = array(), $params = array ());	
} 
?>