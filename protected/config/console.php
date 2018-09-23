<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log', 'nodeSocket'),
    
	// application components
	'components'=>array(
        'nodeSocket' => array(
            'class' => 'application.extensions.yii-node-socket.lib.php.NodeSocket',
            'host' => '74.208.192.73',  // default is 127.0.0.1, can be ip or domain name, without http
            'port' => 3001      // default is 3001, should be integer
        ),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
    'commandMap' => array(
        'node-socket' => 'application.extensions.yii-node-socket.lib.php.NodeSocketCommand'
    ),
);