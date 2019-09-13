<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_thread.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['class_id']) && !empty($_POST['thread_id']) && !empty($_POST['user']) && !empty($_POST['mes_id']) && !empty($_POST['type']) && ($_SESSION['logged_in_id'] == (int) $_POST['user'])) {

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
    $class_id = input($_POST['class_id']);
    $thread_id = input($_POST['thread_id']);
    $user = input($_POST['user']);
    $mes_id = input($_POST['mes_id']);
    $type = input($_POST['type']);


    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($class_id, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $user)->find()->asArray();
        if (!!$array && $array[0]['type'] == 2) {
            $array = Lazer::table('threads')->limit(1)->where('id', '=', (int) $thread_id)->andWhere('belong_class', '=', (int) $class_id)->find()->asArray();
            if (!!$array) {
                $array = Lazer::table('messages')->limit(1)->where('id', '=', (int) $mes_id)->andWhere('thread', '=', (int) $thread_id)->find()->asArray();
                if (!!$array || ($type == 'remove')) {
                    if (!empty($array[0]['content']) || ($type == 'remove')) {
                        if ($type == 'remove') {
                            $mes_id = 0;
                        }
                        $thread = Lazer::table('threads')->limit(1)->where('id', '=', (int) $thread_id)->andWhere('belong_class', '=', (int) $class_id)->find();
                        $thread->set(array(
                            'pin' => (int) $mes_id
                        ));
                        $thread->save();

                        $status = 1;
                        $code = 127;
                        $mes = 'Successfully pinned a message';
                    } else {
                        $status = 0;
                        $code = 128;
                        $mes = 'Message content can not be empty';
                    }
                } else {
                    $status = 0;
                    $code = 126;
                    $mes = 'Message does not exist';
                }
            } else {
                $status = 0;
                $code = 125;
                $mes = 'Thread does not exist';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'User does not exist or not a teacher';
        }
    }
} else {
    $status = 0;
    $code = 103;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes
);
echo json_encode($return);
