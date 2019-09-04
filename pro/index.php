<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="Shortcut Icon" href="https://static.ouorz.com/eugrade.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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


    try {
        \Lazer\Classes\Helpers\Validate::table('users')->exists();
    } catch (\Lazer\Classes\LazerException $e) {
        die();
    }

    session_start();
    if (!isset($_SESSION['logged_in_id'])) {
        ?>
        <script>
            window.location.href = '../login.html';
        </script>
    <?php } else {

        //获取登录用户信息
        $array = Lazer::table('users')->where('id', '=', $_SESSION['logged_in_id'])->limit(1)->find()->asArray();
        $id = $_SESSION['logged_in_id'];
        $name = $array[0]['name'];
        $email = $array[0]['email'];
        $avatar = $array[0]['avatar'];
        $type = $array[0]['type'];
    } ?>

    <script type="text/javascript" src="../statics/js/vue.js"></script>
    <script type="text/javascript" src="../statics/js/axios.min.js"></script>
    <link type="text/css" rel="stylesheet" href="../statics/css/antd.css">
    <script type="text/javascript" src="../statics/js/antd.js"></script>
    <link type="text/css" rel="stylesheet" href="../dist/css/main.css">
    <script type="text/javascript" src="../statics/js/jquery.min.js"></script>
    <script type="text/javascript" src="../statics/js/qiniu.js"></script>
</head>

