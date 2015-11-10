
 ___ _  _ ___  ___                   _     _      ___                        
| _ \ || | _ \/ __|_ _ ___ _ _    _ | |___| |__  | _ \_  _ _ _  _ _  ___ _ _ 
|  _/ __ |  _/ (__| '_/ _ \ ' \  | || / _ \ '_ \ |   / || | ' \| ' \/ -_) '_|
|_| |_||_|_|  \___|_| \___/_||_|  \__/\___/_.__/ |_|_\\_,_|_||_|_||_\___|_|                                                                              
PHPCron Job Runner 1.0.0
-------------------------------------------------------------------------------
Start and stop tasks in the background

This package can start and stop tasks in the background.

The main class takes an object as parameter that will be used to implement common functionality of the task that is meant to be executed like the code to run when the task is started, stopped, when receiving new commands, etc..

Applications should extend the main class to implement the functionality of the background task that is going to be executed.

The class can start the task in the background in either Linux or Windows. It uses the PHP CLI version to execute the background task. It can take a custom php.ini configuration file.

The class can be started from the PHP CLI version or as Web server HTTP request. In the later in can preserve the super-global variables $_GET, $_POST, $_COOKIE, $_SESSION, $_FILES.
####################################


Features Summary:
**********************
@ Support to run through command line 
@ Support to run through url request via http server (Apache)
@ $_GET/$_POST/$_COOKIE/$_SESSION/$_FILES will perserve if you run through URL Request via http server (Apache)
@ Custom error log
@ External command listner 
@ Load Average check.
@ OnStop/ onStart/ onFail / onValidate / onReceive callback implemnted
@ Direct write debuger data to a output file
@ Capture last error during end of excecution of the script
@ Support currently activity to status file like "10 of 50 Steps are completed"
@ support to run another extranal script
@ Output response are form of text, json or xml
**********************

!!!!!  Required!!!!!
********************
 1. php binary executable file version >=5.3 (Obviously)
 2. nohup binary executable file (in case of Linux)
 3. temp folder must have write permission to all
 4. php_com_dotnet.dll php extension should be enabled (in case of Window)
 	[If not, No Problem, already added nohup.exe in bin folder]
********************* 


Example 1
Root service folder: crontabs [Place all your service inside this folder]
Folder: ExampleCronJob1 [** temp folder with write permission should be created here]
Filename: ExampleCronJob1.php
---------
Simple cronjob that runs only one instance of the script defined in the script file i,e 1001
On Linux:
	/usr/bin/php crontabs/ExampleCronJob1/ExampleCronJob1.php -INSTID 1001 &
On Window:
	bin\nohup.exe d:/xampp/php/php.exe crontabs/ExampleCronJob1/ExampleCronJob1.php -INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob1.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=xml
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for foreground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  	3GCRON[OUTPUT]='xml' or text;| or xml or json

Example 2
	Folder: ExampleCronJob2
	Filename: ExampleCronJob2.php
---------
This example of cron job demonstrate about how to start a script IN background when user request via web
Also it preserves all data of $_GET/$_POST/$_SESSION/$_COOKIE/$_FILES

Through Browser:
	http://localhost/crontabs/ExampleCronJob2/ExampleCronJob2.php


Example 3
	Folder: ExampleCronJob3

	Filename: ExampleCronJob3.php
--------
	This example of cron job demonstrate how to validate a service before start

On Linux:
	/usr/bin/php crontabs/ExampleCronJob3/ExampleCronJob3.php -INSTID 1001 &

On Window:
	bin\nohup.exe d:/xampp/php/php.exe crontabs/ExampleCronJob3/ExampleCronJob3.php -INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob3.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=json
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for forground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  		3GCRON[OUTPUT]='json' or text;| or xml or json
Example 4
	folder : ExampleCronJob4
--------
Full example with:-
     @Read external command,
     @Default command ping, 
     @Kill added

On Linux:
	/usr/bin/php crontabs/ExampleCronJob3/ExampleCronJob3.php --INSTID 1001 &

On Window:
	bin\nohup.exe  d:/xampp/php/php.exe crontabs/ExampleCronJob3/ExampleCronJob3.php --INSTID 1001 

Through Browser:
	http://localhost/phpclasses/crontabs/ExampleCronJob1/ExampleCronJob1.php?3GCRON[SHELL]=1001&3GCRON[TESTMODE]=0&3GCRON[CONFIRM]=2&3GCRON[OUTPUT]=json
	Params:
    	3GCRON[SHELL]=1001  for instance id of the script
    	3GCRON[TESTMODE]=1  1 for foreground, 0 for background
    	3GCRON[CONFIRM]=2  0-60 for wait until the service started
  		3GCRON[OUTPUT]='json' or text;| or xml or json
