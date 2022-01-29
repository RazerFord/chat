<?php
define('PORT', 8090);
define('ADRESS', '127.0.0.1');

require_once("classes/Chat.php");



$chat = new Chat();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, true);
socket_bind($socket, ADRESS, PORT);

socket_listen($socket, SOMAXCONN);

// $clientSocketArray = array($socket);

while (true) {

    // $newSocketArray = $clientSocketArray;
    // socket_select($newSocketArray, NULL, NULL, 0);

    $newSocket = socket_accept($socket);
    $header = socket_read($newSocket, 1024);
    $chat->sendHeaders($header, $newSocket, ADRESS, PORT);
}

socket_close($socket);
