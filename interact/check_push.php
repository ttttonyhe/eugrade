<?php

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('threads')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    Lazer::create('threads', array(
        'id' => 'integer',
        'creator' => 'integer',
        'belong_class' => 'integer',
        'date' => 'integer',
        'name' => 'string',
        'message_count' => 'integer'
    ));
}

session_start();

if (!empty($_GET['thread_count']) && !empty($_GET['thread_id']) && !empty($_SESSION['logged_in_id'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace("'", "&#39;", $data);
        $data = str_replace('"', "&#34;", $data);
        return $data;
    }

    //获取参数
    $thread = input($_GET['thread_id']);
    $count = input($_GET['thread_count']);

    $thread = explode('a',$thread);
    $count = explode('a',$count);

    if($thread == null){ //无法分隔数组，仅一个元素
        $thread = input($_GET['thread_id']);
        $count = input($_GET['thread_count']);
        $t = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread)->find();
        if((int)$count !== (int)$t->message_count){
            $array[0]['classid'] = $t->belong_class;
            $array[0]['thread'] = (int)$thread;
            $array[0]['index'] = 0;
            $array[0]['name'] = $t->name;
            $array[0]['count'] = $t->message_count;
            $array[0]['diff'] = abs((int)$t->message_count - $count);
        }
    }else{

    $j = 0;
    for($i = 0;$i<count($thread);$i++){
        $t = Lazer::table('threads')->limit(1)->where('id', '=', (int)$thread[$i])->find();
        if((int)$count[$i] !== (int)$t->message_count){
            $array[$j]['classid'] = $t->belong_class;
            $array[$j]['thread'] = (int)$thread[$i];
            $array[$j]['index'] = $i;
            $array[$j]['name'] = $t->name;
            $array[$j]['count'] = $t->message_count;
            $array[$j]['diff'] = abs((int)$t->message_count - $count[$i]);
            $j += 1;
        }
    }
}

}
echo json_encode($array);