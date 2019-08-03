<?php 
//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('series')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('series', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'topics' => 'string'
    ));
}
