<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('topics')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('topics', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'belong_series' => 'integer', //所属系列
        'series_order' => 'integer', //所属系列的排序位置
        'date' => 'integer',
        'name' => 'string',
        'candidate_count' => 'integer', //参与人数(存在数据的人数,不包含缺席)
        'scale' => 'string',
        'average' => 'string' //总平均数
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['creator']) && !empty($_POST['name']) && !empty($_POST['belong_class']) && !empty($_POST['belong_series']) && !empty($_POST['topic_id']) && ($_SESSION['logged_in_id'] == (int)$_POST['creator'])) {

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
    $id = input($_POST['creator']);
    $class = input($_POST['belong_class']);
    $series = input($_POST['belong_series']);
    $name = input($_POST['name']);
    $topic = input($_POST['topic_id']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->andWhere('super','=',(int)$id)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('series')->limit(1)->where('id', '=', (int)$series)->andWhere('belong_class','=',(int)$class)->find()->asArray();
        if (!!$array) {
            $array = Lazer::table('topics')->limit(1)->where('id', '=', (int)$topic)->andWhere('belong_series', '=', (int)$series)->find()->asArray();
            if (!empty($array)) {
                $row = Lazer::table('topics')->limit(1)->where('id', '=', (int)$topic)->andWhere('belong_series', '=', (int)$series)->find();
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
        } else {
            $status = 0;
            $code = 104;
            $mes = 'The series does not exist';
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
