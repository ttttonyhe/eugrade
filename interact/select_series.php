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
        'candidate_count' => 'integer' //参与人数(存在数据的人数,不包含缺席),
    ));
}

if (!empty($_GET['class_id'])) {

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
    $class = input($_GET['class_id']);
    $type = input($_GET['type']);

    if (empty($type)) {
        //业务逻辑
        $array = Lazer::table('classes')->where('id', '=', $class)->findAll()->asArray();
        if (!!$array) {
            $array = Lazer::table('series')->where('belong_class', '=', (int) $class)->findAll()->asArray();
            for ($i = 0; $i < count($array); $i++) {
                $array[$i]['topics_info'] = Lazer::table('topics')->where('belong_series', '=', (int) $array[$i]['id'])->findAll()->asArray();
            }
        } else {
            $array = array(
                'status' => 0,
                'code' => 101,
                'mes' => 'Class not exist'
            );
        }
    } else {
        //业务逻辑
        $array = Lazer::table('series')->limit(1)->where('id', '=', (int) $class)->find()->asArray();
        if (!$array) {
            $array = array(
                'status' => 0,
                'code' => 101,
                'mes' => 'series not exist'
            );
        }
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
