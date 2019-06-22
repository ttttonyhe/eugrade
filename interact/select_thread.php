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

if (!empty($_GET['class_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $class = input($_GET['class_id']);

    //业务逻辑
    $array = Lazer::table('classes')->where('id', '=', $class)->findAll()->asArray();
    if (!!$array) {
        $array = Lazer::table('threads')->where('belong_class', '=', (int)$class)->findAll()->asArray();
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Class not exist'
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
