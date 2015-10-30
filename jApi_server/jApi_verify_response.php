<?php
  
  //
  define('jApi_ds', DIRECTORY_SEPARATOR );
  define('jApi_root', dirname(__FILE__));
  
  //
  require_once (jApi_root . '/'  . 'jApi_config.php' );

  //
  jApi::verify_response_init($jApi_rules);

  //
  jApi::verify_response_output();
 ?>