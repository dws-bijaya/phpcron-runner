<?php 
if ( isset($_POST) && !empty($_POST	)) {
	
	define('DS', DIRECTORY_SEPARATOR);
	define("CRON_ROOT_DIR", dirname(__FILE__) . DS  ); 
	define("CRON_BIN_DIR", realpath(CRON_ROOT_DIR  . '..' . DS . '..' . DS . 'bin') . DS);
	define("CRON_TMP_DIR", CRON_ROOT_DIR . 'tmp' . DS );
	define("CRON_CLASS_DIR", realpath(CRON_ROOT_DIR . '../../systems') . DS );
	define("CRON_CHANGE_DIR", @chdir(CRON_ROOT_DIR));
	define("CRON_CUSTOM_PHPINI", CRON_ROOT_DIR . "../ExampleCronJob1/.configs"  );

	# Linux: /usr/local/bin/php, Window: D:/xampp/php/php.exe
	define("CRON_PHP_PATH", 'D:/xampp/php/php.exe');
	// Linux : /usr/bin/nohup, Window: CRON_BIN_DIR . 'nohup.exe' or COM or start or any custom exe 
	define("BG_RUN_COMMAND", 'start');

	require_once( CRON_CLASS_DIR . 'cron_runner_class.php' );
	require_once( CRON_CLASS_DIR . 'cron_service_class.php' );

	
	// start external crons
	$params = array('var1' => 'val1', 'var2' => 'val2' , 'var3' => 'val3'  );
	$instid = 1001;
	$confirm = 10 ;
	$script_file =  realpath(CRON_ROOT_DIR . '..' . DS . 'ExampleCronJob1/ExampleCronJob1.php');
	$ret = cronRunner::start( $script_file, $instid , $params, $confirm);
	#var_dump($ret); die;	
} ?>
<html>
<head></head>
<body>
<?php if(isset($ret)):?>
	<center>
		<table border="1" with="100%" align="center">
			<tbody>
				<tr>

					<td> <b>status </b></td>
					<td><?php echo $ret['status'];?></td>
				</tr>

				<tr>

					<td> <b>msg </b></td>
					<td><?php echo $ret['msg'];?></td>
				</tr>

				<tr>

					<td> <b>cronid </b></td>
					<td><?php echo $ret['cronid'];?></td>
				</tr>


				<tr>

					<td> <b>pid </b></td>
					<td><?php echo $ret['pid'];?></td>
				</tr>

				<tr>

					<td> <b>instid </b></td>
					<td><?php echo $ret['instid'];?></td>
				</tr>


				<tr>

					<td> <b>time </b></td>
					<td><?php echo $ret['time'];?></td>
				</tr>



			</tbody>

		</table>
	</center>
<?php else: ?>
<form method="post" enctype="multipart/form-data">
<input type="checkbox" name="form[chkbox]" value="1" /> CheckBox
<input type="radio" name="form[radio]" value="1" /> Radio 1<input type="radio" name="form[radio]" value="2" /> Radio 2
<input type="text" name="form[text]" value="" />
<textarea name="form[text]"></textarea>
<input type="file" name="file" />
<select name="form[select]">
	<option value="1"> 1 </option>
	<option value="2"> 2 </option>
	<option value="3"> 3 </option>
</select>

<select name="form[select2]" multiple>
	<option value="1"> 1 </option>
	<option value="2"> 2 </option>
	<option value="3"> 3 </option>
</select>
<input type="submit" value="submit & run in background" /> 
</form>
<?php endif; ?>
</body>
</html>
