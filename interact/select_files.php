<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('messages')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    die();
}

if (!empty($_GET['class_id']) && !empty($_GET['thread_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //获取参数
    $class = input($_GET['class_id']);
    $thread = input($_GET['thread_id']);

    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->find()->asArray();
    if (!!$array) {
        $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->find()->asArray();
        if (!!$array) {
            $array = array();
            $array['files'] = array_reverse(Lazer::table('messages')->where('thread', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->andWhere('type', '=', 'file')->findAll()->asArray());

            $temp_array = array();
            foreach ($array['files'] as $a) {
                $temp_array[] = $a['speaker'];
            }
            $array['speakers'] = implode(',', $temp_array);
        } else {
            $array = array(
                'status' => 0,
                'code' => 102,
                'mes' => 'Thread not exist'
            );
        }
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Class not exist'
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
