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
                index: null,
                logs: []
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
                last: null, //最后一段内容的唯一 id，
                logs: []
            },
            mes_input: { //发送内容框
                text: 'Add a comment...',
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
            enter: {
                status: false,
                text: 'Click'
            },
            check: {
                file: {
                    status: false
                },
                img: {
                    status: false
                }
            },
            push: {
                info: [],
                classid: null,
                thread: null,
                key: []
            },
            log: {
                visible: false,
                visible_class: false
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

        //新消息推送
        var func_push = function () {
            antd.check_push();
        }
        window.get_push_interval = setInterval(func_push, 10000);
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
        open_class(id, index, push) {
            //选中增加 class，删除其余选中
            $('.left .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class_left' + id).addClass('clicked');

            this.opened_class_info.id = id;
            if (!push) {
                if (!!index || index == 0) {
                    this.opened_class_info.index = index;
                }
                this.opened_class_info.superid = this.user.classes_info[parseInt(index)].super;
            }
            this.spinning.center = true;
            axios.get('../interact/select_thread.php?class_id=' + id)
                .then(resp => {
                    this.status.mark = false;
                    this.opened_thread_info = resp.data;
                    this.status.thread = true;
                    this.spinning.center = false;
                    this.push.classid = null;
                })
        },



        //点击主题获取消息在 right 列展示
        open_mes(index, id, belong_class) {

            //清除当前 interval
            window.clearInterval(window.get_mes_interval);
            window.clearInterval(window.get_push_interval);

            //关闭当前 push 通知
            this.push.classid = null;
            this.push.thread = null;

            this.spinning.loading = true;
            this.status.user = false;
            this.status.chat = true;
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#thread_sub' + id).addClass('clicked');

            this.opened_mes_info.thread_id = id;
            this.opened_mes_info.class_id = belong_class;
            this.opened_mes_info.thread_info = this.opened_thread_info[index];
            this.opened_mes_info.index = index;
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
                            //输入框监听回车发送
                            if (cookie.get('pokers_sending') == "2") {
                                $("#message_input").unbind();
                                $("#message_input").bind("keydown", function (e) {
                                    // 兼容FF和IE和Opera    
                                    var theEvent = e || window.event;
                                    var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
                                    if (code == 13) {
                                        antd.handle_input_send('text');
                                    }
                                });
                                this.enter.status = true;
                                this.enter.text = 'Enter';
                            }
                            //新消息推送 cookie 设置
                            if (!!cookie.get('pokers_push') && !!cookie.get('pokers_thread_count')) {
                                if (cookie.get('pokers_push').split('a').indexOf(id.toString()) < 0) {
                                    var push = cookie.get('pokers_push') + 'a' + id;
                                    var count = cookie.get('pokers_thread_count') + 'a' + this.opened_mes_info.thread_info.message_count;
                                    cookie.set('pokers_push', push);
                                    cookie.set('pokers_thread_count', count);
                                }
                            } else {
                                cookie.set('pokers_push', id);
                                cookie.set('pokers_thread_count', this.opened_mes_info.thread_info.message_count);
                            }
                            this.spinning.loading = false;
                        })
                })

            //数据更新
            antd.update_mes();
            var func = function () {
                antd.update_mes(1);
            }
            var func_push = function () {
                antd.check_push();
            }
            window.get_mes_interval = setInterval(func, 5000);
            window.get_push_interval = setInterval(func_push, 10000);

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
                        var query_string = "type=user&stu_id=" + antd.mark.id + "&marker=" + antd.user.id;

                        axios.post(
                                '../interact/create_mark.php',
                                query_string
                            )
                            .then(res => {
                                if (res.data.status) {
                                    antd.$message.success(res.data.mes);
                                    antd.check_mark(antd.opened_member_info.info.id, 'user');
                                    antd.open_marks();
                                } else {
                                    antd.$message.error(res.data.mes);
                                }
                            })

                    }
                })
            } else {
                this.$confirm({
                    title: 'Do you want to mark this class?',
                    content: 'you can remove the mark later',
                    onOk() {
                        var query_string = "type=class&class_id=" + antd.mark.id + "&marker=" + antd.user.id;

                        axios.post(
                                '../interact/create_mark.php',
                                query_string
                            )
                            .then(res => {
                                if (res.data.status) {
                                    antd.$message.success(res.data.mes);
                                    antd.check_mark(antd.opened_member_info.info.id, 'class');
                                    antd.open_marks();
                                } else {
                                    antd.$message.error(res.data.mes);
                                }
                            })

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
                        var query_string = "type=user&stu_id=" + antd.mark.id + "&marker=" + antd.user.id;

                        axios.post(
                                '../interact/delete_mark.php',
                                query_string
                            )
                            .then(res => {
                                if (res.data.status) {
                                    antd.$message.success(res.data.mes);
                                    antd.check_mark(antd.opened_member_info.info.id, 'user');
                                    antd.open_marks();
                                } else {
                                    antd.$message.error(res.data.mes);
                                }
                            })


                    }
                })
            } else {
                this.$confirm({
                    title: 'Do you want to remove the mark of this class?',
                    content: 'you are able to mark back',
                    onOk() {

                        var query_string = "type=class&class_id=" + antd.mark.id + "&marker=" + antd.user.id;

                        axios.post(
                                '../interact/delete_mark.php',
                                query_string
                            )
                            .then(res => {
                                if (res.data.status) {
                                    antd.$message.success(res.data.mes);
                                    antd.check_mark(antd.opened_class_info.id, 'class');
                                    antd.open_marks();
                                } else {
                                    antd.$message.error(res.data.mes);
                                }
                            })


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

            var query_string = "class_id=" + this.add.join.id + "&stu_id=" + this.user.id;

            axios.post(
                    '../interact/join_classes.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.add.confirm_join_loading = false;
                        this.handle_create_cancel();
                        this.get_all_classes();
                        this.add.join.id = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.add.confirm_join_loading = false;
                    }
                })


        },
        //关闭 modal
        handle_thread_cancel() {
            this.add.visible_thread = false;
        },
        //处理加入班级
        handle_thread_submit(id) {
            this.add.confirm_thread_loading = true;
            this.add.thread.id = id;

            var query_string = "belong_class=" + this.add.thread.id + "&name=" + this.add.thread.name + "&creator=" + this.user.id;

            axios.post(
                    '../interact/create_thread.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.add.confirm_thread_loading = false;
                        axios.get('../interact/select_thread.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_thread_info = resp.data;
                            })
                        this.handle_thread_cancel();
                        this.add.thread.id = null;
                        this.add.thread.name = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.add.confirm_thread_loading = false;
                    }
                })


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
            this.mes_input.text = 'Sending...';
            this.mes_input.disable = true;

            var query_string = "speaker=" + this.user.id + " &speaker_name=" + encodeURIComponent(this.user.info.name) + " &belong_class=" + this.opened_mes_info.class_id + " &thread=" + this.opened_mes_info.thread_id;

            if (type == 'img') {
                if (!!this.mes_input.img.url) {
                    query_string = query_string + '&img_url=' + encodeURIComponent(this.mes_input.img.url);
                    if (this.mes_input.markdown.status) {
                        if (!!this.mes_input.content) {
                            var content = this.md.render(this.mes_input.content);
                            query_string = query_string + '&content=' + encodeURIComponent(content);
                        }
                    } else {
                        if (!!this.mes_input.content) {
                            var content = this.mes_input.content;
                            query_string = query_string + '&content=' + encodeURIComponent(content);
                        }
                    }
                    this.mes_input.type = 'text';
                    query_string = query_string + "&type=" + this.mes_input.type;
                    status = 1;
                } else {
                    status = 0;
                }
            } else if (type == 'file') {
                if (!!this.mes_input.file.url && !!this.mes_input.file.name) {
                    query_string = query_string + '&file_url=' + encodeURIComponent(this.mes_input.file.url) + '&file_name=' + encodeURIComponent(this.mes_input.file.name);
                    this.mes_input.type = 'file';
                    query_string = query_string + "&type=" + this.mes_input.type;
                    status = 1;
                } else {
                    status = 0;
                }
            } else {
                if (!!this.mes_input.content) {
                    if (this.mes_input.markdown.status) {
                        var content = this.md.render(this.mes_input.content);
                        query_string = query_string + '&content=' + encodeURIComponent(content);
                    } else {
                        var content = this.mes_input.content;
                        query_string = query_string + '&content=' + encodeURIComponent(content);
                    }
                    this.mes_input.type = 'text';
                    query_string = query_string + "&type=" + this.mes_input.type;
                    status = 1;
                } else {
                    status = 0;
                }
            }

            if (status) {

                axios.post(
                        '../interact/add_message.php',
                        query_string
                    )
                    .then(res => {
                        if (res.data.status) {
                            this.update_mes();
                            this.mes_input.content = null;
                            this.mes_input.disable = false;
                            this.handle_cancel_upload();
                            this.opened_thread_info[this.opened_mes_info.index].message_count++;
                            this.bottom_mes();
                            this.mes_input.text = 'Add a comment...';
                        } else {
                            this.$message.error(res.data.mes);
                            this.mes_input.disable = false;
                            this.handle_cancel_upload();
                            this.mes_input.text = 'Add a comment...';
                        }
                        this.push_add_one(); //cookie 中当前 thread 消息数加一
                    })


            } else {
                this.$message.error('Illegal request');
                this.mes_input.text = 'Add a comment...';
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
            this.check.file.status = false;
            this.check.img.status = false;
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

            var query_string = "user=" + this.user.id + "&name=" + encodeURIComponent(this.edit.name) + "&class_id=" + this.opened_mes_info.class_id + "&thread_id=" + this.opened_mes_info.thread_id;

            axios.post(
                    '../interact/edit_thread.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.edit.confirm_edit_loading = false;
                        this.handle_edit_cancel();
                        this.opened_mes_info.thread_info.name = this.edit.name;
                        this.opened_thread_info[this.opened_mes_info.index].name = this.edit.name;
                    } else {
                        this.$message.error(res.data.mes);
                        this.edit.confirm_edit_loading = false;
                    }
                })


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

                    var query_string = "super=" + antd.user.id + "&class_id=" + antd.opened_mes_info.class_id + "&thread_id=" + antd.opened_mes_info.thread_id;

                    axios.post(
                            '../interact/delete_thread.php',
                            query_string
                        )
                        .then(res => {
                            if (res.data.status) {
                                antd.$message.success(res.data.mes);
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
                                antd.$message.error(res.data.mes);
                            }
                        })

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

                var query_string = "mes_id=" + mes_id + "&emoji_id=" + type + "&class_id=" + this.opened_mes_info.class_id + "&thread_id=" + this.opened_mes_info.thread_id;

                axios.post(
                        '../interact/add_emoji.php',
                        query_string
                    )
                    .then(res => {
                        if (res.data.status) {
                            this.load_mes();
                        } else {
                            this.$message.error(res.data.mes);
                        }
                    })


            } else {
                this.$message.error('You are too emotional!');
            }
        },
        remove_emoji(type, mes_id) {
            if (this.emoji_removed_count < 30) {
                this.emoji_removed_count += 1;

                var query_string = "mes_id=" + mes_id + "&emoji_id=" + type + "&class_id=" + this.opened_mes_info.class_id + "&thread_id=" + this.opened_mes_info.thread_id;

                axios.post(
                        '../interact/delete_emoji.php',
                        query_string
                    )
                    .then(res => {
                        if (res.data.status) {
                            this.load_mes();
                        } else {
                            this.$message.error(res.data.mes);
                        }
                    })

            } else {
                this.$message.error('You are too emotional!');
            }
        },
        remove_mes(mes_id) {

            var query_string = "user=" + this.user.id + "&mes_id=" + mes_id + "&class_id=" + this.opened_mes_info.class_id + "&thread_id=" + this.opened_mes_info.thread_id;

            axios.post(
                    '../interact/delete_message.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.load_mes();
                        this.opened_thread_info[this.opened_mes_info.index].message_count--;
                    } else {
                        this.$message.error(res.data.mes);
                    }
                })

        },
        handle_edit_mes_submit() {

            var query_string = "content=" + this.edit.mes.content + "&user=" + this.user.id + "&mes_id=" + this.edit.mes.id + "&class_id=" + this.opened_mes_info.class_id + "&thread_id=" + this.opened_mes_info.thread_id;

            axios.post(
                    '../interact/edit_message.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.load_mes();
                        this.handle_edit_mes_cancel();
                    } else {
                        this.$message.error(res.data.mes);
                    }
                })
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
        reverse_order(key) {
            switch (key) {
                case 'threads':
                    this.opened_thread_info = this.opened_thread_info.reverse();
                    $('.center .class-item').each(function () {
                        $(this).removeClass('clicked');
                    });
                    break;
                case 'classes':
                    this.user.joined_classes = this.user.joined_classes.reverse();
                    this.user.classes_info = this.user.classes_info.reverse();
                    $('.left .class-item').each(function () {
                        $(this).removeClass('clicked');
                    });
                    break;
                case 'meses':
                    this.opened_mes_info.meses = this.opened_mes_info.meses.reverse();
                    break;
            }
        },
        enter_send() {
            if (this.enter.status) {
                $("#message_input").unbind();
                this.enter.status = false;
                this.enter.text = 'Click';
                cookie.set('pokers_sending', 1);
            } else {
                this.enter.status = true;
                this.enter.text = 'Enter';
                cookie.set('pokers_sending', 2);
                $("#message_input").unbind();
                //输入框监听回车发送
                $("#message_input").bind("keydown", function (e) {
                    // 兼容FF和IE和Opera    
                    var theEvent = e || window.event;
                    var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
                    if (code == 13) {
                        antd.handle_input_send('text');
                    }
                });
            }

        },
        check_file_selected() {
            if (!!$("#upload_file")[0]) {
                this.check.file.status = true;
            } else {
                this.check.file.status = false;
            }
        },
        check_image_selected() {
            if (!!$("#upload_img")[0]) {
                this.check.img.status = true;
            } else {
                this.check.img.status = false;
            }
        },
        //检查通知
        check_push() {
            if (!!cookie.get('pokers_push') && !!cookie.get('pokers_thread_count')) {
                axios.get('../interact/check_push.php?thread_id=' + cookie.get('pokers_push') + '&thread_count=' + cookie.get('pokers_thread_count'))
                    .then(res => {
                        this.push.info = res.data;
                        if (this.push.info !== null) {
                            for (var i = 0; i < this.push.info.length; i++) {
                                this.notify_push(this.push.info[i].classid, this.push.info[i].thread, this.push.info[i].name, this.push.info[i].diff + ' new messages');
                                var push = cookie.get('pokers_thread_count').split('a');
                                push[this.push.info[i].index] = this.push.info[i].count;
                                cookie.set('pokers_thread_count', push.join('a'));
                            }
                        }
                    })
            }
        },
        //处理通知
        notify_push(classid, thread) {
            if (parseInt(this.opened_mes_info.thread_id) !== parseInt(thread)) {
                this.push.classid = classid;
                this.push.thread = thread;
            }
        },
        view_logs(type) {
            if (type == 'thread') {
                if (this.user.info.type == 2) {
                    axios.get('../interact/select_logs.php?thread_id=' + this.opened_mes_info.thread_id)
                        .then(res => {
                            this.opened_mes_info.logs = res.data;
                            this.log.visible = true;
                        })
                } else {
                    antd.$message.error('You are not allow to view logs');
                }
            } else if (type == 'class') {
                if (this.user.info.type == 2) {
                    axios.get('../interact/select_class_logs.php?class_id=' + this.opened_class_info.id)
                        .then(res => {
                            this.opened_class_info.logs = res.data;
                            this.log.visible_class = true;
                        })
                } else {
                    antd.$message.error('You are not allow to view logs');
                }
            }
        },
        class_push(id) {
            if (id == this.push.classid) {
                return 'border-left: 4px solid rgb(255, 193, 7)';
            }
        },
        //发送内容后改变 push 的 cookie
        push_add_one() {
            var push_now = cookie.get('pokers_thread_count').split('a'); //获取消息数
            var thread_now = cookie.get('pokers_push').split('a'); //获取 thread
            var index_now = thread_now.indexOf(antd.opened_mes_info.thread_id.toString()); //获取当前 thread 所在消息数数组中的位置
            push_now[index_now] = antd.opened_thread_info[antd.opened_mes_info.index].message_count; //改变消息数
            cookie.set('pokers_thread_count', push_now.join('a')); //更新 cookie
        },
    }
});