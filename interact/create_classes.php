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

session_start();

//判断发送参数是否齐全，请求创建班级的用户是否为当前登录用户
if (!empty($_POST['name']) && !empty($_POST['des']) && !empty($_POST['super']) && ($_SESSION['logged_in_id'] == (int)$_POST['super'])) {

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
    $des = input($_POST['des']);
    $name = input($_POST['name']);
    $super = input($_POST['super']);

    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('name');
    if (array_key_exists($name, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class name has been used';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->findAll()->asArray();
        if (!!$array) {
            if ($array[0]['type'] == 2) {
                //建立 class
                $this_id = Lazer::table('classes')->findAll()->count() + 1;
                $row = Lazer::table('classes');
                $row->id = $this_id;
                $row->name = $name;
                $row->des = $des;
                $row->super = (int)$super;
                $row->member = $super;
                $row->count = 1;
                $row->date = time();
                $row->save();

                //更改管理员的 class 字段
                $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$super)->find();
                if (!empty($array->class)) {
                    if (!in_array($this_id, explode(',',$array->class))) {
                        $array->set(array(
                            'class' => $array->class . ',' . $this_id
                        ));
                        $array->save();
                    }
                } else {
                    $array->set(array(
                        'class' => (string)$this_id
                    ));
                    $array->save();
                }

                $status = 1;
                $code = 102;
                $mes = 'Successfully created a class';
            } else {
                $status = 0;
                $code = 105;
                $mes = 'The group admin cannot be a student';
            }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'The group admin does not exist';
        }
    }
} else {
    $status = 0;
    $code = 103;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes
);
echo json_encode($return);
