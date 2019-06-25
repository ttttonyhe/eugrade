<?php
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
use Lazer\Classes\Database as Lazer;

//数据库创建与判断
try {
    \Lazer\Classes\Helpers\Validate::table('users')->exists();
} catch (\Lazer\Classes\LazerException $e) { //不存在则创建
    die();
}

if (!empty($_GET['type']) && !empty($_GET['value'])) {
    //获取参数
    switch ($_GET['type']) {
        case 'name':
            $type = 1;
            break;
        case 'email':
            $type = 0;
            break;
        default:
            $type = 2;
            break;
    }

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

    //验证类
    class check
    {
        //验证用户名
        public function name()
        {
            $name = input($_GET['value']);
            $array = Lazer::table('users')->findAll()->asArray('name');
            if (array_key_exists($name, $array)) {
                return 0;
            } else {
                return 1;
            }
        }
        //验证邮箱
        public function email()
        {
            $email = input($_GET['value']);
            $array = Lazer::table('users')->findAll()->asArray('email');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (array_key_exists($email, $array)) {
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    //业务逻辑
    $check = new check();
    if ($type) {
        //验证用户名
        if ($type == 1) {
            $status = $check->name();
            $code = 102;
        } else { //无验证
            $status = 0;
            $code = 103;
        }
    } else { //验证邮箱
        $status = $check->email();
        $code = 104;
    }
} else { //信息不全
    $status = 0;
    $code = 101;
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code
);
echo json_encode($return);
