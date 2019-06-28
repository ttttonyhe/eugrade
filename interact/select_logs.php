<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('logs')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('logs', array(
        'speaker' => 'string', //发送者
        'operator' => 'string', //操作者
        'thread' => 'integer', //主题
        'content' => 'string', //内容
        'operation' => 'string', //操作
        'date' => 'integer' //时间
    ));
}
session_start();
if (!empty($_GET['thread_id'])) {

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

    //获取参数
    $thread = input($_GET['thread_id']);

    $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $_SESSION['logged_in_id'])->find();
    if ($array->type == 2) {
        $array = Lazer::table('threads')->limit(1)->where('id', '=', (int) $thread)->find()->asArray();
        if (!!$array) {
            $array = Lazer::table('logs')->where('thread', '=', (int) $thread)->findAll()->asArray();
        } else {
            $array = array(
                'status' => 0,
                'code' => 102,
                'mes' => 'Thread not exist'
            );
        }
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Only teachers can view the log'
        );
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
