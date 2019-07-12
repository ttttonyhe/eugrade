<?php

require '../vendor/autoload.php';
require_once '../vendor/workerman/workerman/Autoloader.php';
require_once '../vendor/workerman/channel/src/Server.php';
require_once '../vendor/workerman/channel/src/Client.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;
use Workerman\Worker;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('messages')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('messages', array(
        'id' => 'integer', //内容条段 id
        'speaker' => 'integer', //发送者
        'speaker_name' => 'string', //发送者名字,减少前端数据库请求
        'is_super' => 'integer', //发送者级别,减少前端数据库请求
        'belong_class' => 'integer', //主题对应班级
        'content' => 'string', //内容
        'thread' => 'integer', //班级下的主题 id
        'emoji_1' => 'integer', //添加 emoji1
        'emoji_2' => 'integer', //添加 emoji2
        'emoji_3' => 'integer', //添加 emoji3
        'img_url' => 'string', //类型为文本，但有图片附件
        'date' => 'integer', //发送时间
        'type' => 'string', //类型：文件 or 文本(+图片)
        'file_url' => 'string', //类型为文件时文件的 url
        'file_name' => 'string', //类型文文件时的文件名,用于判断展示图标
        'log' => 'integer'
    ));
}

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('logs')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('logs', array(
        'id' => 'integer', //内容条段 id
        'speaker' => 'integer', //发送者
        'speaker_name' => 'string', //发送者名字,减少前端数据库请求
        'belong_class' => 'integer', //主题对应班级
        'content' => 'string', //内容
        'thread' => 'integer', //班级下的主题 id
        'date' => 'integer', //发送时间
    ));
}

session_start();


$channel_server = new Channel\Server('127.0.0.1', 2206);
$worker = new Worker('websocket://127.0.0.1:2000');
$worker->count = 2;
// 全局群组到连接的映射数组
$group_con_map = array();

$worker->onWorkerStart = function () {
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('127.0.0.1', 2206);

    // 监听全局分组发送消息事件
    Channel\Client::on('send', function ($event_data) {
        $thread = $event_data['thread_id'];
        $class = $event_data['class_id'];
        $speaker = $event_data['speaker'];
        $speaker_name = $event_data['speaker_name'];
        $content = $event_data['message'];
        $array = [
            'op' => 'sent',
            'status' => true,
            'code' => 106,
            'speaker' => $speaker,
            'speaker_name' => $speaker_name,
            'content' => $content,
        ];
        global $group_con_map;
        if (isset($group_con_map[$thread])) {
            foreach ($group_con_map[$thread] as $con) {
                $con->send(json_encode($array));
            }
        }
    });
};

//发送消息
$worker->onMessage = function ($con, $data) {
    $data = json_decode($data, true);
    $cmd = $data['action'];
    $user = $data['speaker'];
    $user_name = $data['speaker_name'];
    $thread = $data['thread_id'];
    $class = $data['class_id'];
    $type = $data['type'];
    $content = $data['message'];

    if (!empty($user_name) && !empty($thread) && !empty($class) && !empty($user)) {
        $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->find()->asArray();
        if (!!$array) {
            $array = Lazer::table('threads')->limit(1)->where('id', '=', (int) $thread)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
            if (!!$array) {
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $user)->andWhere('name', '=', (string) $user_name)->find()->asArray();
                if (!!$array && in_array($class,explode(',',$array[0]['class']))) { //判断用户存在




                    if ($array[0]['type'] == 2) { //教师发送者
                        $is_super = 1;
                    } else { //学生发送者
                        $is_super = 0;
                    }

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
                                'type' => $type,
                                'is_super' => $is_super,
                                'message' => $content
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
                        'msg' => 'User does not exist'
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
    var_dump($group_con_map);
};

$worker->onConnect = function ($con) {
    $array = [
        'op' => 'connect',
        'status' => true
    ];
    $con->send(json_encode($array));
};

Worker::runAll();
