<?php
/* CONFIG */
return array(
     'DB' => array(
        'MASTER' => array(
            'use' => 'pdo_mysqli',
            'host' => 'localhost',
            'port' => 3307,
            'user' => 'postgres',
            'password' => '123456',
            'database' => 'backlinks',
            'prefix' => 'bl_'
        ),
        'BACKLINKS' => array(
            'use' => 'pdo_mysqli',
            'host' => 'localhost',
            'port' => 3308,
            'user' => 'root',
            'password' => '',
            'database' => 'backlinks2',
            'prefix' => 'bl_'
        )
    ),
    'DEBUG' => array('pid_check' => 1,
					 'email_user'=>'notifications@crm.rankwatch.com', 'email_from' =>'Rank Watch',
					 'email_host' =>'crm.rankwatch.com',
					 'email_port' =>25,
					 'email_password' => '123456'
					 ),
    /* PID SEPECIFIC CONFIG */
    'PID' => array('file_name' => 'runnow', 'location' => dirname(__FILE__) . DS,
        'pid_chk_command' => ( strtolower(PHP_OS) == strtolower('LINUX') ? 'ps [[PID]]' : 'tasklist /fi "PID eq [[PID]]"' )
    ),
    /* Allowed */
    'allowed' => array(
        'instances' => array(11, 12, 13, 14, 15) /* only 5 instances */
    ),
	'clear_exit' => false,	
);
?>