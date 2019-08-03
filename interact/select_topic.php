<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_topic.php';

require 'database/db_record.php';

if (!empty($_GET['topic_id'])) {

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
    $topic = input($_GET['topic_id']);

    $array = Lazer::table('topics')->where('id', '=', (int) $topic)->find()->asArray();
    $array['records'] = Lazer::table('records')->where('belong_topic', '=', (int) $topic)->findAll()->asArray();
    $total = 0;
    $array_1 = Lazer::table('records')->andWhere('belong_topic', '=', (int) $topic)->findAll()->asArray();
    for ($i = 0; $i < count($array_1); $i++) { //当前 topic 的总和 score
        $total += (float) $array_1[$i]['score'];
    }
    if(count($array_1) !== 0){
        $array[0]['average'] = $total / count($array_1);
    }else{
        $array[0]['average'] = 0;
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
