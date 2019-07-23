<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('topics')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('topics', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'belong_series' => 'integer', //所属系列
        'series_order' => 'integer', //所属系列的排序位置
        'date' => 'integer',
        'name' => 'string',
        'candidate_count' => 'integer', //参与人数(存在数据的人数,不包含缺席)
        'scale' => 'string'
    ));
}

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
        'percent' => 'string' //得分比 浮点数
    ));
}

if (!empty($_GET['topic_ids']) && !empty($_GET['type'])) {

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
    $topic = input($_GET['topic_ids']);
    $type = input($_GET['type']);
    $user = input($_GET['user']);
    $topic_array = explode(',', $topic);
    $array = array();

    for ($k = 0; $k < count($topic_array); $k++) {
        $total = 0;
        $total_total = 0;
        if($type == 'all'){
            $array_1 = Lazer::table('records')->andWhere('belong_topic', '=', (int) $topic_array[$k])->findAll()->asArray();
        }else{
            $array_1 = Lazer::table('records')->limit(1)->andWhere('belong_topic', '=', (int) $topic_array[$k])->andWhere('user_id','=',(int)$user)->findAll()->asArray();
        }
        for ($i = 0; $i < count($array_1); $i++) { //当前 topic 的总和 score
            $total += (float) $array_1[$i]['score'];
            $total_total += (float) $array_1[$i]['total'];
        }
        if (count($array_1) !== 0) {
            $array[$k][0] = $total / count($array_1);
            $array[$k][1] = $total / $total_total;
        } else {
            $array[$k][0] = 0;
            $array[$k][1] = 0;
        }
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
