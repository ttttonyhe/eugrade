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
            user : {
                status : 0,
                name : null,
                id : null,
                avatar : null
            }
        }
    },
    mounted(){
        if(!!cookie.get('pokers_uid') && !!cookie.get('pokers_uname')){
            this.user.status = 1;
            this.user.name = cookie.get('pokers_uname');
            this.user.id = parseInt(cookie.get('pokers_uid'));
            axios.get('interact/select.php?type=user_info&uid=1')
            .then(e=>{
                this.user.avatar = e.data.avatar;
            })
            .catch(()=>{
                this.$notification.error({
                    message: '获取错误',
                    description: '头像获取错误'
                });
            })
        }
    }
});