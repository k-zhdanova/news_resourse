var documentRoot = './';
var indexFile = documentRoot + 'index.html';
var templatesSrc = 'templates/**/*.mustache';
var templatesFolderOutput = 'templates_min/';

var jsOutput = documentRoot + 'js/';
var jsSrc = documentRoot + 'js/src/';
var cssSrc = documentRoot + 'css/';

//gulp & plugins init
var gulp = require('gulp');
var path = require('path');
var minHtml = require('gulp-htmlmin');
var exec = require('gulp-exec');
var chokidar = require('chokidar'); //optinal for remove gulp-watch
var rename = require('gulp-rename');
var dbg = require('gulp-debug');
var concat = require('gulp-concat');
var stream = require('event-stream');
var babel = require('gulp-babel');



var browserSync = require('browser-sync');
var log = console.log.bind(console);


gulp.task('watch', function () {
	var watcherGeneric = chokidar.watch([indexFile,(cssSrc+'.css')], {ignored: /[\/\\]\./}).on('all', function(event, path) {
	  console.log(event, path);
	  // reload;
	});
	watcherGeneric.on('change', function(path) { log('File', path, 'has been changed'); browserSync.reload(); browserSync.reload; });



	var watcherTemplates = chokidar.watch([templatesSrc], {ignored: /[\/\\]\./}).on('all', function(event, path) {
	  console.log(event, path);
	  // reload;
	});
	watcherTemplates.on('change', function(path) { log('File', path, 'has been changed'); gulp.series('minhtml');
	browserSync.reload(); browserSync.reload;
	});

	var watcherJs = chokidar.watch([jsSrc], {ignored: /[\/\\]\./}).on('all', function(event, path) {
		console.log(event, path);
		// reload;
	  });
	  watcherJs.on('change', function(path) { log('File', path, 'has been changed'); gulp.series('scripts');
	  browserSync.reload(); browserSync.reload;
	  });
});

gulp.task('scripts', function() {
    return gulp.src(
      [
      'node_modules/babel-polyfill/dist/polyfill.js',
      'js/src/*.js'
      ])
      .pipe(babel({presets: ['@babel/preset-env']}))
      .pipe(gulp.dest('js'))
});

gulp.task('serve', function () {
	browserSync.init({
		server: {
			baseDir: documentRoot
		}
	});
	gulp.series('watch');
});

gulp.task('templates', function() {
	var isFirst = true;
	var pathes = [];

	function template() {
		function transform(file, cb) {
			var content = '';
			if (isFirst) {
				content = 'var templates = {};\n'
				isFirst = false;
			}

			var fileName = file.relative.replace(/[\/\\]/g, '.').replace(/\.mustache/, '');
			var requiredPath = fileName.split('.').slice(0, -1).join('.')
			if (pathes.indexOf(requiredPath) == -1) {
				content += ('templates.' + requiredPath + ' = {};\n');
				pathes.push(requiredPath);
			}

			var templateContent = String(file.contents).replace(/'/g, '\\\'');
			content += ('templates.' + fileName + ' = \'' + templateContent + '\';');
			file.contents = new Buffer(String(content));

			cb(null, file);
		}

		return stream.map(transform);
	}

	return gulp.src(templatesSrc)
	.pipe(dbg())
	.pipe(minHtml({collapseWhitespace: true, removeComments: true, minifyJS: true}))
	.pipe(template())
	.pipe(concat('templates.js'))
	.pipe(gulp.dest(jsOutput));
});

gulp.task('minhtml', function() {
	// var command = 'W:\\modules\\php\\PHP_8.0\\php _template_compiler.php admin';
	var command = 'php _template_compiler.php admin';

	return gulp.src(templatesSrc)
	.pipe(dbg())
	.pipe(minHtml({collapseWhitespace: true, removeComments: true, minifyJS: true}))
	.pipe(rename(function (path) {
		if (path.dirname !='.') {
			path.basename = path.dirname + '.' + path.basename;
			path.dirname ='';
			path.extname = '.mustache';
		}
	}))
	.pipe(gulp.dest(templatesFolderOutput))
	.pipe(exec(command));
});

gulp.task('reload',function(){
	browserSync.reload;
	browserSync.reload();
});

gulp.task('default', gulp.series('serve'));
