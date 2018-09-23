<?php
header('Content-Type: text/plain;');     
error_reporting(E_ALL ^ E_WARNING);     
set_time_limit(0);     
ob_implicit_flush();         
echo "-= Client =-\n\n";     
$address = gethostbyname('api.garpie.co');    
$port    = 10001;     
try {             
	echo 'Create socket ... ';         
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);         
	if ($socket < 0) {             
		throw new Exception('socket_create() failed: '.socket_strerror(socket_last_error())."\n");         
	} else {
		echo "OK\n";         
	}         
	
	echo 'Connect socket ... ';         
	$result = socket_connect($socket, $address, $port);         
	if ($result === false) {             
		throw new Exception('socket_connect() failed: '.socket_strerror(socket_last_error())."\n");         
	} else {             
		echo "OK\n";         
	}        
	
	echo 'Server said: ';         
	$out = socket_read($socket, 1024);         
	echo $out."\n";
        
        $item = array(
            'uniqueid'=>'911',
            'latitude'=>44.1, 
            'longitude'=>1.01,
            'altitude'=>10);
        
	$msg = json_encode($item);// "Hello, Server!";         
	echo "Say to server ($msg) ...";         
	socket_write($socket, $msg, strlen($msg));         
	echo "OK\n";                 
	echo 'Server said: ';         
	$out = socket_read($socket, 1024);         
	echo $out."\n";                 
	$msg = 'shutdown';         
	echo "Say to server ($msg) ... ";         
	socket_write($socket, $msg, strlen($msg));         
	echo "OK\n";
} catch (Exception $e) {         
	echo "\nError: ".$e->getMessage();     
}
	
if (isset($socket)) {             
	echo 'Close socket ... ';         
	socket_close($socket);         
	echo "OK\n";             
}

?>