var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var autoprefixer = require('gulp-autoprefixer');
var prefix = autoprefixer.default || autoprefixer;
var sourcemaps = require('gulp-sourcemaps');
var gutil = require('gulp-util');



// Development Tasks
// -----------------

// Compile SCSS
gulp.task('sass', function () {
    return gulp.src('src/sass/*.scss') // Gets all files ending with .scss in styles/scss and children dirs
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'})) // Passes it through a gulp-sass
        .on('error', function(err) {
            gutil.log(err.message);
            this.emit('end');
        })
        .pipe(prefix({
            cascade: false
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('dist/css')); // Outputs it in the css folder
});

// Watchers
gulp.task('watch', function () {
    gulp.watch('src/sass/**/*.scss', gulp.series('sass'));
});

gulp.task('build', gulp.series('sass'));

gulp.task('default', gulp.series('sass', 'watch'));
