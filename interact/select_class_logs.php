<?php

//error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_log.php';

session_start();
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

    $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $_SESSION['logged_in_id'])->find();
    if ($array->type == 2) {
        $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->find()->asArray();
        $threads = Lazer::table('threads')->where('belong_class', '=', (int) $class)->findAll()->asArray();
        if (!!$array) {
            $array = array();
            //拼接当前班级所有 threads 的 logs
            for($i=0;$i<count($threads);$i++){
                $array[]['divide'] = $threads[$i]['name']; //插入 thread 名
                $array = array_merge($array,Lazer::table('logs')->where('thread', '=', (int) $threads[$i]['id'])->findAll()->asArray());
            }
        } else {
            $array = array(
                'status' => 0,
                'code' => 102,
                'mes' => 'Class not exist'
            );
        }
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Only teachers can view the log'
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
