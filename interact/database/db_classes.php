<?php 

//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('classes')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('classes', array(
        'id' => 'integer',
        'name' => 'string',
        'des' => 'string',
        'img' => 'string',
        'count' => 'integer',
        'super' => 'integer',
        'member' => 'string',
        'date' => 'integer'
    ));
}
?>