<?php
class default_jApi_class extends jApi_class
{

	/* */
	static function alert($mid, $params) {
		$uname = $params['uname'];
		jApi::$err_debug = 1 ;
		//jApi::$jApiParams  = array("p1","p2"=>"pval2");
		echo "eeeee";

		jApi::$jApiExec="
				alert('Hi $uname !, your id is $mid' );
			";
		return array("Hello", "World");
	}
	/* */
	static function showParam () {
		jApi::$jApiExec="
				$('#showParam').html(" . json_encode( '<pre>' . print_r($_GET, true) . '</pre>' ) . ");
			";
		return 1;
	}
	/* */
	static function getServerTime () {
		jApi::$jApiExec="
				$('#st').html(" . json_encode(date('Y-m-d H:i:s'))  . ");
			";
		return 1;
	}
	/* */
	static function implement_event () {
		return array( (int) $_GET['var1'], (int) $_GET['var2'], $_GET['var1']+ $_GET['var2']);
	}

	/* */
	static function sum () {
		return array( (int) $_GET['var1'], (int) $_GET['var2'], $_GET['var1']+ $_GET['var2']);
	}

	/* */
	static function error () {
		jApi::$err_no = 1; // 0 no error other than 0 treats as error
		jApi::$err_msg = 'not ok' ;
		return false;
	}

	/* */
	static function debug () {

		echo "Something .... output data";
	}
	/* */
	static function exec_js () {
		jApi::$jApiExec="
				\$('#execJs').hide().fadeIn('show', function(){ \$(this).fadeOut('slow', function(){\$(this).fadeIn('slow')})});
			";
	}

	/* */
	static function submit_form () {

		$form = json_encode(print_r($_GET, TRUE));
		jApi::$jApiExec="
				\$('#sbmtForm').html($form);
			";
			return true ;
	}


	
}
?>
