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
                left: 1,
                center: 0
            },
            opened_class_info: {
                status: 0,
                name: null,
                des: null,
                supername: null,
                super: null,
                members: [],
                img: null
            },
            add: {
                visible: false,
                confirm_create_loading: false,
                confirm_join_loading: false,
            }
        }
    },
    mounted() {
        axios.get('../interact/select_users.php?type=class&id=' + cookie.get('logged_in_id') + '&form=single')
            .then(re => {
                this.user.joined_classes = re.data.class.split(',');
                console.log(this.user.joined_classes);
                axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
                    .then(res => {
                        this.user.classes_info = res.data;
                        this.spinning.left = 0;
                    })
            })
    },
    methods: {
        class_super(index) {
            if (this.user.id == parseInt(this.user.classes_info[index].super)) {
                return 'super';
            } else {
                return '';
            }
        },
        add_class() {
            this.add.visible = true;
        },
        handle_create_submit() {
            this.add.confirm_create_loading = true;
            setTimeout(() => {
                this.add.confirm_create_loading = false;
            }, 1000);
        },
        handle_create_cancel() {
            this.add.visible = false
        },
        open_class_info(index) {
            this.spinning.center = 1;
            this.opened_class_info.name = this.user.classes_info[index].name;
            this.opened_class_info.des = this.user.classes_info[index].des;
            axios.get('../interact/select_users.php?type=name&id=' + parseInt(this.user.classes_info[index].super) + '&form=single')
                .then(rec => {
                    this.opened_class_info.supername = rec.data.name;
                });
            this.opened_class_info.super = this.user.classes_info[index].super;

            axios.get('../interact/select_users.php?type=name&id=' + this.user.classes_info[index].member + '&form=all')
                .then(rec => {
                    this.opened_class_info.members = rec.data;
                });

            this.opened_class_info.img = this.user.classes_info[index].img;
            this.opened_class_info.status = 1;
            this.spinning.center = 0;
        },
        get_level(type) {
            if (parseInt(type) == 1) {
                return 'Student';
            } else {
                return 'Teacher';
            }
        }
    }
});