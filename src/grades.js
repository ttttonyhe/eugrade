var antd = new Vue({
    el: '#app',
    data() {
        return {
            sort: true,
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
                img: null,
                index: null
            },
            add: {
                visible: {
                    series: false,
                    topic: false,
                    record: false
                },
                confirm: {
                    series: false,
                    topic: false,
                    record: false
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
                    record: {
                        name: null,
                        date: null,
                        score: null,
                        total: null,
                        dd: null,
                        yy: null,
                        mm: null,
                        user_id: null,
                        member_index: null
                    }
                },
            },
            range: {
                visible: false,
                confirm: false,
                scale: [{
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    },
                    {
                        max: null,
                        min: null
                    }
                ]
            },
            edit: {
                visible: false,
                confirm: false,
                info: [],
                date: null,
                score: null,
                total: null,
                dd: null,
                yy: null,
                mm: null,
            },
            edit_info : {
                visible : {
                    series : false,
                    topic: false
                },
                confirm : {
                    series : false,
                    topic : false
                },
                info : {
                    series : {
                        name : null,
                        id: null
                    },
                    topic : {
                        name : null
                    }
                }
            },
            delete: {
                id: null,
                series_id: null
            },
            level_count: [0, 0, 0, 0, 0, 0, 0, 0],
            range_sign: ['a*', 'a', 'b', 'c', 'd', 'e', 'f', 'u'],
            opened_topic_info: {
                status: false,
                info: null,
                section: true,
                records_data: [],
                members_unused: [],
                series_index: null,
                grading: null,
                grading_array: [],
                average: null
            },
            opened_series_info: {
                info: null,
                status: false
            },
            chartData: {
                columns: ['name', 'score', 'total', 'percent'],
                rows: null
            },
        }
    },
    mounted() {
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
        handle_topic_submit() {
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

        //处理创建记录
        handle_record_submit() {
            this.add.info.record.date = Date.parse(this.add.info.record.yy + '-' + this.add.info.record.mm + '-' + this.add.info.record.dd) / 1000;
            this.add.confirm.record = true;
            var query_string = "user_id=" + this.add.info.record.user_id + "&belong_class=" + this.opened_class_info.id + "&belong_topic=" + this.opened_topic_info.info.id + "&name=" + this.add.info.record.name + "&creator=" + this.user.id + "&date=" + this.add.info.record.date + "&score=" + this.add.info.record.score + "&total=" + this.add.info.record.total;

            axios.post(
                    '../interact/create_record.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.add.confirm.record = false;
                        this.open_topic_info(this.opened_topic_info.info.id, this.opened_topic_info.series_index);
                        axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_series_info.info = resp.data;
                            })
                        this.add.visible.record = false;
                    } else {
                        this.$message.error(res.data.mes);
                        this.add.visible.record = false;
                        this.add.confirm.record = false;
                    }
                })


        },
        handle_record_cancel() {
            this.add.visible.record = false;
        },
        //处理修改范围
        handle_range_submit() {
            this.range.confirm = true;

            //连接范围字符串(等级|最大|最小,等级|最大|最小)
            var range_string = '';
            for (i = 0; i < 8; i++) {
                if ("undefined" != typeof this.range.scale[i].max && this.range.scale[i].max !== null) {
                    range_string += this.range_sign[i] + '|' + this.range.scale[i].max + '|' + this.range.scale[i].min + ',';
                }
            }
            range_string = range_string.substr(0, range_string.length - 1);

            var query_string = "belong_class=" + this.opened_class_info.id + "&belong_topic=" + this.opened_topic_info.info.id + "&scale=" + range_string + "&creator=" + this.user.id;

            axios.post(
                    '../interact/edit_topic_scale.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.range.confirm = false;
                        this.open_topic_info(this.opened_topic_info.info.id, this.opened_topic_info.series_index);
                        this.range.visible = false;
                    } else {
                        this.$message.error(res.data.mes);
                        this.range.confirm = false;
                        this.range.visible = false;
                    }
                })

        },
        handle_range_cancel() {
            this.range.visible = false;
        },
        edit_record(index) {
            this.edit.visible = true;
            this.edit.info = this.opened_topic_info.records_data[index];
            this.edit.yy = this.get_date(this.edit.info.date, 'y');
            this.edit.mm = this.get_date(this.edit.info.date, 'm');
            this.edit.dd = this.get_date(this.edit.info.date, 'd');
            this.edit.score = this.edit.info.score;
            this.edit.total = this.edit.info.total;
        },
        //处理修改记录
        handle_edit_submit() {
            this.edit.confirm = true;

            var date = Date.parse(this.edit.yy + '-' + this.edit.mm + '-' + this.edit.dd) / 1000;

            var query_string = "record=" + this.edit.info.id + "&user_id=" + this.edit.info.user_id + "&belong_class=" + this.opened_class_info.id + "&belong_topic=" + this.opened_topic_info.info.id + "&creator=" + this.user.id + "&date=" + date + "&score=" + this.edit.score + "&total=" + this.edit.total;

            axios.post(
                    '../interact/edit_record.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.edit.confirm = false;
                        this.open_topic_info(this.opened_topic_info.info.id, this.opened_topic_info.series_index);
                        this.edit.visible = false;
                    } else {
                        this.$message.error(res.data.mes);
                        this.edit.confirm = false;
                        this.edit.visible = false;
                    }
                })

        },
        handle_edit_cancel() {
            this.edit.visible = false;
        },
        //处理修改记录
        delete_record(id) {
            this.delete.id = id;
            this.$confirm({
                title: 'Do you want to delete this record?',
                content: 'the process can not be redone',
                onOk() {

                    var query_string = "class_id=" + antd.opened_class_info.id + "&super=" + antd.user.id + "&topic_id=" + antd.opened_topic_info.info.id + "&record_id=" + antd.delete.id;

                    axios.post(
                            '../interact/delete_record.php',
                            query_string
                        )
                        .then(res => {
                            if (res.data.status) {
                                antd.$message.success(res.data.mes);
                                antd.open_topic_info(antd.opened_topic_info.info.id, antd.opened_topic_info.series_index);
                            } else {
                                antd.$message.error(res.data.mes);
                            }
                        })

                }
            })
        },
        //处理删除主题
        delete_topic() {
            this.$confirm({
                title: 'Do you want to delete this topic?',
                content: 'the process can not be redone',
                onOk() {

                    var query_string = "class_id=" + antd.opened_class_info.id + "&super=" + antd.user.id + "&topic_id=" + antd.opened_topic_info.info.id + "&series_id=" + antd.opened_topic_info.info.belong_series;

                    axios.post(
                            '../interact/delete_topic.php',
                            query_string
                        )
                        .then(res => {
                            if (res.data.status) {
                                antd.$message.success(res.data.mes);
                                antd.opened_topic_info.status = false;
                                antd.open_class_info(antd.opened_class_info.index);
                            } else {
                                antd.$message.error(res.data.mes);
                            }
                        })

                }
            })
        },
        //处理删除系列
        delete_series(id) {
            this.delete.series_id = id;
            this.$confirm({
                title: 'Do you want to delete this series?',
                content: 'the process can not be redone',
                onOk() {

                    var query_string = "class_id=" + antd.opened_class_info.id + "&super=" + antd.user.id + "&series_id=" + antd.delete.series_id;

                    axios.post(
                            '../interact/delete_series.php',
                            query_string
                        )
                        .then(res => {
                            if (res.data.status) {
                                antd.$message.success(res.data.mes);
                                antd.open_class_info(antd.opened_class_info.index);
                            } else {
                                antd.$message.error(res.data.mes);
                            }
                        })

                }
            })
        },
        //处理编辑主题
        handle_edit_info_topic_submit() {
            var query_string = "belong_series=" + this.opened_topic_info.info.belong_series + "&belong_class=" + this.opened_class_info.id + "&topic_id=" + this.opened_topic_info.info.id + "&name=" + this.edit_info.info.topic.name + "&creator=" + this.user.id;

            axios.post(
                    '../interact/edit_topic.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.edit_info.confirm.topic = false;
                        axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_series_info.info = resp.data;
                            })
                        this.opened_topic_info.status = false;
                        this.edit_info.visible.topic = false;
                        this.edit_info.info.topic.name = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.edit_info.visible.topic = false;
                        this.edit_info.info.topic.name = null;
                        this.edit_info.confirm.topic = false;
                    }
                })


        },
        handle_edit_info_topic_cancel() {
            this.edit_info.visible.topic = false;
        },
        //处理编辑系列
        handle_edit_info_series_submit() {
            var query_string = "&belong_class=" + this.opened_class_info.id + "&series_id=" + this.edit_info.info.series.id + "&name=" + this.edit_info.info.series.name + "&creator=" + this.user.id;

            axios.post(
                    '../interact/edit_series.php',
                    query_string
                )
                .then(res => {
                    if (res.data.status) {
                        this.$message.success(res.data.mes);
                        this.edit_info.confirm.series = false;
                        axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                            .then(resp => {
                                this.opened_series_info.info = resp.data;
                            })
                        this.opened_topic_info.status = false;
                        this.edit_info.visible.series = false;
                        this.edit_info.info.series.name = null;
                    } else {
                        this.$message.error(res.data.mes);
                        this.edit_info.visible.topic = false;
                        this.edit_info.info.topic.name = null;
                        this.edit_info.confirm.topic = false;
                    }
                })


        },
        handle_edit_info_series_cancel() {
            this.edit_info.visible.series = false;
        },
        open_edit_info_series(id,name){
            this.edit_info.info.series.id = id;
            this.edit_info.info.series.name = name;
            this.edit_info.visible.series = true;
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

            this.level_count = [0, 0, 0, 0, 0, 0, 0, 0];
            this.opened_topic_info.status = false;

            this.spinning.center = true;
            this.opened_class_info.index = index;
            this.opened_class_info.id = this.user.classes_info[index].id;
            this.opened_class_info.superid = this.user.classes_info[index].super;

            //获取全部班级成员
            axios.get('../interact/select_classes.php?type=member&id=' + this.opened_class_info.id + '&form=single')
                .then(recc => {
                    this.opened_class_info.members_string = recc.data.member;
                    axios.get('../interact/select_users.php?type=name&id=' + recc.data.member + '&form=all')
                        .then(rec => {
                            this.opened_class_info.members = rec.data;
                            axios.get('../interact/select_series.php?class_id=' + this.opened_class_info.id)
                                .then(resp => {
                                    this.opened_series_info.info = resp.data;
                                    this.spinning.center = false;
                                    this.opened_series_info.status = true;
                                })
                        });
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
        //点击用户获取信息在 right 列展示
        open_topic_info(id, index) {
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#topic_sub' + id).addClass('clicked');

            this.spinning.right = true;
            this.opened_topic_info.series_index = index;
            this.opened_topic_info.grading_array = [];

            this.level_count = [0, 0, 0, 0, 0, 0, 0, 0];

            this.range.scale = [{
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }, {
                max: null,
                min: null
            }];

            axios.get('../interact/select_topic.php?topic_id=' + parseInt(id))
                .then(res => {

                    this.opened_topic_info.info = res.data[0];
                    this.opened_topic_info.average = this.opened_topic_info.info.average;
                    //初始按照 score 升序排序
                    this.opened_topic_info.records_data = res.data['records'].sort(
                        firstBy('score')
                    );

                    this.edit_info.info.topic.name = this.opened_topic_info.info.name;

                    this.opened_topic_info.grading = this.opened_topic_info.info.scale;
                    //转换全部成员 id 字符串为数组
                    var members_array = this.opened_class_info.members_string.split(',');
                    //循环全部已有记录匹配已记录的成员
                    for (i = 0; i < (this.opened_topic_info.records_data).length; ++i) {
                        if (members_array.indexOf(this.opened_topic_info.records_data[i].user_id + '') > -1) {
                            members_array.splice(members_array.indexOf(this.opened_topic_info.records_data[i].user_id + ''), 1);
                        }
                    }
                    //转换未存在记录的成员 id 数组为字符串
                    var members_string = members_array.join(',');

                    axios.get('../interact/select_users.php?type=name&id=' + members_string + '&form=all')
                        .then(rec => {
                            this.opened_topic_info.members_unused = rec.data;
                            if ((this.opened_topic_info.members_unused).length) {
                                this.add.info.record.name = this.opened_topic_info.members_unused[0].name;
                                this.add.info.record.user_id = this.opened_topic_info.members_unused[0].id;
                                this.add.info.record.member_index = 0;
                            }
                        });

                    //统计表
                    this.chartSettings = {
                        showLine: ['percent'],
                        axisSite: {
                            right: ['percent']
                        },
                        yAxisType: ['KMB', 'percent'],
                        yAxisName: ['Score', 'Percentage']
                    }
                    this.chartData.rows = this.opened_topic_info.records_data;


                    //范围展示
                    if (!!this.opened_topic_info.grading) {
                        var array_1 = this.opened_topic_info.grading.split(',');
                        for (i = 0; i < array_1.length; ++i) { //所有存在记录的段位
                            for (k = 0; k < (this.range_sign).length; ++k) { //当前段位与全部段位名匹配
                                if (array_1[i].split('|')[0] == this.range_sign[k]) { //赋值匹配到的段位最大最小值
                                    this.range.scale[k].max = array_1[i].split('|')[1];
                                    this.range.scale[k].min = array_1[i].split('|')[2];
                                    break;
                                } else {
                                    continue;
                                }
                            }
                        }
                    }

                    this.opened_topic_info.status = true;
                    this.spinning.right = false;
                })
        },
        //转换时间戳为时间格式
        get_date(timeStamp, key) {
            var date = new Date();
            date.setTime(timeStamp * 1000);
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? ('0' + m) : m;
            var d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            if (key == 'y') {
                return y;
            } else if (key == 'm') {
                return m;
            } else if (key == 'd') {
                return d;
            }
            return y + '-' + m + '-' + d;
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
            if (this.sort) {
                this.opened_topic_info.records_data = this.opened_topic_info.records_data.sort(
                    firstBy(key, -1)
                );
                this.sort = !this.sort;
            } else {
                this.opened_topic_info.records_data = this.opened_topic_info.records_data.sort(
                    firstBy(key)
                );
                this.sort = !this.sort;
            }
        },
        change_y(value) {
            this.add.info.record.yy = value;
        },
        change_d(value) {
            this.add.info.record.dd = value;
        },
        change_m(value) {
            this.add.info.record.mm = value;
        },
        change_name(value) {
            this.add.info.record.name = value.split('|')[0];
            this.add.info.record.user_id = parseInt(value.split('|')[1]);
            this.add.info.record.member_index = parseInt(value.split('|')[2]);
        },
        change_a(value) {
            this.range.scale[0].min = (value + 0.1).toFixed(1);
        },
        change_b(value) {
            this.range.scale[1].min = (value + 0.1).toFixed(1);
        },
        change_c(value) {
            this.range.scale[2].min = (value + 0.1).toFixed(1);
        },
        change_d(value) {
            this.range.scale[3].min = (value + 0.1).toFixed(1);
        },
        change_e(value) {
            this.range.scale[4].min = (value + 0.1).toFixed(1);
        },
        change_f(value) {
            this.range.scale[5].min = (value + 0.1).toFixed(1);
        },
        change_u(value) {
            this.range.scale[6].min = (value + 0.1).toFixed(1);
        },
        inRange(x, min, max) {
            return ((x - min) * (x - max) <= 0);
        },
        get_record_level(percent,index) {
            if (!!this.opened_topic_info.grading) {
                //百分比乘 100
                var percent = percent * 100;
                var array = [];
                //段位数组
                var array_1 = this.opened_topic_info.grading.split(',');
                for (i = 0; i < array_1.length; i++) { //段位范围、名字数组
                    array[i] = [];
                    array[i]['sign'] = array_1[i].split('|')[0];
                    array[i]['max'] = array_1[i].split('|')[1];
                    array[i]['min'] = array_1[i].split('|')[2];
                }
                for (i = 0; i < array.length; i++) { //遍历全部段位
                    if (this.inRange(percent, array[i]['min'], array[i]['max'])) {
                        //record 源数据无 level 信息，赋值到 index 对应 record 的 level 信息
                        this.opened_topic_info.records_data[index]['level'] = array[i]['sign'];
                        return array[i]['sign'].toUpperCase();
                    } else {
                        continue;
                    }
                }
            }
        },
        //遍历全部 record 数据，变更 level_count 数据
        get_levels(){
            for(i=0;i<(this.opened_topic_info.records_data).length;i++){
                if(!!this.opened_topic_info.records_data[i].level){
                    this.level_count[this.range_sign.indexOf(this.opened_topic_info.records_data[i].level)] += 1;
                }
            }
        },
    }
});