<?php

header('Content-Type: text/plain;');
error_reporting(E_ALL ^ E_WARNING);
set_time_limit(0);
ob_implicit_flush();

echo "-= Server =-\n\n";

$address = 'localhost';
$port = 10001;


$data = array();


echo 'Create socket ... ';
try {
	echo 'Create socket ... ';         
	if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) { 
		throw new Exception('socket_create() failed: '.socket_strerror(socket_last_error())."\n");        
	} else {
		echo "OK\n";        
	}

	echo 'Bind socket ... ';         
	if (($ret = socket_bind($sock, $address, $port)) < 0) {
		throw new Exception('socket_bind() failed: '.socket_strerror(socket_last_error())."\n");         
	} else {
		echo "OK\n";        
	}

	echo 'Listen socket ... ';         
	if (($ret = socket_listen($sock, 5)) < 0) {
		throw new Exception('socket_listen() failed: '.socket_strerror(socket_last_error())."\n");         
	} else {
		echo "OK\n";         
	}

	do {             
		echo 'Accept socket ... ';             
		if (($msgsock = socket_accept($sock)) < 0) { 
			throw new Exception('socket_accept() failed: '.socket_strerror(socket_last_error())."\n");  
		} else {                 
			echo "OK\n";             
		}

		$msg = "Hello, Client!";             
		echo "Say to client ($msg) ... ";             
		socket_write($msgsock, $msg, strlen($msg));             
		echo "OK\n";

		do {                             
			echo 'Client said: ';                 
			if (false === ($buf = socket_read($msgsock, 1024))) {
				throw new Exception('socket_read() failed: '.socket_strerror(socket_last_error())."\n");
			} else {   
                            $json = json_decode($buf, true);
                            $data[$json['uniqueid']] = $json;
                            echo json_encode($data)."\n";                 
			}                                 
			
			if (!$buf = trim($buf)) {                     
				continue;                 
			}                                 
			
			if ($buf == 'shutdown') {                     
				socket_close($msgsock);                     
				break 2;                 
			}                                 
			
                        $session = json_encode($data);
			echo "Say to client ($session) ... ";                 
			socket_write($msgsock, $session, strlen($session));                 
			echo "OK\n";                             
		} while (true);
	} while (true);
	
} catch (Exception $e) {         
	echo "\nError: ".$e->getMessage();     
}
	
if (isset($sock)) { 
	echo 'Close socket ... ';        
	socket_close($sock);         
	echo "OK\n";         
}

?>