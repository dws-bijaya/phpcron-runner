
______ _   _ ______  _____                  ______                            
| ___ \ | | || ___ \/  __ \                 | ___ \                           
| |_/ / |_| || |_/ /| /  \/_ __ ___  _ __   | |_/ /   _ _ __  _ __   ___ _ __ 
|  __/|  _  ||  __/ | |   | '__/ _ \| '_ \  |    / | | | '_ \| '_ \ / _ \ '__|
| |   | | | || |    | \__/\ | | (_) | | | | | |\ \ |_| | | | | | | |  __/ |   
\_|   \_| |_/\_|     \____/_|  \___/|_| |_| \_| \_\__,_|_| |_|_| |_|\___|_|   
                                                                              
PHPCron Runner 1.0.0
-------------------------------------------------------------------------------
Run your php script in background/forground as a process
####################################


Features Summary:
**********************
@ Support to run through command line 
@ Support to run through url request vai http server (Apache)
@ $_GET/$_POST/$_COOKIE/$_SESSION/$_FILES will perserve if you run through URL Request vai http server (Apache)
@ Custom error log support
@ External command listner 
@ Load Average check.
@ OnStop/ onStart/ onFail / onValidate / onReceive callback implemnted
@ Direvt write Debuger output to a file
@ Capture last error during end of excecution of script
@ Support currently activity to status file like "10 of 50 Steps are completed"
@ support to run another extranal script
@ Output response are form of text, json or xml
**********************

!!!!!  Required !!!!!
**********************
 1. php binary excecutable file version >=5.3 (Obviously)
 2. nohup binary excecutable file (in case of Linux)
 3. temp folder must be writable permission
 4. php_com_dotnet.dll php extension (in case of Window)
 	if not, No Problem,  added nohup.exe in bin folder
********************* 


Example 1
folder : ExampleCronJob1
---------
Simple cronjob that run only one instance of the script defined in the script file i,e 1001

On Linux:
	/usr/bin/php crontabs/ExampleCronJob1/ExampleCronJob1.php -INSTID 1001 &

On Window:
	bin\nohup.exe d:/xampp/php/php.exe crontabs/ExampleCronJob1/ExampleCronJob1.php -INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob1.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=xml
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for forground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  		3GCRON[OUTPUT]='xml' or text;| or xml or json

Example 2
	folder : ExampleCronJob2
---------
This example cron job demonstrate about how to start a script IN background when user  request via web
Also it preserved all data of $_GET/$_POST/$_SESSION/$_COOKIE/$_FILES

Through Browser:
	http://localhost/crontabs/ExampleCronJob2/ExampleCronJob2.php


Example 3
	folder : ExampleCronJob3
--------
	This example cron job demonstrate how to validate a service before start

On Linux:
	/usr/bin/php crontabs/ExampleCronJob3/ExampleCronJob3.php -INSTID 1001 &

On Window:
	bin\nohup.exe d:/xampp/php/php.exe crontabs/ExampleCronJob3/ExampleCronJob3.php -INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob1.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=json
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for forground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  		3GCRON[OUTPUT]='json' or text;| or xml or json
Example 4
	folder : ExampleCronJob4
--------
		full example with read external command, default command ping, kill added

On Linux:
	/usr/bin/php crontabs/ExampleCronJob3/ExampleCronJob3.php --INSTID 1001 &

On Window:
	bin\nohup.exe  d:/xampp/php/php.exe crontabs/ExampleCronJob3/ExampleCronJob3.php --INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob1.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=json
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for forground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  		3GCRON[OUTPUT]='json' or text;| or xml or json
