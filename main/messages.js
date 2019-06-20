var antd = new Vue({
    el : '#app',
    data(){
        return{
            user: {
                id: cookie.get('logged_in_id'),
                joined_classes: [],
                classes_info: []
            },
            spinning: {
                left: true,
                center: false,
                right: false
            },
            display_classes : true,
            display_classes_text : 'Hide All'
        }
    },
    mounted(){
        axios.get('../interact/select_users.php?type=class&id=' + cookie.get('logged_in_id') + '&form=single')
            .then(re => {
                if (!!re.data.class) {
                    this.user.joined_classes = re.data.class.split(',');
                    console.log(this.user.joined_classes);
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
                        .then(res => {
                            this.user.classes_info = res.data;
                            this.spinning.left = false;
                        })
                } else {
                    //若不存在班级信息
                    this.spinning.left = false;
                }
            });
    },
    methods : {
        display_class(){
            if(this.display_classes_text == 'View All'){
                this.display_classes_text = 'Hide All';
                this.display_classes = true;
            }else{
                this.display_classes_text = 'View All';
                this.display_classes = false;
            }
        }
    }
});