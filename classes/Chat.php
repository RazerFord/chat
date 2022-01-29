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
}
