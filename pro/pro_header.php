<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pokers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="Shortcut Icon" href="../statics/img/pokers_icon.ico" type="image/x-icon">
    <?php
    //引入composer
    require '../vendor/autoload.php';
    define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
    use Lazer\Classes\Database as Lazer;
    use Qiniu\Auth;
    $bucket = 'ouorz';
    $accessKey = '4mGogia1PY-PXaYvct65vITq9PeZtZXa1qxE5Ce8';
    $secretKey = 'J-NECV03NfUfVgrdIfA1jkSoqMf6PS5XauY-BcxN';
    $auth = new Auth($accessKey, $secretKey);
    $upToken = $auth->uploadToken($bucket);
    header('Access-Control-Allow-Origin:*');

    session_start();
    if (!isset($_SESSION['logged_in_id'])) {
        ?>
        <script>
            window.location.href = '../login.html';
        </script>
    <?php }else{

        //获取登录用户信息
        $array = Lazer::table('users')->where('id', '=', $_SESSION['logged_in_id'])->limit(1)->find()->asArray();
        $id = $_SESSION['logged_in_id'];
        $name = $array[0]['name'];
        $email = $array[0]['email'];
        $avatar = $array[0]['avatar'];
        $type = $array[0]['type'];

    } ?>

    <script type="text/javascript" src="../statics/js/vue.js"></script>
    <script>
        Vue.config.devtools = true
    </script>
    <script type="text/javascript" src="../statics/js/axios.min.js"></script>
    <link type="text/css" rel="stylesheet" href="../statics/css/antd.css">
    <script type="text/javascript" src="../statics/js/sort.js"></script>
    <script type="text/javascript" src="../statics/js/antd.js"></script>
    <script type="text/javascript" src="../statics/js/jquery.min.js"></script>
    <script type="text/javascript" src="../statics/js/moment.min.js"></script>
    <link type="text/css" rel="stylesheet" href="../dist/css/main.css">
    <script type="text/javascript" src="../statics/js/qiniu.js"></script>
    <script>
    var cookie = {
    "set": function setCookie(name, value) {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
    },
    "get": function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    },
    "del": function delCookie(name) {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval = cookie.get(name);
        if (cval != null)
            document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
    }
}
    </script>
</head>

<body>
    <div id="app">