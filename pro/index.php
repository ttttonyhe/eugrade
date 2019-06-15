<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pokers | Clear & Organized Student-Teachers Communication</title>
    <?php
    //引入composer
    require '../vendor/autoload.php';
    define('LAZER_DATA_PATH', dirname(dirname(__FILE__)) . '/data/');
    use Lazer\Classes\Database as Lazer;

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
    <link type="text/css" rel="stylesheet" href="../statics/css/antd.css">
    <script type="text/javascript" src="../statics/js/antd.js"></script>
    <link type="text/css" rel="stylesheet" href="../statics/css/main.css">
</head>

<body>
    <div id="app" style="max-height: 100vh;overflow-y: hidden;">
    <div>
    <a-menu mode="horizontal" v-model="current">
        <a-menu-item key="messages" @click="switch('messages')">
            <a-icon type="project"></a-icon>Messages
        </a-menu-item>
        <a-menu-item key="files" @click="switch('files')">
            <a-icon type="save"></a-icon>Files
        </a-menu-item>
        <a-menu-item key="Classes" @click="switch('classes')">
            <a-icon type="bank"></a-icon>Classes
        </a-menu-item>
    </a-menu>
    <div class="header-user">
            <a-dropdown :trigger="['click']">
                <a style="color:#555">
                <div style="display: flex">
                    <div>
                        <span style="letter-spacing: 1px;padding-top: 5px"><?php echo $name; ?></span>
                    </div>
                    <div>
                        <img style="width:26px;height:26px;border-radius: 50%;margin-left: 10px;margin-top: -4px" src="https://static.ouorz.com/tonyhe.jpg">
                    </div>
                </div>
                </a>
                <a-menu slot="overlay">
                    <a-menu-item key="0">
                        <span style="color:#999"><?php echo $email; ?></span>
                    </a-menu-item>
                    <a-menu-item key="1">
                        <a href="logout.php">Logout</a>
                    </a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="3">3rd menu item</a-menu-item>
                </a-menu>
            </a-dropdown>
        </div>
</div>
<iframe name='main' id="main" src="messages.php" frameborder="0" width="100%" class="main-iframe"></iframe>
</div>
</body>
<script type="text/javascript" src="../main/pro.js"></script>
</html>