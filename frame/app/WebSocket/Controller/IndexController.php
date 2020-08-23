<?php
namespace App\WebSocket\Controller;


use Swoole\WebSocket\Server;

class IndexController{

    public function open(Server $server,$request)
    {
        echo "server :handshake success with fd{$request->fd}\n";
    }

    public function message(Server $server,$frame)
    {
        echo "接收到{$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd,'this is server');
    }
    public function close($ser,$fd)
    {
        echo "client {$fd} closed\n";
    }
}