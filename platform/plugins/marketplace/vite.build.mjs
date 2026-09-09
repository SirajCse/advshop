export default {
    vue: true,
    js: [
        'marketplace-product',
        'marketplace-setting',
        'store-revenue',
        'store',
        { src: 'resources/js/vendor-dashboard/marketplace.js', out: 'marketplace.js' },
        { src: 'resources/js/vendor-dashboard/marketplace-vendor.js', out: 'marketplace-vendor.js' },
        { src: 'resources/js/vendor-dashboard/discount.js', out: 'discount.js' },
        'customer-register',
    ],
    sass: [
        { src: 'resources/sass/vendor-dashboard/marketplace.scss', out: 'marketplace.css' },
        { src: 'resources/sass/vendor-dashboard/marketplace-rtl.scss', out: 'marketplace-rtl.css' },
    ],
}
