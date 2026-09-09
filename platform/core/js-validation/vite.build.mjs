// Legacy file concatenation: these scripts are jQuery plugins that mutate
// globals and can't be imported as ES modules. `combine` pipes them through
// a simple read → join → minify pass without Vite/Rollup in the middle.
export default {
    combine: {
        srcs: [
            'resources/js/jquery-validation/jquery.validate.js',
            'resources/js/phpjs/strlen.js',
            'resources/js/phpjs/array_diff.js',
            'resources/js/phpjs/strtotime.js',
            'resources/js/phpjs/is_numeric.js',
            'resources/js/php-date-formatter/php-date-formatter.js',
            'resources/js/js-validation.js',
            'resources/js/helpers.js',
            'resources/js/timezones.js',
            'resources/js/validations.js',
        ],
        out: 'js/js-validation.js',
    },
}
