<?php

/*
* 验证临时验证数据
* @author TonyHe <he@holptech.com>
* @package Lazer\Classes\Database
* @return json results
*/

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_temp.php';


session_start();

/*
    判断参数是否齐全
    //请求
    @var string token 验证标记
    @var string name 验证目的
    @var string input 验证内容
    @var integer current_date 当前时间
*/
if (!empty($_POST['input']) && !empty($_POST['token']) && !empty($_POST['name'])) {

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


    /* 业务逻辑开始 */

    //获取参数
    $token = input($_POST['token']);
    $name = input($_POST['name']);
    $input = input($_POST['input']);
    $time = time();

    //判断验证目的
    switch ($name) {
        case 'reset_pwd': //重设密码
            $email = input($_POST['email']);
            if (!empty($email)) { //email 地址不为空

                $array = Lazer::table('temp')->limit(1)->where('k', '=', (string) $token)->find()->asArray();
                $array_user = Lazer::table('users')->limit(1)->where('email', '=', (string) $email)->find()->asArray();

                if (!!$array && !!$array_user) { //存在验证与用户
                    if ((int) $time <= (int) $array[0]['d']) { //未过期
                        if ((int) $array[0]['v'] == (int) $input) { //判断正确

                            //重设密码
                            $t = Lazer::table('users')->limit(1)->where('email', '=', (string) $email)->find();
                            $t->set(array(
                                'pwd' => md5(md5('12345678') . md5('12345678')),
                            ));
                            $t->save();

                            //删除验证记录
                            Lazer::table('temp')->limit(1)->where('k', '=', (string) $token)->delete();

                            $status = 1;
                            $code = 107;
                            $mes = 'Successfully reset your password to 12345678';
                        } else {
                            $status = 0;
                            $code = 111;
                            $mes = 'Incorrect verification code';
                        }
                    } else {
                        $status = 0;
                        $code = 106;
                        $mes = 'This request has been depreciated';
                    }
                } else {
                    $status = 0;
                    $code = 108;
                    $mes = 'Illegal request';
                }
            } else {
                $status = 0;
                $code = 110;
                $mes = 'Illegal request';
            }
            break;
        default:
            $status = 0;
            $code = 104;
            $mes = 'Illegal request';
            break;
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
