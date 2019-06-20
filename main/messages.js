var antd = new Vue({
    el: '#app',
    data() {
        return {
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
            display_classes: true,
            display_classes_text: 'Hide All',
            status: {
                mark: false,
                chat: false
            },
            opened_mark_info: {
                user: null,
                class_c: null,
                user_info : [],
                class_info : []
            }
        }
    },
    mounted() {
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
    methods: {
        display_class() {
            if (this.display_classes_text == 'View All') {
                this.display_classes_text = 'Hide All';
                this.display_classes = true;
            } else {
                this.display_classes_text = 'View All';
                this.display_classes = false;
            }
        },
        open_marks() {
            this.spinning.center = true;
            axios.get('../interact/select_marks.php?form=user&marker=' + this.user.id)
                .then(res => {
                    this.opened_mark_info.user = res.data;
                    axios.get('../interact/select_users.php?type=name&form=all&id=' + this.opened_mark_info.user.combine)
                        .then(res => {
                            this.opened_mark_info.user_info = res.data;
                        })
                    axios.get('../interact/select_marks.php?form=class&marker=' + this.user.id)
                        .then(res => {
                            this.opened_mark_info.class_c = res.data;
                            axios.get('../interact/select_classes.php?type=name&form=all&id=' + this.opened_mark_info.class_c.combine)
                                .then(res => {
                                    this.opened_mark_info.class_info = res.data;
                                })
                            this.status.mark = true;
                            this.spinning.center = false;
                        })
                })
        },
        //获取用户类型
        get_level(type) {
            if (parseInt(type) == 1) {
                return 'Student';
            } else {
                return 'Teacher';
            }
        },
    }
});