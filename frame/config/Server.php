<?php
return [
    "http"=>[
        "host"=>'42.51.192.37',
        "port"=> 9700,
        'tcpable'=>1,
        'rpc'=>[
            'host'=>'127.0.0.1',
            'port'=>8000,
            'swoole'=>[
                'worker_num'=>1,
            ]
        ],

    ],
    'route'=>[
        'server'=>[
            'host'=>'42.51.192.37',
            'port'=>'9500',
        ],
        'jwt'=>[
            'key'=>'easycloud',
            'alg'=>'HS256'
        ]
    ],
    'ws'=>[
        'is_handshake'=>1,
    ]
];
