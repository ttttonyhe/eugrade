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
if (!empty($_POST['class_id']) && !empty($_POST['thread_id']) && !empty($_POST['mes_id']) && !empty($_POST['emoji_id'])  && !empty($_SESSION['logged_in_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace("'","&#39;",$data);
        $data = str_replace('"',"&#34;",$data);
        return $data;
    }

    //获取参数
    $class_id = input($_POST['class_id']);
    $thread_id = input($_POST['thread_id']);
    $mes_id = input($_POST['mes_id']);
    $emoji_id = input($_POST['emoji_id']);


    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class_id)->find()->asArray();
    if (!$array) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread_id)->andWhere('belong_class', '=', (int)$class_id)->find()->asArray();
        if (!!$array) {
            $array = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread_id)->find()->asArray();
            if (!!$array) {
                function remove_one($key)
                {
                    if (!!$key) {
                        $key = (int)$key;
                        return (int)($key -= 1);
                    } else {
                        $key = 0;
                        return (int)($key -= 1);
                    }
                }
                switch ($emoji_id) {
                    case 1:
                        $emoji_1 = remove_one($array[0]['emoji_1']);
                        $emoji_2 = $array[0]['emoji_2'];
                        $emoji_3 = $array[0]['emoji_3'];
                        break;
                    case 2:
                        $emoji_2 = remove_one($array[0]['emoji_2']);
                        $emoji_1 = $array[0]['emoji_1'];
                        $emoji_3 = $array[0]['emoji_3'];
                        break;
                    case 3:
                        $emoji_3 = remove_one($array[0]['emoji_3']);
                        $emoji_1 = $array[0]['emoji_1'];
                        $emoji_2 = $array[0]['emoji_2'];
                        break;
                    default:
                        $emoji_1 = remove_one($array[0]['emoji_1']);
                        $emoji_2 = $array[0]['emoji_2'];
                        $emoji_3 = $array[0]['emoji_3'];
                        break;
                }

                $mes = Lazer::table('messages')->limit(1)->where('id', '=', (int)$mes_id)->andWhere('thread', '=', (int)$thread_id)->find();
                $mes->set(array(
                    'emoji_1' => $emoji_1,
                    'emoji_2' => $emoji_2,
                    'emoji_3' => $emoji_3
                ));
                $mes->save();
                $status = 1;
                $code = 126;
                $mes = 'Successfully deleted an emoji';
            } else {
                $status = 0;
                $code = 125;
                $mes = 'Message does not exist';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'Thread does not exist';
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
