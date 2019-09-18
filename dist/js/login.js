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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/login.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/login.js":
/*!**********************!*\
  !*** ./src/login.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var cookie = {\n    \"set\": function setCookie(name, value) {\n        var Days = 30;\n        var exp = new Date();\n        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);\n        document.cookie = name + \"=\" + escape(value) + \";expires=\" + exp.toGMTString();\n    },\n    \"get\": function getCookie(name) {\n        var arr, reg = new RegExp(\"(^| )\" + name + \"=([^;]*)(;|$)\");\n        if (arr = document.cookie.match(reg))\n            return unescape(arr[2]);\n        else\n            return null;\n    },\n    \"del\": function delCookie(name) {\n        var exp = new Date();\n        exp.setTime(exp.getTime() - 1);\n        var cval = cookie.get(name);\n        if (cval != null)\n            document.cookie = name + \"=\" + cval + \";expires=\" + exp.toGMTString();\n    }\n}\n\nvar antd = new Vue({\n    el: '#app',\n    data() {\n        return {\n            login_status: 0,\n            form: null,\n            email_valid: null,\n            valid_text: null\n        }\n    },\n    mounted() {\n        document.getElementById('form_view').style.opacity = 1;\n        this.form = this.$form.createForm(this);\n    },\n    methods: {\n        handleSubmit(e) {\n            e.preventDefault();\n            this.form.validateFields((err, values) => {\n                console.log(values);\n                if (!err) { //无填写错误\n                    this.email_valid = 'validating';\n                    var formData = new FormData();\n                    formData.append('email', values['email']);\n                    formData.append('password', values['password']);\n\n                    $.ajax({\n                        url: 'interact/login.php',\n                        type: \"POST\",\n                        data: formData,\n                        cache: false,\n                        dataType: 'json',\n                        processData: false,\n                        contentType: false,\n                        success: function (data) {\n                            if (data.status) {\n                                antd.email_valid = 'success';\n                                antd.valid_text = null;\n                                cookie.set('logged_in_id',parseInt(data.user_id));\n                                window.location.href = 'pro';\n                            } else {\n                                antd.email_valid = 'error';\n                                antd.valid_text = data.mes;\n                            }\n                        }\n                    });\n                } else {\n                    this.email_valid = 'warning';\n                    this.valid_text = 'Incorrect email or password';\n                }\n            });\n        },\n    },\n});\n\n//# sourceURL=webpack:///./src/login.js?");

/***/ })

/******/ });