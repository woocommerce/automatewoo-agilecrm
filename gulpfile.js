const gulp   = require('gulp');
const del    = require( 'del' );
const dest   = require('gulp-dest');
const zip    = require('gulp-zip');
const rename = require('gulp-rename');
const clean  = require('gulp-clean');
const uglify = require('gulp-uglify');
const sass   = require('gulp-sass');
const merge  = require('gulp-merge');
const plumber      = require( 'gulp-plumber' );
const minifyCSS    = require('gulp-csso');
const sourcemaps   = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const minimist     = require('minimist');
const po2mo        = require('gulp-po2mo');
const packageJSON  = require( './package.json' );


const options = minimist( process.argv.slice( 2 ), {
    string: [ 'suffix' ],
    default: {
        suffix: process.env.SUFFIX || ''
    }
} );

const paths = {
	php: ["*.php", "{includes}/**/*.php"],
};

function handleErrors() {
	const args = Array.prototype.slice.call( arguments );

	// See: https://github.com/mikaelbr/node-notifier#all-notification-options-with-their-defaults
	notify.onError( {
		'title': 'Task Failed [<%= error.message %>',
		'message': 'See console.',
		'sound': 'Sosumi'
	} ).apply( this, args );

	gutil.beep();

	// Prevent the 'watch' task from stopping.
	this.emit( 'end' );
}

/**
 * Get a formatted date string.
 * @returns {string}
 */
function getDateString() {
	const now = new Date();
	const realMonth = now.getUTCMonth() + 1,
		month = (realMonth < 10 ? '0' : '') + realMonth,
		day = (now.getUTCDate() < 10 ? '0' : '') + now.getUTCDate();
	return `${now.getUTCFullYear()}-${month}-${day} ` + `${now.getUTCHours()}:${now.getUTCMinutes()}:${now.getUTCSeconds()}+00:00`;
}

gulp.task( 'clean', () => gulp.src('dist/').pipe( clean() ) )
gulp.task( 'clean:pot', () => del( [ `languages/${packageJSON.name}.pot` ] ) );

/**
 * Scan the theme and create a POT file.
 */
gulp.task( 'wp-pot', [ 'clean:pot' ], () => {
	const wpPot = require( 'gulp-wp-pot' ), sort = require( 'gulp-sort' );
	return gulp.src( paths.php )
		.pipe( plumber( { 'errorHandler': handleErrors } ) )
		.pipe( sort() )
		.pipe( wpPot( {
			//'bugReport': packageJSON.bugs.url,
			'domain': packageJSON.name,
			'package': packageJSON.title,
			'team': '',
			'writeFile': false,
			'headers': {
				'Language': 'en_US',
				'POT-Creation-Date': getDateString()
			}
		} ) )
		.pipe( gulp.dest( `languages/${packageJSON.name}.pot` ) );
} );

/**
 * Compile translation .PO files to .MO
 */
gulp.task( 'po2mo', () => {
	gulp.src( 'languages/**/*.po' )
		.pipe( po2mo() )
		.pipe( gulp.dest( 'languages/' ) );
});

gulp.task('zip', ['clean', 'i18n', 'default'], () => gulp
    .src(['*.{php,txt,gitignore}', 'CHANGELOG.md', 'wpml-config.xml', '{includes,languages,templates,assets}/**/*'])
    .pipe(rename(f => {
        f.dirname = (options.suffix ? `${packageJSON.name}-${options.suffix}/` : `${packageJSON.name}/`) + f.dirname;
    }))
    .pipe(gulp.dest('dist/'))
    .pipe(zip(`${packageJSON.name}.zip`))
    .pipe(gulp.dest('dist/'))
);

gulp.task( 'i18n', [ 'wp-pot', 'po2mo' ] );
gulp.task('default', '');
