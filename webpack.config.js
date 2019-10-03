const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
    entry: {
        messages : './src/messages.js',
        files : './src/files.js',
        classes : './src/classes.js',
        login : './src/login.js',
        reset : './src/reset.js',
        signup : './src/signup.js',
        grades : './src/grades.js'
    },

    output : {
        filename : '[name].js',
        path : path.resolve('dist/js/')
    },
    
    plugins: [
        new HtmlWebpackPlugin({
            chunks:['login'],
            minify:{
                collapseWhitespace:true //压缩代码
            },
            hash:true,
            filename: '../../login.html', // 配置输出文件名和路径
            template: 'assets/login.html', // 配置文件模板
          }),
        new HtmlWebpackPlugin({
            chunks:['signup'],
            minify:{
                collapseWhitespace:true //压缩代码
            },
            hash:true,
            filename: '../../signup.html', // 配置输出文件名和路径
            template: 'assets/signup.html', // 配置文件模板
          }),
        new HtmlWebpackPlugin({
            chunks:['reset'],
            minify:{
                collapseWhitespace:true //压缩代码
            },
            hash:true,
            filename: '../../reset.html', // 配置输出文件名和路径
            template: 'assets/reset.html', // 配置文件模板
          }),
          new HtmlWebpackPlugin({
            chunks:['messages'],
            hash:true,
            filename: '../../pro/messages.html', // 配置输出文件名和路径
            template: 'assets/messages.html', // 配置文件模板
          }),
          new HtmlWebpackPlugin({
            chunks:['files'],
            hash:true,
            filename: '../../pro/files.html', // 配置输出文件名和路径
            template: 'assets/files.html', // 配置文件模板
          }),
          new HtmlWebpackPlugin({
            chunks:['classes'],
            hash:true,
            filename: '../../pro/classes.html', // 配置输出文件名和路径
            template: 'assets/classes.html', // 配置文件模板
          }),
      ],
}