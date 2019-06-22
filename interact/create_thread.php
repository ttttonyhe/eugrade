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
    Lazer::create('threads', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'message_count' => 'integer'
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['creator']) && !empty($_POST['name']) && !empty($_POST['belong_class']) && ($_SESSION['logged_in_id'] == (int)$_POST['creator'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $id = input($_POST['creator']);
    $name = input($_POST['name']);
    $class = input($_POST['belong_class']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find()->asArray();
        if (!!$array) {
            if ($array[0]['type'] == 2) {
                //建立 thread
                $this_id = Lazer::table('threads')->findAll()->count() + 1;
                $row = Lazer::table('threads');
                $row->id = $this_id;
                $row->name = $name;
                $row->belong_class = (int)$class;
                $row->creator = (int)$id;
                $row->date = time();
                $row->message_count = 0;
                $row->save();

                $status = 1;
                $code = 102;
                $mes = 'Successfully created a thread';
            } else {
                $status = 0;
                $code = 105;
                $mes = 'Creator of the thread cannot be a student';
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