<body>
    <div id="app" style="max-height: 100vh;overflow-y: hidden;opacity:0">
        <title v-html="current.title">Eugrade | Communication and collaboration platform for Education</title>
        <div>
            <a-menu mode="horizontal" v-model="current.nav">
                <a-sub-menu style="border-right: 1px solid #eee;">
                    <span slot="title" class="submenu-title-wrapper">
                        <img src="https://static.ouorz.com/eugrade_logo.png" class="header-logo" />
                    </span>
                    <a-menu-item><a href="https://www.ouorz.com/pokers-terms-of-service.html">
                            <a-icon type="file-done"></a-icon>Terms of Service
                        </a></a-menu-item>
                    <a-menu-item><a href="https://www.ouorz.com/pokers-privacy-policy.html">
                            <a-icon type="file-protect"></a-icon>Privacy Policy
                        </a></a-menu-item>
                    <a-menu-item><a href="https://www.snapaper.com/donate" style="color:hotpink">
                            <a-icon type="pay-circle"></a-icon>Dnoate to Me
                        </a></a-menu-item>
                    <a-menu-item>
                        <a-icon type="robot"></a-icon>Beta v0.181
                    </a-menu-item>
                    <a-menu-item><a href="https://shimo.im/forms/KghdKCKqGYtHygKj/fill">
                            <a-icon type="customer-service"></a-icon>FeedBack
                        </a></a-menu-item>
                </a-sub-menu>
                <a-menu-item key="messages" @click="switch_section('messages')" style="border-right: 1px solid #eee;">
                    <a-icon type="project"></a-icon>Messages
                </a-menu-item>
                <a-menu-item key="files" @click="switch_section('files')" style="border-right: 1px solid #eee;">
                    <a-icon type="save"></a-icon>Files
                </a-menu-item>
                <a-menu-item key="grades" @click="switch_section('grades')" style="border-right: 1px solid #eee;">
                    <a-icon type="bar-chart"></a-icon>Grades
                </a-menu-item>
                <a-menu-item key="classes" @click="switch_section('classes')" style="border-right: 1px solid #eee;">
                    <a-icon type="bank"></a-icon>Classes
                </a-menu-item>
                <a-sub-menu style="border-right: 1px solid #eee;">
                    <span slot="title" class="submenu-title-wrapper">
                        <a-icon type="appstore"></a-icon>Resources
                    </span>
                    <a-menu-item><a href="https://www.snapaper.com">
                            <a-icon type="solution"></a-icon>Snapaper
                        </a></a-menu-item>
                    <a-menu-item><a href="https://platform.snapaper.com">
                            <a-icon type="compass"></a-icon>Snapaper Platform
                        </a></a-menu-item>
                </a-sub-menu>
            </a-menu>
            <div class="header-user">
                <a-dropdown :trigger="['click']">
                    <a style="color:#555">
                        <div style="display: flex">
                            <div>
                                <span style="letter-spacing: 1px;padding-top: 5px" v-html="edit.user.name"></span>
                            </div>
                            <div>
                                <img style="width:26px;height:26px;border-radius: 50%;margin-left: 10px;margin-top: -4px" :src="edit.user.avatar">
                            </div>
                        </div>
                    </a>
                    <a-menu slot="overlay">
                        <a-menu-item key="0">
                            <span style="color:#999">{{ edit.user.email }}</span>
                        </a-menu-item>
                        <a-menu-divider></a-menu-divider>
                        <a-menu-item key="1" @click="edit.user.visible = true">
                            <a-icon type="form"></a-icon>Edit Profile
                        </a-menu-item>
                        <a-menu-item key="2">
                            <a href="logout.php">
                                <a-icon type="logout"></a-icon>&nbsp;&nbsp;Logout
                            </a>
                        </a-menu-item>
                    </a-menu>
                </a-dropdown>
            </div>
        </div>
        <div class="header-notice" v-if="notice.status">
            <p><b>Notice</b>{{ notice.content }}</p>
        </div>

        <!-- 用户信息修改 -->
        <a-modal title="Edit Profile" :visible="edit.user.visible" @ok="handle_edit_submit()" :confirm-loading="edit.confirm_edit_loading" @cancel="handle_edit_cancel">
            <div>
                <template v-if="!!edit.user.avatar">
                    <a-avatar size="large" :src="edit.user.avatar"></a-avatar>
                </template>
                <template v-else>
                    <a-avatar size="large" :style="{backgroundColor: '#32a3bf', verticalAlign: 'middle'}">{{ edit.user.name }}</a-avatar>
                </template>
                <input type="file" name="user_img" id="user_img" />
                <a-button size="small" :style="{ marginLeft: 16, verticalAlign: 'middle' }" @click="upload_img('<?php echo $upToken; ?>')">Upload</a-button>
            </div>
            <div v-show="edit.user.display_percent">
                <a-progress :percent="edit.user.percent" status="active"></a-progress>
                <br />
            </div>
            <br />
            <a-input placeholder="Nickname" v-model="edit.user.name">
                <a-icon slot="prefix" type="user" />
            </a-input>
            <br /><br />
            <a-input placeholder="Email" v-model="edit.user.email">
                <a-icon slot="prefix" type="mail" />
            </a-input>
            <br /><br />
            <a-input placeholder="Password(stays the same if kept empty)" v-model="edit.user.pwd">
                <a-icon slot="prefix" type="key" />
            </a-input>
        </a-modal>
        <!-- 用户信息修改结束 -->



        <a-spin :spinning="spinning">
            <iframe name='main' id="main" :src="current.page" frameborder="0" width="100%" class="main-iframe"></iframe>
        </a-spin>


    </div>
