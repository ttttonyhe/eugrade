<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;
use Qiniu\Auth;

require 'database/db_user.php';

if (!empty($_GET['user']) && !empty($_GET['email'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $id = input($_GET['user']);
    $email = input($_GET['email']);

    //业务逻辑
    $a = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->andWhere('email', '=', $email)->find();
    if (!!$a->type) {
        $bucket = 'xxx';
        $accessKey = 'xxx';
        $secretKey = 'xxx';
        $auth = new Auth($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket);
        $array['key'] = $upToken;
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'User not exist'
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
