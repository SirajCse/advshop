/**
 * Shortcode List Module
 * Handles rendering and interaction with the shortcode list
 */

const ShortcodeList = {
    $container: null,
    sortableInstance: null,

    /**
     * Initialize the module
     */
    init() {
        this.$container = $('#vb-shortcode-list');
        this.render();
        this.attachEvents();
        this.initDragDrop();

        // Listen to state changes
        VisualBuilderState.on('shortcode-added', () => this.render());
        VisualBuilderState.on('shortcode-deleted', () => this.render());
        VisualBuilderState.on('shortcode-updated', () => this.render());
        VisualBuilderState.on('shortcodes-reordered', () => this.render());
        VisualBuilderState.on('active-changed', (id) => this.updateActiveState(id));
    },

    /**
     * Render the shortcode list
     */
    render() {
        const shortcodes = VisualBuilderState.getAllShortcodes();

        if (shortcodes.length === 0) {
            this.$container.html(`
                <div class="text-center text-muted py-4">
                    <i class="ti ti-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p>No shortcodes yet. Click "Add Shortcode" to get started.</p>
                </div>
            `);
            return;
        }

        const html = shortcodes.map((sc, index) => this.renderItem(sc, index)).join('');
        this.$container.html(html);

        // Re-initialize drag-drop after render
        this.initDragDrop();
    },

    /**
     * Render a single shortcode item
     */
    renderItem(shortcode, index) {
        const isActive = VisualBuilderState.activeShortcode === shortcode.id;
        const activeClass = isActive ? 'active' : '';

        // Get preview text from attributes
        let previewText = '';
        if (shortcode.attributes) {
            const attrs = Object.entries(shortcode.attributes)
                .slice(0, 2)
                .map(([key, val]) => `${key}: ${val}`)
                .join(', ');
            previewText = attrs || shortcode.name;
        } else {
            previewText = shortcode.name;
        }

        return `
            <div class="vb-shortcode-item ${activeClass}" data-id="${shortcode.id}">
                <div class="vb-item-drag-handle">
                    <i class="ti ti-grip-vertical"></i>
                </div>
                <div class="vb-item-icon">
                    <i class="ti ti-code"></i>
                </div>
                <div class="vb-item-content">
                    <h4 class="vb-item-name">${this.escapeHtml(shortcode.name)}</h4>
                    <div class="vb-item-preview">${this.escapeHtml(previewText)}</div>
                </div>
                <div class="vb-item-actions">
                    <button type="button" class="btn btn-sm btn-ghost vb-delete-btn" data-id="${shortcode.id}" title="Delete">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
    },

    /**
     * Attach event handlers
     */
    attachEvents() {
        const self = this;

        // Click on item to edit
        this.$container.on('click', '.vb-shortcode-item', function(e) {
            // Don't trigger if clicking delete button
            if ($(e.target).closest('.vb-delete-btn').length) {
                return;
            }

            const id = $(this).data('id');
            self.openEditPanel(id);
        });

        // Delete button
        this.$container.on('click', '.vb-delete-btn', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            self.deleteShortcode(id);
        });
    },

    /**
     * Initialize drag and drop with Sortable.js
     */
    initDragDrop() {
        // Destroy existing instance
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }

        // Create new Sortable instance
        if (typeof Sortable !== 'undefined' && this.$container[0]) {
            this.sortableInstance = new Sortable(this.$container[0], {
                animation: 150,
                handle: '.vb-item-drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: (evt) => {
                    this.handleReorder(evt);
                }
            });
        }
    },

    /**
     * Handle reorder event
     */
    handleReorder(evt) {
        const shortcodes = VisualBuilderState.getAllShortcodes();
        const movedItem = shortcodes[evt.oldIndex];

        // Create new array with reordered items
        const newOrder = [...shortcodes];
        newOrder.splice(evt.oldIndex, 1);
        newOrder.splice(evt.newIndex, 0, movedItem);

        // Update state
        VisualBuilderState.reorderShortcodes(newOrder);

        // Reload preview
        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.reload();
        }
    },

    /**
     * Open edit panel for shortcode
     */
    openEditPanel(id) {
        VisualBuilderState.setActive(id);

        // Hide list, show edit panel
        $('#vb-list-view').hide();
        $('#vb-edit-panel').show();

        // Render edit form
        if (typeof ShortcodeEditPanel !== 'undefined') {
            ShortcodeEditPanel.render(id);
        }

        // Highlight in preview
        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.highlightShortcode(id);
        }
    },

    /**
     * Delete shortcode with confirmation
     */
    deleteShortcode(id) {
        const config = window.visualBuilderData || {};
        const confirmMsg = config.translations?.confirmDelete || 'Are you sure you want to delete this shortcode?';

        if (confirm(confirmMsg)) {
            VisualBuilderState.deleteShortcode(id);

            // Reload preview
            if (typeof PreviewIframe !== 'undefined') {
                PreviewIframe.reload();
            }
        }
    },

    /**
     * Update active state styling
     */
    updateActiveState(activeId) {
        this.$container.find('.vb-shortcode-item').removeClass('active');
        if (activeId) {
            this.$container.find(`.vb-shortcode-item[data-id="${activeId}"]`).addClass('active');
        }
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Export to global scope
window.ShortcodeList = ShortcodeList;
