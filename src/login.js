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
            login_status: 0,
            form: null,
            email_valid: null,
            valid_text: null
        }
    },
    mounted() {
        this.form = this.$form.createForm(this);
    },
    methods: {
        handleSubmit(e) {
            e.preventDefault();
            this.form.validateFields((err, values) => {
                console.log(values);
                if (!err) { //无填写错误
                    this.email_valid = 'validating';
                    var formData = new FormData();
                    formData.append('email', values['email']);
                    formData.append('password', values['password']);

                    $.ajax({
                        url: 'interact/login.php',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status) {
                                antd.email_valid = 'success';
                                antd.valid_text = null;
                                cookie.set('logged_in_id',parseInt(data.user_id));
                                window.location.href = 'pro';
                            } else {
                                antd.email_valid = 'error';
                                antd.valid_text = data.mes;
                            }
                        }
                    });
                } else {
                    this.email_valid = 'warning';
                    this.valid_text = 'Incorrect email or password';
                }
            });
        },
    },
});