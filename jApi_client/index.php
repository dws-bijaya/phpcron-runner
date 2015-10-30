<?php
/*
*  Example
*/
require_once('jApi_config.php');
session_start();
$refrshTokens =  array();
if ( !isset($_SESSION['AUTH']['uname']) or empty($_SESSION['AUTH']['uname']) ){
	header('Location: login.php');	
	exit ;
} else {
	// store the refresh token
	$refrshTokens[jApi_auth_token] = ( isset( $_SESSION['AUTH']['jApi_refresh_token'][jApi_auth_token]) ? $_SESSION['AUTH']['jApi_refresh_token'][jApi_auth_token] : '' ); 
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Example : jApi 1.0 Client</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script  src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>


<script type="text/javascript" src="jApi_libs/js/jApi.js"></script>
<script type="text/javascript">
  /* <![CDATA[ */
  /* Global setting for jApi Server Details*/
  jApi.auth_token = '<?php echo jApi_auth_token;?>';
  jApi.uri = '<?php echo jApi_uri;?>'; 
  // save refesh token for next communication to server
  jApi.__auth_refresh_tokens[jApi.auth_token] = '<?php echo $refrshTokens[jApi_auth_token] ; ?>';
  //jApi.__auth_refresh_tokens['jApi-13-9858'] = '<?php // echo $refrshTokens['jApi-13-9858'] ; ?>';
   /* <![CDATA[ */
</script>
<script type="text/javascript" src="js/functions.js"></script>
</head>
<body>

<h2>Welcome  <?php echo $_SESSION['AUTH']['uname']; ?> ! <a href="login.php?logout"> Logout </a> </h2>  





<h1> Example 1:</h1>
<p> show alert message
<hr />
<input type="button" value="Alert" onclick="javascript:jApi.doApiCall('alert');" />
<hr />


<h1> Example 2:</h1>
<p> Pass parameter to api server
<hr />
You have Passed :<span id='showParam'></span>
<input type="button" value="Show Param" onclick="javascript:jApi.doApiCall('showParam',{var1:'value1',var2:'value2'});" />
<hr />



<h1> Example 3:</h1>
<p> Dedicated request
<hr />
Server Time is :<span id='st'></span>
<input type="button" value="Get Server Time" onclick="javascript:jApi.doApiCall('getServerTime',{}, 'st_1');" />
<hr />


<h1> Example 4:</h1>
<p> Implement event onComplete, onError, onCall [1]
<hr />
<span id='implement'>
Will be filled

</span>
<input type="button" value="Add No" onclick="javascript:jApi.doApiCall(example4,{var1:45});" />
<hr />





<h1> Example 5:</h1>
<p> Implement event onComplete, onError, onCall [2] 
<hr />
<span id='implement'>
Will be filled

</span>
<input type="button" value="Sum Two No" onclick="javascript:jApi.doApiCall('sum',{var1:45});" />
<hr />



<h1> Example 6:</h1>
<p> Implement event onError fire
<hr />
<span id='implement'>

</span>
<input type="button" value="Error" onclick="javascript:jApi.doApiCall('error',{var1:45});" />
<hr />




<h1> Example 7:</h1>
<p> Developer debugger output
<hr />
<span id='implement'>

</span>
<input type="button" value="Debug" onclick="javascript:jApi.doApiCall('debug',{var1:45});" />
<hr />



<h1> Example 8:</h1>
<p> Execute javascript code directly
<hr />
<span id='execJs'>
	Execute javascript code directly
</span>
<input type="button" value="Exec JS" onclick="javascript:jApi.doApiCall('exec_js');" />
<hr />


<h1> Example 9:</h1>
<p> Submit a Form
<hr />
<span id='sbmtForm'>
	
</span>
<form method="post" id="oForm" >
Enter Text Value : <input type="text" name="field1" value="" />
Choose <input type="radio" name="field2" value="1" /> Or <input type="radio" name="field2" value="2" /> 
Select <select name="field3" >
		<option value="1" > 1 </option>
		<option value="2" > 2</option>
		<option value="3" > 3 </option>
	   </select>
Check Box <input type="checkbox" name="field4" value="1" /> and <input type="checkbox" name="field4" value="2" /> 
</form>
<input type="button" value="Exec JS" onclick="javascript:jApi.doApiCall('submit_form',{frm_name:'#oForm'});" />
<hr />





<div id="jApiDebuger" style="display: block;"></div>
</body>
</html>
