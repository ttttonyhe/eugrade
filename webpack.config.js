const path = require('path');
module.exports = {
    entry: {
        messages : './main/messages.js',
        files : './main/files.js',
        classes : './main/classes.js',
        login : './main/login.js',
        signup : './main/signup.js'
    },
    output : {
        filename : '[name].js',
        path : path.resolve('dist')
    }
}