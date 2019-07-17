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

//输入处理
function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = str_replace("'", "&#39;", $data);
    $data = str_replace('"', "&#34;", $data);
    return $data;
}

$worker->onWorkerStart = function ($worker) {
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('0.0.0.0', 2206);

    // 监听全局分组发送消息事件
    Channel\Client::on('send', function ($event_data) {
        $thread = input($event_data['thread_id']);
        $con_id = input($event_data['con_id']);
        $mes_id = input($event_data['mes_id']);
        $speaker = input($event_data['speaker']);
        $class = input($event_data['class_id']);
        //判断广播类型：新消息、删除、emoji
        $type = input($event_data['type']);
        @$emoji = input($event_data['emoji_id']);
        @$count_id = input($event_data['count_id']);
        @$emoji_type = input($event_data['emoji_type']);

        if (!empty($thread) && !empty($mes_id) && !empty($speaker) && !empty($class) && !empty($type)) {
            switch ($type) { //判断类型
                case 'emoji': //emoji 删除或增加
                    if (!empty($emoji) && !empty($count_id) && !empty($emoji_type)) { //判断 emoji 参数
                        if ($emoji_type == 'add') {
                            $array = [
                                'op' => 'emoji_add',
                                'status' => true,
                                'code' => 110,
                                'count_id' => (int) $count_id,
                                'emoji_id' => (int) $emoji
                            ];
                        } else {
                            $array = [
                                'op' => 'emoji_remove',
                                'status' => true,
                                'code' => 110,
                                'count_id' => (int) $count_id,
                                'emoji_id' => (int) $emoji
                            ];
                        }
                        global $group_con_map;
                        if (isset($group_con_map[$thread])) {
                            foreach ($group_con_map[$thread] as $con) {
                                $con->send(json_encode($array));
                            }
                        }
                    } else {
                        $array = [
                            'op' => 'sent',
                            'status' => false,
                            'code' => 112,
                            'msg' => 'Illegal Request'
                        ];
                        global $group_con_map;
                        $group_con_map[$thread][$con_id]->send(json_encode($array));
                    }
                    break;
                case 'delete': //删除操作
                    if (!empty($count_id)) { //删除条段 id 判断
                        $array = [
                            'op' => 'delete',
                            'status' => true,
                            'code' => 113,
                            'count_id' => (int) $count_id
                        ];
                        global $group_con_map;
                        if (isset($group_con_map[$thread])) {
                            foreach ($group_con_map[$thread] as $con) {
                                $con->send(json_encode($array));
                            }
                        }
                    } else {
                        $array = [
                            'op' => 'sent',
                            'status' => false,
                            'code' => 114,
                            'msg' => 'Illegal Request'
                        ];
                        global $group_con_map;
                        $group_con_map[$thread][$con_id]->send(json_encode($array));
                        break;
                    }
                    break;
                case 'message': //发送内容
                    $array = Lazer::table('messages')->limit(1)->where('id', '=', (int) $mes_id)->andWhere('speaker', '=', (int) $speaker)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
                    global $group_con_map;
                    if (isset($group_con_map[$thread])) {
                        foreach ($group_con_map[$thread] as $con) {
                            $con->send(json_encode($array[0]));
                        }
                    }
                    break;
                case 'edit': //编辑内容，不发送内容
                    $array = [
                        'op' => 'edit',
                        'status' => true,
                        'code' => 115,
                    ];
                    global $group_con_map;
                    if (isset($group_con_map[$thread])) {
                        foreach ($group_con_map[$thread] as $con) {
                            $con->send(json_encode($array));
                        }
                    }
                    break;
                default:
                    $array = [
                        'op' => 'sent',
                        'status' => false,
                        'code' => 108,
                        'msg' => 'Illegal Request'
                    ];
                    global $group_con_map;
                    $group_con_map[$thread][$con_id]->send(json_encode($array));
                    break;
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
    $cmd = input($data['action']);
    $thread = input($data['thread_id']);
    $class = input($data['class_id']);
    $user = input($data['speaker']);
    $user_name = input($data['speaker_name']);
    @$mes_id = input($data['mes_id']);
    $type = input($data['type']);
    @$emoji = input($data['emoji_id']);
    @$count_id = input($data['count_id']);
    @$emoji_type = input($data['emoji_type']);

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
                            $con->send(json_encode($array));
                            break;
                        case "send":
                            Channel\Client::publish('send', array(
                                'thread_id' => $thread,
                                'class_id' => $class,
                                'speaker' => $user,
                                'con_id' => $con->id,
                                'mes_id' => $mes_id,
                                'emoji_id' => $emoji,
                                'count_id' => $count_id,
                                'emoji_type' => $emoji_type,
                                'type' => $type
                            ));
                            break;
                        default:
                            $array = [
                                'op' => 'send',
                                'status' => false,
                                'code' => 101,
                                'msg' => 'Illegal request'
                            ];
                            $con->send(json_encode($array));
                            break;
                    }
                } else {
                    $array = [
                        'op' => 'send',
                        'status' => false,
                        'code' => 107,
                        'msg' => 'User does not exist or not in the class'
                    ];
                    $con->send(json_encode($array));
                }
            } else {
                $array = [
                    'op' => 'send',
                    'status' => false,
                    'code' => 102,
                    'msg' => 'Thread does not exist'
                ];
                $con->send(json_encode($array));
            }
        } else {
            $array = [
                'op' => 'send',
                'status' => false,
                'code' => 103,
                'msg' => 'Class does not exist'
            ];
            $con->send(json_encode($array));
        }
    } else {
        $array = [
            'op' => 'send',
            'status' => false,
            'code' => 104,
            'msg' => 'Illegal request'
        ];
        $con->send(json_encode($array));
    }
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
