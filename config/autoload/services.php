<?php
declare(strict_types = 1);

use App\JsonRpc\Contract\InterfaceUserService;

return [
    'consumers' => value(function ()
    {
        $consumers = [];
        // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
        // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
        $services = [
            'UserService'   => InterfaceUserService::class,

        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name'          => $name,
                'service'       => $interface,
                'registry'      => [
                    'protocol' => 'consul',
                    'address'  => 'http://127.0.0.1:8500',
                ],
                'id'            => $interface,
                // 服务提供者的服务协议，可选，默认值为 jsonrpc-http
                // 可选 jsonrpc-http jsonrpc jsonrpc-tcp-length-check
                'protocol'      => 'jsonrpc-tcp-length-check',
                // 负载均衡算法，可选，默认值为 random
                'load_balancer' => 'random',
                // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息
                // 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
                'nodes'         => [
                    ['host' => '127.0.0.1', 'port' => 9504],
                ],
                // 配置项，会影响到 Packer 和 Transporter
                'options'       => [
                    'connect_timeout' => 5.0,
                    'recv_timeout'    => 5.0,
                    'settings'        => [
                        'open_length_check'     => true,
                        'package_length_type'   => 'N',
                        'package_length_offset' => 0,
                        'package_body_offset'   => 4,
                    ],
                    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
                    'pool'            => [
                        'min_connections' => 1,
                        'max_connections' => 50,
                        'connect_timeout' => 10.0,
                        'wait_timeout'    => 3.0,
                        'heartbeat'       => -1,
                        'max_idle_time'   => 60.0,
                    ],
                ]
            ];
        }
        return $consumers;
    }),
];



