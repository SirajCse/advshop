const VisualBuilderState = {
    shortcodes: [],
    activeShortcode: null,
    hasChanges: false,
    isSaving: false,
    deviceMode: 'desktop',
    listeners: {},

    init(shortcodes) {
        this.shortcodes = shortcodes || []
        this.activeShortcode = null
        this.hasChanges = false
        this.isSaving = false
        this.deviceMode = 'desktop'

        if (typeof HistoryManager !== 'undefined') {
            this.history = new HistoryManager(this)
            this.history.init(this.shortcodes)
        }
    },

    getShortcode(id) {
        return this.shortcodes.find((sc) => sc.id === id)
    },

    getAllShortcodes() {
        return this.shortcodes
    },

    getActiveShortcode() {
        if (!this.activeShortcode) {
            return null
        }
        return this.getShortcode(this.activeShortcode)
    },

    updateShortcode(id, updates) {
        const index = this.shortcodes.findIndex((sc) => sc.id === id)
        if (index !== -1) {
            this.shortcodes[index] = { ...this.shortcodes[index], ...updates }
            this.markChanged()
            this.emit('shortcode-updated', this.shortcodes[index])
            if (this.history) this.history.push(this.shortcodes)
        }
    },

    addShortcode(shortcode, position) {
        const newShortcode = {
            id: this.generateId(),
            ...shortcode,
        }

        if (typeof position === 'number' && position >= 0 && position <= this.shortcodes.length) {
            this.shortcodes.splice(position, 0, newShortcode)
        } else {
            this.shortcodes.push(newShortcode)
        }

        this.markChanged()
        this.emit('shortcode-added', newShortcode)
        if (this.history) this.history.push(this.shortcodes)
        return newShortcode
    },

    deleteShortcode(id) {
        const index = this.shortcodes.findIndex((sc) => sc.id === id)
        if (index !== -1) {
            const deleted = this.shortcodes.splice(index, 1)[0]
            this.markChanged()
            this.emit('shortcode-deleted', deleted)
            if (this.history) this.history.push(this.shortcodes)
            return true
        }
        return false
    },

    reorderShortcodes(newOrder) {
        this.shortcodes = newOrder
        this.markChanged()
        this.emit('shortcodes-reordered', newOrder)
        if (this.history) this.history.push(this.shortcodes)
    },

    reorderShortcodesByIds(ids) {
        const currentMap = new Map(this.shortcodes.map(s => [s.id, s]))
        const newOrder = []

        ids.forEach(id => {
            if (currentMap.has(id)) {
                newOrder.push(currentMap.get(id))
                currentMap.delete(id)
            }
        })

        currentMap.forEach(s => newOrder.push(s))

        this.reorderShortcodes(newOrder)
    },

    setActive(id) {
        this.activeShortcode = id
        this.emit('active-changed', id)
    },

    clearActive() {
        this.activeShortcode = null
        this.emit('active-changed', null)
    },

    markChanged() {
        if (!this.hasChanges) {
            this.hasChanges = true
            this.emit('has-changes', true)
        }
    },

    clearChanges() {
        this.hasChanges = false
        this.emit('has-changes', false)
    },

    setDeviceMode(mode) {
        this.deviceMode = mode
        this.emit('device-mode-changed', mode)
    },

    setSaving(isSaving) {
        this.isSaving = isSaving
        this.emit('saving-changed', isSaving)
    },

    generateId() {
        return 'sc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
    },

    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = []
        }
        this.listeners[event].push(callback)
    },

    off(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter((cb) => cb !== callback)
        }
    },

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach((callback) => callback(data))
        }
    },

    undo() {
        if (this.history && this.history.canUndo()) {
            const previousState = this.history.undo()
            if (previousState) {
                this.shortcodes = previousState
                this.emit('state-restored', this.shortcodes)
            }
        }
    },

    redo() {
        if (this.history && this.history.canRedo()) {
            const nextState = this.history.redo()
            if (nextState) {
                this.shortcodes = nextState
                this.emit('state-restored', this.shortcodes)
            }
        }
    },
}

window.VisualBuilderState = VisualBuilderState
