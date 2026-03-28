var gulp = require('gulp');
var browserify = require('browserify');
var sass = require('gulp-sass');
var prefix = require('gulp-autoprefixer');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var es = require('event-stream');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var cssnano = require('gulp-cssnano');
var cache = require('gulp-cache');
var del = require('del');
var runSequence = require('run-sequence');
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

// Compile JavaScript
gulp.task('js', function() {

    var sourcePath = 'src/js/';

    var files = [
        'resources.js',
    ];

    var tasks = files.map(function(entry) {
        return browserify({ entries: [sourcePath + entry], debug: true })
            .bundle()
            .pipe(source(entry))
            .pipe(rename({
                extname: '.min.js'
            }))
            .pipe(buffer())
            .pipe(sourcemaps.init({loadMaps: true}))
            // Add transformation tasks to the pipeline here.
            .pipe(uglify())
            .on('error', gutil.log)
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest('dist/js/'));
    });

    return es.merge.apply(null, tasks);
});

// Watchers
gulp.task('watch', function () {
    gulp.watch('src/js/**/*.js', ['js']);
    gulp.watch('src/sass/**/*.scss', ['sass']);
});

// Optimization Tasks
// ------------------

// Cleaning
gulp.task('clean', function () {
    return del.sync('dist').then(function (cb) {
        return cache.clearAll(cb);
    });
});

// Build Sequences
// ---------------

gulp.task('default', function (callback) {
    runSequence(['js', 'sass'], 'watch',
        callback
    )
});

gulp.task('build', function (callback) {
    runSequence(
        'clean:dist',
        'js',
        'sass',
        callback
    )
});