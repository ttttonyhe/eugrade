<?php
//引入composer
require '../vendor/autoload.php';
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