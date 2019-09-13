<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_classes.php';
require 'database/pinyin.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['pwd']) && !empty($_POST['class_id']) && !empty($_POST['user_id']) && !empty($_POST['names'])) {

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
    $id = input($_POST['class_id']);
    $names = input($_POST['names']);
    $user = input($_POST['user_id']);
    $pwd = input($_POST['pwd']);

    //业务逻辑
    $check = new check;
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($id, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $user)->findAll()->asArray();
        if (!!$array) {


            if ($array[0]['type'] == 2) {
                $name_array = explode('|', $names);
                $status = true;

                //转换昵称为拼音，存入数组
                for ($i = 0; $i < count($name_array); $i++) {
                    if (empty($name_array[$i])) {
                        $status = false;
                    } else {
                        $names_array[] = Pinyin::getPinyin($name_array[$i]);
                    }
                }

                if ($status) {

                    for ($j = 0; $j < count($names_array); $j++) {

                        /* 注册用户 */
                        //用户信息
                        $count_n = 0;
                        $count_e = 0;
                        $name = $name_array[$j];
                        //原始输入用户名
                        $name_origin = $name_array[$j];
                        $email = $names_array[$j] . '@eugrade.com';
                        //判断用户名重复
                        if (!$check->name($name)) {
                            while (!$check->name($name) && $count_n <= 10) {
                                $count_n++;
                                $name = $name_array[$j] . rand(0, 999);
                            }
                        }
                        //判断邮箱重复
                        if (!$check->email($email)) {
                            while (!$check->email($email) && $count_e <= 10) {
                                $count_e++;
                                $email = $names_array[$j] . rand(0, 999) . '@eugrade.com';
                            }
                        }

                        //尝试 10 次添加随机数生成唯一用户名+邮箱失败
                        if ($count_e > 10 || $count_n > 10) {
                            $false[] = $name_origin; //保存注册失败的用户名
                        } else {
                            $pwd = (string) $pwd;
                            $this_id = Lazer::table('users')->findAll()->count() + 1;

                            //存入数据库
                            $row = Lazer::table('users');
                            $row->id = $this_id;
                            $row->name = $name;
                            $row->email = $email;
                            $row->pwd = md5(md5($pwd) . md5($pwd));
                            $row->class = (string) $id;
                            $row->type = 1;
                            $row->avatar = 'https://static.ouorz.com/default_avatar.png';
                            $row->date = time();
                            $row->save();

                            //储存注册数据
                            $return_array[$j]['name'] = $name_origin;
                            $return_array[$j]['email'] = $email;
                            /* 注册用户结束 */

                            /* 加入班级 */
                            $class = Lazer::table('classes')->limit(1)->where('id', '=', (int) $id)->find();
                            $class->set(array(
                                'member' => $class->member . ',' . (string) $this_id
                            ));
                            $class->save();
                            /* 加入班级结束 */
                        }
                    }

                    //存在注册失败的用户
                    if (!!$false) {
                        $status = 1;
                        $code = 126;
                        $info = array(
                            'fails' => $false,
                            'successes' => $return_array
                        );
                        $mes = 'Successfully created some members, but there are still some errors...';
                    } else {
                        //全部注册成功
                        $status = 1;
                        $code = 127;
                        $info = array(
                            'successes' => $return_array
                        );
                        $mes = 'Successfully created the members';
                    }
                } else {
                    $status = 0;
                    $code = 128;
                    $mes = 'System error';
                }
            } else {
                $status = 0;
                $code = 125;
                $mes = 'Only teachers can create members';
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
if (!!$info) {
    $return = array(
        'status' => $status,
        'code' => $code,
        'info' => $info,
        'mes' => $mes
    );
} else {
    $return = array(
        'status' => $status,
        'code' => $code,
        'mes' => $mes
    );
}
echo json_encode($return);
