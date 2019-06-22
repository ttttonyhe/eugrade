<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('threads')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    die();
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['class_id']) && !empty($_POST['thread_id']) && !empty($_POST['super'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $class_id = input($_POST['class_id']);
    $thread_id = input($_POST['thread_id']);
    $super = input($_POST['super']);


    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($class_id, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->find()->asArray();
        if (!!$array) {
            if ($array[0]['type'] == 2) {
                //更改 thread
                $class = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class_id)->find();
                if ((int)$class->super == (int)$super) {
                    $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread_id)->andWhere('belong_class', '=', (int)$class_id)->find()->asArray();
                    if (!!$array) {
                        Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread_id)->andWhere('belong_class', '=', (int)$class_id)->find()->delete();
                        //暂时不删除 messages 内容
                        $status = 1;
                        $code = 126;
                        $mes = 'Successfully deleted the thread';
                    } else {
                        $status = 0;
                        $code = 125;
                        $mes = 'Thread does not exist';
                    }
                } else {
                    $status = 0;
                    $code = 124;
                    $mes = 'Permission denied';
                }
            } else {
                $status = 0;
                $code = 105;
                $mes = 'Current user cannot be a student';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'User does not exist';
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
