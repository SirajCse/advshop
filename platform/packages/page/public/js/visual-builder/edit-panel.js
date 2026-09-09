/**
 * Shortcode Edit Panel Module
 * Handles rendering and interaction with the edit form
 */

const ShortcodeEditPanel = {
    $container: null,
    $content: null,
    $title: null,
    currentShortcode: null,
    updateTimeout: null,

    /**
     * Initialize the module
     */
    init() {
        this.$container = $('#vb-edit-panel');
        this.$content = $('#vb-panel-content');
        this.$title = $('#vb-panel-title');
    },

    /**
     * Render edit form for shortcode
     */
    render(shortcodeId) {
        const shortcode = VisualBuilderState.getShortcode(shortcodeId);

        if (!shortcode) {
            console.error('Shortcode not found:', shortcodeId);
            return;
        }

        this.currentShortcode = shortcode;

        // Update title
        this.$title.text(`Edit: ${shortcode.name}`);

        // Render form fields
        const formHtml = this.renderForm(shortcode);
        this.$content.html(formHtml);

        // Attach event handlers
        this.attachEvents();
    },

    /**
     * Render form fields
     */
    renderForm(shortcode) {
        let html = '<form id="vb-edit-form">';

        // Get shortcode schema from available shortcodes
        const schema = this.getShortcodeSchema(shortcode.name);

        if (schema && schema.attributes) {
            // Render fields based on schema
            for (const [key, config] of Object.entries(schema.attributes)) {
                html += this.renderField(key, config, shortcode.attributes[key]);
            }
        } else {
            // Render generic fields for all attributes
            for (const [key, value] of Object.entries(shortcode.attributes || {})) {
                html += this.renderField(key, { type: 'text', label: key }, value);
            }
        }

        // Content field if shortcode has content
        if (!shortcode.isSelfClosing) {
            html += this.renderField('content', {
                type: 'textarea',
                label: 'Content',
                rows: 5
            }, shortcode.content);
        }

        html += '</form>';

        return html;
    },

    /**
     * Render a single form field
     */
    renderField(name, config, value) {
        const fieldType = config.type || 'text';
        const label = config.label || name;
        const required = config.required ? 'required' : '';
        const helpText = config.help || '';
        const escapedValue = this.escapeHtml(value || '');

        let fieldHtml = '';

        switch (fieldType) {
            case 'textarea':
                const rows = config.rows || 3;
                fieldHtml = `<textarea class="form-control" name="${name}" rows="${rows}" ${required}>${escapedValue}</textarea>`;
                break;

            case 'select':
                fieldHtml = `<select class="form-control" name="${name}" ${required}>`;
                if (config.options) {
                    for (const [optValue, optLabel] of Object.entries(config.options)) {
                        const selected = optValue === value ? 'selected' : '';
                        fieldHtml += `<option value="${optValue}" ${selected}>${optLabel}</option>`;
                    }
                }
                fieldHtml += `</select>`;
                break;

            case 'checkbox':
                const checked = value === 'yes' || value === '1' || value === true ? 'checked' : '';
                fieldHtml = `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="${name}" value="yes" ${checked}>
                        <label class="form-check-label">${label}</label>
                    </div>
                `;
                break;

            case 'url':
            case 'email':
            case 'number':
                fieldHtml = `<input type="${fieldType}" class="form-control" name="${name}" value="${escapedValue}" ${required}>`;
                break;

            case 'text':
            default:
                fieldHtml = `<input type="text" class="form-control" name="${name}" value="${escapedValue}" ${required}>`;
                break;
        }

        return `
            <div class="form-group">
                ${fieldType !== 'checkbox' ? `<label>${label}</label>` : ''}
                ${fieldHtml}
                ${helpText ? `<div class="form-text">${helpText}</div>` : ''}
            </div>
        `;
    },

    /**
     * Attach event handlers
     */
    attachEvents() {
        const self = this;

        // Debounced input change handler
        this.$content.on('input change', 'input, textarea, select', function() {
            clearTimeout(self.updateTimeout);
            self.updateTimeout = setTimeout(() => {
                self.handleFieldChange();
            }, 300);
        });
    },

    /**
     * Handle field changes
     */
    handleFieldChange() {
        if (!this.currentShortcode) return;

        const formData = this.getFormData();

        // Update state
        VisualBuilderState.updateShortcode(this.currentShortcode.id, {
            attributes: formData.attributes,
            content: formData.content
        });

        // Update preview (for simple changes, try live update; otherwise reload)
        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.updateShortcode(this.currentShortcode.id, formData);
        }
    },

    /**
     * Get form data
     */
    getFormData() {
        const $form = $('#vb-edit-form');
        const attributes = {};
        let content = '';

        $form.find('input, textarea, select').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            let value;

            if (name === 'content') {
                content = $field.val();
            } else if ($field.attr('type') === 'checkbox') {
                value = $field.is(':checked') ? 'yes' : 'no';
                attributes[name] = value;
            } else {
                value = $field.val();
                attributes[name] = value;
            }
        });

        return { attributes, content };
    },

    /**
     * Get shortcode schema from available shortcodes
     */
    getShortcodeSchema(shortcodeName) {
        const config = window.visualBuilderData || {};
        const available = config.availableShortcodes || [];

        return available.find(sc => sc.key === shortcodeName);
    },

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Export to global scope
window.ShortcodeEditPanel = ShortcodeEditPanel;
