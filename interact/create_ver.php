<?php

/*
* 创建临时验证数据
* @author TonyHe <he@holptech.com>
* @package Lazer\Classes\Database
* @return json results
*/

error_reporting(E_ALL & ~E_NOTICE);
//引入composer
require '../vendor/autoload.php';
define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');

use Lazer\Classes\Database as Lazer;

require 'database/db_temp.php';
include_once 'database/aliyun-php-sdk-core/Config.php';

use Dm\Request\V20151123 as Dm;

$iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "LTAI4FucLenrs5vQyp2GuVQ5", "vbiaAJJZT7bI7UCPmqpYM4cFA9KphH");
$client = new DefaultAcsClient($iClientProfile);
$request = new Dm\SingleSendMailRequest();
$request->setAccountName("noreply@eugrade.com");
$request->setFromAlias("Eugrade");
$request->setAddressType(1);
$request->setReplyToAddress("true");


session_start();

/*
    判断参数是否齐全
    //请求
    @var integer ran 随机数
    @var string name 生成目的
    //处理
    @var string token 验证标记
    @var integer expire_date 过期时间
    @var integer rand 邮件验证码
*/
if (!empty($_POST['ran']) && !empty($_POST['name'])) {

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


    /* 业务逻辑开始 */

    //获取参数
    $ran = input($_POST['ran']);
    $name = input($_POST['name']);

    //生成保存参数
    $token = md5(md5($ran) . md5($name));
    $expire_date = strtotime('+1day', time());
    $rand = rand(0, 32768);

    //判断验证目的
    switch ($name) {
        case 'reset_pwd': //重设密码
            if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $email_get = input($_POST['email']);
                $array = Lazer::table('users')->limit(1)->where('email', '=', $email_get)->find()->asArray();
                if (!!$array) {
                    //建立验证记录
                    $this_id = Lazer::table('temp')->lastId() + 1;
                    $row = Lazer::table('temp');
                    $row->id = $this_id;
                    $row->k = (string) $token; //标记
                    $row->v = (string) $rand; //值
                    $row->d = (int) $expire_date; //过期时间
                    $row->save();

                    //发送邮件
                    $request->setTagName("resetpwd");
                    $request->setToAddress($email_get);
                    $request->setSubject("Eugrade Password Reset");
                    $request->setHtmlBody("Hi there,<br/><br/>This is your verification code for Eugrade password reset request: <b style='font-size:20px'>" . (string) $rand . "</b><br/>Please verify your request within one day, thank you.<br/><br/><a href='https://www.eugrade.com'>www.eugrade.com</a>");
                    $client = $client->getAcsResponse($request);

                    $status = 1;
                    $code = 105;
                    $mes = 'Successfully sent a request';
                } else {
                    $status = 0;
                    $code = 107;
                    $mes = 'No such user with this email address';
                }
            } else {
                $status = 0;
                $code = 106;
                $mes = 'Illegal Request';
            }
            break;
        default:
            $status = 0;
            $code = 104;
            $mes = 'Illegal request';
            break;
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
    'client' => $client, //返回邮件发送 id
    'token' => (string) $token, //返回唯一标记
    'mes' => $mes
);
echo json_encode($return);
