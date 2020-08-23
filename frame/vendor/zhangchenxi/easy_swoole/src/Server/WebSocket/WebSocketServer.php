<?php

namespace EasySwoole\Server\WebSocket;

use EasySwoole\Console\Input;
use EasySwoole\Server\WebSocket\Connections;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server as SwooleServer;
use EasySwoole\Server\Http\HttpServer;

class WebSocketServer extends HttpServer
{

    protected $connections;

    protected function creatServer()
    {
        $this->swooleServer = new SwooleServer($this->host, $this->port);

        Input::info('ws://' . $this->host . ":" . $this->port, 'websocket服务');
    }

    protected function initEvent()
    {

        $event = [
            'request'   => 'onRequest',
            'open'      => 'onOpen',
            'message'   => 'onMessage',
            'close'     => 'onClose',
            'handshake' => 'onHandShake'
        ];
        $this->setEvent('sub', $event);
    }

    public function onOpen(SwooleServer $server, $request)
    {
        dd('open');
//        Connections::init($request->fd, $request->server['path_info']);
        Connections::init($request->fd, $request);
        app('route')->setFlag('WebSocket')->setMethod('open')->match($request->server['path_info'],
            [$server, $request]);
    }


    public function onMessage(SwooleServer $server, $frame)
    {

        $path = (Connections::get($frame->fd))->server['path_info'];
        $return = app('route')->setFlag('WebSocket')->setMethod('message')->match($path, [$server, $frame]);
        $this->app->make('event')->trigger('ws.message.front', [$this, $server, $frame]);


    }

    public function onClose($ser, $fd)
    {

        $path = (Connections::get($fd))->server['path_info'];
        $return = app('route')->setFlag('WebSocket')->setMethod('close')->match($path, [$ser, $fd]);

        $this->app->make('event')->trigger('ws.close', [$this,$fd]);

        Connections::del($fd);
    }

    public function onHandShake(Request $request, Response $response)
    {
        $this->app->make('event')->trigger('ws.handshake', [$this, $request, $response]);
        //设置onHandShanke后，就不会触发onOpen事件

        $this->onOpen($this->swooleServer,$request);
    }


    public function sendAll($data)
    {
        foreach($this->swooleServer->connections as $key=>$fd){
            if($this->swooleServer->exist($fd)){
                var_dump($fd);
                $this->swooleServer->push($fd,json_encode($data));
            }
        }
    }
}