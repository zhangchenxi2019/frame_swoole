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
use EasySwoole\Server\Server;
use EasySwoole\Server\WebSocket\Connections;
use Firebase\JWT\JWT;

class WSCloseListener extends Listener
{

    protected $name = 'ws.close';

    public function handler(Server $server = null, $fd = null)
    {
        $isHandShake = $this->app->make('config')->get('Server.ws.is_handshake');

        if ($isHandShake) {
            $this->cancle($server, $fd);
        }
    }

    public function cancle(Server $server, $fd)
    {
        $request = Connections::get($fd)['request'];
        $token   = $request->header['sec-websocket-protocol'];
        $config = $this->app->make('config');
        $key = $config->get('Server.route.jwt.key');
        $alg = $config->get('Server.route.jwt.alg');
        $jwt = JWT::decode($token,$key,[$alg]);
        $uid = $jwt->data->uid;
        $server->getRedis()->hdel($key,$uid);
    }


}