</body>


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

    var antd = new Vue({
        el: '#app',
        data() {
            return {
                current: {
                    nav: ['messages'],
                    page: 'messages.php',
                    title: 'Eugrade | Messages'
                },
                spinning: true,
                edit: {
                    confirm_edit_loading: false,
                    user: {
                        visible: false,
                        name: '<?php echo $name; ?>',
                        email: '<?php echo $email; ?>',
                        pwd: '',
                        percent: 0,
                        id: <?php echo $id; ?>,
                        avatar: '<?php echo $avatar; ?>',
                        display_percent: false
                    }
                },
                notice: {
                    status: false,
                    content: null
                }
            }
        },
        mounted() {
            $('#app').css('opacity', 1);
            this.spinning = false;
        },
        methods: {
            switch_section(key) {
                this.current.nav = key;
                this.current.title = 'Pokers | ' + this.capital(key);
                this.current.page = key + '.php';
                this.spinning = true;
                setTimeout('antd.spinning = false;', 1000);
            },
            capital(str) {
                //将字符串转化为消协，并拆分成单词
                str = str.toLowerCase().split(" ");
                //循环将每个单词的首字母大写
                for (var i = 0; i < str.length; i++) {
                    //选取首个字符
                    var char = str[i].charAt(0);
                    //将单子首字符替换为大写
                    str[i] = str[i].replace(char, function(s) {
                        return s.toUpperCase();
                    });
                }
                //拼合数组
                str = str.join(" ");
                return str;
            },
            //处理修改用户信息
            handle_edit_submit(type) {
                this.edit.confirm_edit_user_loading = true;
                var formData = new FormData();
                formData.append('user_id', this.edit.user.id);
                formData.append('name', this.edit.user.name);
                formData.append('email', this.edit.user.email);
                formData.append('pwd', antd.edit.user.pwd);
                formData.append('type', 'info');

                $.ajax({
                    url: '../interact/edit_users.php',
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.status) {
                            antd.$message.success(data.mes);
                            antd.edit.confirm_edit_loading = false;
                            antd.edit.user.pwd = null;
                            antd.handle_edit_cancel();
                        } else {
                            antd.$message.error(data.mes);
                            antd.edit.confirm_edit_loading = false;
                        }
                    }
                });
            },
            //关闭 modal
            handle_edit_cancel() {
                this.edit.user.visible = false;
            },
            upload_img(token) {
                if ($("#user_img")[0].files[0] !== undefined) {
                    if ($("#user_img")[0].files[0].size <= 2000000) {
                        this.edit.confirm_edit_user_loading = true;
                        this.edit.user.display_percent = true;
                        var get_suffix = function(name) {
                            var index = name.lastIndexOf('.');
                            return name.substring(index);
                        }
                        var pre_name = new Date().getTime();
                        var suffix = get_suffix($("#user_img")[0].files[0].name);
                        var name = pre_name + suffix;
                        var config = {
                            useCdnDomain: true
                        };
                        var putExtra = {
                            mimeType: ["image/png", "image/jpeg"]
                        };

                        var file = $("#user_img")[0].files[0];
                        var observable = qiniu.upload(file, name, token, putExtra, config)
                        var observer = {
                            next(res) {
                                antd.edit.user.percent = Math.round(res.total.percent);
                            },
                            error(err) {
                                antd.$message.error(err.message);
                                antd.edit.confirm_edit_loading = false;
                                antd.edit.user.display_percent = false;
                            },
                            complete(res) {
                                var formData = new FormData();
                                formData.append('user_id', antd.edit.user.id);
                                formData.append('type', 'img');
                                formData.append('url', 'https://static.ouorz.com/' + name)

                                $.ajax({
                                    url: '../interact/edit_users.php',
                                    type: "POST",
                                    data: formData,
                                    cache: false,
                                    dataType: 'json',
                                    processData: false,
                                    contentType: false,
                                    success: function(data) {
                                        if (data.status) {
                                            antd.$message.success(data.mes);
                                            antd.edit.confirm_edit_loading = false;
                                            /* 清空编辑内容 */
                                            antd.edit.user.percent = 0;
                                            antd.edit.user.avatar = 'https://static.ouorz.com/' + name;
                                            antd.edit.user.display_percent = false;
                                            $("#user_img").val('');
                                            /* 结束清空编辑内容 */
                                            antd.handle_edit_cancel();
                                        } else {
                                            antd.$message.error(data.mes);
                                            antd.edit.confirm_edit_loading = false;
                                        }
                                    }
                                });
                            }
                        }
                        var subscription = observable.subscribe(observer);
                    } else {
                        antd.$message.error('This img exceeded 2MB upload limit');
                    }
                } else {
                    antd.$message.error('No img selected');
                }
            }
        }
    });
</script>

</html>