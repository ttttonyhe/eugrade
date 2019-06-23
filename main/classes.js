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
            remove_id: null,
            member_marked: false,
            class_marked: false,
            mark: {
                id: null
            },
            edit: {
                confirm_edit_class_loading: false,
                confirm_edit_user_loading: false,
                class: {
                    visible: false,
                        name: null,
                        des: null,
                        percent: 0,
                        id: null,
                        display_percent: false
                },
                user: {
                    visible: false,
                        name: '',
                        email: '',
                        pwd:'',
                        percent: 0,
                        id: null,
                        display_percent: false
                }
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
                $('#main-container').attr('style',''); //避免爆代码
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
            this.edit.class.name = this.opened_class_info.name = this.user.classes_info[index].name;
            this.edit.class.des = this.opened_class_info.des = this.user.classes_info[index].des;
            this.edit.class.id = this.opened_class_info.id = this.user.classes_info[index].id;
            axios.get('../interact/select_users.php?type=name&id=' + parseInt(this.user.classes_info[index].super) + '&form=single')
                .then(rec => {
                    this.opened_class_info.supername = rec.data.name;
                    this.opened_class_info.superid = this.user.classes_info[index].super;

                    axios.get('../interact/select_classes.php?type=member&id=' + this.opened_class_info.id + '&form=single')
                        .then(recc => {
                            axios.get('../interact/select_users.php?type=name&id=' + recc.data.member + '&form=all')
                                .then(rec => {
                                    this.opened_class_info.members = rec.data;
                                    this.opened_class_info.img = this.user.classes_info[index].img;
                                    this.opened_class_info.status = 1;
                                    this.check_mark(this.opened_class_info.id, 'class');
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
                    this.check_mark(this.opened_member_info.info.id, 'user'); //判断是否收藏用户
                    this.spinning.right = false;
                    this.edit.user.id = this.opened_member_info.info.id;
                    this.edit.user.name = this.opened_member_info.info.name;
                    this.edit.user.email = this.opened_member_info.info.email;
                    this.edit.user.avatar = this.opened_member_info.info.avatar;
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
        },
        //判断当前展示用户是否被标记
        check_mark(id, type) {
            if (type == 'user') {
                axios.get('../interact/select_marks.php?form=user&marker=' + this.user.id)
                    .then(res => {
                        this.member_marked = false; //.user 在数组为空时无法获取,直接初始值 false
                        if (!!res.data[id.toString()].user) {
                            this.member_marked = true;
                        } else {
                            this.member_marked = false;
                        }
                    })
            } else {
                axios.get('../interact/select_marks.php?form=class&marker=' + this.user.id)
                    .then(res => {
                        this.class_marked = false; //.class 在数组为空时无法获取,直接初始值 false
                        if (!!res.data[id.toString()].class) {
                            this.class_marked = true;
                        } else {
                            this.class_marked = false;
                        }
                    })
            }
        },
        //标记收藏
        mark_process(id, type) {
            this.mark.id = id;
            if (type == 'user') {
                this.$confirm({
                    title: 'Do you want to mark this student?',
                    content: 'you can remove the mark by clicking the button again',
                    onOk() {
                        var formData = new FormData();
                        formData.append('stu_id', antd.mark.id);
                        formData.append('type', 'user');
                        formData.append('marker', antd.user.id);

                        $.ajax({
                            url: '../interact/create_mark.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.check_mark(antd.opened_member_info.info.id, 'user');
                                } else {
                                    antd.$message.error(data.mes);
                                }
                            }
                        });
                    }
                })
            } else {
                this.$confirm({
                    title: 'Do you want to mark this class?',
                    content: 'you can remove the mark later',
                    onOk() {
                        var formData = new FormData();
                        formData.append('class_id', antd.mark.id);
                        formData.append('type', 'class');
                        formData.append('marker', antd.user.id);

                        $.ajax({
                            url: '../interact/create_mark.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.check_mark(antd.opened_class_info.id, 'class');
                                } else {
                                    antd.$message.error(data.mes);
                                }
                            }
                        });
                    }
                })
            }
        },
        //删除标记
        demark_process(id, type) {
            this.mark.id = id;
            if (type == 'user') {
                this.$confirm({
                    title: 'Do you want to remove the mark of this student?',
                    content: 'you can mark back by clicking the button again',
                    onOk() {
                        var formData = new FormData();
                        formData.append('stu_id', antd.mark.id);
                        formData.append('type', 'user');
                        formData.append('marker', antd.user.id);

                        $.ajax({
                            url: '../interact/delete_mark.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.check_mark(antd.opened_member_info.info.id, 'user');
                                } else {
                                    antd.$message.error(data.mes);
                                }
                            }
                        });
                    }
                })
            } else {
                this.$confirm({
                    title: 'Do you want to remove the mark of this class?',
                    content: 'you are able to mark back',
                    onOk() {
                        var formData = new FormData();
                        formData.append('class_id', antd.mark.id);
                        formData.append('type', 'class');
                        formData.append('marker', antd.user.id);

                        $.ajax({
                            url: '../interact/delete_mark.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.check_mark(antd.opened_class_info.id, 'class');
                                } else {
                                    antd.$message.error(data.mes);
                                }
                            }
                        });
                    }
                })
            }

        },
        //处理修改班级
        handle_edit_class_submit() {
            this.edit.confirm_edit_class_loading = true;
            var formData = new FormData();
            formData.append('class_id', this.edit.class.id);
            formData.append('name', this.edit.class.name);
            formData.append('des', this.edit.class.des);
            formData.append('super', this.user.id);
            formData.append('type', 'info');

            $.ajax({
                url: '../interact/edit_classes.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.edit.confirm_edit_class_loading = false;
                        antd.handle_edit_class_cancel();
                        antd.get_all_classes(); //重新获取班级列表
                        antd.edit.class.name = null; //清空编辑框
                        antd.edit.class.des = null;
                        antd.opened_class_info.status = 0; //关闭中栏
                    } else {
                        antd.$message.error(data.mes);
                        antd.edit.confirm_edit_class_loading = false;
                    }
                }
            });
        },
        //关闭 modal
        handle_edit_class_cancel() {
            this.edit.class.visible = false;
        },
        upload_class_img(token) {
            if ($("#class_img")[0].files[0] !== undefined) {
                if($("#class_img")[0].files[0].size <= 2000000){
                this.edit.confirm_edit_class_loading = true;
                this.edit.class.display_percent = true;
                var get_suffix = function (name) {
                    var index = name.lastIndexOf('.');
                    return name.substring(index);
                }
                var pre_name = new Date().getTime();
                var suffix = get_suffix($("#class_img")[0].files[0].name);
                var name = pre_name + suffix;
                var config = {
                    useCdnDomain: true
                };
                var putExtra = {
                    mimeType: ["image/png", "image/jpeg"]
                  };

                var file = $("#class_img")[0].files[0];
                var observable = qiniu.upload(file, name, token, putExtra, config)
                var observer = {
                    next(res) {
                        antd.edit.class.percent = res.total.percent;
                    },
                    error(err) {
                        antd.$message.error(err.message);
                        antd.edit.confirm_edit_class_loading = false;
                        antd.edit.class.display_percent = false;
                    },
                    complete(res) {
                        var formData = new FormData();
                        formData.append('class_id', antd.edit.class.id);
                        formData.append('super', antd.user.id);
                        formData.append('type', 'img');
                        formData.append('url', 'https://static.ouorz.com/' + name)

                        $.ajax({
                            url: '../interact/edit_classes.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.edit.confirm_edit_class_loading = false;
                                    antd.handle_edit_class_cancel();
                                    antd.get_all_classes(); //重新获取班级列表
                                    /* 清空编辑内容 */
                                    antd.edit.class.display_percent = false;
                                    antd.edit.class.name = null;
                                    antd.edit.class.des = null;
                                    antd.edit.class.id = null;
                                    antd.edit.class.percent = 0;
                                    antd.edit.class.display_percent = false;
                                    $("#class_img").val('');
                                    /* 结束清空编辑内容 */
                                    antd.opened_class_info.status = 0; //关闭中栏
                                } else {
                                    antd.$message.error(data.mes);
                                    antd.edit.confirm_edit_class_loading = false;
                                }
                            }
                        });
                    }
                }
                var subscription = observable.subscribe(observer);
            }else{
                antd.$message.error('This img exceeded 2MB upload limit');
            }
            } else {
                antd.$message.error('No img selected');
            }
        },
        //处理修改用户信息
        handle_edit_user_submit(type) {
            this.edit.confirm_edit_user_loading = true;
            var formData = new FormData();
            formData.append('user_id', this.edit.user.id);
            formData.append('name', this.edit.user.name);
            formData.append('email', this.edit.user.email);
            if(type == 'teacher'){
                formData.append('super', antd.user.id);
            }else{
                formData.append('pwd', antd.edit.user.pwd);
            }
            formData.append('type', 'info');

            $.ajax({
                url: '../interact/edit_users.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.edit.confirm_edit_user_loading = false;
                        antd.handle_edit_user_cancel();
                        antd.edit.user.name = null; //清空编辑框
                        antd.edit.user.email = null;
                        antd.opened_member_info.status = 0; //关闭右栏
                        antd.opened_class_info.status = 0; //关闭中栏
                    } else {
                        antd.$message.error(data.mes);
                        antd.edit.confirm_edit_user_loading = false;
                    }
                }
            });
        },
        //关闭 modal
        handle_edit_user_cancel() {
            this.edit.user.visible = false;
        },
        upload_user_img(token,type) {
            if ($("#user_img")[0].files[0] !== undefined) {
                if($("#user_img")[0].files[0].size <= 2000000){
                this.edit.confirm_edit_user_loading = true;
                this.edit.user.display_percent = true;
                var get_suffix = function (name) {
                    var index = name.lastIndexOf('.');
                    return name.substring(index);
                }
                var pre_name = new Date().getTime();
                var suffix = get_suffix($("#user_img")[0].files[0].name);
                var name = pre_name + suffix;
                var config = {
                    useCdnDomain: true
                };
                var putExtra = {
                    mimeType: ["image/png", "image/jpeg"]
                  };

                var file = $("#user_img")[0].files[0];
                var observable = qiniu.upload(file, name, token, putExtra, config)
                var observer = {
                    next(res) {
                        antd.edit.user.percent = Math.round(res.total.percent);
                    },
                    error(err) {
                        antd.$message.error(err.message);
                        antd.edit.confirm_edit_user_loading = false;
                        antd.edit.user.display_percent = false;
                    },
                    complete(res) {
                        var formData = new FormData();
                        formData.append('user_id', antd.edit.user.id);
                        if(type == 'teacher'){
                            formData.append('super', antd.user.id);
                        }
                        formData.append('type', 'img');
                        formData.append('url', 'https://static.ouorz.com/' + name)

                        $.ajax({
                            url: '../interact/edit_users.php',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status) {
                                    antd.$message.success(data.mes);
                                    antd.edit.confirm_edit_user_loading = false;
                                    /* 清空编辑内容 */
                                    antd.edit.user.display_percent = false;
                                    antd.edit.user.name = null;
                                    antd.edit.user.email = null;
                                    antd.edit.user.id = null;
                                    antd.edit.user.percent = 0;
                                    antd.edit.user.display_percent = false;
                                    $("#user_img").val('');
                                    /* 结束清空编辑内容 */
                                    antd.handle_edit_user_cancel();
                                    antd.opened_member_info.status = 0; //关闭右栏
                                    antd.opened_class_info.status = 0; //关闭中栏
                                } else {
                                    antd.$message.error(data.mes);
                                    antd.edit.confirm_edit_user_loading = false;
                                }
                            }
                        });
                    }
                }
                var subscription = observable.subscribe(observer);
            }else{
                antd.$message.error('This img exceeded 2MB upload limit');
            }
            } else {
                antd.$message.error('No img selected');
            }
        }
    }
});