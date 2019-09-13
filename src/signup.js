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
            valid: null,
            valid_text: null,
            type: 1,
            disable: true
        }
    },
    mounted() {
        this.form = this.$form.createForm(this);
    },
    methods: {
        handle_type_change(type){
            if(type.target.value == 'b'){
                this.type = 2;
            }else{
                this.type = 1;
            }
        },
        handle_check_change(value){
            if(value.target.checked){
                this.disable = false;
            }else{
                this.disable = true;
            }
        },
        handleSubmit(e) {
            e.preventDefault();
            this.form.validateFields((err, values) => {
                if (!err) { //无填写错误
                    var formData = new FormData();
                    formData.append('name', values['name']);
                    formData.append('email', values['email']);
                    formData.append('password', values['password']);
                    formData.append('type', values['type']);
                    formData.append('invite', values['invite']);

                    $.ajax({
                        url: 'interact/register.php',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status) {
                                antd.valid = 'success';
                                antd.$message.success('Welcome to Pokers');
                                cookie.set('logged_in_id',parseInt(data.mes));
                                setTimeout('window.location.href = "pro"',1000);
                            } else {
                                antd.valid = 'error';
                                antd.$message.error(data.mes);
                            }
                        }
                    });
                } else {
                    this.$message.error('Incorrect information entered');
                }
            });
        },
    },
});