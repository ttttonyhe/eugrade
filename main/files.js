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
                right: false,
                loading: true,
                drawer: false
            },
            status: {
                files : false
            },
            opened_class_info: {
                id: null,
                superid: null,
                index: null
            },
            opened_thread_info: [],
            opened_mes_info: { //打开内容列表
                thread_id: null,
                class_id: null,
                files: [],
                thread_info: [],
                speakers: [], //每段内容对应的发送者头像
                index: null
            },
        }
    },
    mounted() {
        axios.get('../interact/select_users.php?type=name&id=' + cookie.get('logged_in_id') + '&form=all')
            .then(re => {
                if (!!re.data[0].class) {
                    this.user.joined_classes = re.data[0].class.split(',');
                    this.user.info = re.data[0];
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data[0].class + '&form=all')
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
                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')
                        .then(res => {
                            this.user.classes_info = res.data;
                            this.spinning.left = false;
                        })
                });
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
        //点击班级获取主题在 center 列展示
        open_class(id, index) {
            //选中增加 class，删除其余选中
            $('.left .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#class' + id).addClass('clicked');
            $('#class_sub' + id).addClass('clicked');

            this.opened_class_info.id = id;
            if (!!index || index == 0) {
                this.opened_class_info.index = index;
            }
            this.opened_class_info.superid = this.user.classes_info[this.opened_class_info.index].super;

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
            this.status.files = true;
            //选中增加 class，删除其余选中
            $('.center .class-item').each(function () {
                $(this).removeClass('clicked');
            });
            $('#thread_sub' + id).addClass('clicked');

            this.opened_mes_info.thread_id = id;
            this.opened_mes_info.thread_info = this.opened_thread_info[index];
            this.opened_mes_info.class_id = belong_class;

            axios.get('../interact/select_files.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)
                .then(response => {
                    this.opened_mes_info.files = response.data.files;
                    axios.get('../interact/select_users.php?type=avatar&id=' + response.data.speakers_unique + '&mes=1')
                        .then(res => {
                            this.opened_mes_info.speakers = res.data;
                            setTimeout('antd.spinning.loading = false', 600);
                        })
                })
        },
        //滑动到内容列表底部
        bottom_mes() {
            $("#mes-container").scrollTop($("#mes-inner")[0].scrollHeight);
        },
        //获取文件后缀
        get_suffix(name) {
            var index = name.lastIndexOf('.');
            return name.substring(index);
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
    }
});