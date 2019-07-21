<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('records')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('records', array(
        'id' => 'integer',
        'creator' => 'integer',
        'user_id' => 'integer',
        'belong_topic' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'score' => 'string', //包含浮点数
        'total' => 'string', //包含浮点数
        'percent' => 'string' //得分比浮点数
    ));
}

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['record']) && !empty($_POST['user_id']) && !empty($_POST['creator']) && !empty($_POST['belong_class']) && !empty($_POST['date']) && !empty($_POST['total']) && !empty($_POST['belong_topic']) && ($_SESSION['logged_in_id'] == (int) $_POST['creator'])) {

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
    $record = (int) input($_POST['record']);
    $user = (int) input($_POST['creator']);
    $date = (int) input($_POST['date']);
    $score = (string) input($_POST['score']);
    $total = (string) input($_POST['total']);
    $topic = (int) input($_POST['belong_topic']);
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

                $array = Lazer::table('records')->limit(1)->where('id', '=', (int) $record)->andWhere('belong_topic', '=', (int) $topic)->andWhere('user_id', '=', (int) $stu)->find()->asArray();
                if (!empty($array)) {
                    $t = Lazer::table('records')->limit(1)->where('id', '=', (int) $record)->find();
                    $t->set(array(
                        'date' => $date,
                        'score' => $score,
                        'total' => $total,
                        'percent' => (string) round((float) $score / (float) $total, 3)
                    ));
                    $t->save();

                    $status = 1;
                    $code = 102;
                    $mes = 'Successfully edited a record';
                } else {
                    $status = 0;
                    $code = 105;
                    $mes = 'Record of this member does not exist in the topic';
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
