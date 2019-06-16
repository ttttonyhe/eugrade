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
            opened_class_info: {
                id: null,
                status: 0,
                name: null,
                des: null,
                supername: null,
                superid: null,
                members: [],
                img: null
            },
            add: {
                visible: false,
                confirm_create_loading: false,
                confirm_join_loading: false,
                class: {
                    name: null,
                        des: null
                },
                join: {
                    id: null
                }
            },
            opened_member_info: {
                status: 0,
                info: null,
                superid: null
            },
            remove_id: null
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
        //创建/加入新班级后重新加载列表
        get_all_classes() {
            axios.get('../interact/select_users.php?type=class&id=' + cookie.get('logged_in_id') + '&form=single')
                .then(re => {
                    this.user.joined_classes = re.data.class.split(',');
                    console.log(this.user.joined_classes);
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
                        .then(res => {
                            this.user.classes_info = res.data;
                            this.spinning.left = false;
                        })
                });
        },
        //判断是否为班级管理员，输出特殊样式
        class_super(index) {
            if (parseInt(this.user.classes_info[index].super) == this.user.id) {
                return 'super';
            } else {
                return '';
            }
        },
        //显示加入/创建班级的 modal
        add_class() {
            this.add.visible = true;
        },
        //处理创建班级
        handle_create_submit() {
            this.add.confirm_create_loading = true;
            var formData = new FormData();
            formData.append('name', this.add.class.name);
            formData.append('des', this.add.class.des);
            formData.append('super', this.user.id);

            $.ajax({
                url: '../interact/create_classes.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.add.confirm_create_loading = false;
                        antd.handle_create_cancel();
                        antd.get_all_classes();
                        antd.add.class.name = null;
                        antd.add.class.des = null;
                    } else {
                        antd.$message.error(data.mes);
                        antd.add.confirm_create_loading = false;
                    }
                }
            });
        },
        //关闭 modal
        handle_create_cancel() {
            this.add.visible = false
        },
        //处理加入班级
        handle_join_submit() {
            this.add.confirm_join_loading = true;
            var formData = new FormData();
            formData.append('class_id', this.add.join.id);
            formData.append('stu_id', this.user.id);

            $.ajax({
                url: '../interact/join_classes.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.add.confirm_join_loading = false;
                        antd.handle_create_cancel();
                        antd.get_all_classes();
                        antd.add.join.id = null;
                    } else {
                        antd.$message.error(data.mes);
                        antd.add.confirm_join_loading = false;
                    }
                }
            });
        },
        //点击班级获取信息在 center 列展示
        open_class_info(index) {
            //选中增加 class，删除其余选中
            $('.class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class' + index).addClass('clicked');

            this.spinning.center = true;
            this.opened_class_info.name = this.user.classes_info[index].name;
            this.opened_class_info.des = this.user.classes_info[index].des;
            this.opened_class_info.id = this.user.classes_info[index].id;
            axios.get('../interact/select_users.php?type=name&id=' + parseInt(this.user.classes_info[index].super) + '&form=single')
                .then(rec => {
                    this.opened_class_info.supername = rec.data.name;
                    this.opened_class_info.superid = this.user.classes_info[index].super;

                    axios.get('../interact/select_classes.php?type=member&id='+this.opened_class_info.id+'&form=single')
                    .then(recc=>{
                        axios.get('../interact/select_users.php?type=name&id=' + recc.data.member + '&form=all')
                        .then(rec => {
                            this.opened_class_info.members = rec.data;
                            this.opened_class_info.img = this.user.classes_info[index].img;
                            this.opened_class_info.status = 1;
                            this.spinning.center = false;
                        });
                    })
                });
        },
        //获取用户类型
        get_level(type) {
            if (parseInt(type) == 1) {
                return 'Student';
            } else {
                return 'Teacher';
            }
        },
        //点击用户获取信息在 right 列展示
        open_member_info(id) {
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#member' + id).addClass('clicked');

            this.spinning.right = true;
            axios.get('../interact/select_users.php?type=name&form=all&id=' + id)
                .then(resp => {
                    this.opened_member_info.info = resp.data[0];
                    this.opened_member_info.status = 1;
                    this.opened_member_info.superid = this.opened_class_info.superid;
                    this.opened_member_info.classid = this.opened_class_info.id;
                    this.spinning.right = false;
                })
        },
        //转换时间戳为时间格式
        get_date(timeStamp) {
            var date = new Date();
            date.setTime(timeStamp * 1000);
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? ('0' + m) : m;
            var d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            var h = date.getHours();
            h = h < 10 ? ('0' + h) : h;
            var minute = date.getMinutes();
            var second = date.getSeconds();
            minute = minute < 10 ? ('0' + minute) : minute;
            second = second < 10 ? ('0' + second) : second;
            return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
        },
        stu_remove(class_id) {
            this.remove_id = class_id;
            this.$confirm({
                title: 'Do you want to leave the class?',
                content: 'the process can not be redone',
                onOk() {
                    var formData = new FormData();
                    formData.append('class_id', antd.remove_id);
                    formData.append('stu_id', antd.user.id);
                    formData.append('from', 'student');

                    $.ajax({
                        url: '../interact/exit_classes.php',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status) {
                                antd.$message.success(data.mes);
                                antd.get_all_classes();
                                antd.opened_member_info.status = 0;
                                antd.opened_class_info.status = 0;
                            } else {
                                antd.$message.error(data.mes);
                            }
                        }
                    });
                },
            });
        },
        tea_remove(class_id, stu_id) {
            this.remove_id = class_id;
            this.remove_stu_id = stu_id;
            this.$confirm({
                title: 'Do you want to remove this student from the class?',
                content: 'the process can not be redone',
                onOk() {
                    var formData = new FormData();
                    formData.append('class_id', antd.remove_id);
                    formData.append('stu_id', antd.remove_stu_id);
                    formData.append('from', 'teacher');
                    formData.append('tea_id', antd.user.id);

                    $.ajax({
                        url: '../interact/exit_classes.php',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status) {
                                antd.$message.success(data.mes);
                                antd.opened_member_info.status = 0;
                                antd.opened_class_info.status = 0;
                            } else {
                                antd.$message.error(data.mes);
                            }
                        }
                    });
                }
            })
        }
    }
});