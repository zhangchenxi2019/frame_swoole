<?php

namespace App\Listener;


use EasySwoole\Event\Listener;
use EasySwoole\Server\Server;
use Swoole\Coroutine;

class StartListener extends Listener
{
    protected $name = 'start';

    public function handler(Server $server = null)
    {
        $config = $this->app->make('config');

        Coroutine::create(function () use ($server, $config) {
            $host   = $config->get('Server.route.server.host');
            $port   = $config->get('Server.route.server.port');
            $client = new Coroutine\Http\Client($host, $port);
            if ($client->upgrade("/")) {
                $data = [
                    'ip'         => $server->getHost(),
                    'port'       => $server->getPort(),
                    'serverName' => 'im1',
                    'method'     => 'register'
                ];
                $client->push(json_encode($data));

                swoole_timer_tick(3000, function () use ($client) {
                    $client->push('', WEBSOCKET_OPCODE_PING);
                });
            }
        });
    }


}
