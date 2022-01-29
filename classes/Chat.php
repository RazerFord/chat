<?php

class Chat
{
    public function sendHeaders($headersText, $newSocket, $host, $port)
    {
        $headers = array();
        $tmpLine = preg_split("/\r\n/", $headersText);

        foreach ($tmpLine as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $mathes)) {
                $headers[$mathes[1]] = $mathes[2];
            }
        }
        $key = $headers['Sec-WebSocket-Key'];
        $hash = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $hash = sha1($hash, true);
        $hash = base64_encode($hash);

        $strHeader = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Accept: " . $hash . "\r\n\r\n";

        socket_write($newSocket, $strHeader, strlen($strHeader));
    }

    public function newConnectionACK($client_ip_adress)
    {
        $message = "New client " . $client_ip_adress . " connected\n";
        $messageArray = [
            "message" => $message,
            "type" => "newConnectionACK"
        ];

        $ask = $this->seal(json_encode($messageArray));
        return $ask;
    }
    
    public function newDisconnectedACK($client_ip_adress)
    {
        $message = "Client " . $client_ip_adress . " disconnected\n";
        $messageArray = [
            "message" => $message,
            "type" => "newDisonnectedACK"
        ];

        $ask = $this->seal(json_encode($messageArray));
        return $ask;
    }

    public function seal($socketData)
    {
        $byte1 = 0x81;
        $lenght = strlen($socketData);
        $header = "";

        if ($lenght <= 125) {
            $header = pack('CC', $byte1, $lenght);
        } elseif ($lenght > 125 && $lenght <= 65536) {
            $header = pack('CCn', $byte1, 126, $lenght);
        } elseif ($lenght > 65536) {
            $header = pack('CCNN', $byte1, 127, $lenght);
        }

        return $header . $socketData;
    }

    public function send($message, $clientSocketArray)
    {
        $messageLenght = strlen($message);

        foreach ($clientSocketArray as $clientSocket) {
            @socket_write($clientSocket, $message, $messageLenght);
        }

        return true;
    }

    public function unseal($socketData)
    {
        $lenght = ord($socketData[1]) & 127;

        if ($lenght == 126) {
            $mask = substr($socketData, 4, 4);
            $data = substr($socketData, 8);
        } elseif ($lenght == 127) {
            $mask = substr($socketData, 10, 4);
            $data = substr($socketData, 14);
        } else {
            $mask = substr($socketData, 2, 4);
            $data = substr($socketData, 6);
        }

        
        $socketStr = "";
        for ((int)$i = 0; $i < strlen($data); ++$i) {
            $socketStr .= $data[$i] ^ $mask[$i % 4];
        }
        
        return $socketStr;
    }

    public function createChatMessage($userName, $message)
    {
        $message = $userName . "<div>" . $message . "</div>";
        $messageArray = [
            "type" => "chat-box",
            "message" => $message
        ];

        return $this->seal(json_encode($messageArray));
    }
}
