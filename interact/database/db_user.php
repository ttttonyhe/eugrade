<?php
//引入composer
require '../vendor/autoload.php';
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('users')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('users', array(
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'avatar' => 'string',
        'type' => 'integer',
        'pwd' => 'string',
        'class' => 'string',
        'date' => 'integer'
    ));
}

?>