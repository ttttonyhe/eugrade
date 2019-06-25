<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
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

if (!empty($_GET['form']) && !empty($_GET['type']) && !empty($_GET['id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace("'","&#39;",$data);
        $data = str_replace('"',"&#34;",$data);
        return $data;
    }

    //获取参数
    $form = input($_GET['form']);
    $type = input($_GET['type']);
    $id = input($_GET['id']);

    //业务逻辑
    $array = Lazer::table('classes')->where('id', '=', $id)->findAll()->asArray();
    if (!!$array) {
        $array = array();
        if ($form == 'all') { //获取多个班级信息
            $all_id = explode(',',$id);
            foreach($all_id as $temp_id){
                $temp_array = Lazer::table('classes')->where('id', '=', (int)$temp_id)->find()->asArray();
                $array = array_merge($array,$temp_array);
            }
        } else { //获取单个班级信息
            $array = Lazer::table('classes')->where('id', '=', $id)->find()->asArray();
            $array = array(
                $type => $array[0][$type],
            );
        }
    } else {
        $array = array(
            'status' => 0,
            'code' => 101,
            'mes' => 'Class not exist'
        );
    }
} else {
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
