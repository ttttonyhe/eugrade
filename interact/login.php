<?php
error_reporting(E_ALL & ~E_NOTICE);

//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
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

if (!empty($_POST['email']) && !empty($_POST['password'])) {

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
    $email = input($_POST['email']);
    $pwd = input($_POST['password']);
    $user_id = 'invaild';

    //业务逻辑
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $array = Lazer::table('users')->where('email', '=', $email)->limit(1)->find()->asArray();
        if(count($array) >= 1 && $array[0]['pwd'] == md5(md5($pwd).md5($pwd))){
            $status = 1;
            $code = 103;
            $mes = 'Successfully logged in';
            $user_id = (int)$array[0]['id'];
            //设置登录 SESSION
            session_start();
            $_SESSION['logged_in_id'] = (int)$array[0]['id'];
        }else{
            $status = 0;
            $code = 102;
            $mes = 'Invalid email and password combination';
        }
    }else{
        $status = 0;
        $code = 101;
        $mes = 'Invalid email address';
    }

} else { //信息不全
    $status = 0;
    $code = 101;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes,
    'user_id' => $user_id
);

echo json_encode($return)

?>