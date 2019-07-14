/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/files.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/files.js":
/*!**********************!*\
  !*** ./src/files.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var antd = new Vue({\n    el: '#app',\n    data() {\n        return {\n            user: {\n                id: cookie.get('logged_in_id'),\n                joined_classes: [],\n                classes_info: [],\n                info: []\n            },\n            spinning: {\n                left: true,\n                center: false,\n                right: false,\n                loading: true,\n                drawer: false\n            },\n            status: {\n                files: false\n            },\n            opened_class_info: {\n                id: null,\n                superid: null,\n                index: null\n            },\n            opened_thread_info: [],\n            opened_mes_info: { //打开内容列表\n                thread_id: null,\n                class_id: null,\n                files: [],\n                thread_info: [],\n                speakers: [], //每段内容对应的发送者头像\n                index: null\n            },\n            office: {\n                visible: false,\n                title: null,\n                url: null\n            },\n            edit: {\n                file: {\n                    mes_id: null,\n                    visible: false,\n                    content: null,\n                    confirm_edit_file_loading: false,\n                }\n            },\n        }\n    },\n    mounted() {\n        axios.get('../interact/select_users.php?type=name&id=' + cookie.get('logged_in_id') + '&form=all')\n            .then(re => {\n                if (!!re.data[0].class) {\n                    this.user.joined_classes = re.data[0].class.split(',');\n                    this.user.info = re.data[0];\n                    axios.get('../interact/select_classes.php?type=class&id=' + re.data[0].class + '&form=all')\n                        .then(res => {\n                            this.user.classes_info = res.data;\n                            this.spinning.left = false;\n                        })\n                } else {\n                    //若不存在班级信息\n                    this.spinning.left = false;\n                }\n                $('#main-container').attr('style', ''); //避免爆代码\n            });\n    },\n    methods: {\n        //判断是否为班级管理员，输出特殊样式\n        class_super(index) {\n            if (parseInt(this.user.classes_info[index].super) == this.user.id) {\n                return 'super';\n            } else {\n                return '';\n            }\n        },\n        //创建/加入新班级后重新加载列表\n        get_all_classes() {\n            axios.get('../interact/select_users.php?type=class&id=' + cookie.get('logged_in_id') + '&form=single')\n                .then(re => {\n                    this.user.joined_classes = re.data.class.split(',');\n                    axios.get('../interact/select_classes.php?type=class&id=' + re.data.class + '&form=all')\n                        .then(res => {\n                            this.user.classes_info = res.data;\n                            this.spinning.left = false;\n                        })\n                });\n        },\n        //转换时间戳为时间格式\n        get_date(timeStamp) {\n            var date = new Date();\n            date.setTime(timeStamp * 1000);\n            var y = date.getFullYear();\n            var m = date.getMonth() + 1;\n            m = m < 10 ? ('0' + m) : m;\n            var d = date.getDate();\n            d = d < 10 ? ('0' + d) : d;\n            var h = date.getHours();\n            h = h < 10 ? ('0' + h) : h;\n            var minute = date.getMinutes();\n            var second = date.getSeconds();\n            minute = minute < 10 ? ('0' + minute) : minute;\n            second = second < 10 ? ('0' + second) : second;\n            return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;\n        },\n        //通过时间戳只获取年月日\n        get_date_d(timeStamp) {\n            var date = new Date();\n            date.setTime(timeStamp * 1000);\n            var y = date.getFullYear();\n            var m = date.getMonth() + 1;\n            m = m < 10 ? ('0' + m) : m;\n            var d = date.getDate();\n            d = d < 10 ? ('0' + d) : d;\n            return y + '-' + m + '-' + d;\n        },\n        //转换时间戳为分秒时时间格式\n        get_time(timeStamp) {\n            var date = new Date();\n            date.setTime(timeStamp * 1000);\n            var h = date.getHours();\n            h = h < 10 ? ('0' + h) : h;\n            var minute = date.getMinutes();\n            var second = date.getSeconds();\n            minute = minute < 10 ? ('0' + minute) : minute;\n            second = second < 10 ? ('0' + second) : second;\n            return h + ':' + minute + ':' + second;\n        },\n        //今日与之前的内容段展示不同的日期格式\n        get_mes_date(timeStamp) {\n            //发送于今日\n            if (this.get_date_d(timeStamp) == this.get_date_d(Math.round(new Date().getTime() / 1000))) {\n                return this.get_time(timeStamp);\n            } else { //未在今日\n                return this.get_date(timeStamp);\n            }\n        },\n\n        load_file() {\n            axios.get('../interact/select_files.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)\n                .then(response => {\n                    this.opened_mes_info.files = response.data.files;\n                })\n        },\n        remove_file(file_id) {\n            var formData = new FormData();\n            formData.append('user', antd.user.id);\n            formData.append('mes_id', file_id);\n            formData.append('class_id', antd.opened_mes_info.class_id);\n            formData.append('thread_id', antd.opened_mes_info.thread_id);\n\n            $.ajax({\n                url: '../interact/delete_message.php',\n                type: \"POST\",\n                data: formData,\n                cache: false,\n                dataType: 'json',\n                processData: false,\n                contentType: false,\n                success: function (data) {\n                    if (data.status) {\n                        antd.load_file();\n                    } else {\n                        antd.$message.error(data.mes);\n                    }\n                }\n            });\n        },\n        handle_edit_file_submit() {\n            var formData = new FormData();\n            formData.append('user', antd.user.id);\n            formData.append('mes_id', antd.edit.file.id);\n            formData.append('class_id', antd.opened_mes_info.class_id);\n            formData.append('file_name', antd.edit.file.content);\n            formData.append('thread_id', antd.opened_mes_info.thread_id);\n\n            $.ajax({\n                url: '../interact/edit_file.php',\n                type: \"POST\",\n                data: formData,\n                cache: false,\n                dataType: 'json',\n                processData: false,\n                contentType: false,\n                success: function (data) {\n                    if (data.status) {\n                        antd.load_file();\n                        antd.handle_edit_file_cancel();\n                    } else {\n                        antd.$message.error(data.mes);\n                    }\n                }\n            });\n        },\n        handle_edit_file_cancel() {\n            this.edit.file.visible = false;\n        },\n        open_file_edit(id, content) {\n            this.edit.file.id = id;\n            if (content == '') {\n                this.edit.file.content = 'No Name';\n            } else {\n                this.edit.file.content = content;\n            }\n            this.edit.file.visible = true;\n        },\n\n        //点击班级获取主题在 center 列展示\n        open_class(id, index) {\n            //选中增加 class，删除其余选中 class 与 thread\n            $('.center .class-item').each(function () {\n                $(this).removeClass('clicked');\n            });\n            $('.left .class-item').each(function () {\n                $(this).removeClass('clicked');\n            });\n            $('#class_left' + id).addClass('clicked');\n\n            this.opened_class_info.id = id;\n            if (!!index || index == 0) {\n                this.opened_class_info.index = index;\n            }\n            this.opened_class_info.superid = this.user.classes_info[index].super;\n\n            this.spinning.center = true;\n            axios.get('../interact/select_thread.php?class_id=' + id)\n                .then(resp => {\n                    this.status.mark = false;\n                    this.opened_thread_info = resp.data;\n                    this.status.thread = true;\n                    this.spinning.center = false;\n                })\n        },\n\n\n\n        //点击主题获取消息在 right 列展示\n        open_mes(index, id, belong_class) {\n\n            this.spinning.loading = true;\n            this.status.files = true;\n            //选中增加 class，删除其余选中\n            $('.center .class-item').each(function () {\n                $(this).removeClass('clicked');\n            });\n            $('#thread_sub' + id).addClass('clicked');\n\n            this.opened_mes_info.thread_id = id;\n            this.opened_mes_info.thread_info = this.opened_thread_info[index];\n            this.opened_mes_info.class_id = belong_class;\n\n            axios.get('../interact/select_files.php?thread_id=' + this.opened_mes_info.thread_id + '&class_id=' + this.opened_mes_info.class_id)\n                .then(response => {\n                    this.opened_mes_info.files = response.data.files;\n                    axios.get('../interact/select_users.php?type=avatar&id=' + response.data.speakers + '&mes=1')\n                        .then(res => {\n                            this.opened_mes_info.speakers = res.data;\n                            antd.spinning.loading = false;\n                        })\n                })\n        },\n        //滑动到内容列表底部\n        bottom_mes() {\n            $(\"#mes-container\").scrollTop($(\"#mes-inner\")[0].scrollHeight);\n        },\n        //获取文件后缀\n        get_suffix(name) {\n            var index = name.lastIndexOf('.');\n            return name.substring(index);\n        },\n        //获取文件格式的内容段图标、颜色\n        get_file_icon(name) {\n            switch (name) {\n                case 'pdf':\n                    return new Array('pdf', 'rgb(233, 30, 99)');\n                    break;\n                case 'md':\n                    return new Array('markdown', 'rgb(0, 150, 136)');\n                    break;\n                case 'jpeg':\n                    return new Array('jpg', 'rgb(233, 30, 99)');\n                    break;\n                case 'jpg':\n                    return new Array('jpg', 'rgb(233, 30, 99)');\n                    break;\n                case 'ppt':\n                    return new Array('ppt', 'rgb(244, 67, 54)');\n                    break;\n                case 'pptx':\n                    return new Array('ppt', 'rgb(244, 67, 54)');\n                    break;\n                case 'key':\n                    return new Array('ppt', 'rgb(244, 67, 54)');\n                    break;\n                case 'doc':\n                    return new Array('word', 'rgb(3, 169, 244)');\n                    break;\n                case 'docx':\n                    return new Array('word', 'rgb(3, 169, 244)');\n                    break;\n                case 'xlsx':\n                    return new Array('excel', 'rgb(76, 175, 80)');\n                    break;\n                case 'xls':\n                    return new Array('excel', 'rgb(76, 175, 80)');\n                    break;\n                case 'png':\n                    return new Array('jpg', 'rgb(233, 30, 99)');\n                    break;\n                case 'zip':\n                    return new Array('text', 'rgb(96, 125, 139)');\n                    break;\n                case 'rar':\n                    return new Array('text', 'rgb(96, 125, 139)');\n                    break;\n                default:\n                    return new Array('unknown', 'rgb(158, 158, 158)');\n                    break;\n            }\n        },\n        open_office_preview(url, name) {\n            this.office.url = url;\n            this.office.title = name;\n            this.office.visible = true;\n        },\n        handle_office_close() {\n            this.office.visible = false;\n        },\n        if_office(name) {\n            switch (name) {\n                case 'pptx':\n                    return true;\n                    break;\n                case 'ppt':\n                    return true;\n                    break;\n                case 'doc':\n                    return true;\n                    break;\n                case 'docx':\n                    return true;\n                    break;\n                case 'xls':\n                    return true;\n                    break;\n                case 'xlsx':\n                    return true;\n                    break;\n            }\n        },\n        reverse_order(key){\n            switch(key){\n                case 'threads':\n                    this.opened_thread_info = this.opened_thread_info.reverse();\n                    $('.center .class-item').each(function () {\n                        $(this).removeClass('clicked');\n                    });\n                    break;\n                case 'classes':\n                    this.user.joined_classes = this.user.joined_classes.reverse();\n                    this.user.classes_info = this.user.classes_info.reverse();\n                    $('.left .class-item').each(function () {\n                        $(this).removeClass('clicked');\n                    });\n                    break;\n                case 'files':\n                    this.opened_mes_info.files = this.opened_mes_info.files.reverse();\n                    break;\n            }\n        },\n    }\n});\n\n//# sourceURL=webpack:///./src/files.js?");

/***/ })

/******/ });