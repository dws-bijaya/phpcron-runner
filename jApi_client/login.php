<?php
/*
*  Example
*/
session_start();
require_once("./jApi_config.php");

if (isset($_GET['logout'])) {
	require_once("./jApi_libs/src/jApi_verify.class.php");
	
	// logout jApi session
	if ( isset($_SESSION['AUTH']['jApi_refresh_token'][jApi_auth_token]) ){
		jApi_verify::logout(jApi_session_callback, jApi_auth_token ,$_SESSION['AUTH']['jApi_refresh_token'][jApi_auth_token]);
	}

	session_destroy();
	header('Location: index.php');
	exit();
}

if ( isset($_SESSION['AUTH']['uid']) && !empty($_SESSION['AUTH']['uid']) )
	header('Location: index.php');
If ( isset($_POST['uname']) && isset($_POST['upass'])  && ($_POST['uname'] == 'user' && $_POST['upass'] =='password' ) ) {
	$_SESSION['AUTH']['uname'] = 'user';
	$_SESSION['AUTH']['uid'] = '1';
	header('Location: index.php');	
}
?>
<h2> Login</h2>
<hr />
<form method="post" >
User Name : <input type="text" name="uname" value="" />
Password :  <input type="password" name="upass" value="" />
<input type='submit' name="btnLogin" value='login' />
<br /><i> user : user, password: password</i>
</form>