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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/signup.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/signup.js":
/*!***********************!*\
  !*** ./src/signup.js ***!
  \***********************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var cookie = {\n    \"set\": function setCookie(name, value) {\n        var Days = 30;\n        var exp = new Date();\n        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);\n        document.cookie = name + \"=\" + escape(value) + \";expires=\" + exp.toGMTString();\n    },\n    \"get\": function getCookie(name) {\n        var arr, reg = new RegExp(\"(^| )\" + name + \"=([^;]*)(;|$)\");\n        if (arr = document.cookie.match(reg))\n            return unescape(arr[2]);\n        else\n            return null;\n    },\n    \"del\": function delCookie(name) {\n        var exp = new Date();\n        exp.setTime(exp.getTime() - 1);\n        var cval = cookie.get(name);\n        if (cval != null)\n            document.cookie = name + \"=\" + cval + \";expires=\" + exp.toGMTString();\n    }\n}\n\nvar antd = new Vue({\n    el: '#app',\n    data() {\n        return {\n            login_status: 0,\n            form: null,\n            valid: null,\n            valid_text: null,\n            type: 1,\n            disable: true\n        }\n    },\n    mounted() {\n        this.form = this.$form.createForm(this);\n    },\n    methods: {\n        handle_type_change(type){\n            if(type.target.value == 'b'){\n                this.type = 2;\n            }else{\n                this.type = 1;\n            }\n        },\n        handle_check_change(value){\n            if(value.target.checked){\n                this.disable = false;\n            }else{\n                this.disable = true;\n            }\n        },\n        handleSubmit(e) {\n            e.preventDefault();\n            this.form.validateFields((err, values) => {\n                if (!err) { //无填写错误\n                    var formData = new FormData();\n                    formData.append('name', values['name']);\n                    formData.append('email', values['email']);\n                    formData.append('password', values['password']);\n                    formData.append('type', values['type']);\n\n                    $.ajax({\n                        url: 'interact/register.php',\n                        type: \"POST\",\n                        data: formData,\n                        cache: false,\n                        dataType: 'json',\n                        processData: false,\n                        contentType: false,\n                        success: function (data) {\n                            if (data.status) {\n                                antd.valid = 'success';\n                                antd.$message.success('Welcome to Pokers');\n                                cookie.set('logged_in_id',parseInt(data.mes));\n                                setTimeout('window.location.href = \"pro\"',1000);\n                            } else {\n                                antd.valid = 'error';\n                                antd.$message.error(data.mes);\n                            }\n                        }\n                    });\n                } else {\n                    this.$message.error('Incorrect information entered');\n                }\n            });\n        },\n    },\n});\n\n//# sourceURL=webpack:///./src/signup.js?");

/***/ })

/******/ });