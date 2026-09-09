/**
 * jQuery 4.0 Compatibility Shim
 * Restores deprecated methods removed in jQuery 4.0 for legacy library compatibility
 */
(function($) {
    'use strict';

    // $.isArray was removed in jQuery 4.0
    if (typeof $.isArray !== 'function') {
        $.isArray = Array.isArray;
    }

    // $.isFunction was removed in jQuery 4.0
    if (typeof $.isFunction !== 'function') {
        $.isFunction = function(obj) {
            return typeof obj === 'function';
        };
    }

    // $.isNumeric was removed in jQuery 4.0
    if (typeof $.isNumeric !== 'function') {
        $.isNumeric = function(obj) {
            var type = typeof obj;
            return (type === 'number' || type === 'string') && !isNaN(obj - parseFloat(obj));
        };
    }

    // $.isWindow was removed in jQuery 4.0
    if (typeof $.isWindow !== 'function') {
        $.isWindow = function(obj) {
            return obj != null && obj === obj.window;
        };
    }

    // $.type was removed in jQuery 4.0
    if (typeof $.type !== 'function') {
        var class2type = {};
        'Boolean Number String Function Array Date RegExp Object Error Symbol'.split(' ').forEach(function(name) {
            class2type['[object ' + name + ']'] = name.toLowerCase();
        });

        $.type = function(obj) {
            if (obj == null) {
                return obj + '';
            }
            return typeof obj === 'object' || typeof obj === 'function'
                ? class2type[Object.prototype.toString.call(obj)] || 'object'
                : typeof obj;
        };
    }

    // $.trim was removed in jQuery 4.0
    if (typeof $.trim !== 'function') {
        $.trim = function(str) {
            return str == null ? '' : String(str).trim();
        };
    }

    // $.now was removed in jQuery 4.0
    if (typeof $.now !== 'function') {
        $.now = Date.now;
    }

    // $.parseJSON was removed in jQuery 4.0
    if (typeof $.parseJSON !== 'function') {
        $.parseJSON = JSON.parse;
    }

    // $.camelCase was removed in jQuery 4.0
    if (typeof $.camelCase !== 'function') {
        $.camelCase = function(str) {
            return str.replace(/-([a-z])/g, function(all, letter) {
                return letter.toUpperCase();
            });
        };
    }

})(jQuery);
