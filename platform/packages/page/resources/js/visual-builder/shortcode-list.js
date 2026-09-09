const ShortcodeList = {
    $container: null,
    sortableInstance: null,

    init() {
        this.$container = $('#vb-shortcode-list')
        this.render()
        this.attachEvents()
        this.initDragDrop()

        VisualBuilderState.on('shortcode-added', () => this.render())
        VisualBuilderState.on('shortcode-deleted', () => this.render())
        VisualBuilderState.on('shortcode-updated', () => this.render())
        VisualBuilderState.on('shortcodes-reordered', () => this.render())
        VisualBuilderState.on('active-changed', (id) => this.updateActiveState(id))
    },

    render() {
        const shortcodes = VisualBuilderState.getAllShortcodes()
        const activeShortcode = VisualBuilderState.getActiveShortcode()
        const activeId = activeShortcode ? activeShortcode.id : null

        if (shortcodes.length === 0) {
            this.$container.html(`
                <div class="text-center text-muted py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.3;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"></path>
                        <path d="M10 16h-3a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h9a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <p>No shortcodes yet. Click "Add Shortcode" to get started.</p>
                </div>
            `)
            return
        }

        const config = window.visualBuilderData || {}
        $.ajax({
            url: config.renderItemsUrl,
            method: 'POST',
            data: {
                _token: config.csrfToken,
                shortcodes: shortcodes,
                activeId: activeId,
            },
            success: (response) => {
                if (response.data && response.data.html) {
                    this.$container.html(response.data.html)
                    this.initDragDrop()
                }
            },
        })
    },

    renderItem(shortcode, index) {
        const isActive = VisualBuilderState.activeShortcode === shortcode.id
        const activeClass = isActive ? 'active' : ''

        let previewText = ''
        if (shortcode.attributes) {
            const attrs = Object.entries(shortcode.attributes)
                .slice(0, 2)
                .map(([key, val]) => `${key}: ${val}`)
                .join(', ')
            previewText = attrs || shortcode.name
        } else {
            previewText = shortcode.name
        }

        return `
            <div class="vb-shortcode-item ${activeClass}" data-id="${shortcode.id}">
                <div class="vb-item-drag-handle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M9 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        <path d="M9 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        <path d="M9 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        <path d="M15 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        <path d="M15 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                        <path d="M15 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                    </svg>
                </div>
                <div class="vb-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M7 8l-4 4l4 4"></path>
                        <path d="M17 8l4 4l-4 4"></path>
                        <path d="M14 4l-4 16"></path>
                    </svg>
                </div>
                <div class="vb-item-content">
                    <h4 class="vb-item-name">${this.escapeHtml(shortcode.name)}</h4>
                    <div class="vb-item-preview">${this.escapeHtml(previewText)}</div>
                </div>
                <div class="vb-item-actions">
                    <button type="button" class="btn btn-sm btn-ghost vb-delete-btn" data-id="${shortcode.id}" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 7l16 0"></path>
                            <path d="M10 11l0 6"></path>
                            <path d="M14 11l0 6"></path>
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `
    },

    attachEvents() {
        const self = this

        this.$container.on('click', '.vb-delete-btn', function (e) {
            e.preventDefault()
            e.stopPropagation()

            const $btn = $(this)
            const id = $btn.data('id')

            self.deleteShortcode(id)
        })

        this.$container.on('click', '.vb-shortcode-item', function (e) {
            if ($(e.target).hasClass('vb-delete-btn') || $(e.target).closest('.vb-delete-btn').length) {
                return
            }

            if ($(e.target).closest('.vb-item-drag-handle').length) {
                return
            }

            const id = $(this).data('id')
            self.openEditPanel(id)
        })

        this.$container.on('mouseenter', '.vb-shortcode-item', function (e) {
            const id = $(this).data('id')

            if (typeof PreviewIframe !== 'undefined' && PreviewIframe.isReady) {
                PreviewIframe.highlightShortcode(id)
            }
        })

        this.$container.on('mouseleave', '.vb-shortcode-item', function (e) {
            if (typeof PreviewIframe !== 'undefined' && PreviewIframe.isReady) {
                PreviewIframe.unhighlightShortcode()
            }
        })
    },

    initDragDrop() {
        if (this.sortableInstance) {
            this.sortableInstance.destroy()
        }

        if (typeof Sortable !== 'undefined' && this.$container[0]) {
            this.sortableInstance = new Sortable(this.$container[0], {
                animation: 150,
                handle: '.vb-item-drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: (evt) => {
                    this.handleReorder(evt)
                },
            })
        }
    },

    handleReorder(evt) {
        const shortcodes = VisualBuilderState.getAllShortcodes()
        const movedItem = shortcodes[evt.oldIndex]

        const newOrder = [...shortcodes]
        newOrder.splice(evt.oldIndex, 1)
        newOrder.splice(evt.newIndex, 0, movedItem)

        VisualBuilderState.reorderShortcodes(newOrder)

        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.reload()
        }
    },

    openEditPanel(id) {
        VisualBuilderState.setActive(id)

        $('#vb-edit-panel').removeClass('d-none').show()

        if (typeof ShortcodeEditPanel !== 'undefined') {
            ShortcodeEditPanel.render(id)
        }

        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.highlightShortcode(id)
        }
    },

    deleteShortcode(id) {
        if (!id) {
            return
        }

        const config = window.visualBuilderData || {}
        const confirmMsg = config.translations?.confirmDelete || 'Are you sure you want to delete this shortcode?'

        if (confirm(confirmMsg)) {
            VisualBuilderState.deleteShortcode(id)

            if (typeof PreviewIframe !== 'undefined') {
                PreviewIframe.reload()
            }
        }
    },

    updateActiveState(activeId) {
        this.$container.find('.vb-shortcode-item').removeClass('active')
        if (activeId) {
            this.$container.find(`.vb-shortcode-item[data-id="${activeId}"]`).addClass('active')
        }
    },

    escapeHtml(text) {
        const div = document.createElement('div')
        div.textContent = text
        return div.innerHTML
    },
}

window.ShortcodeList = ShortcodeList
