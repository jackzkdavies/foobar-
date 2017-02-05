var gulp = require('gulp');

var useref = require('gulp-useref');
var uglify = require('gulp-uglify');
var gulpIf = require('gulp-if');
var sass = require('gulp-sass');

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
    .pipe(gulp.dest('dist'));
});
