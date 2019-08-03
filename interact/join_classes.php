<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_classes.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['class_id']) && !empty($_POST['stu_id'])) {

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
    $id = input($_POST['class_id']);
    $id = str_replace('cxk', '', $id);
    $stu = input($_POST['stu_id']);

    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($id, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$stu)->findAll()->asArray();
        if (!!$array) {
            //更改 class
            $class = Lazer::table('classes')->limit(1)->where('id', '=', (int)$id)->find();
            if (!in_array($stu, explode(',', $class->member))) {
                $class->set(array(
                    'member' => $class->member . ',' . (string)$stu
                ));
                $class->save();

                //更改管理员的 class 字段
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$stu)->find();
                if (!empty($array->class)) {
                    if (!in_array($id, explode(',', $array->class))) {
                        $array->set(array(
                            'class' => $array->class . ',' . $id
                        ));
                        $array->save();
                    }
                } else {
                    $array->set(array(
                        'class' => (string)$id
                    ));
                    $array->save();
                }

                $status = 1;
                $code = 102;
                $mes = 'Successfully joined a class';
            } else {
                $status = 0;
                $code = 112;
                $mes = 'You are already in the class';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'User does not exist';
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
