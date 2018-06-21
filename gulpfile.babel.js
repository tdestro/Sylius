import chug from 'gulp-chug';
import gulp from 'gulp';
import yargs from 'yargs';

const { argv } = yargs
  .options({
    rootPath: {
      description: '<path> path to web assets directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
    nodeModulesPath: {
      description: '<path> path to node_modules directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
  });

const config = [
  '--rootPath',
  argv.rootPath || '../../../../web/assets',
  '--nodeModulesPath',
  argv.nodeModulesPath || '../../../../node_modules',
];

export const buildAdmin = function buildAdmin() {
  return gulp.src('src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config }));
};
buildAdmin.description = 'Build admin assets.';

export const watchAdmin = function watchAdmin() {
  return gulp.src('src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'watch' }));
};
watchAdmin.description = 'Watch admin asset sources and rebuild on changes.';

export const buildShop = function buildShop() {
    gulp.src(['customassets/images/**/*']).pipe(gulp.dest('web/media/image'));
    gulp.src(['customassets/fonts/**/*']).pipe(gulp.dest('web/assets/shop/css/themes/default/assets/fonts'));
    gulp.src(['node_modules/semantic-ui-less/themes/default/assets/fonts/**/*']).pipe(gulp.dest('web/assets/shop/css/themes/default/assets/fonts'));

    return gulp.src('src/Sylius/Bundle/ShopBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config }));
};
buildShop.description = 'Build shop assets.';

export const watchShop = function watchShop() {
  return gulp.src('src/Sylius/Bundle/ShopBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'watch' }));
};
watchShop.description = 'Watch shop asset sources and rebuild on changes.';

export const build = gulp.parallel(buildAdmin, buildShop);
build.description = 'Build assets.';

gulp.task('admin', buildAdmin);
gulp.task('admin-watch', watchAdmin);
gulp.task('shop', buildShop);
gulp.task('shop-watch', watchShop);

export default build;
