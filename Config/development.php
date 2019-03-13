<?php

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'HOST' => '127.0.0.1',
        'PORT' => 9501,
        'SERVER_TYPE' => \EasySwoole\Core\Swoole\ServerManager::TYPE_WEB_SOCKET_SERVER,
        'SOCK_TYPE' => SWOOLE_TCP,//该配置项当为SERVER_TYPE值为TYPE_SERVER时有效
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'task_worker_num' => 2, //异步任务进程
            'task_max_request' => 10,
            'max_request' => 5000,//强烈建议设置此配置项
            'worker_num' => 2
        ],
    ],
    'DEBUG' => true,
    'TEMP_DIR' => null,//若不配置，则默认框架初始化
    'LOG_DIR' => null,//若不配置，则默认框架初始化
    'EXPORT_DIR' => EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Export',
    'EASY_CACHE' => [
        'PROCESS_NUM' => 0,//若不希望开启，则设置为0
        'PERSISTENT_TIME' => 0//如果需要定时数据落地，请设置对应的时间周期，单位为秒
    ],
    'CLUSTER' => [
        'enable' => false,
        'token' => null,
        'broadcastAddress' => ['255.255.255.255:9556'],
        'listenAddress' => '127.0.0.1',
        'listenPort' => '9556',
        'broadcastTTL' => 5,
        'nodeTimeout' => 10,
        'nodeName' => 'easySwoole',
        'nodeId' => null
    ],
    'DATABASE' => [
        'master' => [
            [
                'host' => '192.168.1.202',
                'user' => 'testdbh',
                'pwd' => '43Hzaodao197',
                'db' => ['zd_netschool', 'zd_class', 'zd_uc', 'zd_sales']
            ],
            [
                'host' => '192.168.1.202',
                'user' => 'testdbh',
                'pwd' => '43Hzaodao197',
                'db' => ['zd_jpdata']
            ],
            [
                'host' => '192.168.1.202',
                'user' => 'testdbh',
                'pwd' => '43Hzaodao197',
                'db' => ['zd_spread']
            ]
        ],
        'slave' => []
    ],
    'REDIS' => [
        'host' => '192.168.1.198',
        'port' => 6380,
        'pwd' => ''
    ],
    'REDIS_KEY' => [
        'bindUid' => 'ZD_BIND_UID_HASH',
        'bindFdByUid' => 'ZD_BIND_FD_BY_UID_HASH',
        'waitProcessTaskByUid' => 'WAIT_PROCESS_TASK_HASH_BY_UID:',
        'noticeTaskByUid' => 'NOTICE_TASK_LIST_BY_UID:',
    ],
    'RPC' => [
        'host' => '192.168.1.19',
        'port' => '9202'
    ],
    'ORIGIN_WHITE_LIST' => ['*'],
    'SIGN_KEY' => 'saljwegoagjaw209]]af23rhwhf09aegv',
    //支付中心(java)接口
    'PAY_API'=>[
        'payApiHost'    =>'https://tpayapi1.izaodao.com/rest/',
        'payAppId'      =>'d9078439eea9436c92e4d0206562c5a9',
        'paySecretKey'  =>'EJXwCCODy4zCu9UsbyLhRA',
    ],

    'LINK_HOST_ZDMIS' => 'http://dzdmis.izaodao.com/',
    'LINK_HOST_KEEPERAPI' => 'https://tkeeperapi.izaodao.com/'
];