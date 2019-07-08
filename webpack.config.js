const path = require('path');
module.exports = {
    entry: {
        messages : './src/messages.js',
        files : './src/files.js',
        classes : './src/classes.js',
        login : './src/login.js',
        signup : './src/signup.js'
    },
    output : {
        filename : '[name].js',
        path : path.resolve('dist/js/')
    }
}