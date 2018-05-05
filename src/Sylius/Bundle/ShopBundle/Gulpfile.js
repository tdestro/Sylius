var concat = require('gulp-concat');
var env = process.env.GULP_ENV;
var gulp = require('gulp');
var less = require('gulp-less');
var gulpif = require('gulp-if');
var livereload = require('gulp-livereload');
var merge = require('merge-stream');
var order = require('gulp-order');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var uglifycss = require('gulp-uglifycss');
var argv = require('yargs').argv;

var rootPath = argv.rootPath;
var shopRootPath = rootPath + 'shop/';
var vendorPath = argv.vendorPath || '';
var vendorShopPath = '' === vendorPath ? '' : vendorPath + 'ShopBundle/';
var vendorUiPath = '' === vendorPath ? '../UiBundle/' : vendorPath + 'UiBundle/';
var nodeModulesPath = argv.nodeModulesPath;

var paths = {
    shop: {
        js: [
            nodeModulesPath + 'jquery/dist/jquery.min.js',
            '../../../semantic/semantic.js',
            nodeModulesPath + 'semantic-ui-less/definitions/globals/site.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/api.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/colorize.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/form.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/state.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/visibility.js',
            nodeModulesPath + 'semantic-ui-less/definitions/behaviors/visit.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/accordion.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/checkbox.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/dimmer.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/dropdown.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/embed.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/modal.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/nag.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/popup.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/progress.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/rating.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/search.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/shape.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/sidebar.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/sticky.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/tab.js',
            nodeModulesPath + 'semantic-ui-less/definitions/modules/transition.js',
            nodeModulesPath + 'lightbox2/dist/js/lightbox.js',
            vendorUiPath + 'Resources/private/js/**',
            vendorShopPath + 'Resources/private/js/**'
        ],
        less: ['../../../semantic/semantic.less'],
        sass: [
            vendorUiPath + 'Resources/private/sass/**',
            vendorShopPath + 'Resources/private/sass/**'
        ],
        css: [
            nodeModulesPath + 'lightbox2/dist/css/lightbox.css',
            vendorUiPath + 'Resources/private/css/**',
            vendorShopPath + 'Resources/private/css/**',
            vendorShopPath + 'Resources/private/scss/**'
        ],
        img: [
            vendorUiPath + 'Resources/private/img/**',
            vendorShopPath + 'Resources/private/img/**'
        ]
    }
};

gulp.task('shop-js', function () {
    return gulp.src(paths.shop.js)
        .pipe(concat('app.js'))
        .pipe(gulpif(env === 'prod', uglify()))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(shopRootPath + 'js/'))
    ;
});

gulp.task('shop-css', function() {
    gulp.src([nodeModulesPath + 'semantic-ui-css/themes/**/*']).pipe(gulp.dest(shopRootPath + 'css/themes/'));

    var cssStream = gulp.src(paths.shop.css)
            .pipe(concat('css-files.css'))
        ;

    var sassStream = gulp.src(paths.shop.sass)
            .pipe(sass())
            .pipe(concat('sass-files.scss'))
        ;

    var lessStream = gulp.src(paths.shop.less)
        .pipe(less())
        .pipe(concat('less-files.less'))
    ;

    return merge(cssStream, sassStream, lessStream)
        .pipe(order(['css-files.css', 'sass-files.scss', 'less-files.less']))
        .pipe(concat('style.css'))
        .pipe(gulpif(env === 'prod', uglifycss()))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(shopRootPath + 'css/'))
        .pipe(livereload())
    ;
});

gulp.task('shop-img', function() {
    gulp.src([nodeModulesPath + 'lightbox2/dist/images/*']).pipe(gulp.dest(shopRootPath + 'images/'));

    return gulp.src(paths.shop.img)
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(shopRootPath + 'img/'))
    ;
});

gulp.task('shop-watch', function() {
    livereload.listen();

    gulp.watch(paths.shop.js, ['shop-js']);
    gulp.watch(paths.shop.sass, ['shop-css']);
    gulp.watch(paths.shop.css, ['shop-css']);
    gulp.watch(paths.shop.img, ['shop-img']);
});

gulp.task('default', ['shop-js', 'shop-css', 'shop-img']);
gulp.task('watch', ['default', 'shop-watch']);
