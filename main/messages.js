var antd = new Vue({
    el: '#app',
    data() {
        return {
            user: {
                id: cookie.get('logged_in_id'),
                joined_classes: [],
                classes_info: [],
                info: []
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
                chat: false,
                user: false,
                thread: false
            },
            opened_mark_info: {
                user: null,
                class_c: null,
                user_info: [],
                class_info: []
            },
            opened_member_info: {
                status: 0,
                info: null
            },
            opened_class_info: {
                id: null
            },
            opened_thread_info: [],
            member_marked: false,
            class_marked: false,
            mark: {
                id: null
            },
            add: {
                visible: false,
                visible_thread: false,
                confirm_join_loading: false,
                join: {
                    id: null
                },
                thread: {
                    id: null,
                    name: null
                }
            },
            opened_mes_info: {
                thread_id: null,
                thread_info: [],
                class_id: null,
                meses: [],
                speakers: [],
                index: null
            },
            mes_input: {
                rows: 1,
                op_display: false,
                container: 'mes-container-normal',
                input: 'mes-input-normal',
                content: null,
                disable: false,
                type: 'text',
                visible: {
                    picture: false,
                    upload: false,
                    text: true
                },
                token: null,
                file_progress: false,
                img_progress: false,
                progress_file: 0, //进度
                progress_img: 0,
                img: {
                    url: null
                },
                file: {
                    url: null,
                    name: null
                }
            },
        }
    },
    mounted() {
        axios.get('../interact/select_users.php?type=name&id=' + cookie.get('logged_in_id') + '&form=all')
            .then(re => {
                if (!!re.data[0].class) {
                    this.user.joined_classes = re.data[0].class.split(',');
                    this.user.info = re.data[0];
                    axios.get('../interact/get_token.php?user=' + this.user.id + '&email=' + this.user.info.email)
                        .then(res => {
                            this.mes_input.token = res.data.key;
                        })
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data[0].class + '&form=all')
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
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
                        .then(res => {
                            this.user.classes_info = res.data;
                            this.spinning.left = false;
                        })
                });
        },
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
                                    this.status.thread = false;
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
        //通过时间戳只获取年月日
        get_date_d(timeStamp) {
            var date = new Date();
            date.setTime(timeStamp * 1000);
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? ('0' + m) : m;
            var d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            return y + '-' + m + '-' + d;
        },
        //转换时间戳为分秒时时间格式
        get_time(timeStamp) {
            var date = new Date();
            date.setTime(timeStamp * 1000);
            var h = date.getHours();
            h = h < 10 ? ('0' + h) : h;
            var minute = date.getMinutes();
            var second = date.getSeconds();
            minute = minute < 10 ? ('0' + minute) : minute;
            second = second < 10 ? ('0' + second) : second;
            return h + ':' + minute + ':' + second;
        },
        //今日与之前的内容段展示不同的日期格式
        get_mes_date(timeStamp) {
            //发送于今日
            if (this.get_date_d(timeStamp) == this.get_date_d(Math.round(new Date().getTime() / 1000))) {
                return this.get_time(timeStamp);
            } else { //未在今日
                return this.get_date(timeStamp);
            }
        },
        //点击用户获取信息在 right 列展示
        open_user(id) {
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#member' + id).addClass('clicked');

            this.spinning.right = true;
            axios.get('../interact/select_users.php?type=name&form=all&id=' + id)
                .then(resp => {
                    this.opened_member_info.info = resp.data[0];
                    this.status.user = true;
                    this.check_mark(this.opened_member_info.info.id, 'user'); //判断是否收藏用户
                    this.spinning.right = false;
                    this.edit.user.id = this.opened_member_info.info.id;
                    this.edit.user.name = this.opened_member_info.info.name;
                    this.edit.user.email = this.opened_member_info.info.email;
                    this.edit.user.avatar = this.opened_member_info.info.avatar;
                })
        },
        //点击班级获取主题在 center 列展示
        open_class(id) {
            //选中增加 class，删除其余选中
            $('.left .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class' + id).addClass('clicked');
            $('#class_sub' + id).addClass('clicked');

            this.opened_class_info.id = id;
            this.spinning.center = true;
            axios.get('../interact/select_thread.php?class_id=' + id)
                .then(resp => {
                    this.status.mark = false;
                    this.opened_thread_info = resp.data;
                    this.status.thread = true;
                    this.spinning.center = false;
                })
        },



        //点击主题获取消息在 right 列展示
        open_mes(index, id, belong_class) {

            this.spinning.right = true;
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#thread_sub' + id).addClass('clicked');

            this.opened_mes_info.index = index;
            this.opened_mes_info.thread_id = id;
            this.opened_mes_info.class_id = belong_class;
            this.opened_mes_info.thread_info = this.opened_thread_info[index];

            axios.get('../interact/select_messages.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)
                .then(response => {
                    this.opened_mes_info.meses = response.data.mes;
                    axios.get('../interact/select_users.php?form=all&type=name&id=' + response.data.speakers)
                        .then(res => {
                            this.opened_mes_info.speakers = res.data;
                        })
                    this.status.user = false;
                    this.status.chat = true;
                    this.spinning.right = false;
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
                                    antd.open_marks();
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
                                    antd.open_marks();
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
                                    antd.open_marks();
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
                                    antd.open_marks();
                                } else {
                                    antd.$message.error(data.mes);
                                }
                            }
                        });
                    }
                })
            }

        },
        //显示加入/创建班级的 modal
        add_class() {
            this.add.visible = true;
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
        //关闭 modal
        handle_thread_cancel() {
            this.add.visible_thread = false;
        },
        //处理加入班级
        handle_thread_submit(id) {
            this.add.confirm_thread_loading = true;
            this.add.thread.id = id;
            var formData = new FormData();
            formData.append('belong_class', this.add.thread.id);
            formData.append('name', this.add.thread.name);
            formData.append('creator', this.user.id);

            $.ajax({
                url: '../interact/create_thread.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.add.confirm_thread_loading = false;
                        antd.handle_thread_cancel();
                        antd.open_class(antd.add.thread.id);
                        antd.add.thread.id = null;
                        antd.add.thread.name = null;
                    } else {
                        antd.$message.error(data.mes);
                        antd.add.confirm_thread_loading = false;
                    }
                }
            });
        },
        handle_input_up() {
            this.mes_input.rows = 3;
            this.mes_input.op_display = true;
            this.mes_input.container = 'mes-container';
            this.mes_input.input = 'mes-input';
            this.bottom_mes();
        },
        handle_input_down() {
            this.mes_input.rows = 1;
            this.mes_input.op_display = false;
            this.mes_input.container = 'mes-container-normal';
            this.mes_input.input = 'mes-input-normal';
        },
        handle_input_send(type) {
            this.mes_input.disable = true;
            var formData = new FormData();

            if (type == 'img') {
                formData.append('img_url', this.mes_input.img.url);
                formData.append('content', this.mes_input.content);
                this.mes_input.type = 'text';
            } else if (type == 'file') {
                formData.append('file_url', this.mes_input.file.url);
                formData.append('file_name', this.mes_input.file.name);
                this.mes_input.type = 'file';
            } else {
                formData.append('content', this.mes_input.content);
                this.mes_input.type = 'text';
            }

            formData.append('speaker', this.user.id);
            formData.append('speaker_name', this.user.info.name);
            formData.append('belong_class', this.opened_mes_info.class_id);
            formData.append('thread', this.opened_mes_info.thread_id);
            formData.append('type', this.mes_input.type);

            $.ajax({
                url: '../interact/add_message.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.update_mes();
                        antd.mes_input.content = null;
                        antd.mes_input.disable = false;
                    } else {
                        antd.$message.error(data.mes);
                        antd.mes_input.disable = false;
                    }
                }
            });
        },
        update_mes() {
            axios.get('../interact/select_messages.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)
                .then(response => {
                    this.opened_mes_info.meses = response.data.mes;
                    axios.get('../interact/select_users.php?form=all&type=name&id=' + response.data.speakers)
                        .then(res => {
                            this.opened_mes_info.speakers = res.data;
                            antd.bottom_mes();
                        })
                })
        },
        bottom_mes() {
            $("#mes-container").scrollTop($("#mes-inner")[0].scrollHeight);
        },
        upload_file() {
            if ($("#upload_file")[0].files[0] !== undefined) {
                if ($("#upload_file")[0].files[0].size <= 50000000) {

                    this.mes_input.visible.text = false;
                    this.mes_input.file_progress = true;
                    this.mes_input.disable = true;

                    var pre_name = new Date().getTime();
                    var suffix = this.get_suffix($("#upload_file")[0].files[0].name);
                    var name = pre_name + suffix;

                    this.mes_input.file.name = name;
                    this.mes_input.file.url = 'https://static.ouorz.com/' + name;

                    var config = {
                        useCdnDomain: true
                    };

                    var token = this.mes_input.token;

                    var file = $("#upload_file")[0].files[0];
                    var observable = qiniu.upload(file, name, token, config)
                    var observer = {
                        next(res) {
                            antd.mes_input.progress_file = res.total.percent;
                        },
                        error(err) {
                            antd.$message.error(err.message);
                            antd.mes_input.file_progress = false;
                        },
                        complete(res) {
                            antd.handle_input_send('file');
                            antd.handle_cancel_upload();
                            $("#upload_file").val(''); //清空文件选择
                            this.mes_input.visible.text = true;
                        }
                    }
                    var subscription = observable.subscribe(observer);
                } else {
                    antd.$message.error('This file exceeded 50MB upload limit');
                }
            } else {
                antd.$message.error('No file selected');
            }
        },
        upload_img() {
            if ($("#upload_img")[0].files[0] !== undefined) {
                if ($("#upload_img")[0].files[0].size <= 20000000) {

                    this.mes_input.img_progress = true;

                    var pre_name = new Date().getTime();
                    var suffix = this.get_suffix($("#upload_img")[0].files[0].name);
                    var name = pre_name + suffix;

                    this.mes_input.file.name = name;
                    this.mes_input.file.url = 'https://static.ouorz.com/' + name;

                    var config = {
                        useCdnDomain: true
                    };

                    var token = this.mes_input.token;

                    var file = $("#upload_img")[0].files[0];
                    var observable = qiniu.upload(file, name, token, config)
                    var observer = {
                        next(res) {
                            antd.mes_input.progress_file = res.total.percent;
                        },
                        error(err) {
                            antd.$message.error(err.message);
                            antd.mes_input.file_progress = false;
                        },
                        complete(res) {
                            antd.$message.success('Successfully uploaded an image');
                        }
                    }
                    var subscription = observable.subscribe(observer);
                } else {
                    antd.$message.error('This file exceeded 20MB upload limit');
                }
            } else {
                antd.$message.error('No file selected');
            }
        },
        get_suffix(name) {
            var index = name.lastIndexOf('.');
            return name.substring(index);
        },
        handle_cancel_upload() {
            this.mes_input.type = 'text';
            this.mes_input.disable = false;
            this.mes_input.file_progress = false;
            this.mes_input.img_progress = false;
            this.mes_input.progress_file = 0;
            this.mes_input.progress_img = 0;
            this.mes_input.file.name = null;
            this.mes_input.file.url = null;
            this.mes_input.img.url = null;
        },
    }
});