<?php

require '../vendor/autoload.php';
require_once '../vendor/workerman/workerman/Autoloader.php';
require_once '../vendor/workerman/channel/src/Server.php';
require_once '../vendor/workerman/channel/src/Client.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;
use Workerman\Worker;
use Workerman\Lib\Timer;


$channel_server = new Channel\Server('0.0.0.0', 2206);
$worker = new Worker('websocket://0.0.0.0:2000');
$worker->count = 2;
// 全局群组到连接的映射数组
$group_con_map = array();

$worker->onWorkerStart = function ($worker) {
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('0.0.0.0', 2206);

    // 监听全局分组发送消息事件
    Channel\Client::on('send', function ($event_data) {
        $thread = $event_data['thread_id'];
        $con_id = $event_data['con_id'];
        $mes_id = $event_data['mes_id'];
        $speaker = $event_data['speaker'];
        $class = $event_data['class_id'];

        $array = Lazer::table('messages')->limit(1)->where('id', '=', (int) $mes_id)->andWhere('speaker', '=', (int) $speaker)->andWhere('belong_class', '=', (int) $class)->find()->asArray();

        if (!!$array[0]['speaker']) {
            global $group_con_map;
            if (isset($group_con_map[$thread])) {
                foreach ($group_con_map[$thread] as $con) {
                    $con->send(json_encode($array[0]));
                }
            }
        } else {
            $array = [
                'op' => 'sent',
                'status' => false,
                'code' => 108,
                'msg' => 'Illegal Request'
            ];
            global $group_con_map;
            $group_con_map[$thread][$con_id]->send(json_encode($array));
        }
    });

    //心跳计时
    Timer::add(55, function () use ($worker) {
        foreach ($worker->connections as $connection) {
            $array = [
                'op' => 'keep'
            ];
            $connection->send(json_encode($array));
        }
    });
};

//发送消息
$worker->onMessage = function ($con, $data) {

    $data = json_decode($data, true);
    $cmd = $data['action'];
    $thread = $data['thread_id'];
    $class = $data['class_id'];
    $user = $data['speaker'];
    $user_name = $data['speaker_name'];
    @$mes_id = $data['mes_id'];

    if (!empty($user_name) && !empty($thread) && !empty($class) && !empty($user)) {
        $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->find()->asArray();
        if (!!$array) {
            $array = Lazer::table('threads')->limit(1)->where('id', '=', (int) $thread)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
            if (!!$array) {
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $user)->andWhere('name', '=', (string) $user_name)->find()->asArray();
                if (!!$array && in_array((string) $class, explode(',', $array[0]['class']))) { //判断用户存在

                    switch ($cmd) {
                        case "join":
                            global $group_con_map;
                            // 将连接加入到对应的群组数组里
                            $group_con_map[$thread][$con->id] = $con;
                            $array = [
                                'op' => 'join',
                                'thread' => $thread,
                                'status' => true,
                                'code' => 100
                            ];
                            break;
                        case "send":
                            Channel\Client::publish('send', array(
                                'thread_id' => $thread,
                                'class_id' => $class,
                                'speaker' => $user,
                                'speaker_name' => $user_name,
                                'con_id' => $con->id,
                                'mes_id' => $mes_id
                            ));
                            $array = [
                                'op' => 'send',
                                'status' => true,
                                'code' => 105
                            ];
                            break;
                        default:
                            $array = [
                                'op' => 'send',
                                'status' => false,
                                'code' => 101,
                                'msg' => 'Illegal request'
                            ];
                            break;
                    }
                } else {
                    $array = [
                        'op' => 'send',
                        'status' => false,
                        'code' => 107,
                        'msg' => 'User does not exist or not in the class'
                    ];
                }
            } else {
                $array = [
                    'op' => 'send',
                    'status' => false,
                    'code' => 102,
                    'msg' => 'Thread does not exist'
                ];
            }
        } else {
            $array = [
                'op' => 'send',
                'status' => false,
                'code' => 103,
                'msg' => 'Class does not exist'
            ];
        }
    } else {
        $array = [
            'op' => 'send',
            'status' => false,
            'code' => 104,
            'msg' => 'Illegal request'
        ];
    }
    $con->send(json_encode($array));
};

// 这里很重要，连接关闭时把连接从全局群组数据中删除，避免内存泄漏
$worker->onClose = function ($con) {
    global $group_con_map;
    if (isset($con->group_id)) {
        unset($group_con_map[$con->group_id][$con->id]);
        if (empty($group_con_map[$con->group_id])) {
            unset($group_con_map[$con->group_id]);
        }
    }
};

$worker->onConnect = function ($con) {
    $array = [
        'op' => 'connect',
        'status' => true
    ];
    $con->send(json_encode($array));
};

Worker::runAll();
