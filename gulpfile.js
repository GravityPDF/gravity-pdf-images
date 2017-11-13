var gulp = require('gulp'),
  wpPot = require('gulp-wp-pot')

/* Generate the latest language files */
gulp.task('language', function () {
  return gulp.src(['src/**/*.php', '*.php'])
    .pipe(wpPot({
      domain: 'gravity-pdf-images',
      package: 'Gravity PDF Images'
    }))
    .pipe(gulp.dest('languages/gravity-pdf-images.pot'))
})

gulp.task('default', ['language'])