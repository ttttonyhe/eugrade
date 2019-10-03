//引入 css 文件
import '../dist/css/main.css';
import 'ant-design-vue/dist/antd.css';

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
            input: {
                email: null,
                code: null
            },
            send: {
                status: false,
                count: 60,
                token: null,
                loading: false,
                text: 'Resend Code'
            }
        }
    },
    mounted(){
        document.getElementById('form_view').style.opacity = 1;
    },
    methods: {
        send_email() {
            this.send.loading = true;
            var query_string = "email="+ this.input.email +"&name=reset_pwd&ran=" + Math.ceil(Math.random() * 100000);

            axios.post(
                    'interact/create_ver.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.send.status = true;
                        this.$message.success(res.data.mes);
                        this.send.token = res.data.token;
                        var interval = setInterval(function () {
                            if(antd.send.count > 0){
                                antd.send.count--;
                                antd.send.text = 'Resend Code('+antd.send.count+')';
                            }else{
                                antd.send.count = 60;
                                antd.send.loading = false;
                                antd.send.text = 'Resend Code';
                                clearInterval(interval);
                            }
                        }, 900);
                    } else {
                        this.send.loading = false;
                        this.$message.error(res.data.mes);
                    }
                })
        },
        check_code() {
            this.send.loading = true;
            var query_string = "email="+ this.input.email +"&name=reset_pwd&input=" + this.input.code + "&token=" + this.send.token;

            axios.post(
                    'interact/check_ver.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.send.status = false;
                        setTimeout('window.location.href = "login.html"',1000);
                    } else {
                        if(this.send.count == 60){
                            this.send.loading = false;
                        }
                        this.$message.error(res.data.mes);
                    }
                })
        }
    },
});