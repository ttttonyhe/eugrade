<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('series')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('series', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'topics' => 'string'
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['series_id']) && !empty($_POST['super']) && !empty($_POST['class_id']) && ($_SESSION['logged_in_id'] == (int) $_POST['super'])) {

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
    $super = (int) input($_POST['super']);
    $class = (int) input($_POST['class_id']);
    $series = (int) input($_POST['series_id']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->andWhere('super', '=', (int) $super)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('series')->limit(1)->where('id', '=', (int) $series)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
        if (!empty($array)) {
            Lazer::table('series')->limit(1)->where('id', '=', (int) $series)->andWhere('belong_class', '=', (int) $class)->find()->delete();

            $status = 1;
            $code = 102;
            $mes = 'Successfully deleted a series';
        } else {
            $status = 0;
            $code = 106;
            $mes = 'This series does not exist in the class';
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
