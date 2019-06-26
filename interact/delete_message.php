<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

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
        'file_name' => 'string' //类型文文件时的文件名,用于判断展示图标
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['user']) && !empty($_POST['mes_id']) && !empty($_POST['thread_id']) && !empty($_POST['class_id']) && !empty($_SESSION['logged_in_id'])) {

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
    $super = input($_POST['user']);
    $mes_id = input($_POST['mes_id']);
    $class = input($_POST['class_id']);
    $thread = input($_POST['thread_id']);


    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($class, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$_SESSION['logged_in_id'])->find()->asArray();
        if (!!$array) { //判断操作的用户存在


            if ($array[0]['type'] == 2) { //教师操作
                $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->andWhere('super', '=', (int)$_SESSION['logged_in_id'])->find();
                $speak = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                if (!!$array->id) { //必须为班级管理员或当前用户才可操作
                    $status = 1;
                } else {
                    if ((int)$speak->speaker !== (int)$super) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }
                }
            } else { //学生操作
                $array = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                if ((int)$array->speaker !== (int)$super) { //必须为发送者才可操作
                    $status = 0;
                } elseif ($_SESSION['logged_in_id'] == (int)$super) {
                    $status = 1;
                } else {
                    $status = 0;
                }
            }

            if ($status) {

                $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->find();
                if (!!$array->name) { //判断主题存在

                    $array = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread)->find();
                    if (!empty($array->speaker)) {

                        $array->delete();

                        $t = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->find();
                        $t->set(array(
                            'message_count' => $t->message_count - 1
                        ));
                        $t->save();

                        $status = 1;
                        $code = 133;
                        $mes = 'Successfully deleted a message';
                    } else {
                        $status = 0;
                        $code = 120;
                        $mes = 'The content can not be empty';
                    }
                } else {
                    $status = 0;
                    $code = 122;
                    $mes = 'The thread does not exist';
                }
            } else {
                $status = 0;
                $code = 114;
                $mes = 'The editor does not match the speaker';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'The speaker does not exist';
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
