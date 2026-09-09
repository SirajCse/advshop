/**
 * Add Shortcode Modal Module
 * Handles the modal for adding new shortcodes
 */

const AddShortcodeModal = {
    $modal: null,
    $typesContainer: null,
    $formContainer: null,
    $footer: null,
    selectedType: null,

    /**
     * Initialize the module
     */
    init() {
        this.$modal = $('#vb-add-modal');
        this.$typesContainer = $('#vb-shortcode-types');
        this.$formContainer = $('#vb-shortcode-form');
        this.$footer = $('#vb-modal-footer');

        // Use Bootstrap modal if available, otherwise create simple show/hide
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            this.modal = new bootstrap.Modal(this.$modal[0]);
        }
    },

    /**
     * Show the modal
     */
    show() {
        this.selectedType = null;
        this.renderTypes();
        this.$formContainer.hide();

        if (this.modal) {
            this.modal.show();
        } else {
            this.$modal.fadeIn();
        }
    },

    /**
     * Hide the modal
     */
    hide() {
        if (this.modal) {
            this.modal.hide();
        } else {
            this.$modal.fadeOut();
        }
    },

    /**
     * Render shortcode types
     */
    renderTypes() {
        const config = window.visualBuilderData || {};
        const availableShortcodes = config.availableShortcodes || [];

        if (availableShortcodes.length === 0) {
            this.$typesContainer.html('<p class="text-muted">No shortcodes available</p>');
            return;
        }

        const html = availableShortcodes.map(sc => this.renderTypeCard(sc)).join('');
        this.$typesContainer.html(html);

        // Attach click handlers
        this.$typesContainer.find('.vb-shortcode-type').on('click', (e) => {
            const key = $(e.currentTarget).data('key');
            this.selectType(key);
        });
    },

    /**
     * Render a shortcode type card
     */
    renderTypeCard(shortcode) {
        return `
            <div class="vb-shortcode-type" data-key="${shortcode.key}">
                <div class="vb-type-icon">
                    <i class="ti ti-code"></i>
                </div>
                <div class="vb-type-name">${this.escapeHtml(shortcode.name)}</div>
                ${shortcode.description ? `<div class="vb-type-description">${this.escapeHtml(shortcode.description)}</div>` : ''}
            </div>
        `;
    },

    /**
     * Select a shortcode type
     */
    selectType(key) {
        const config = window.visualBuilderData || {};
        const availableShortcodes = config.availableShortcodes || [];
        const shortcode = availableShortcodes.find(sc => sc.key === key);

        if (!shortcode) return;

        this.selectedType = shortcode;
        this.renderForm(shortcode);

        // Hide types, show form
        this.$typesContainer.hide();
        this.$formContainer.show();

        // Update footer buttons
        this.updateFooter(true);
    },

    /**
     * Render shortcode configuration form
     */
    renderForm(shortcode) {
        let html = '<form id="vb-add-form">';

        html += `<h6 class="mb-3">${this.escapeHtml(shortcode.name)}</h6>`;

        // Position selector
        html += this.renderPositionSelector();

        // Attributes (if schema available)
        if (shortcode.attributes) {
            for (const [key, config] of Object.entries(shortcode.attributes)) {
                html += this.renderField(key, config);
            }
        } else {
            // Default fields
            html += this.renderField('url', { type: 'url', label: 'URL' });
            html += this.renderField('title', { type: 'text', label: 'Title' });
        }

        html += '</form>';

        this.$formContainer.html(html);
    },

    /**
     * Render position selector
     */
    renderPositionSelector() {
        const shortcodes = VisualBuilderState.getAllShortcodes();

        let html = `
            <div class="form-group">
                <label>Insert Position</label>
                <select class="form-control" name="position">
                    <option value="0">Beginning</option>
        `;

        shortcodes.forEach((sc, index) => {
            html += `<option value="${index + 1}">After: ${this.escapeHtml(sc.name)}</option>`;
        });

        html += `
                    <option value="${shortcodes.length}">End</option>
                </select>
            </div>
        `;

        return html;
    },

    /**
     * Render form field
     */
    renderField(name, config) {
        const fieldType = config.type || 'text';
        const label = config.label || name;
        const required = config.required ? 'required' : '';
        const helpText = config.help || '';

        let fieldHtml = '';

        switch (fieldType) {
            case 'textarea':
                const rows = config.rows || 3;
                fieldHtml = `<textarea class="form-control" name="${name}" rows="${rows}" ${required}></textarea>`;
                break;

            case 'select':
                fieldHtml = `<select class="form-control" name="${name}" ${required}>`;
                fieldHtml += `<option value="">Select...</option>`;
                if (config.options) {
                    for (const [optValue, optLabel] of Object.entries(config.options)) {
                        fieldHtml += `<option value="${optValue}">${optLabel}</option>`;
                    }
                }
                fieldHtml += `</select>`;
                break;

            case 'checkbox':
                fieldHtml = `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="${name}" value="yes">
                        <label class="form-check-label">${label}</label>
                    </div>
                `;
                break;

            case 'url':
            case 'email':
            case 'number':
                fieldHtml = `<input type="${fieldType}" class="form-control" name="${name}" ${required}>`;
                break;

            case 'text':
            default:
                fieldHtml = `<input type="text" class="form-control" name="${name}" ${required}>`;
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
     * Update modal footer
     */
    updateFooter(showInsert) {
        if (showInsert) {
            this.$footer.html(`
                <button type="button" class="btn btn-secondary" id="vb-modal-back-btn">Back</button>
                <button type="button" class="btn btn-primary" id="vb-modal-insert-btn">Insert Shortcode</button>
            `);

            $('#vb-modal-back-btn').on('click', () => this.backToTypes());
            $('#vb-modal-insert-btn').on('click', () => this.insertShortcode());
        } else {
            this.$footer.html(`
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            `);
        }
    },

    /**
     * Go back to types list
     */
    backToTypes() {
        this.$formContainer.hide();
        this.$typesContainer.show();
        this.selectedType = null;
        this.updateFooter(false);
    },

    /**
     * Insert shortcode
     */
    insertShortcode() {
        if (!this.selectedType) return;

        const formData = this.getFormData();
        const position = parseInt(formData.position) || 0;

        // Create new shortcode object
        const newShortcode = {
            name: this.selectedType.key,
            attributes: formData.attributes,
            content: formData.content || '',
            isSelfClosing: false,
            position: position,
            raw: '' // Will be generated on save
        };

        // Add to state
        VisualBuilderState.addShortcode(newShortcode, position);

        // Reload preview
        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.reload();
        }

        // Close modal
        this.hide();
    },

    /**
     * Get form data
     */
    getFormData() {
        const $form = $('#vb-add-form');
        const attributes = {};
        let content = '';
        let position = 0;

        $form.find('input, textarea, select').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            let value;

            if (name === 'position') {
                position = parseInt($field.val()) || 0;
            } else if (name === 'content') {
                content = $field.val();
            } else if ($field.attr('type') === 'checkbox') {
                value = $field.is(':checked') ? 'yes' : 'no';
                attributes[name] = value;
            } else {
                value = $field.val();
                if (value) {
                    attributes[name] = value;
                }
            }
        });

        return { attributes, content, position };
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
window.AddShortcodeModal = AddShortcodeModal;
