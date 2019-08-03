<?php
//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;


//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('marks')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('marks', array(
        'id' => 'integer',
        'class' => 'integer',
        'user' => 'integer',
        'type' => 'string',
        'marker' => 'integer'
    ));
}

?>