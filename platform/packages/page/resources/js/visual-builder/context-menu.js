class ContextMenu {
    constructor() {
        this.$menu = null
        this.iframe = null
        this.targetId = null
    }

    init() {
        this.createMenu()
        this.iframe = document.getElementById('vb-preview-iframe')

        if (this.iframe) {
            this.iframe.addEventListener('load', () => {
                this.attachListener()
            })
            if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
                this.attachListener()
            }
        }

        $(document).on('click', () => this.hide())

        this.$menu.on('click', '.dropdown-item', (e) => {
            e.preventDefault()
            const action = $(e.currentTarget).data('action')
            this.handleAction(action)
            this.hide()
        })
    }

    createMenu() {
        const menuHtml = `
            <div id="vb-context-menu" class="dropdown-menu dropdown-menu-end position-fixed" style="z-index: 9999; display: none;">
                <a class="dropdown-item" href="#" data-action="edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -4 -4l-10.5 10.5v4" /><line x1="13.5" y1="6.5" x2="17.5" y2="10.5" /></svg>
                    Edit
                </a>
                <a class="dropdown-item" href="#" data-action="duplicate">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="8" y="8" width="12" height="12" rx="2" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                    Duplicate
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-action="copy-style">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brush me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-4a4 4 0 1 1 4 4h-4" /><path d="M21 3a16 16 0 0 0 -12.8 10.2" /><path d="M21 3a16 16 0 0 1 -10.2 12.8" /><path d="M10.6 9a9 9 0 0 1 4.4 4.4" /></svg>
                    Copy Style
                </a>
                <a class="dropdown-item" href="#" data-action="paste-style" id="vb-ctx-paste-style" style="display:none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bucket me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 11v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /><path d="M13 13l9 -9" /><path d="M13 13l-9 9" /><path d="M13 13l9 9" /><path d="M13 13l-9 -9" /></svg>
                    Paste Style
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-action="move-up">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-up me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="18" y1="11" x2="12" y2="5" /><line x1="6" y1="11" x2="12" y2="5" /></svg>
                    Move Up
                </a>
                <a class="dropdown-item" href="#" data-action="move-down">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-down me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="18" y1="13" x2="12" y2="19" /><line x1="6" y1="13" x2="12" y2="19" /></svg>
                    Move Down
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" data-action="delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    Delete
                </a>
            </div>
        `
        $('body').append(menuHtml)
        this.$menu = $('#vb-context-menu')
    }

    attachListener() {
        const doc = this.iframe.contentDocument
        if (!doc) return

        doc.body.addEventListener('contextmenu', (e) => {
            const target = e.target.closest('[data-shortcode-id]')
            if (target) {
                e.preventDefault()
                this.targetId = target.getAttribute('data-shortcode-id')

                const $pasteBtn = this.$menu.find('#vb-ctx-paste-style')
                if (window.vbClipboard && window.vbClipboard.type === 'style') {
                     const current = VisualBuilderState.getShortcode(this.targetId)
                     if (current && current.name === window.vbClipboard.shortcodeName) {
                         $pasteBtn.show()
                     } else {
                         $pasteBtn.hide()
                     }
                } else {
                    $pasteBtn.hide()
                }

                const rect = this.iframe.getBoundingClientRect()
                const x = rect.left + e.clientX
                const y = rect.top + e.clientY

                this.show(x, y)
            } else {
                this.hide()
            }
        })

        doc.body.addEventListener('click', (e) => {
            this.hide()
        })
    }

    show(x, y) {
        this.$menu.css({
            top: y + 'px',
            left: x + 'px',
            display: 'block'
        })

        const menuWidth = this.$menu.outerWidth()
        const menuHeight = this.$menu.outerHeight()

        const viewportWidth = $(window).width()
        const viewportHeight = $(window).height()

        let finalX = x
        if (x + menuWidth > viewportWidth) {
            finalX = viewportWidth - menuWidth - 10
        }

        let finalY = y
        if (y + menuHeight > viewportHeight) {
            finalY = viewportHeight - menuHeight - 10
        }

        this.$menu.css({
            top: finalY + 'px',
            left: finalX + 'px'
        })
    }

    hide() {
        this.$menu.hide()
    }

    handleAction(action) {
        const id = this.targetId
        if (!id) return

        const shortcodes = VisualBuilderState.getAllShortcodes()
        const index = shortcodes.findIndex(s => s.id === id)

        if (index === -1) return

        switch (action) {
            case 'edit':
                VisualBuilderState.setActive(id)
                if (typeof ShortcodeEditPanel !== 'undefined') {
                }
                break

            case 'duplicate':
                const clone = JSON.parse(JSON.stringify(shortcodes[index]))
                clone.id = VisualBuilderState.generateId()
                VisualBuilderState.addShortcode(clone, index + 1)
                break

            case 'copy-style':
                window.vbClipboard = {
                    type: 'style',
                    shortcodeName: shortcodes[index].name,
                    attributes: JSON.parse(JSON.stringify(shortcodes[index].attributes || {}))
                }
                if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                    Botble.showSuccess('Style copied!')
                }
                break

            case 'paste-style':
                if (window.vbClipboard && window.vbClipboard.type === 'style') {
                    const newAttributes = { ...shortcodes[index].attributes, ...window.vbClipboard.attributes }
                    VisualBuilderState.updateShortcode(id, { attributes: newAttributes })

                    if (typeof PreviewIframe !== 'undefined') {
                        PreviewIframe.updateShortcode(id, { attributes: newAttributes })
                    }
                    if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                        Botble.showSuccess('Style pasted!')
                    }
                }
                break

            case 'delete':
                if (confirm(window.visualBuilderData.translations.confirmDelete)) {
                    VisualBuilderState.deleteShortcode(id)
                }
                break

            case 'move-up':
                if (index > 0) {
                    const newOrder = [...shortcodes]
                    const temp = newOrder[index]
                    newOrder[index] = newOrder[index - 1]
                    newOrder[index - 1] = temp
                    VisualBuilderState.reorderShortcodes(newOrder)
                }
                break

            case 'move-down':
                if (index < shortcodes.length - 1) {
                    const newOrder = [...shortcodes]
                    const temp = newOrder[index]
                    newOrder[index] = newOrder[index + 1]
                    newOrder[index + 1] = temp
                    VisualBuilderState.reorderShortcodes(newOrder)
                }
                break
        }

        if (['duplicate', 'delete', 'move-up', 'move-down'].includes(action)) {
             if (typeof PreviewIframe !== 'undefined') {
                 setTimeout(() => PreviewIframe.reload(), 50)
             }
             if (typeof ShortcodeList !== 'undefined') {
                 ShortcodeList.render()
             }
        }
    }
}

window.ContextMenu = new ContextMenu()
