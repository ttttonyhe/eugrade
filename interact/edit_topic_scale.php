<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_topic.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['creator']) && !empty($_POST['scale']) && !empty($_POST['belong_topic']) && !empty($_POST['belong_class']) && ($_SESSION['logged_in_id'] == (int) $_POST['creator'])) {

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
    $id = input($_POST['creator']);
    $scale = input($_POST['scale']);
    $class = input($_POST['belong_class']);
    $topic = input($_POST['belong_topic']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->andWhere('super', '=', (int) $id)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $id)->find()->asArray();
        if (!!$array) {
            $type = input($_POST['type']);
            if (!!$type) { //复制等级划分
                $from = input($_POST['from']);
                if (!!$from && (int)$from !== (int)$topic) {
                    $array = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
                    $array_copy = Lazer::table('topics')->limit(1)->where('id', '=', (int) $from)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
                    if (!empty($array) && !empty($array_copy)) {
                        $scale = $array_copy[0]['scale'];
                        $row = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->andWhere('belong_class', '=', (int) $class)->find();
                        $row->scale = $scale;
                        $row->save();

                        $status = 1;
                        $code = 102;
                        $mes = 'Successfully copied the grading scale';
                    } else {
                        $status = 0;
                        $code = 105;
                        $mes = 'Topics do not exist';
                    }
                } else {
                    $status = 0;
                    $code = 114;
                    $mes = 'Cannot copy from nowhere or the same topic';
                }
            } else {
                $array = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->andWhere('belong_class', '=', (int) $class)->find()->asArray();
                if (!empty($array)) {
                    $row = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->andWhere('belong_class', '=', (int) $class)->find();
                    $row->scale = $scale;
                    $row->save();

                    $status = 1;
                    $code = 102;
                    $mes = 'Successfully edited the grading scale';
                } else {
                    $status = 0;
                    $code = 105;
                    $mes = 'Topic does not exist';
                }
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'The creator does not exist';
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
