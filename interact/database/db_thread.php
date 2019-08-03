<?php
//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('threads')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('threads', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'pin' => 'integer', //消息顶置
        'report' => 'string', //举报消息条段 ID
        'message_count' => 'integer'
    ));
}
?>