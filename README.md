<div align="center">
  <h1>Eugrade</h1>
  <p>Communication and Collaboration Platform for Education</p>
  <a href="https://github.com/HelipengTony/pokers">
    <img src="https://img.shields.io/github/forks/HelipengTony/pokers.svg" alt="forks">
  </a>

  <a href="https://github.com/HelipengTony/pokers">
    <img src="https://img.shields.io/github/stars/HelipengTony/pokers.svg" alt="stars">
  </a>

  <a href="https://github.com/HelipengTony/pokers">
    <img src="https://img.shields.io/github/license/HelipengTony/pokers.svg" alt="license">
  </a>
</div>

<br/>

<br/>

## Main Features 功能特色
+ **Based on PHP & Vue.js & And Design for Vue**
  - 基于 PHP 、Vue.js 与 Antd
+ **Developed using Webpack & Sass**
  - 采用了 Webpack 与 Sass
+ **WebSocket Supported (Based on Workerman)**
  - 采用 WebSocket 并支持心跳的聊天系统
+ **Organized Classes / Files / Grades System**
  - 高效的团队(班级)/绩点/文件系统
    - 班级(成员)管理
    - 成绩录入
    - 成绩展示
    - 成绩图标
    - 成绩趋势
    - 文件名编辑
    - 文件按话题归档
    - Office 文件预览
    - 班级成员批量生成
    - ...
+ **Powerful Instant messaging system**
  - 简洁强大的群聊系统
    - 图片上传
    - 文件上传
    - MarkDown 渲染
    - emoji 评论
    - 删除/编辑消息
    - 消息置顶
    - ...
+ **Neat UI design (English/Chinese Language Supported)**
  - 美丽的 UI 设计
    - English
    - 简体中文
    - 语言一键切换
+ **0 sql everywhere (Based on Lazer-Database)**
  - 完全无数据库 (整个系统基于 json)
+ **All-round UX Design (Inspired from Twist)**
  - 全方位多元的用户交互操作
+ **Easy to do secondary development**
  - 易于二次开发

<br/>

## 截图 ScreenShots

### 群聊 Messages
![群聊](https://i.loli.net/2019/08/11/9mlG8aY1pk26vVc.png)

<br/>

### 文件管理 Files
![QQ20190811-154033@2x.png](https://i.loli.net/2019/08/11/7TrzcqCoj6JBMLZ.png)

<br/>

### 成绩管理 Grades
#### 成绩录入 Grades Management
![QQ20190811-154231@2x.png](https://i.loli.net/2019/08/11/slKgJBHyPR3awpY.png)

#### 成绩统计 Grades Presentation
![QQ20190811-154243@2x.png](https://i.loli.net/2019/08/11/geo13G6n4S5Oxud.png)

<br/>

### 班级管理 Classes
![QQ20190811-154309@2x.png](https://i.loli.net/2019/08/11/hVxluvskWZtI7wc.png)

<br/>

## Usage 使用方法
+ 将 repo clone 到目录
+ 本地环境为 Php5.6+ & Nodejs & Ruby (sass + compass)
+ PHP 必须包含 pcntl、posix、Event / libevent 扩展
+ 执行 composer install 安装全部依赖
+ 执行 npm install / cnpm install 安装依赖包
+ 执行 npm run dev 打包编译
+ 参照 Workerman 手册 [配置WSS](http://doc.workerman.net/faq/secure-websocket-server.html)
+ interact 目录执行 php websocket.php start -d & 常驻后台

<br/>

## Todo List 正在开发
+ [x] 群聊支持 WebSocket
+ [ ] 用户系统支持 JWT
+ [ ] API 请求 JWT 鉴权
+ [x] Sass 重写 CSS
+ [x] Grades 成绩管理系统
+ [ ] Schools 校园系统
+ [ ] 模块化重构
+ [x] 网站首页

<br/>

## Donation 赞助作者
If you feel that my project is helpful to you and you are willing to give me a little support, you can donate to me in the following ways, which will help me to maintain the continuous development of this project, thank you very much!
<br/>

![Donate](https://i.loli.net/2019/02/18/5c6a80afd1e26.png)

Your name will be kept on the list [Donation](https://www.snapaper.com/donate)

<br/>

This is a personal project, you will get no help from me :)
