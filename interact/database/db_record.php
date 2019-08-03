<?php
//引入composer
require '../vendor/autoload.php';
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
?>