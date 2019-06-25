<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('messages')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('messages', array(
        'id' => 'integer', //内容条段 id
        'speaker' => 'integer', //发送者
        'speaker_name' => 'string', //发送者名字,减少前端数据库请求
        'is_super' => 'integer', //发送者级别,减少前端数据库请求
        'belong_class' => 'integer', //主题对应班级
        'content' => 'string', //内容
        'thread' => 'integer', //班级下的主题 id
        'emoji_1' => 'integer', //添加 emoji1
        'emoji_2' => 'integer', //添加 emoji2
        'emoji_3' => 'integer', //添加 emoji3
        'img_url' => 'string', //类型为文本，但有图片附件
        'date' => 'integer', //发送时间
        'type' => 'string', //类型：文件 or 文本(+图片)
        'file_url' => 'string', //类型为文件时文件的 url
        'file_name' => 'string' //类型文文件时的文件名,用于判断展示图标
    ));
}

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

    $array = Lazer::table('classes')->limit(1)->where('id', '=', (int)$class)->find()->asArray();
    if (!!$array) {
        $array = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->find()->asArray();
        if (!!$array) {
            $array = array();
            $array['files'] = array_reverse(Lazer::table('messages')->where('thread', '=', (int)$thread)->andWhere('belong_class', '=', (int)$class)->andWhere('type', '=', 'file')->findAll()->asArray());

            $last_speaker = 0;
            $temp_array = array();
            foreach ($array['files'] as $a) {
                if ($a['speaker'] !== $last_speaker) {
                    $temp_array[] = $a['speaker'];
                }
                $last_speaker = $a['speaker'];
            }
            $array['speakers'] = implode(',', $temp_array);
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
    $array = array(
        'status' => 0,
        'code' => 103,
        'mes' => 'Illegal request'
    );
}
echo json_encode($array);
