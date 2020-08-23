# frame_swoole

本框架是基于Swoole开发的一个框架，实现了HTTP,TCP,UDP以及Webscoket服务器，在平时学习swoole时，为了理解swoole而开发的一个简单的框架，仅用来学习使用~

ImServer配置：
```php
frame_swoole/frame/config.php  

      "http"=>[
        "host"=>'42.51.192.37', //配置imserver的本地ip地址
        "port"=> 9700, //配置imserver的本地端口地址
        'tcpable'=>1,
        'rpc'=>[
            'host'=>'127.0.0.1',
            'port'=>8000,
            'swoole'=>[
                'worker_num'=>1,
            ]
        ],

    ]
     'route'=>[ //route 服务的配置
        'server'=>[
            'host'=>'42.51.192.37', route服务的ip地址配置
            'port'=>'9500', //route服务的端口配置
        ],
        'jwt'=>[
            'key'=>'easycloud',
            'alg'=>'HS256'
        ]
    ],
    
```

启动方式：
php  frame_swoole/frame/bin http:start 开启http服务
php  frame_swoole/frame/bin ws:start 开启websocket服务

