<?php
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

if (!empty($_POST['name']) && !empty($_POST['des']) && !empty($_POST['super'])) {

    //输入处理
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
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
        $array = Lazer::table('users')->where('id', '=', (int)$super)->findAll()->asArray();
        if (!!$array) {
            if ($array[0]['type'] == 2) {
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
