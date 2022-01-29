<?php
define('PORT', 8090);
define('ADRESS', '127.0.0.1');

require_once("classes/Chat.php");



$chat = new Chat();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, true);
socket_bind($socket, ADRESS, PORT);

socket_listen($socket, SOMAXCONN);

$clientSocketArray = array($socket);

while (true) {

    $newSocketArray = $clientSocketArray;
    $nullArray = array();
    socket_select($newSocketArray, $nullArray, $nullArray, 0, 10);

    if (in_array($socket, $newSocketArray)) {
        $newSocket = socket_accept($socket);
        $clientSocketArray[] = $newSocket;

        $header = socket_read($newSocket, 1024);
        $chat->sendHeaders($header, $newSocket, ADRESS, PORT);

        socket_getpeername($newSocket, $client_ip_adress, $port);
        $connectionACK = $chat->newConnectionACK($client_ip_adress);
        $chat->send($connectionACK, $clientSocketArray);
    
    }
}

socket_close($socket);
