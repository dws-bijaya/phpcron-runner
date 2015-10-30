<?php
	session_start();
	//
	require_once("./jApi_config.php");
	require_once("./jApi_libs/src/jApi_verify.class.php");
	$vf =  (isset($_SESSION['AUTH']['uid']) && !empty($_SESSION['AUTH']['uid']) ? 1 : 0);
	$jApiAuthRefreshToken = jApi_verify::verify($vf, session_id(), 12 , 60 * 60 * 60 * 89, array('uname' => 'User', 'msg' => 'You are most welcome') ) ;
    $_SESSION['AUTH']['jApi_refresh_token'][jApi_auth_token] = $jApiAuthRefreshToken;
	jApi_verify::redirect();
?>