<?php

declare(strict_types=1);

return [
    'db' => [
        'adapters' => [
            'order-db' => [
                'driver' => 'pdo-mysql',
                'database' => 'order',
                'username' => 'root',
                'password' => 'GCECjdRLf7RvwJLJ',
                'hostname' => 'mysql-real-order'
            ]
        ]
    ],
    'mq' => [
        'nodeConfigs' => [
            'node0' => [
                'host' => 'rabbitmq',
                'port' => '5672',
                'user' => 'admin',
                'password' => 'tNS#@xY&.v6FYAcb',
                'vhost' => '/real/order'
            ]
        ],
        'options' => [
            'keepalive' => true
        ],
    ],
];
