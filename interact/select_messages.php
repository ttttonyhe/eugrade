<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;


require 'database/db_message.php';

if (!empty($_GET['class_id']) && !empty($_GET['thread_id'])) {

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
    $class = input($_GET['class_id']);
    $thread = input($_GET['thread_id']);
    $type = input($_GET['type']);
    $mes_id = input($_GET['mes_id']);

    if (!!$type){ //请求单段
        $array = Lazer::table('messages')->where('id', '=', (int)$mes_id)->findAll()->asArray();
    }else{ //请求全部
        $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->find()->asArray();
            if (!!$array) {
                $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->find()->asArray();
                if (!!$array) {
                    $array = array();
                    if ($type == 'reverse') {
                        $array['mes'] = array_reverse(Lazer::table('messages')->where('thread', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->findAll()->asArray());
                    } else {
                        $array['mes'] = Lazer::table('messages')->where('thread', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->findAll()->asArray();
                    }

                    $temp_array = array();
                    foreach ($array['mes'] as $a) {
                        $temp_array[] = $a['speaker'];
                    }

                    $temp_array = array_flip($temp_array);
                    $temp_array = array_flip($temp_array);
                    $array['speakers_unique'] = implode(',',$temp_array);
                } else {
                    $array = array(
                        'status' => 0,
                        'code' => 102,
                        'mes' => 'Thread not exist'
                    );
                }
            } else {
                $array = array(
                    'status' => 0,
                    'code' => 101,
                    'mes' => 'Class not exist'
                );
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
