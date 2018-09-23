<?php
/* @var $this ChatController */

?>

<h1>Chat</h1>


<?php
Yii::app()->clientScript->registerScript('startTimeBtnClick', '
        

// create object
var socket = new YiiNodeSocket();

// enable debug mode
socket.debug(true);

socket.onConnect(function () {
    // fire when connection established
});

socket.onDisconnect(function () {
    // fire when connection close or lost
});

socket.onConnecting(function () {
    // fire when the socket is attempting to connect with the server
});

socket.onReconnect(function () {
    // fire when successfully reconnected to the server
});

');
?>