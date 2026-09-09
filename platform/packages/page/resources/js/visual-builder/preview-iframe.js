const PreviewIframe = {
    $iframe: null,
    $container: null,
    $loading: null,
    $error: null,
    $preview: null,
    iframe: null,
    isReady: false,
    deviceMode: 'desktop',

    init() {
        this.$iframe = $('#vb-preview-iframe')
        this.$container = $('#vb-preview-frame-container')
        this.$loading = $('#vb-preview-loading')
        this.$error = $('#vb-preview-error')
        this.$preview = $('#vb-preview')
        this.iframe = this.$iframe[0]

        this.$iframe.on('load', () => {
            this.handleLoad()
        })

        this.$iframe.on('error', () => {
            this.showError('Failed to load preview')
        })

        if (typeof PostMessageUtil !== 'undefined') {
            PostMessageUtil.listenToIframe((type, payload) => {
                this.handleMessage(type, payload)
            })
        }

        this.setDeviceMode('desktop')
    },

    handleLoad() {
        this.isReady = true
        this.hideLoading()
        this.$preview.addClass('loaded')

        setTimeout(() => {
            this.syncShortcodeIds()
            this.injectEditIcons()
            this.enableInlineEditing()
            this.enableSorting()
            this.attachLazyLoadListener()
        }, 1000)
    },

    syncShortcodeIds() {
        if (!this.iframe || !this.iframe.contentDocument) return

        const iframeDoc = this.iframe.contentDocument
        const stateShortcodes = VisualBuilderState.getAllShortcodes()

        if (!stateShortcodes.length) return

        const allElements = iframeDoc.querySelectorAll('[data-shortcode-id]:not(.vb-shortcode-toolbar)')

        if (!allElements.length) return

        const uniqueElements = []
        const seenIds = new Set()
        allElements.forEach((el) => {
            const id = el.getAttribute('data-shortcode-id')
            if (!seenIds.has(id)) {
                seenIds.add(id)
                uniqueElements.push(el)
            }
        })

        const count = Math.min(uniqueElements.length, stateShortcodes.length)
        for (let i = 0; i < count; i++) {
            const stateId = stateShortcodes[i].id
            const currentId = uniqueElements[i].getAttribute('data-shortcode-id')

            if (stateId !== currentId) {
                uniqueElements[i].setAttribute('data-shortcode-id', stateId)
            }
        }
    },

    attachLazyLoadListener() {
        if (!this.iframe || !this.iframe.contentDocument) return

        const iframeDoc = this.iframe.contentDocument

        iframeDoc.addEventListener('shortcode.loaded', (e) => {
            setTimeout(() => {
                if (typeof EditIconInjector !== 'undefined') {
                    EditIconInjector.inject(iframeDoc)
                }
            }, 100)
        })
    },

    enableInlineEditing() {
        if (!this.iframe || !this.iframe.contentDocument) return

        const doc = this.iframe.contentDocument

        if (this._inlineCtx) {
             doc.body.removeEventListener('click', this._inlineCtx)
             doc.body.removeEventListener('input', this._inlineInputCtx)
        }

        this._inlineCtx = (e) => {
            const $editForm = $('#vb-edit-form')
            if (!$editForm.length) return

            const target = e.target
            const normalize = (str) => {
                return str ? str.replace(/[\s\u00A0]+/g, ' ').trim().replace(/['""]/g, "'").replace(/[""]/g, '"') : ''
            }

            if (target.tagName === 'IMG') {
                const src = target.getAttribute('src')
                if (!src) return

                const relativeSrc = src.replace(window.location.origin, '')

                const checkMatch = (val) => {
                    if (!val) return false
                    const decodedVal = decodeURIComponent(val)
                    const decodedSrc = decodeURIComponent(src)
                    const decodedRel = decodeURIComponent(relativeSrc)

                    return val.includes(relativeSrc) ||
                           src.includes(val) ||
                           decodedVal === decodedRel ||
                           decodedSrc.endsWith(decodedVal)
                }

                let $match = $editForm.find(`input[type="hidden"], input[type="text"]`).filter((i, el) => {
                     return checkMatch($(el).val())
                }).first()

                if ($match.length) {
                   e.preventDefault()
                   e.stopPropagation()

                   const $trigger = $match.closest('.image-box').find('.btn_gallery')
                   if ($trigger.length) {
                       $trigger.trigger('click')
                   }
                   return
                }
            }

            const isTextNode = (node) => node.nodeType === 3
            const isSimpleElement = (el) => {
                if (el.children.length === 0) return true
                for (let child of el.childNodes) {
                    if (child.nodeType !== 3 && (child.tagName !== 'BR')) {
                        return false
                    }
                }
                return true
            }

            if (isSimpleElement(target) && target.textContent.trim().length > 0) {
                 const text = normalize(target.innerText || target.textContent)

                 const $match = $editForm.find('input[type="text"], input[type="number"], textarea').filter((i, el) => {
                     let val = $(el).val()
                     if (val && val.includes('<')) {
                         val = val.replace(/<[^>]+>/g, ' ')
                     }
                     return normalize(val) === text
                 }).first()

                 if ($match.length) {
                     e.preventDefault()
                     e.stopPropagation()

                     target.contentEditable = true
                     target.focus()

                     $match.addClass('vb-bound-input')
                     $match.css({
                         'border-color': '#206bc4',
                         'box-shadow': '0 0 0 2px rgba(32, 107, 196, 0.25)',
                         'transition': 'all 0.2s ease'
                     })

                     if (typeof $match[0].scrollIntoView === 'function') {
                         $match[0].scrollIntoView({ behavior: 'smooth', block: 'center' })
                     }

                     const handleInput = () => {
                         $match.val(target.innerText)
                     }

                     const handleBlur = () => {
                         target.contentEditable = false

                         $match.removeClass('vb-bound-input')
                         $match.css({
                             'border-color': '',
                             'box-shadow': '',
                             'transition': ''
                         })

                         target.removeEventListener('input', handleInput)
                         target.removeEventListener('blur', handleBlur)
                         target.removeEventListener('keydown', handleKeydown)

                         $match.trigger('change')
                     }

                     const handleKeydown = (k) => {
                         if (k.key === 'Enter') {
                             k.preventDefault()
                             target.blur()
                         }
                     }

                     target.addEventListener('input', handleInput)
                     target.addEventListener('blur', handleBlur)
                     target.addEventListener('keydown', handleKeydown)
                 }
            }
        }

        doc.body.addEventListener('click', this._inlineCtx)
    },

    enableSorting() {
        if (!this.iframe || !this.iframe.contentDocument) return
        const doc = this.iframe.contentDocument

        if (!doc.getElementById('vb-sortable-script')) {
            const script = doc.createElement('script')
            script.id = 'vb-sortable-script'
            script.src = '/vendor/core/core/base/libraries/sortable/sortable.min.js'
            script.onload = () => this.initSortable()
            doc.head.appendChild(script)
        } else {
            this.initSortable()
        }
    },

    initSortable() {
        const doc = this.iframe.contentDocument
        if (!doc || !doc.defaultView.Sortable) return

        const containers = new Set()
        doc.querySelectorAll('[data-shortcode-id]').forEach(el => {
            if (el.parentElement) containers.add(el.parentElement)
        })

        containers.forEach(container => {
             if (container._sortable) return

             container._sortable = new doc.defaultView.Sortable(container, {
                 animation: 150,
                 handle: '[data-shortcode-id]',
                 filter: '[contenteditable]',
                 preventOnFilter: false,
                 onEnd: (evt) => {
                     this.handleSort(evt)
                 }
             })
        })
    },

    handleSort(evt) {
        const item = evt.item
        const newIndex = evt.newIndex
        const oldIndex = evt.oldIndex

        if (newIndex === oldIndex) return

        const doc = this.iframe.contentDocument
        const newOrderIds = []
        doc.querySelectorAll('[data-shortcode-id]').forEach(el => {
            newOrderIds.push(el.getAttribute('data-shortcode-id'))
        })

        VisualBuilderState.reorderShortcodesByIds(newOrderIds)
    },

    handleMessage(type, payload) {
        switch (type) {
            case 'ready':
                this.handleLoad()
                break

            case 'edit-shortcode':
                if (payload && payload.id) {
                    this.handleEditRequest(payload.id)
                }
                break

            case 'delete-shortcode':
                if (payload && payload.id) {
                    this.handleDeleteRequest(payload.id)
                }
                break
        }
    },

    handleEditRequest(shortcodeId) {
        if (typeof ShortcodeList !== 'undefined') {
            ShortcodeList.openEditPanel(shortcodeId)
        }
    },

    handleDeleteRequest(shortcodeId) {
        if (typeof ShortcodeList !== 'undefined') {
            ShortcodeList.deleteShortcode(shortcodeId)
        }
    },

    injectEditIcons() {
        if (!this.iframe || !this.iframe.contentDocument) {
            return
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document

            if (typeof EditIconInjector !== 'undefined') {
                EditIconInjector.inject(iframeDoc)
            }

            this.injectEditIconStyles(iframeDoc)
        } catch (e) {}
    },

    injectEditIconStyles(iframeDoc) {
        if (iframeDoc.getElementById('vb-edit-icon-styles')) {
            return
        }

        const style = iframeDoc.createElement('style')
        style.id = 'vb-edit-icon-styles'
        style.textContent = `
            .vb-shortcode-block {
                position: relative !important;
                min-height: 20px;
                transition: all 0.2s ease;
            }

            .vb-shortcode-toolbar {
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 9999;
                display: flex;
                gap: 4px;
                opacity: 0;
                transition: all 0.2s ease;
                pointer-events: none;
            }

            .vb-shortcode-block:hover .vb-shortcode-toolbar,
            .vb-shortcode-block.vb-active .vb-shortcode-toolbar {
                opacity: 1;
                pointer-events: all;
            }

            .vb-toolbar-btn {
                width: 32px;
                height: 32px;
                border: none;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                font-size: 16px;
            }

            .vb-toolbar-btn i,
            .vb-toolbar-btn svg {
                color: #fff;
                stroke: #fff;
                font-size: 16px;
                line-height: 1;
            }

            .vb-toolbar-btn.vb-edit-btn {
                background: #206bc4;
            }

            .vb-toolbar-btn.vb-edit-btn:hover {
                background: #1a5ba7;
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(32, 107, 196, 0.3);
            }

            .vb-toolbar-btn.vb-delete-btn {
                background: #d63939;
            }

            .vb-toolbar-btn.vb-delete-btn:hover {
                background: #c72e2e;
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(214, 57, 57, 0.3);
            }

            .vb-shortcode-block:hover {
                outline: 2px dashed #206bc4;
                outline-offset: 2px;
            }

            .vb-shortcode-block.vb-active {
                outline: 2px solid #206bc4 !important;
                outline-offset: 2px;
                background: rgba(32, 107, 196, 0.05);
            }

            .vb-shortcode-toolbar * {
                box-sizing: border-box;
            }
        `

        iframeDoc.head.appendChild(style)
    },

    reload() {
        this.isReady = false
        this.showLoading()
        this.$preview.removeClass('loaded error')

        const shortcodes = VisualBuilderState.getAllShortcodes()
        const config = window.visualBuilderData || {}

        const previewUrl = config.previewUrl + (config.previewUrl.includes('?') ? '&' : '?') + 'visual_builder=1'

        const $form = $('<form>', {
            method: 'POST',
            action: previewUrl,
            target: 'vb-preview-iframe',
            style: 'display: none;',
        })

        $form.append(
            $('<input>', {
                type: 'hidden',
                name: '_token',
                value: config.csrfToken,
            })
        )

        $form.append(
            $('<input>', {
                type: 'hidden',
                name: 'shortcodes',
                value: JSON.stringify(shortcodes),
            })
        )

        $form.appendTo('body').submit().remove()
    },

    setDeviceMode(mode) {
        this.deviceMode = mode

        this.$container.removeClass('device-desktop device-tablet device-mobile')
        this.$container.addClass('device-' + mode)
    },

    highlightShortcode(shortcodeId) {
        if (!this.iframe || !this.iframe.contentDocument) {
            return
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document

            if (typeof EditIconInjector !== 'undefined') {
                EditIconInjector.highlight(iframeDoc, shortcodeId)
            }
        } catch (e) {}
    },

    unhighlightShortcode() {
        if (!this.iframe || !this.iframe.contentDocument) {
            return
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document

            if (typeof EditIconInjector !== 'undefined') {
                EditIconInjector.unhighlight(iframeDoc)
            }
        } catch (e) {}
    },

    updateShortcode(shortcodeId, data) {
        const shortcode = VisualBuilderState.getShortcode(shortcodeId)
        if (!shortcode) {
            console.warn('[VB] Shortcode not found for update:', shortcodeId)
            return
        }

        const config = window.visualBuilderData || {}

        const attributes = data && data.attributes ? data.attributes : shortcode.attributes

        const refinedAttributes = {}
        if (attributes) {
            Object.keys(attributes).forEach(key => {
                let val = attributes[key]
                if (Array.isArray(val)) {
                    val = val.join(',')
                }
                refinedAttributes[key] = val
            })
        }

        // Use ref_lang for translated content preview
        let refLang = config.refLang || null
        if (!refLang) {
            const urlParams = new URLSearchParams(window.location.search)
            refLang = urlParams.get('ref_lang')
        }

        const requestData = {
            _token: config.csrfToken,
            name: shortcode.name,
            attributes: refinedAttributes
        }
        if (refLang) {
            requestData.ref_lang = refLang
        }

        $.ajax({
            url: '/ajax/render-ui-blocks',
            method: 'POST',
            data: requestData,
            success: (response) => {
                if (response && response.data) {
                    this.replaceShortcodeContent(shortcodeId, response.data)
                }
            },
            error: (xhr, status, error) => {
                console.error('[VB] Failed to render shortcode:', error, xhr.responseText)
                this.reload()
            }
        })
    },

    replaceShortcodeContent(shortcodeId, newHtml) {
        if (!this.iframe || !this.iframe.contentDocument) {
            return
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document
            const element = iframeDoc.querySelector(`[data-shortcode-id="${shortcodeId}"]`)

            if (element) {
                const tempDiv = iframeDoc.createElement('div')
                tempDiv.innerHTML = newHtml

                const newElement = tempDiv.firstElementChild

                if (newElement) {
                    newElement.setAttribute('data-shortcode-id', shortcodeId)
                    newElement.setAttribute('data-shortcode-name', element.getAttribute('data-shortcode-name') || '')

                    try {
                        element.parentNode.replaceChild(newElement, element)
                    } catch (e) {
                        console.error('[VB] Error replacing child:', e)
                    }

                    if (typeof EditIconInjector !== 'undefined') {
                        EditIconInjector.injectIcon(newElement, iframeDoc)
                    }

                    if (VisualBuilderState.activeShortcode === shortcodeId) {
                        newElement.classList.add('vb-active')
                    }

                    if (iframeDoc.defaultView && typeof iframeDoc.defaultView.reloadInfiniaTheme === 'function') {
                        setTimeout(() => {
                            try {
                                iframeDoc.defaultView.reloadInfiniaTheme()
                            } catch (err) {
                                console.error('[VB] Error executing reloadInfiniaTheme:', err)
                            }
                        }, 100)
                    }
                } else {
                    element.innerHTML = newHtml
                }
            } else {
                console.warn('[VB] Shortcode element not found in iframe, reloading')
                this.reload()
            }
        } catch (e) {
            console.error('[VB] Error replacing shortcode content:', e)
            this.reload()
        }
    },

    showLoading() {
        this.$loading.show()
        this.$error.hide()
        this.$container.hide()
    },

    hideLoading() {
        this.$loading.hide()
        this.$container.show()
    },

    showError(message) {
        this.isReady = false
        this.$loading.hide()
        this.$container.hide()
        this.$error.show()
        this.$preview.addClass('error')

        if (message) {
            $('#vb-error-message').text(message)
        }
    },
}

window.PreviewIframe = PreviewIframe
