var gulp = require('gulp');

var useref = require('gulp-useref');
var uglify = require('gulp-uglify');
var gulpIf = require('gulp-if');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var browserSync = require('browser-sync').create();

gulp.task('default', ['useref', 'libs', 'sass']);

gulp.task('useref', function(){
  return gulp.src('../myAppSrc.html')
    .pipe(useref())
    // Minifies only if it's a JavaScript file
    // .pipe(gulpIf('*.js', uglify())) THIS IS NOT ANGULAR FRIENDLY
    .pipe(gulp.dest('dist'))
});

gulp.task('libs', function(){
  return gulp.src('../libs.html')
    .pipe(useref())
    // Minifies only if it's a JavaScript file
    .pipe(gulpIf('*.js', uglify()))
    .pipe(gulp.dest('dist'))
});

gulp.task('sass', function () {
  return gulp.src('../assets/sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('dist'))    
    // .pipe(browserSync.reload({
    //   stream: true
    // }));
});

gulp.task('browserSync', function() {
  browserSync.init({
    server: {
      baseDir: 'app'
    },
  })
})

gulp.task('watch', ['browserSync'], function () {
  gulp.watch('../assets/sass/**/*.scss', ['sass']);
    // Reloads the browser whenever HTML or JS files change
  gulp.watch('../app/**/*.js', ['useref']);
  // gulp.watch('../app/**/*.html', browserSync.reload); 
});