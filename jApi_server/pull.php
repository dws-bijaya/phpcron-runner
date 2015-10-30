<?php
  
  define('jApi_ds', DIRECTORY_SEPARATOR );
  define('jApi_root', dirname(__FILE__));

  //
  require_once (jApi_root . '/'  . 'jApi_config.php' );
     
  //
  jAPI::init($jApi_rules);

  //
  jAPI::call();

  //
  jAPI::output();
?>