const path = require('path');
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
    }
}