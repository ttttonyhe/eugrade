<?php

//error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('marks')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('marks', array(
        'id' => 'integer',
        'class' => 'integer',
        'user' => 'integer',
        'type' => 'string',
        'marker' => 'integer'
    ));
}

if (!empty($_GET['form']) && !empty($_GET['marker'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $form = input($_GET['form']);
    $marker = input($_GET['marker']);

    //业务逻辑
    $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$marker)->find()->asArray();
    if (!!$array) {
        $array = array();
        $array_string = array();
        if ($form == 'class') { //获取标记的班级
            $array = Lazer::table('marks')->where('marker', '=', (int)$marker)->andWhere('type', '=', 'class')->findAll()->asArray('class');
            foreach ($array as $temp) {
                $array_string[] = $temp->class;
            }
        } else { //获取标记用户
            $array = Lazer::table('marks')->where('marker', '=', (int)$marker)->andWhere('type', '=', 'user')->findAll()->asArray('user');
            foreach ($array as $temp) {
                $array_string[] = $temp->user;
            }
        }
        //生成总字符串
        $array['combine'] = implode(',', $array_string);
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Marker not exist'
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
