<?php

declare(strict_types=1);

return [
    'db' => [
        'adapters' => [
            'voucher-db' => [
                'driver' => 'pdo-mysql',
                'database' => 'voucher',
                'username' => 'root',
                'password' => 'GCECjdRLf7RvwJLJ',
                'hostname' => 'mysql-real-voucher'
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
    'kafka' => [
        'brokers' => [
            [
                'host' => 'kafka',
                'port' => '9092',
            ],
            [
                'host' => 'kafka-2',
                'port' => '9092',
            ],
            [
                'host' => 'kafka-3',
                'port' => '9092',
            ]
        ],
        'options' => [
            'groupId' => 'order-created-listener-group'
        ],

    ],
    'exchange-rates' => [
        'api-key' => 'fe3de2886fa0417bbc259c1a4d208518'
    ]
];
