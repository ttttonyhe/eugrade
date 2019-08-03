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
    $last = input($_GET['last']);

    if (!!$last) { //非首次请求
        $last_id = array_reverse(Lazer::table('messages')->where('thread', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->findAll()->asArray())[0]['id'];
        if ((int)$last !== (int)$last_id) {

            
            //业务逻辑
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

                    $array['update']['status'] = 1;
                    $array['update']['last'] = $last_id;

                    $last_speaker = 0;
                    $temp_array = array();
                    foreach ($array['mes'] as $a) {
                        if($a['speaker'] !== $last_speaker){
                            $temp_array[] = $a['speaker'];
                        }
                        $last_speaker = $a['speaker'];
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


        } else {

            $array['update']['status'] = 0;
            $array['update']['last'] = $last_id;
        }

    }else{ //首次请求
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
