<?php
/********************************************************
 *   Copyright (C) 2020 All rights reserved.
 *
 *   Filename: HandShakeListener.php
 *   Author  :
 *   Date    : 2020/8/9
 *   Describe: 文件描述
 *
 ********************************************************/

namespace App\Listener;
use EasySwoole\Event\Listener;
use EasySwoole\Server\Server as EasySwooleServer;
use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleServer;
use EasySwoole\Server\WebSocket\Connections;
use Firebase\JWT\JWT;

class WSMessageFrontListener extends Listener
{

    protected $name = 'ws.message.front';

    public function handler(EasySwooleServer $easySwooleServer = null,SwooleServer $swooleServer = null,Frame $frame = null)
    {
        $data = json_decode($frame->data,true);
        $this->{$data['method']}($easySwooleServer,$data,$frame->fd,$swooleServer);
    }

    private function serverBroadCast($easySwooleServer,$data)
    {

        $config = $this->app->make('config');
        $host = $config->get('Server.route.server.host');
        $port = $config->get('Server.route.server.port');
        $cli = new Client($host,$port);
        if($cli->upgrade('/')){
            $cli->push(json_encode([
                "method"=>'routeBroadcast',
                "msg"=>$data['msg']
                ])
            );
        }
    }


    private function routeBroadcast(EasySwooleServer $easySwooleServer,$data,$fd,SwooleServer $swooleServer)
    {

        var_dump('接收到了route的消息');

        var_dump($data);
        $dataAck = [
            'method'=>'ack',
            'msg_id'=> $data['msg_id']
        ];
        $swooleServer->push($fd,json_encode($dataAck));
        $easySwooleServer->sendAll($data);
    }


    private function privateChat(EasySwooleServer $easySwooleServer,$data,$fd)
    {

        //获取接收者的服务器信息
        $redisKey = $this->app->make('config')->get('Server.route.jwt.key');
        $clientImServerInfo = $easySwooleServer->getRedis()->hget($redisKey,$data['clientId']);

        $clientImServerInfo = json_decode($clientImServerInfo,true);
        $request = Connections::get($fd)['request'];
        $token = $request->header['sec-websocket-protocol'];
//
        $clientImServerUrl = explode(':',$clientImServerInfo['service_url']);
        $easySwooleServer->send($clientImServerUrl[0],$clientImServerUrl[1],[
            'method'=>'forwarding',
            'msg'=>$data['msg'],
            'fd' =>$clientImServerInfo['fd']
        ],['sec-websocket-protocol'=>$token]);


    }

    private function forwarding($easySwooleServer,$data,$fd,SwooleServer $swooleServer)
    {

        foreach ($swooleServer->connections as $fd) {
            var_dump($fd);
        }
        var_dump("私聊的接收者".$data['fd']);
        var_dump("私聊的接收者fd是否存在".$swooleServer->exist($data['fd']));
        $swooleServer->push($data['fd'],json_encode(['msg'=>$data['msg']]));
    }

}