<?php
/********************************************************
 *   Copyright (C) 2020 All rights reserved.
 *
 *   Filename: HandShakeListener.php
 *   Author  : zhangchenxi@ymt360.com
 *   Date    : 2020/8/9
 *   Describe: 文件描述
 *
 ********************************************************/

namespace App\Listener;

use EasySwoole\Event\Listener;
use EasySwoole\Server\WebSocket\WebSocketServer;
use Firebase\JWT\JWT;
use Swoole\Http\Request;
use Swoole\Http\Response;

class HandShakeListener extends Listener {

    protected $name = 'ws.handshake';

    public function handler( WebSocketServer $server = null ,Request $request = null ,Response $response = null)
    {
        $token = $request->header['sec-websocket-protocol'];

        if(empty($token)){
            $response->end();
            return false;
        }else{
            if(!$this->check($server,$token,$request->fd)){
                return false;
            }

        }
        $this->handShake($request,$response);
    }


    public function check(WebSocketServer $server,$token,$fd)
    {
        try{
            $config = $this->app->make('config');

            $key = $config->get('Server.route.jwt.key');
            $alg = $config->get('Server.route.jwt.alg');
            $jwt = JWT::decode($token,$key,[$alg]);
            $userInfo = $jwt->data;
            $server->getRedis()->hset($key,$userInfo->uid,json_encode(['fd'=>$fd,'name'=>$userInfo->name,'service_url'=>$userInfo->service_url]));
            return true;


        }catch (\Exception $e){
            return false;
        }
    }


    public function handShake(Request $request, Response $response)
    {
        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        echo $request->header['sec-websocket-key'];
        $key = base64_encode(
            sha1(
                $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            )
        );

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();
    }
}