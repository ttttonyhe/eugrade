<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('classes')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('classes', array(
        'id' => 'integer',
        'name' => 'string',
        'des' => 'string',
        'img' => 'string',
        'count' => 'integer',
        'super' => 'integer',
        'member' => 'string',
        'date' => 'integer'
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['class_id']) && !empty($_POST['super']) && !empty($_POST['type'])) {

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
    $id = input($_POST['class_id']);
    $super = input($_POST['super']);
    $name = input($_POST['name']);
    $des = input($_POST['des']);
    $type = input($_POST['type']);
    $url = input($_POST['url']);

    if ($type == 'info') { //修改信息

        if (!empty($_POST['name']) && !empty($_POST['des'])) {

            //业务逻辑
            $array = Lazer::table('classes')->findAll()->asArray('id');
            if (!array_key_exists($id, $array)) {
                $status = 0;
                $code = 101;
                $mes = 'Class does not exist';
            } else {
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->findAll()->asArray();
                if (!!$array) {
                    if ($array[0]['type'] == 2) {
                        //更改 class
                        $class = Lazer::table('classes')->limit(1)->where('id', '=', (int)$id)->find();
                        if((int)$class->super == (int)$super){
                        $class->set(array(
                            'des' => $des,
                            'name' => $name
                        ));
                        $class->save();

                        $status = 1;
                        $code = 102;
                        $mes = 'Successfully edited the class';
                    }else{
                        $status = 0;
                        $code = 124;
                        $mes = 'Permission denied';
                    }
                    } else {
                        $status = 0;
                        $code = 105;
                        $mes = 'Current user cannot be a student';
                    }
                } else {
                    $status = 0;
                    $code = 104;
                    $mes = 'User does not exist';
                }
            }
        } else {
            $status = 0;
            $code = 122;
            $mes = 'Illegal request';
        }
    } else { //修改头像

        if (!empty($_POST['url'])) {
            //业务逻辑
            $array = Lazer::table('classes')->findAll()->asArray('id');
            if (!array_key_exists($id, $array)) {
                $status = 0;
                $code = 101;
                $mes = 'Class does not exist';
            } else {
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->findAll()->asArray();
                if (!!$array) {
                    if ($array[0]['type'] == 2) {
                        //更改 class
                        $class = Lazer::table('classes')->limit(1)->where('id', '=', (int)$id)->find();
                        if((int)$class->super == (int)$super){
                        $class->set(array(
                            'img' => $url
                        ));
                        $class->save();

                        $status = 1;
                        $code = 102;
                        $mes = 'Successfully edited the class avatar';
                    }else{
                        $status = 0;
                        $code = 124;
                        $mes = 'Permission denied';
                    }
                    } else {
                        $status = 0;
                        $code = 105;
                        $mes = 'Current user cannot be a student';
                    }
                } else {
                    $status = 0;
                    $code = 104;
                    $mes = 'User does not exist';
                }
            }
        } else {
            $status = 0;
            $code = 116;
            $mes = 'Illegal request';
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
