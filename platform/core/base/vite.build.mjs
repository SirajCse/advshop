// @botble/core — admin framework, Vue runtime host, shared vendor libs.
// Complex package: 16 JS entries (one consumes Vue SFC), 5 SCSS entries
// (2 emit rtlcss variants), vendor libs shipped alongside compiled output.

const IS_PROD = process.env.NODE_ENV === 'production'

export default {
    vue: true,
    js: [
        'core-ui', 'app', 'core', 'editor', 'global-search', 'license-activation',
        'cache', 'tags', 'system-info', 'tree-category', 'cleanup', 'notification',
        'vue-app', 'repeater-field', 'system-update', 'crop-image',
    ],
    sass: [
        { src: 'resources/sass/core.scss',                     out: 'core.css',              rtl: 'core.rtl.css' },
        { src: 'resources/sass/libraries/select2/select2.scss', out: 'libraries/select2.css', rtl: 'select2.rtl.css' },
        { src: 'resources/sass/components/error-pages.scss',   out: 'error-pages.css' },
        { src: 'resources/sass/components/tree-category.scss', out: 'tree-category.css' },
        { src: 'resources/sass/components/crop-image.scss',    out: 'crop-image.css' },
    ],
    vendor: [
        { from: 'jquery/dist/jquery.min.js', to: 'libraries/jquery.min.js' },
        {
            from: IS_PROD ? 'vue/dist/vue.global.prod.js' : 'vue/dist/vue.global.js',
            to: 'libraries/vue.global.min.js',
        },
    ],
}
