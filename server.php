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

        $newSocketArrayIndex = array_search($socket, $newSocketArray);
        unset($newSocketArray[$newSocketArrayIndex]);
    }

    foreach ($newSocketArray as $newSocketArrayResource) {
        $socketD = socket_recv($newSocketArrayResource, $socketData, 1024, 0);

        while ($socketD > 8) {
            $socketMessage = $chat->unseal($socketData);
            $messageObject = json_decode($socketMessage);
            
            $chatMessage = $chat->createChatMessage($messageObject->chat_user, $messageObject->chat_message);
            $chat->send($chatMessage, $clientSocketArray);

            break 2;
        }

        $socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
        if ($socketData === false) {
            socket_getpeername($newSocketArrayResource, $client_ip_adress, $port);
            $disconnectedACK = $chat->newDisconnectedACK($client_ip_adress);
            $chat->send($disconnectedACK, $clientSocketArray);

            $newSocketArrayIndex = array_search($newSocketArrayResource, $clientSocketArray);
            unset($clientSocketArray[$newSocketArrayIndex]);
        }
    }
}

socket_close($socket);
