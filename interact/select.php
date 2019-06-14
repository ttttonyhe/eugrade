<?php
//require composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)).'/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try{
    \Lazer\Classes\Helpers\Validate::table('users')->exists();
} catch(\Lazer\Classes\LazerException $e){ //不存在则创建
    Lazer::create('users', array(
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'avatar' => 'string'
    ));
}

$i = 0;
$row = Lazer::table('users')->findAll();
foreach($row as $d){
    if($i == 0){
        $o = json_encode($d);
        $i = 1;
    }else{
        $o = $o.','.json_encode($d);
    }
}

echo $o;

?>