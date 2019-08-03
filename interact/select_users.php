<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_user.php';

if (!empty($_GET['type']) && !empty($_GET['id'])) {

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
    $form = input($_GET['form']);
    $type = input($_GET['type']);
    $id = input($_GET['id']);
    $mes = input($_GET['mes']);

    //业务逻辑
    $array = array();
    if ($form == 'all') { //获取多个用户信息
        $all_id = explode(',',$id);
        foreach ($all_id as $temp_id) {
            $temp_array = Lazer::table('users')->limit(1)->where('id', '=', (int)$temp_id)->find()->asArray();
            $temp_array[0] = array_diff_key($temp_array[0], ['pwd' => 'whatever']); //删除 pwd 键
            $array = array_merge($array, $temp_array); //拼接数组
        }
    } elseif(!empty($mes)) { //消息室获取用户信息
        $all_id = explode(',',$id);
        foreach ($all_id as $temp_id) {
            $temp_array = Lazer::table('users')->limit(1)->where('id', '=', (int)$temp_id)->find()->asArray();
            $array[0][$temp_array[0]['id']] = $temp_array[0]['avatar'];
            $array[1][$temp_array[0]['id']] = $temp_array[0]['name'];
        }
    }else{
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find()->asArray();
        $array[0] = array_diff_key($array[0], ['pwd' => 'whatever']); //删除 pwd 键
        $array = array(
            $type => $array[0][$type],
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
