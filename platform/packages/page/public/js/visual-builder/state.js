/**
 * Visual Builder State Management
 * Simple object-based state with event emitter pattern
 */

const VisualBuilderState = {
    // State properties
    shortcodes: [],
    activeShortcode: null,
    hasChanges: false,
    isSaving: false,
    deviceMode: 'desktop',

    // Event listeners
    listeners: {},

    /**
     * Initialize state with data
     */
    init(shortcodes) {
        this.shortcodes = shortcodes || [];
        this.activeShortcode = null;
        this.hasChanges = false;
        this.isSaving = false;
        this.deviceMode = 'desktop';
    },

    /**
     * Get shortcode by ID
     */
    getShortcode(id) {
        return this.shortcodes.find(sc => sc.id === id);
    },

    /**
     * Get all shortcodes
     */
    getAllShortcodes() {
        return this.shortcodes;
    },

    /**
     * Update shortcode attributes
     */
    updateShortcode(id, updates) {
        const index = this.shortcodes.findIndex(sc => sc.id === id);
        if (index !== -1) {
            this.shortcodes[index] = { ...this.shortcodes[index], ...updates };
            this.markChanged();
            this.emit('shortcode-updated', this.shortcodes[index]);
        }
    },

    /**
     * Add new shortcode
     */
    addShortcode(shortcode, position) {
        const newShortcode = {
            id: this.generateId(),
            ...shortcode
        };

        if (typeof position === 'number' && position >= 0 && position <= this.shortcodes.length) {
            this.shortcodes.splice(position, 0, newShortcode);
        } else {
            this.shortcodes.push(newShortcode);
        }

        this.markChanged();
        this.emit('shortcode-added', newShortcode);
        return newShortcode;
    },

    /**
     * Delete shortcode
     */
    deleteShortcode(id) {
        const index = this.shortcodes.findIndex(sc => sc.id === id);
        if (index !== -1) {
            const deleted = this.shortcodes.splice(index, 1)[0];
            this.markChanged();
            this.emit('shortcode-deleted', deleted);
            return true;
        }
        return false;
    },

    /**
     * Reorder shortcodes
     */
    reorderShortcodes(newOrder) {
        this.shortcodes = newOrder;
        this.markChanged();
        this.emit('shortcodes-reordered', newOrder);
    },

    /**
     * Set active shortcode
     */
    setActive(id) {
        this.activeShortcode = id;
        this.emit('active-changed', id);
    },

    /**
     * Clear active shortcode
     */
    clearActive() {
        this.activeShortcode = null;
        this.emit('active-changed', null);
    },

    /**
     * Mark state as changed
     */
    markChanged() {
        if (!this.hasChanges) {
            this.hasChanges = true;
            this.emit('has-changes', true);
        }
    },

    /**
     * Clear changes flag
     */
    clearChanges() {
        this.hasChanges = false;
        this.emit('has-changes', false);
    },

    /**
     * Set device mode
     */
    setDeviceMode(mode) {
        this.deviceMode = mode;
        this.emit('device-mode-changed', mode);
    },

    /**
     * Set saving state
     */
    setSaving(isSaving) {
        this.isSaving = isSaving;
        this.emit('saving-changed', isSaving);
    },

    /**
     * Generate unique ID for shortcodes
     */
    generateId() {
        return 'sc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    },

    /**
     * Event emitter: on
     */
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    },

    /**
     * Event emitter: off
     */
    off(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        }
    },

    /**
     * Event emitter: emit
     */
    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    }
};

// Export for use in other modules
window.VisualBuilderState = VisualBuilderState;
