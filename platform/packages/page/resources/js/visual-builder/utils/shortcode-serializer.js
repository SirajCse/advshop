const ShortcodeSerializer = {
    serialize(shortcodes) {
        if (!Array.isArray(shortcodes)) {
            return ''
        }

        return shortcodes.map((sc) => this.serializeOne(sc)).join('\n\n')
    },

    serializeOne(shortcode) {
        if (!shortcode || !shortcode.name) {
            return ''
        }

        let output = '[' + shortcode.name

        if (shortcode.attributes && typeof shortcode.attributes === 'object') {
            for (const [key, value] of Object.entries(shortcode.attributes)) {
                if (value !== null && value !== undefined && value !== '') {
                    const escapedValue = this.escapeAttribute(value)
                    output += ` ${key}="${escapedValue}"`
                }
            }
        }

        output += ']'
        if (shortcode.content) {
            output += shortcode.content
        }
        output += '[/' + shortcode.name + ']'

        return output
    },

    escapeAttribute(value) {
        if (typeof value !== 'string') {
            value = String(value)
        }

        return value
            .replace(/\\/g, '\\\\')
            .replace(/"/g, '\\"')
    },

    parse(shortcodeString) {
        const pattern = /\[(\w+)([^\]]*?)(?:\s*\/\]|\](.*?)\[\/\1\])/gs
        const matches = [...shortcodeString.matchAll(pattern)]

        return matches.map((match, index) => {
            const name = match[1]
            const attributesString = match[2] || ''
            const content = match[3] || ''
            const isSelfClosing = match[0].endsWith('/]')

            return {
                id: 'sc_parsed_' + Date.now() + '_' + index,
                name: name,
                attributes: this.parseAttributes(attributesString),
                content: content,
                isSelfClosing: isSelfClosing,
                position: index,
                raw: match[0],
            }
        })
    },

    parseAttributes(attributesString) {
        const attributes = {}

        if (!attributesString || !attributesString.trim()) {
            return attributes
        }

        // Pattern that handles escaped quotes within attribute values
        // (?:[^\\"]|\\.)* matches: any char except \ or ", OR backslash followed by any char (escaped sequence)
        const pattern = /(\w+)\s*=\s*(?:"((?:[^\\"]|\\.)*)"|'((?:[^\\']|\\.)*)')/g
        let match

        while ((match = pattern.exec(attributesString)) !== null) {
            const key = match[1]
            const value = match[2] || match[3] || ''
            // Unescape the value after extraction
            attributes[key] = this.unescapeAttribute(value)
        }

        return attributes
    },

    unescapeAttribute(value) {
        if (typeof value !== 'string') {
            return value
        }

        // Unescape in reverse order: quotes first, then backslashes
        return value.replace(/\\"/g, '"').replace(/\\\\/g, '\\')
    },

    validate(shortcodeString) {
        const errors = []

        const openBrackets = (shortcodeString.match(/\[/g) || []).length
        const closeBrackets = (shortcodeString.match(/\]/g) || []).length

        if (openBrackets !== closeBrackets) {
            errors.push('Mismatched brackets')
        }

        const pattern = /\[(\w+)(?:[^\]]*?)(?:\s*\/\]|\](.*?)\[\/\1\])/gs
        const matches = [...shortcodeString.matchAll(pattern)]
        const allShortcodes = shortcodeString.match(/\[\w+/g) || []

        if (matches.length * 2 < allShortcodes.length) {
            errors.push('Possible unclosed shortcodes')
        }

        return {
            valid: errors.length === 0,
            errors: errors,
        }
    },
}

window.ShortcodeSerializer = ShortcodeSerializer
