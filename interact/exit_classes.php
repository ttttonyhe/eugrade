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
if (!empty($_POST['class_id']) && !empty($_POST['stu_id']) && !empty($_POST['from'])) {
    
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
    $id = input($_POST['class_id']);
    $stu = input($_POST['stu_id']);
    $from = input($_POST['from']);
    $super = input($_POST['tea_id']);

    //若为老师删除学生
    if ($from == 'teacher') {
        if (!empty($super)) { //必须出现 tea_id 字段
            $array = Lazer::table('classes')->limit(1)->where('id', '=', $id)->find();
            if ($array->super == $super && $_SESSION['logged_in_id'] == $super) { //教师必须与当前登录用户与删除对应 class 的管理员相同
                $c_status = 1;
            } else {
                $c_status = 0;
            }
        } else {
            $c_status = 0;
        }
    } else { //若为学生删除
        if(empty($super)){ //不可存在 tea_id
            if($_SESSION['logged_in_id'] == $stu){
                $c_status = 1;
            }else{
                $c_status = 0;
            }
        }else{
            $c_status = 0;
        }
    }

    if($c_status){

    //业务逻辑
    $array = Lazer::table('classes')->findAll()->asArray('id');
    if (!array_key_exists($id, $array)) {
        $status = 0;
        $code = 101;
        $mes = 'Class does not exist';
    } else {
        $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$stu)->findAll()->asArray();
        if (!!$array) {
                //更改 class 成员字段
                $class = Lazer::table('classes')->limit(1)->where('id', '=', (int)$id)->find();
                if (in_array($stu, explode(',', $class->member))) {

                    $old_member = explode(',', $class->member);
                    //交换数组下标与值
                    $temp_member = array_flip($old_member);
                    $de_key = $temp_member[(string)$stu];
                    unset($old_member[$de_key]);

                    $class->set(array(
                        'member' => implode(',', $old_member)
                    ));
                    $class->save();

                    //更改用户字段
                    $array = Lazer::table('users')->limit(1)->where('id', '=', (int)$stu)->find();
                    if (in_array($id, explode(',', $array->class))) {
                        $old_class = explode(',', $array->class);
                        //交换数组下标与值
                        $temp_class = array_flip($old_class);
                        $de_key = $temp_class[(string)$id];
                        unset($old_class[$de_key]);

                        $array->set(array(
                            'class' => implode(',', $old_class)
                        ));
                        $array->save();
                        
                        //若退出者标记该班级则删除
                        $mark = Lazer::table('marks')->limit(1)->where('marker', '=', (int)$stu)->andWhere('type','=','class')->andWhere('class','=',(int)$id)->find();
                        if(!!$mark->id){
                            $mark->delete();
                        }

                        $status = 1;
                        $code = 110;
                        $mes = 'Successfully removed from the class';
                    } else {
                        $status = 0;
                        $code = 107;
                        $mes = 'User not joined the class yet';
                    }
                } else {
                    $status = 0;
                    $code = 106;
                    $mes = 'User not exist in the class';
                }
        } else {
            $status = 0;
            $code = 104;
            $mes = 'User does not exist';
        }
    }
}else{
    $status = 0;
    $code = 103;
    $mes = 'Illegal request';
}
} else {
    $status = 0;
    $code = 112;
    $mes = 'Illegal request';
}

//输出 json
$return = array(
    'status' => $status,
    'code' => $code,
    'mes' => $mes
);
echo json_encode($return);
