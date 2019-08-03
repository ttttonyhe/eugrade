<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_mark.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['type']) && !empty($_POST['marker'])) {

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
    $type = input($_POST['type']);
    $stu = input($_POST['stu_id']);
    $class = input($_POST['class_id']);
    $marker = input($_POST['marker']);

    if ($type == 'user') {
        $type = 1;
        $array = Lazer::table('users')->findAll()->asArray('id');
        if (!array_key_exists((int)$stu, $array) || !array_key_exists((int)$marker, $array) || empty($stu)) {
            $c_status = 0;
            $code = 124;
            $mes = 'User or marker does not exist';
        } else {
            $c_status = 1;
        }
    } elseif ($type == 'class') {
        $type = 0;
        $array_1 = Lazer::table('classes')->findAll()->asArray('id');
        $array_2 = Lazer::table('users')->findAll()->asArray('id');
        if (!array_key_exists((int)$class, $array_1) || !array_key_exists((int)$marker, $array_2) || empty($class)) {
            $c_status = 0;
            $code = 123;
            $mes = 'Class or marker does not exist';
        } else {
            $c_status = 1;
        }
    } else {
        $status = 0;
        $code = 121;
        $mes = 'Illegal request';
    }

    if ($c_status) {

        //业务逻辑
        if ($type) {


            //插入 marks
            $mark = Lazer::table('marks')->limit(1)->where('stu', '=', (int)$stu)->andWhere('marker', '=', (int)$marker)->andWhere('type', '=', 'user')->find()->asArray();
            if (empty($mark[0])) {
                $row = Lazer::table('marks');
                $row->id = Lazer::table('marks')->findAll()->count() + 1;
                $row->user = (int)$stu;
                $row->type = 'user';
                $row->marker = (int)$marker;
                $row->save();
                $status = 1;
            $code = 126;
            $mes = 'Successfully marked';
            } else {
            $status = 0;
            $code = 105;
            $mes = 'This user has been marked by you';
        }

    }else{


        //插入 marks
        $mark = Lazer::table('marks')->limit(1)->where('class', '=', (int)$class)->andWhere('marker', '=', (int)$marker)->andWhere('type', '=', 'class')->find()->asArray();
        if (empty($mark[0])) {
            $row = Lazer::table('marks');
            $row->id = Lazer::table('marks')->findAll()->count() + 1;
            $row->class = (int)$class;
            $row->type = 'class';
            $row->marker = (int)$marker;
            $row->save();
            $status = 1;
            $code = 126;
            $mes = 'Successfully marked';
        } else {
        $status = 0;
        $code = 105;
        $mes = 'This class has been marked by you';
    }

    }
        
    } else {
        $status = 0;
        $code = $code;
        $mes = $mes;
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
