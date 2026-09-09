/**
 * Shortcode Serializer
 * Converts shortcode objects to shortcode syntax string
 */

const ShortcodeSerializer = {
    /**
     * Serialize array of shortcodes to text format
     */
    serialize(shortcodes) {
        if (!Array.isArray(shortcodes)) {
            console.error('Invalid shortcodes array');
            return '';
        }

        return shortcodes.map(sc => this.serializeOne(sc)).join('\n\n');
    },

    /**
     * Serialize a single shortcode
     */
    serializeOne(shortcode) {
        if (!shortcode || !shortcode.name) {
            return '';
        }

        let output = '[' + shortcode.name;

        // Add attributes
        if (shortcode.attributes && typeof shortcode.attributes === 'object') {
            for (const [key, value] of Object.entries(shortcode.attributes)) {
                if (value !== null && value !== undefined && value !== '') {
                    const escapedValue = this.escapeAttribute(value);
                    output += ` ${key}="${escapedValue}"`;
                }
            }
        }

        // Self-closing or with content
        if (shortcode.isSelfClosing || !shortcode.content) {
            output += ' /]';
        } else {
            output += ']';
            output += shortcode.content;
            output += '[/' + shortcode.name + ']';
        }

        return output;
    },

    /**
     * Escape attribute value
     */
    escapeAttribute(value) {
        if (typeof value !== 'string') {
            value = String(value);
        }

        return value
            .replace(/\\/g, '\\\\')  // Escape backslashes
            .replace(/"/g, '\\"');   // Escape double quotes
    },

    /**
     * Parse shortcode string to object (for testing/validation)
     */
    parse(shortcodeString) {
        // Basic regex pattern for shortcodes
        const pattern = /\[(\w+)([^\]]*?)(?:\s*\/\]|\](.*?)\[\/\1\])/gs;
        const matches = [...shortcodeString.matchAll(pattern)];

        return matches.map((match, index) => {
            const name = match[1];
            const attributesString = match[2] || '';
            const content = match[3] || '';
            const isSelfClosing = match[0].endsWith('/]');

            return {
                id: 'sc_parsed_' + Date.now() + '_' + index,
                name: name,
                attributes: this.parseAttributes(attributesString),
                content: content,
                isSelfClosing: isSelfClosing,
                position: index,
                raw: match[0]
            };
        });
    },

    /**
     * Parse attributes string
     */
    parseAttributes(attributesString) {
        const attributes = {};

        if (!attributesString || !attributesString.trim()) {
            return attributes;
        }

        // Pattern to match attribute="value" or attribute='value'
        const pattern = /(\w+)\s*=\s*(?:"([^"]*)"|'([^']*)')/g;
        let match;

        while ((match = pattern.exec(attributesString)) !== null) {
            const key = match[1];
            const value = match[2] || match[3] || '';
            attributes[key] = value;
        }

        return attributes;
    },

    /**
     * Validate shortcode syntax
     */
    validate(shortcodeString) {
        const errors = [];

        // Check for unclosed brackets
        const openBrackets = (shortcodeString.match(/\[/g) || []).length;
        const closeBrackets = (shortcodeString.match(/\]/g) || []).length;

        if (openBrackets !== closeBrackets) {
            errors.push('Mismatched brackets');
        }

        // Check for unclosed shortcodes
        const pattern = /\[(\w+)(?:[^\]]*?)(?:\s*\/\]|\](.*?)\[\/\1\])/gs;
        const matches = [...shortcodeString.matchAll(pattern)];
        const allShortcodes = shortcodeString.match(/\[\w+/g) || [];

        if (matches.length * 2 < allShortcodes.length) {
            errors.push('Possible unclosed shortcodes');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
};

// Export to global scope
window.ShortcodeSerializer = ShortcodeSerializer;
