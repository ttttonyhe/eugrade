<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_record.php';

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['user_id']) && !empty($_POST['creator']) && !empty($_POST['belong_class']) && !empty($_POST['name']) && !empty($_POST['date']) && !empty($_POST['total']) && !empty($_POST['belong_topic']) && ($_SESSION['logged_in_id'] == (int) $_POST['creator'])) {

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
    $user = input($_POST['creator']);
    $name = input($_POST['name']);
    $date = (int) input($_POST['date']);
    $score = (float) input($_POST['score']);
    $total = (float) input($_POST['total']);
    $topic = input($_POST['belong_topic']);
    $class = (int) input($_POST['belong_class']);
    $stu = (int) input($_POST['user_id']);

    //业务逻辑
    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int) $class)->andWhere('super', '=', (int) $user)->find();
    if (!$array->super) {
        $status = 0;
        $code = 101;
        $mes = 'Permission denied';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $user)->find()->asArray();
        if (!!$array) {

            $array = Lazer::table('users')->limit(1)->where('id', '=', (int) $stu)->find()->asArray();
            if (in_array($class, explode(',', $array[0]['class']))) {

                $array = Lazer::table('records')->limit(1)->where('belong_topic', '=', (int) $topic)->andWhere('name', '=', (string) $name)->find()->asArray();
                if (empty($array)) {
                    //建立 thread
                    $this_id = Lazer::table('records')->lastId() + 1;
                    $row = Lazer::table('records');
                    $row->id = $this_id;
                    $row->name = (string) $name;
                    $row->belong_topic = (int) $topic;
                    $row->creator = (int) $user;
                    $row->user_id = (int) $stu;
                    $row->date = (int) $date;
                    $row->score = (string) $score;
                    $row->total = (string) $total;
                    $row->percent = (string) round($score / $total, 3);
                    $row->save();

                    //获取当前 topic
                    $t = Lazer::table('topics')->limit(1)->where('id', '=', (int) $topic)->find();
                    $t->set(array(
                        'candidate_count' => $t->candidate_count + 1
                    ));
                    $t->save();

                    $status = 1;
                    $code = 102;
                    $mes = 'Successfully added a record';
                } else {
                    $status = 0;
                    $code = 105;
                    $mes = 'Record of this member has been created in the topic';
                }
            } else {
                $status = 0;
                $code = 104;
                $mes = 'The student does not exist in the class';
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
