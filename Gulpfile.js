var gulp = require('gulp');
var chug = require('gulp-chug');
var argv = require('yargs').argv;

config = [
    '--rootPath',
    argv.rootPath || '../../../../web/assets/',
    '--nodeModulesPath',
    argv.nodeModulesPath || '../../../../node_modules/'
];

gulp.task('admin', function() {
    gulp.src('src/Sylius/Bundle/AdminBundle/Gulpfile.js', { read: false })
        .pipe(chug({ args: config }))
    ;
});

gulp.task('admin-watch', function () {
    gulp.src('src/Sylius/Bundle/AdminBundle/Gulpfile.js', { read: false })
        .pipe(chug({ args: config, tasks: 'watch' }))
    ;
});

gulp.task('shop', function() {

    gulp.src(['customassets/images/**/*']).pipe(gulp.dest('web/media/image'));
    gulp.src(['customassets/fonts/**/*']).pipe(gulp.dest('web/assets/shop/css/themes/default/assets/fonts'));
    gulp.src('src/Sylius/Bundle/ShopBundle/Gulpfile.js', { read: false })
        .pipe(chug({ args: config }))
    ;
});

gulp.task('shop-watch', function () {
    gulp.src('src/Sylius/Bundle/ShopBundle/Gulpfile.js', { read: false })
        .pipe(chug({ args: config, tasks: 'watch' }))
    ;
});

gulp.task('default', ['admin', 'shop']);
