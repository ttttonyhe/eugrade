var antd = new Vue({
    el: '#app',
    data() {
        return {
            md: null,
            user: {
                id: cookie.get('logged_in_id'),
                joined_classes: [],
                classes_info: [],
                info: []
            },
            spinning: {
                left: true,
                center: false,
                right: false,
                loading: true,
                drawer: false
            },
            display_classes: true,
            display_classes_text: 'Hide All',
            status: {
                mark: false,
                chat: false,
                user: false,
                thread: false,
                info: false
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
                id: null,
                superid: null,
                index: null
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
            opened_mes_info: { //打开内容列表
                thread_id: null,
                thread_info: [],
                class_id: null,
                meses: [],
                speakers: [], //每段内容对应的发送者头像
                unique_speakers: [], //去重后的总参与人数
                index: null,
                last: null //最后一段内容的唯一 id
            },
            mes_input: { //发送内容框
                rows: 1,
                op_display: false, //选项
                container: 'mes-container-normal', //聚焦和失焦时展示不同 class
                input: 'mes-input-normal',
                content: null, //内容框内容
                disable: false, //屏蔽内容框修改
                type: 'text',
                visible: { //上传图片、文件的 tooltip
                    picture: false,
                    upload: false,
                    text: true
                },
                token: null, //上传文件的 token
                file_progress: false, //上传进度条展示
                img_progress: false,
                progress_file: 0, //进度
                progress_img: 0,
                img: { //图片上传内容
                    url: null
                },
                file: { //文件上传内容
                    url: null,
                    name: null
                },
                send_text: 'Send', //在图片上传时会改变 send 按钮文字
                markdown: { //支持 markdown 格式的发送
                    status: false,
                    html: null,
                    btn: 'default'
                }
            },
            view: {
                visible: false,
                info: null
            },
            edit: {
                visible: false,
                name: null,
                confirm_edit_loading: false,
                mes: {
                    mes_id: null,
                    visible: false,
                    content: null,
                    confirm_edit_mes_loading: false,
                }
            },
            emoji_added_count: 0,
            emoji_removed_count: 0,
            unread: {
                visible: false
            },
            office: {
                visible: false,
                title: null,
                url: null
            },
            guide: {
                visible: false,
                step: 1,
                title: 'Terms of Service'
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
                $('#main-container').attr('style', ''); //避免爆代码
            });
        //md 函数初始化
        this.md = window.markdownit({
            html: true,
            xhtmlOut: false,
            breaks: true,
            linkify: true
        });

        //新用户引导信息
        if (cookie.get('pokers_intro') !== 'done') {
            this.guide.visible = true;
            cookie.set('pokers_intro', 'done');
        }
    },
    methods: {
        //判断是否为班级管理员，输出特殊样式
        class_super(index) {
            if (parseInt(this.user.classes_info[index].super) == this.user.id) {
                return 'super';
            } else {
                return '';
            }
        },
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
        open_class(id, index) {
            //选中增加 class，删除其余选中
            $('.left .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class_left' + id).addClass('clicked');

            this.opened_class_info.id = id;
            if (!!index || index == 0) {
                this.opened_class_info.index = index;
            }
            this.opened_class_info.superid = this.user.classes_info[parseInt(index)].super;

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

            this.spinning.loading = true;
            this.status.user = false;
            this.status.chat = true;
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#thread_sub' + id).addClass('clicked');

            this.opened_mes_info.index = index;
            this.opened_mes_info.thread_id = id;
            this.opened_mes_info.class_id = belong_class;
            this.opened_mes_info.thread_info = this.opened_thread_info[index];
            this.edit.name = this.opened_mes_info.thread_info.name;

            axios.get('../interact/select_messages.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)
                .then(response => {
                    this.opened_mes_info.meses = response.data.mes;
                    if (!!response.data.speakers_unique) {
                        this.opened_mes_info.unique_speakers = response.data.speakers_unique.split(',');
                    } else {
                        this.opened_mes_info.unique_speakers = [];
                    }
                    axios.get('../interact/select_users.php?type=avatar&id=' + response.data.speakers_unique + '&mes=1')
                        .then(res => {
                            this.opened_mes_info.speakers = res.data;
                            setTimeout('antd.spinning.loading = false', 600);
                        })
                })

            //数据更新
            antd.update_mes();
            var func = function () {
                antd.update_mes(1);
            }
            window.get_mes_interval = setInterval(func, 4000);
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
                        axios.get('../interact/select_thread.php?class_id=' + antd.opened_class_info.id)
                            .then(resp => {
                                antd.opened_thread_info = resp.data;
                            })
                        antd.handle_thread_cancel();
                        antd.add.thread.id = null;
                        antd.add.thread.name = null;
                    } else {
                        antd.$message.error(data.mes);
                        antd.add.confirm_thread_loading = false;
                    }
                }
            });
        },
        //获取输入框焦点回调函数
        handle_input_up() {
            this.mes_input.rows = 3;
            this.mes_input.op_display = true;
            this.mes_input.container = 'mes-container';
            this.mes_input.input = 'mes-input';
            this.bottom_mes();
        },
        //discard 按钮
        handle_input_down() {
            this.mes_input.rows = 1;
            this.mes_input.op_display = false;
            this.mes_input.container = 'mes-container-normal';
            this.mes_input.input = 'mes-input-normal';
            this.handle_cancel_upload();
        },
        //发送内容
        handle_input_send(type) {
            this.mes_input.disable = true;
            var formData = new FormData();

            if (type == 'img') {
                if (!!this.mes_input.img.url) {
                    formData.append('img_url', this.mes_input.img.url);
                    if (this.mes_input.markdown.status) {
                        if (!!this.mes_input.content) {
                            var content = this.md.render(this.mes_input.content);
                        } else {
                            var content = '';
                        }
                    } else {
                        var content = this.mes_input.content;
                    }
                    formData.append('content', content);
                    this.mes_input.type = 'text';
                    status = 1;
                } else {
                    status = 0;
                }
            } else if (type == 'file') {
                if (!!this.mes_input.file.url && !!this.mes_input.file.name) {
                    formData.append('file_url', this.mes_input.file.url);
                    formData.append('file_name', this.mes_input.file.name);
                    this.mes_input.type = 'file';
                    status = 1;
                } else {
                    status = 0;
                }
            } else {
                if (!!this.mes_input.content) {
                    if (this.mes_input.markdown.status) {
                        var content = this.md.render(this.mes_input.content);
                    } else {
                        var content = this.mes_input.content;
                    }
                    formData.append('content', content);
                    this.mes_input.type = 'text';
                    status = 1;
                } else {
                    status = 0;
                }
            }

            if (status) {

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
                            antd.handle_cancel_upload();
                            antd.opened_thread_info[antd.opened_mes_info.index].message_count++;
                            antd.bottom_mes();
                        } else {
                            antd.$message.error(data.mes);
                            antd.mes_input.disable = false;
                            antd.handle_cancel_upload();
                        }
                    }
                });
            } else {
                this.$message.error('Illegal request');
            }
        },
        //更新内容列表(按照最后一段内容唯一 id 判断是否需要更新并滑动到底部)
        update_mes(type) {
            axios.get('../interact/select_messages.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id + '&last=' + this.opened_mes_info.last)
                .then(response => {
                    if (!!response.data.update) {
                        if (response.data.update.status == 1) {
                            this.opened_mes_info.last = response.data.update.last;
                            this.opened_mes_info.meses = response.data.mes;
                            this.opened_mes_info.unique_speakers = response.data.speakers_unique.split(',');
                            axios.get('../interact/select_users.php?type=avatar&id=' + response.data.speakers_unique + '&mes=1')
                                .then(res => {
                                    this.opened_mes_info.speakers = res.data;
                                    if (!!type) {
                                        if ($(window).height() + $('#mes-container').scrollTop() >= $('#mes-inner').height()) {
                                            //当前窗口可视区域+滑动距离大于总可滑动高度,有更新直接到底部
                                            this.bottom_mes();
                                        } else {
                                            this.unread.visible = true;
                                            setTimeout("antd.unread.visible = false", 2000);
                                        }
                                        //新消息闪烁
                                        $('#mes-inner div.mes-stream:last').eq(0).addClass('mes-new-notify');
                                        setTimeout("$('#mes-inner div.mes-stream:last').eq(0).removeClass('mes-new-notify')", 500);
                                    }
                                })
                        } else {
                            this.opened_mes_info.last = response.data.update.last;
                        }
                    }
                })
        },
        load_mes() {
            axios.get('../interact/select_messages.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)
                .then(response => {
                    this.opened_mes_info.meses = response.data.mes;
                })
        },
        //滑动到内容列表底部
        bottom_mes() {
            $("#mes-container").scrollTop($("#mes-inner")[0].scrollHeight);
            this.unread.visible = false;
            this.unread.count += 0;
        },
        //上传文件(不可存在内容，上传后自动发送)
        upload_file() {
            if ($("#upload_file")[0].files[0] !== undefined) {
                if ($("#upload_file")[0].files[0].size <= 50000000) {

                    this.mes_input.visible.text = false;
                    this.mes_input.file_progress = true;
                    this.mes_input.disable = true;
                    this.mes_input.type = 'file';

                    var pre_name = new Date().getTime();
                    var suffix = this.get_suffix($("#upload_file")[0].files[0].name);
                    var name = pre_name + suffix;

                    this.mes_input.file.name = $("#upload_file")[0].files[0].name;
                    this.mes_input.file.url = 'https://static.ouorz.com/' + name;

                    var config = {
                        useCdnDomain: true
                    };

                    var token = this.mes_input.token;

                    var file = $("#upload_file")[0].files[0];
                    var observable = qiniu.upload(file, name, token, config)
                    var observer = {
                        next(res) {
                            antd.mes_input.progress_file = Math.round(res.total.percent);
                        },
                        error(err) {
                            antd.$message.error(err.message);
                            antd.mes_input.file_progress = false;
                        },
                        complete(res) {
                            antd.handle_input_send('file');
                            antd.handle_cancel_upload();
                            $("#upload_file").val(''); //清空文件选择
                            antd.mes_input.visible.text = true;
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
        //上传图片(可继续增加内容，上传后不直接发送)
        upload_img() {
            if ($("#upload_img")[0].files[0] !== undefined) {
                if ($("#upload_img")[0].files[0].size <= 20000000) {

                    this.mes_input.img_progress = true;
                    this.mes_input.type = 'img';

                    var pre_name = new Date().getTime();
                    var suffix = this.get_suffix($("#upload_img")[0].files[0].name);
                    var name = pre_name + suffix;

                    this.mes_input.img.url = 'https://static.ouorz.com/' + name;

                    var config = {
                        useCdnDomain: true
                    };

                    var putExtra = {
                        mimeType: ["image/png", "image/jpeg", "image/gif"]
                    };

                    var token = this.mes_input.token;

                    var file = $("#upload_img")[0].files[0];
                    var observable = qiniu.upload(file, name, token, putExtra, config)
                    var observer = {
                        next(res) {
                            antd.mes_input.progress_img = Math.round(res.total.percent);
                        },
                        error(err) {
                            antd.$message.error(err.message);
                            antd.mes_input.img_progress = false;
                        },
                        complete(res) {
                            antd.$message.success('Successfully uploaded an image');
                            antd.mes_input.img_progress = false;
                            $("#upload_img").val('');
                            antd.mes_input.send_text = 'Send(with an image)';
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
        //获取文件后缀
        get_suffix(name) {
            var index = name.lastIndexOf('.');
            return name.substring(index);
        },
        //取消上传文件
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
            this.mes_input.send_text = 'Send';
            $("#upload_img").val('');
            $("#upload_file").val('');
            this.mes_input.markdown.status = false;
            this.mes_input.markdown.btn = 'default';
        },
        //获取文件格式的内容段图标、颜色
        get_file_icon(name) {
            switch (name) {
                case 'pdf':
                    return new Array('pdf', 'rgb(233, 30, 99)');
                    break;
                case 'md':
                    return new Array('markdown', 'rgb(0, 150, 136)');
                    break;
                case 'jpeg':
                    return new Array('jpg', 'rgb(233, 30, 99)');
                    break;
                case 'jpg':
                    return new Array('jpg', 'rgb(233, 30, 99)');
                    break;
                case 'ppt':
                    return new Array('ppt', 'rgb(244, 67, 54)');
                    break;
                case 'pptx':
                    return new Array('ppt', 'rgb(244, 67, 54)');
                    break;
                case 'key':
                    return new Array('ppt', 'rgb(244, 67, 54)');
                    break;
                case 'doc':
                    return new Array('word', 'rgb(3, 169, 244)');
                    break;
                case 'docx':
                    return new Array('word', 'rgb(3, 169, 244)');
                    break;
                case 'xlsx':
                    return new Array('excel', 'rgb(76, 175, 80)');
                    break;
                case 'xls':
                    return new Array('excel', 'rgb(76, 175, 80)');
                    break;
                case 'png':
                    return new Array('jpg', 'rgb(233, 30, 99)');
                    break;
                case 'zip':
                    return new Array('text', 'rgb(96, 125, 139)');
                    break;
                case 'rar':
                    return new Array('text', 'rgb(96, 125, 139)');
                    break;
                default:
                    return new Array('unknown', 'rgb(158, 158, 158)');
                    break;
            }
        },
        //开启 markdown，提交时将内容渲染为 html
        handle_markdown() {
            if (this.mes_input.markdown.status) {
                this.mes_input.markdown.status = false;
                this.mes_input.markdown.btn = 'default';
            } else {
                this.mes_input.markdown.status = true;
                this.mes_input.markdown.btn = 'primary';
            }
        },
        //处理内容段(markdown 内容最后会误增加一个 \n 回车，在此与 \n 换行一起处理)
        process_content(content) {
            if (content.charAt(content.length - 1) == '\n') {
                return content.substr(0, content.length - 2).replace(/\n/g, '<br/>');
            } else {
                return content.replace(/\n/g, '<br/>');
            }
        },
        view_user_info(id) {
            this.view.visible = true;
            this.spinning.drawer = true;
            axios.get('../interact/select_users.php?type=name&form=all&id=' + id)
                .then(resp => {
                    this.opened_member_info.info = resp.data[0];
                    this.check_mark(id, 'user'); //判断是否收藏用户
                    this.status.info = true;
                    this.spinning.drawer = false;
                })
        },
        view_close() {
            this.view.visible = false;
        },
        //处理修改用户信息
        handle_edit_submit() {
            this.edit.confirm_edit_loading = true;
            var formData = new FormData();
            formData.append('user', this.user.id);
            formData.append('name', this.edit.name);
            formData.append('class_id', this.opened_mes_info.class_id);
            formData.append('thread_id', this.opened_mes_info.thread_id);

            $.ajax({
                url: '../interact/edit_thread.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.$message.success(data.mes);
                        antd.edit.confirm_edit_loading = false;
                        antd.handle_edit_cancel();
                        antd.opened_mes_info.thread_info.name = antd.edit.name;
                        antd.opened_thread_info[antd.opened_mes_info.index].name = antd.edit.name;
                    } else {
                        antd.$message.error(data.mes);
                        antd.edit.confirm_edit_loading = false;
                    }
                }
            });
        },
        //关闭 modal
        handle_edit_cancel() {
            this.edit.visible = false;
        },
        //删除标记
        delete_thread() {
            this.$confirm({
                title: 'Do you want to delete this thread?',
                content: 'the process can not be redone',
                onOk() {
                    var formData = new FormData();
                    formData.append('super', antd.user.id);
                    formData.append('class_id', antd.opened_mes_info.class_id);
                    formData.append('thread_id', antd.opened_mes_info.thread_id);

                    $.ajax({
                        url: '../interact/delete_thread.php',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status) {
                                antd.$message.success(data.mes);
                                antd.status.chat = false;
                                //移除当前加深
                                $('.center .class-item').each(function () {
                                    $(this).removeClass('clicked');
                                });
                                //重新获取 threads 列表
                                antd.spinning.center = true;
                                axios.get('../interact/select_thread.php?class_id=' + antd.opened_mes_info.class_id)
                                    .then(resp => {
                                        antd.status.mark = false;
                                        antd.opened_thread_info = resp.data;
                                        antd.spinning.center = false;
                                    })
                            } else {
                                antd.$message.error(data.mes);
                            }
                        }
                    });
                }
            })

        },
        comment_action: function (event) {
            event.currentTarget.className += ' mes-display';
        },
        comment_action_leave(event) {
            event.currentTarget.className = 'mes-stream';
        },
        add_emoji(type, mes_id) {
            if (this.emoji_added_count < 20) {
                this.emoji_added_count += 1;
                var formData = new FormData();
                formData.append('emoji_id', type);
                formData.append('class_id', antd.opened_mes_info.class_id);
                formData.append('mes_id', mes_id);
                formData.append('thread_id', antd.opened_mes_info.thread_id);

                $.ajax({
                    url: '../interact/add_emoji.php',
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status) {
                            antd.load_mes();
                        } else {
                            antd.$message.error(data.mes);
                        }
                    }
                });
            } else {
                antd.$message.error('You are too emotional!');
            }
        },
        remove_emoji(type, mes_id) {
            if (this.emoji_removed_count < 30) {
                this.emoji_removed_count += 1;
                var formData = new FormData();
                formData.append('emoji_id', type);
                formData.append('class_id', antd.opened_mes_info.class_id);
                formData.append('mes_id', mes_id);
                formData.append('thread_id', antd.opened_mes_info.thread_id);

                $.ajax({
                    url: '../interact/delete_emoji.php',
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status) {
                            antd.load_mes();
                        } else {
                            antd.$message.error(data.mes);
                        }
                    }
                });
            } else {
                antd.$message.error('You are too emotional!');
            }
        },
        remove_mes(mes_id) {
            var formData = new FormData();
            formData.append('user', antd.user.id);
            formData.append('mes_id', mes_id);
            formData.append('class_id', antd.opened_mes_info.class_id);
            formData.append('thread_id', antd.opened_mes_info.thread_id);

            $.ajax({
                url: '../interact/delete_message.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.load_mes();
                    } else {
                        antd.$message.error(data.mes);
                    }
                }
            });
        },
        handle_edit_mes_submit() {
            var formData = new FormData();
            formData.append('user', antd.user.id);
            formData.append('mes_id', antd.edit.mes.id);
            formData.append('class_id', antd.opened_mes_info.class_id);
            formData.append('content', antd.edit.mes.content);
            formData.append('thread_id', antd.opened_mes_info.thread_id);

            $.ajax({
                url: '../interact/edit_message.php',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        antd.load_mes();
                        antd.handle_edit_mes_cancel();
                    } else {
                        antd.$message.error(data.mes);
                    }
                }
            });
        },
        handle_edit_mes_cancel() {
            this.edit.mes.visible = false;
        },
        open_mes_edit(id, content) {
            this.edit.mes.id = id;
            if (content == '') {
                this.edit.mes.content = 'Empty Content';
            } else {
                this.edit.mes.content = content;
            }
            this.edit.mes.visible = true;
        },
        open_office_preview(url, name) {
            this.office.url = url;
            this.office.title = name;
            this.office.visible = true;
        },
        handle_office_close() {
            this.office.visible = false;
        },
        if_office(name) {
            switch (name) {
                case 'pptx':
                    return true;
                    break;
                case 'ppt':
                    return true;
                    break;
                case 'doc':
                    return true;
                    break;
                case 'docx':
                    return true;
                    break;
                case 'xls':
                    return true;
                    break;
                case 'xlsx':
                    return true;
                    break;
            }
        },
        doneGuide(key) {
            if (key == 1) {
                this.guide.step = 2;
                this.guide.title = 'Introducing Threads';
            } else if (key == 2) {
                this.guide.step = 3;
                this.guide.title = 'Introducing Classes';
            } else {
                this.guide.visible = false;
            }
        },
        same_speaker(id, index) {
            if (index !== 0 && (id == this.opened_mes_info.meses[index - 1].speaker)) {
                return 'border-left:2px solid #eee';
            }
        },
    }
});