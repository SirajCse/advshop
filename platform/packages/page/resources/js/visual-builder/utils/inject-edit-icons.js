const EditIconInjector = {
    inject(iframeDocument) {
        if (!iframeDocument) {
            return
        }

        this.blockLinks(iframeDocument)

        const shortcodeElements = iframeDocument.querySelectorAll('[data-shortcode-id]')

        if (shortcodeElements.length === 0) {
            return
        }

        shortcodeElements.forEach((element) => {
            this.injectIcon(element, iframeDocument)
        })
    },

    injectIcon(element, iframeDocument) {
        const shortcodeId = element.getAttribute('data-shortcode-id')
        const shortcodeName = element.getAttribute('data-shortcode-name') || 'shortcode'

        if (element.querySelector(':scope > .vb-shortcode-toolbar')) {
            return
        }

        if (element.classList.contains('vb-shortcode-toolbar')) {
            return
        }

        const position = window.getComputedStyle(element).position
        if (position === 'static') {
            element.style.position = 'relative'
        }

        element.classList.add('vb-shortcode-block')

        const toolbar = iframeDocument.createElement('div')
        toolbar.className = 'vb-shortcode-toolbar'
        toolbar.setAttribute('data-shortcode-id', shortcodeId)

        const editBtn = iframeDocument.createElement('button')
        editBtn.className = 'vb-toolbar-btn vb-edit-btn'
        editBtn.setAttribute('title', `Edit ${shortcodeName}`)
        editBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>`
        editBtn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this.handleEditClick(shortcodeId)
        })

        const deleteBtn = iframeDocument.createElement('button')
        deleteBtn.className = 'vb-toolbar-btn vb-delete-btn'
        deleteBtn.setAttribute('title', `Delete ${shortcodeName}`)
        deleteBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>`
        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this.handleDeleteClick(shortcodeId)
        })

        toolbar.appendChild(editBtn)
        toolbar.appendChild(deleteBtn)

        element.insertBefore(toolbar, element.firstChild)
    },

    handleEditClick(shortcodeId) {
        if (typeof ShortcodeList !== 'undefined') {
            ShortcodeList.openEditPanel(shortcodeId)
        }
    },

    handleDeleteClick(shortcodeId) {
        if (typeof ShortcodeList !== 'undefined') {
            ShortcodeList.deleteShortcode(shortcodeId)
        }
    },

    blockLinks(iframeDocument) {
        if (iframeDocument._linksBlocked) {
            return
        }

        iframeDocument.addEventListener('click', (e) => {
            const link = e.target.closest('a')
            if (link) {
                e.preventDefault()
                e.stopPropagation()
            }
        }, true)

        iframeDocument._linksBlocked = true
    },

    removeAll(iframeDocument) {
        if (!iframeDocument) return

        const toolbars = iframeDocument.querySelectorAll('.vb-shortcode-toolbar')
        toolbars.forEach((toolbar) => toolbar.remove())
    },

    highlight(iframeDocument, shortcodeId) {
        if (!iframeDocument) return

        const previousActive = iframeDocument.querySelectorAll('.vb-active')
        previousActive.forEach((el) => el.classList.remove('vb-active'))

        if (shortcodeId) {
            const element = iframeDocument.querySelector(`[data-shortcode-id="${shortcodeId}"]`)
            if (element) {
                element.classList.add('vb-active')
                element.scrollIntoView({ behavior: 'smooth', block: 'center' })
            }
        }
    },

    unhighlight(iframeDocument) {
        if (!iframeDocument) return

        const activeElements = iframeDocument.querySelectorAll('.vb-active')
        activeElements.forEach((el) => el.classList.remove('vb-active'))
    },
}

window.EditIconInjector = EditIconInjector
