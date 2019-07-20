var antd = new Vue({
    el: '#app',
    data() {
        return {
            sort:true,
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
                visible: {
                    series: false,
                    topic: false,
                },
                confirm: {
                    series: false,
                    topic: false,
                },
                info: {
                    series: {
                        name: null
                    },
                    topic: {
                        name: null,
                        series: {
                            name: null,
                            id: null
                        }
                    },
                },
            },
            opened_topic_info: {
                status: false,
                info: null,
                section: true
            },
            opened_series_info: {
                info: null,
                status: false
            },
            chartData: {
                columns: ['name','score'],
                rows: null
            },
            cdata: []
        }
    },
    mounted() {
        for (let i = 0; i < 3; i++) {
            this.cdata.push({
                date: i.toString(),
                name: `Edrward ${i}`,
                score: Math.random(),
                total: 50,
                level: 'A'
            })
        }
        axios.get('../interact/select_users.php?type=class&id=' + cookie.get('logged_in_id') + '&form=single')
            .then(re => {
                if (!!re.data.class) {
                    this.user.joined_classes = re.data.class.split(',');
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
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
    },
    methods: {
        //处理创建系列
        handle_series_submit(id) {
            this.add.confirm.series = true;
            var query_string = "belong_class=" + parseInt(id) + "&name=" + this.add.info.series.name + "&creator=" + this.user.id;

            axios.post(
                    '../interact/create_series.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.add.confirm.series = false;
                        axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_series_info.info = resp.data;
                            })
                        this.add.visible.series = false;
                        this.add.info.series.name = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.add.visible.series = false;
                        this.add.info.series.name = null;
                        this.add.confirm.series = false;
                    }
                })


        },
        handle_series_cancel() {
            this.add.visible.series = false;
        },
        open_topic_submit(id, name) {
            this.add.visible.topic = true;
            this.add.info.topic.series.name = name;
            this.add.info.topic.series.id = id;
        },
        //处理创建主题
        handle_topic_submit(id) {
            this.add.confirm.topic = true;
            var query_string = "belong_series=" + this.add.info.topic.series.id + "&belong_class=" + this.opened_class_info.id + "&name=" + this.add.info.topic.name + "&creator=" + this.user.id;

            axios.post(
                    '../interact/create_topic.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.add.confirm.topic = false;
                        axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_series_info.info = resp.data;
                            })
                        this.add.visible.topic = false;
                        this.add.info.topic.name = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.add.visible.topic = false;
                        this.add.info.topic.name = null;
                        this.add.confirm.topic = false;
                    }
                })


        },
        handle_topic_cancel() {
            this.add.visible.topic = false;
        },



        //判断是否为班级管理员，输出特殊样式
        class_super(index) {
            if (parseInt(this.user.classes_info[index].super) == this.user.id) {
                return 'super';
            } else {
                return '';
            }
        },
        //点击班级获取信息在 center 列展示
        open_class_info(index) {
            //选中增加 class，删除其余选中
            $('.class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class' + index).addClass('clicked');

            this.spinning.center = true;
            axios.get('../interact/select_users.php?type=name&id=' + parseInt(this.user.classes_info[index].super) + '&form=single')
                .then(rec => {
                    this.opened_class_info.supername = rec.data.name;
                    this.opened_class_info.superid = this.user.classes_info[index].super;
                    this.opened_class_info.id = this.user.classes_info[index].id;

                    axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                        .then(resp => {
                            this.opened_series_info.info = resp.data;
                            this.spinning.center = false;
                            this.opened_series_info.status = true;
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
        open_topic_info(id) {
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#topic_sub' + id).addClass('clicked');
            this.spinning.right = true;
            this.opened_topic_info.status = true;
            this.cdata = this.cdata.sort(
                firstBy('score')
            );
            this.chartData.rows = this.cdata;
            this.spinning.right = false;
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
        reverse_order(key) {
            switch (key) {
                case 'classes':
                    this.user.joined_classes = this.user.joined_classes.reverse();
                    this.user.classes_info = this.user.classes_info.reverse();
                    $('.left .class-item').each(function () {
                        $(this).removeClass('clicked');
                    });
                    break;
            }
        },
        sortBy(key) {
            if(this.sort){
                this.cdata = this.cdata.sort(
                    firstBy(key, -1)
                );
                this.sort = !this.sort;
            }else{
                this.cdata = this.cdata.sort(
                    firstBy(key)
                );
                this.sort = !this.sort;
            }
        },
    }
});