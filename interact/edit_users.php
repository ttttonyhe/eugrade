<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

require 'database/db_user.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['user_id']) && !empty($_POST['type']) && isset($_SESSION['logged_in_id'])) {

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
    $id = input($_POST['user_id']);
    $name = input($_POST['name']);
    $email = input($_POST['email']);
    $pwd = $_POST['pwd'];
    $type = input($_POST['type']);
    $url = input($_POST['url']);
    $super = input($_POST['super']);

    if ($type == 'info') { //修改信息

        if (!empty($_POST['name']) && !empty($_POST['email'])) {

            //业务逻辑
            $array = Lazer::table('users')->findAll()->asArray('id');
            if (!array_key_exists($id, $array)) {
                $status = 0;
                $code = 101;
                $mes = 'User does not exist';
            } else {
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$_SESSION['logged_in_id'])->find();
                if ($array->type !== 2) { //非教师操作
                    //更改 user
                    $user = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find();
                    if ((int)$user->id == (int)$_SESSION['logged_in_id']) { //判断账户拥有者操作
                        if (!empty($pwd)) { //判断修改密码
                            if (strlen($pwd) < 6) {
                                $status = 0;
                                $code = 131;
                                $mes = 'Password must has a length more than 6';
                            } else {
                                $user->set(array(
                                    'name' => $name,
                                    'email' => $email,
                                    'pwd' => md5(md5($pwd) . md5($pwd))
                                ));
                                $user->save();
                                $status = 1;
                                $code = 132;
                                $mes = 'Successfully edited user info';
                            }
                        } else { //不修改密码
                            $user->set(array(
                                'name' => $name,
                                'email' => $email
                            ));
                            $user->save();
                            $status = 1;
                            $code = 133;
                            $mes = 'Successfully edited user info';
                        }
                    } else {
                        $status = 0;
                        $code = 124;
                        $mes = 'Permission denied';
                    }
                } else {
                    //更改 user
                    $teacher = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->find();
                    if ((int)$teacher->id == (int)$_SESSION['logged_in_id'] && $teacher->type == 2) { //判断当前教师操作
                        if (empty($pwd)) { //判断是否要求修改密码
                            $user = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find();
                            $user->set(array(
                                'name' => $name,
                                'email' => $email
                            ));
                            $user->save();
                            $status = 1;
                            $code = 136;
                            $mes = 'Successfully edited user info';
                        } else {
                            $status = 0;
                            $code = 135;
                            $mes = 'A teacher cannot edit the password';
                        }
                    } else {
                        $status = 0;
                        $code = 124;
                        $mes = 'Permission denied';
                    }
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
            $array = Lazer::table('users')->findAll()->asArray('id');
            if (!array_key_exists($id, $array)) {
                $status = 0;
                $code = 101;
                $mes = 'User does not exist';
            } else {
                if (!empty($super)) {
                    //更改 user
                    $teacher = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->find();
                    if (!!$teacher->type && $teacher->type == 2) {
                        if ((int)$teacher->id == (int)$_SESSION['logged_in_id']) { //判断当前教师操作
                            $user = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find();
                            $user->set(array(
                                'avatar' => $url
                            ));
                            $user->save();
                            $status = 1;
                            $code = 136;
                            $mes = 'Successfully edited user avatar';
                        } else {
                            $status = 0;
                            $code = 124;
                            $mes = 'Permission denied';
                        }
                    } else {
                        $status = 0;
                        $code = 144;
                        $mes = 'Permission denied';
                    }
                } else {
                    $user = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find();
                    if ((int)$user->id == (int)$_SESSION['logged_in_id']) { //判断当前用户操作
                        $user = Lazer::table('users')->limit(1)->where('id', '=', (int)$id)->find();
                        $user->set(array(
                            'avatar' => $url
                        ));
                        $user->save();
                        $status = 1;
                        $code = 146;
                        $mes = 'Successfully edited user avatar';
                    } else {
                        $status = 0;
                        $code = 145;
                        $mes = 'Permission denied';
                    }
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
