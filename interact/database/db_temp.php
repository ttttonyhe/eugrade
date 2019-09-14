<?php 
//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;


//临时验证数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('temp')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('temp', array(
        'id' => 'integer', //自增id
        'k' => 'string', //唯一标记
        'v' => 'string', //内容
        'd' => 'integer', //过期时间
        'e' => 'string', //冗余
    ));
}