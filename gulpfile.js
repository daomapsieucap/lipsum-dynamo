const gulp = require('gulp'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');

gulp.task('uglify', function(){
    return gulp.src(['assets/**/*.js', '!assets/**/*.min.js'])
        .pipe(uglify())
        .pipe(rename({dirname: '', extname: '.min.js'}))
        .pipe(gulp.dest('assets/js/'));
});

gulp.task('minify-css', function(){
    return gulp.src(['assets/**/*.css', '!assets/**/*.min.css'])
        .pipe(cleanCSS())
        .pipe(rename({dirname: '', extname: '.min.css'}))
        .pipe(gulp.dest('assets/css/'));
});