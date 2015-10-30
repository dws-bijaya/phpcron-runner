<?php

  define('jApi_class_path', jApi_root . jApi_ds . 'jApi_libs' );
  define('jApi_plugin_dir', jApi_root . jApi_ds  );
  define('jApi_sess_path', jApi_root . jApi_ds . 'tmp' . jApi_ds );
  define('jApi_account_path', jApi_root  . jApi_ds .  'jApi_accounts' );
  
  //
  define('jApi_output', 'json' );
  define('jApi_content_disposition', false );
  define('jApi_cookie_name', 'API_jssseion' );
  define('jApi_cache', false  );
  define('jApi_security_salt', 'jAPI'  );

  
  // Setup all your clients a/c here
  $jApi_rules['jApi-12-4858'] = array(
                              'referer' => 'http://localhost/phpclasses/jApi_client/',
                     					'cookie_name' => 'jAPI_session',
                     					'output' =>'json',
                     					'callback' => 'callback',
                     					'cache'    => false ,
                     					'security_salt' => 'jAPI',
                              'content_disposition' => false,
                              'auth_ip' => array('192.168.1.6', '::1'),
                              'auth_url' => 'http://localhost/phpclasses/jApi_client/jApi_verify.php'  
                          );
  //
  require_once (jApi_class_path . '/'  . 'jApi_class.php' );
  