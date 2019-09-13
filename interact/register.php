<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_user.php';

if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['name']) && !empty($_POST['type'])) {

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

    //验证类
    class check
    {
        //验证用户名
        public function name($name)
        {
            $name = $name;
            $array = Lazer::table('users')->findAll()->asArray('name');
            if (array_key_exists($name, $array)) {
                return 0;
            } else {
                return 1;
            }
        }
        //验证邮箱
        public function email($email)
        {
            $email = $email;
            $array = Lazer::table('users')->findAll()->asArray('email');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (array_key_exists($email, $array)) {
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }


    //获取参数
    $pwd = input($_POST['password']);
    $name = input($_POST['name']);
    $email = input($_POST['email']);
    $type = input($_POST['type']);
    $code = input($_POST['invite']);

    //业务逻辑
    $check = new check();
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($check->email($email)) {
            if ($check->name($name)) {
                //判断注册类型，教师账户需要输入邀请码
                if($type == 'a' || ($type == 'b' && $code == 'eugrade_teacher')){
                    
                    $this_id = Lazer::table('users')->lastId() + 1;
                    $row = Lazer::table('users');
                    $row->id = $this_id;
                    $row->name = $name;
                    $row->email = $email;
                    $row->pwd = md5(md5($pwd).md5($pwd));
                    if($type == 'b'){
                        $row->type = 2;
                    }else{
                        $row->type = 1;
                    }
                    $row->avatar = 'https://static.ouorz.com/default_avatar.png';
                    $row->date = time();
                    $row->save();
                $status = 1;
                $code = 104;
                $mes = Lazer::table('users')->lastId();

                //自动登录
                session_start();
                $_SESSION['logged_in_id'] = (int)$this_id;

                }else {
                    $status = 0;
                    $code = 105;
                    $mes = 'Invaild invite code';
                }
            } else {
                $status = 0;
                $code = 103;
                $mes = 'Email or username has been used';
            }
        } else {
            $status = 0;
            $code = 102;
            $mes = 'Email or username has been used';
        }
    } else {
        $status = 0;
        $code = 101;
        $mes = 'Invalid email address';
    }
} else {
    $status = 0;
    $code = 105;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes
);
echo json_encode($return);
