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
    el : '#app',
    data(){
        return{
            user_status : {
                is_login : 0,
                is_teacher : 0,
                is_student : 0
            },
            feedback : {
                login : {
                    status : 0,
                    text : 'User not logged in',
                    name : null,
                    id : null
                }
            }
        }
    },
    mounted(){
        if(!!cookie.get('pokers_uid') && !!cookie.get('pokers_uname')){
            this.feedback.login.text = 'User logged in';
            this.feedback.login.status = 1;
            this.feedback.login.name = cookie.get('pokers_uname');
            this.feedback.login.id = parseInt(cookie.get('pokers_uid'));
        }
    }
});