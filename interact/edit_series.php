<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_series.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['creator']) && !empty($_POST['name']) && !empty($_POST['belong_class']) && !empty($_POST['series_id']) && ($_SESSION['logged_in_id'] == (int) $_POST['creator'])) {

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
    $id = input($_POST['creator']);
    $class = input($_POST['belong_class']);
    $series = input($_POST['series_id']);
    $name = input($_POST['name']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->andWhere('super', '=', (int) $id)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('series')->limit(1)->where('id', '=', (int) $series)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
        if (!empty($array)) {
            $row = Lazer::table('series')->limit(1)->where('id', '=', (int) $series)->andWhere('belong_class', '=', (int) $class)->find();
            $row->name = $name;
            $row->save();

            $status = 1;
            $code = 102;
            $mes = 'Successfully edited the topic name';
        } else {
            $status = 0;
            $code = 105;
            $mes = 'Topic does not exist';
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
