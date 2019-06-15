var antd = new Vue({
    el : '#app',
    data(){
        return{
            current :{
                nav : ['messages'],
                page : 'messages.php'
            },
            spinning : false
        }
    },
    methods : {
        switch_section(key){
            this.current.nav = key;
            this.current.page = key+'.php';
            this.spinning = true;
            setTimeout('antd.spinning = false;',1000);
        }
    }
});