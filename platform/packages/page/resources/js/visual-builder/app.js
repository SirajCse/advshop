;(function ($) {
    'use strict'

    const VisualBuilderApp = {
        config: {},
        modules: {},
        draftCheckPerformed: false,

        init() {
            this.config = window.visualBuilderData || {}

            VisualBuilderState.init(this.config.shortcodes)

            this.initHeader()
            this.initUndoRedo()
            this.initSidebar()
            this.initPreview()
            this.initAutoSave()
            this.initUnsavedWarning()

            if (typeof ContextMenu !== 'undefined') {
                ContextMenu.init()
            }
        },

        initHeader() {
            const self = this

            $('#vb-save-btn').on('click', function () {
                self.save()
            })

            $('#vb-close-btn').on('click', function (e) {
                if (VisualBuilderState.hasChanges) {
                    const confirmed = confirm(self.config.translations.unsavedChanges)
                    if (confirmed) {
                        VisualBuilderState.clearChanges()
                    } else {
                        e.preventDefault()
                        return false
                    }
                }
            })

            $('#vb-device-modes input[type="radio"]').on('change', function () {
                if (this.checked) {
                    const device = $(this).data('device')
                    VisualBuilderState.setDeviceMode(device)
                }
            })

            VisualBuilderState.on('has-changes', (hasChanges) => {
                if (hasChanges) {
                    $('#vb-unsaved-indicator').show()
                } else {
                    $('#vb-unsaved-indicator').hide()
                }
            })

            VisualBuilderState.on('saving-changed', (isSaving) => {
                const $btn = $('#vb-save-btn')
                const saveIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-device-floppy"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>'

                if (isSaving) {
                    $btn.prop('disabled', true)
                    $btn.html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-loader animate-spin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6l0 -3" /><path d="M16.25 7.75l2.15 -2.15" /><path d="M18 12l3 0" /><path d="M16.25 16.25l2.15 2.15" /><path d="M12 18l0 3" /><path d="M7.75 16.25l-2.15 2.15" /><path d="M6 12l-3 0" /><path d="M7.75 7.75l-2.15 -2.15" /></svg> <span class="d-none d-sm-inline">' + self.config.translations.saving + '</span>')
                } else {
                    $btn.prop('disabled', false)
                    $btn.html(saveIcon + ' <span class="d-none d-sm-inline">' + self.config.translations.saved + '</span>')

                    setTimeout(() => {
                        if (!VisualBuilderState.isSaving) {
                            $btn.html(saveIcon + ' <span class="d-none d-sm-inline">' + (self.config.translations.save || 'Save') + '</span>')
                        }
                    }, 2000)
                }
            })
        },

        initUndoRedo() {
            $('#vb-undo-btn').on('click', () => {
                VisualBuilderState.undo()
            })

            $('#vb-redo-btn').on('click', () => {
                VisualBuilderState.redo()
            })

            VisualBuilderState.on('history-changed', (data) => {
                $('#vb-undo-btn').prop('disabled', !data.canUndo)
                $('#vb-redo-btn').prop('disabled', !data.canRedo)
            })

            VisualBuilderState.on('state-restored', () => {
                if (typeof ShortcodeList !== 'undefined') {
                    ShortcodeList.render()
                }
                if (typeof PreviewIframe !== 'undefined') {
                    PreviewIframe.reload()
                }
                VisualBuilderState.clearActive()
                $('#vb-edit-panel').addClass('d-none').hide()
            })

            $(document).on('keydown', (e) => {
                const isCmdOrCtrl = e.metaKey || e.ctrlKey

                if (isCmdOrCtrl) {
                    if (e.key === 'z') {
                        e.preventDefault()
                        if (e.shiftKey) {
                            VisualBuilderState.redo()
                        } else {
                            VisualBuilderState.undo()
                        }
                    } else if (e.key === 'y') {
                        e.preventDefault()
                        VisualBuilderState.redo()
                    }
                }
            })
        },

        initSidebar() {
            if (typeof ShortcodeList !== 'undefined') {
                ShortcodeList.init()
            }

            if (typeof ShortcodeEditPanel !== 'undefined') {
                ShortcodeEditPanel.init()
            }

            if (typeof AddShortcodeModal !== 'undefined') {
                AddShortcodeModal.init()
            }

            $('#vb-back-btn, #vb-close-panel-btn').on('click', function () {
                $('#vb-edit-panel').addClass('d-none').hide()
                VisualBuilderState.clearActive()
            })

            $('#vb-add-shortcode-btn').on('click', function () {
                if (typeof AddShortcodeModal !== 'undefined') {
                    AddShortcodeModal.show()
                }
            })

            VisualBuilderState.on('active-changed', (id) => {
                if (id) {
                    $('#vb-edit-panel').removeClass('d-none').show()
                    if (typeof ShortcodeEditPanel !== 'undefined') {
                        ShortcodeEditPanel.render(id)
                    }
                } else {
                    $('#vb-edit-panel').addClass('d-none').hide()
                }
            })
        },

        initPreview() {
            if (typeof PreviewIframe !== 'undefined') {
                PreviewIframe.init()
                PreviewIframe.reload()
            }

            $('#vb-reload-preview-btn').on('click', function () {
                if (typeof PreviewIframe !== 'undefined') {
                    PreviewIframe.reload()
                }
            })

            VisualBuilderState.on('device-mode-changed', (mode) => {
                if (typeof PreviewIframe !== 'undefined') {
                    PreviewIframe.setDeviceMode(mode)
                }
            })
        },

        save() {
            const self = this

            VisualBuilderState.setSaving(true)

            let content = ''
            if (typeof ShortcodeSerializer !== 'undefined') {
                content = ShortcodeSerializer.serialize(VisualBuilderState.getAllShortcodes())
            }

            $.ajax({
                url: this.config.saveUrl,
                method: 'POST',
                data: {
                    _token: this.config.csrfToken,
                    content: content,
                },
                success: function (response) {
                    VisualBuilderState.setSaving(false)
                    VisualBuilderState.clearChanges()

                    localStorage.removeItem('vb_draft_' + self.config.pageId)

                    if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                        Botble.showSuccess(self.config.translations.saved)
                    } else {
                        alert(self.config.translations.saved)
                    }
                },
                error: function (xhr) {
                    VisualBuilderState.setSaving(false)

                    let errorMsg = self.config.translations.error
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message
                    }

                    if (typeof Botble !== 'undefined' && Botble.showError) {
                        Botble.showError(errorMsg)
                    } else {
                        alert(errorMsg)
                    }
                },
            })
        },

        initAutoSave() {
            const self = this
            let autoSaveInterval = null

            VisualBuilderState.on('has-changes', (hasChanges) => {
                if (hasChanges && !autoSaveInterval) {
                    autoSaveInterval = setInterval(() => {
                        self.autoSave()
                    }, 30000)
                } else if (!hasChanges && autoSaveInterval) {
                    clearInterval(autoSaveInterval)
                    autoSaveInterval = null
                }
            })

            this.checkDraft()
        },

        autoSave() {
            const draft = {
                timestamp: Date.now(),
                shortcodes: VisualBuilderState.getAllShortcodes(),
            }

            try {
                localStorage.setItem('vb_draft_' + this.config.pageId, JSON.stringify(draft))
            } catch (e) {}
        },

        checkDraft() {
            if (this.draftCheckPerformed) {
                return
            }

            this.draftCheckPerformed = true

            const self = this
            const draftKey = 'vb_draft_' + this.config.pageId
            const draftStr = localStorage.getItem(draftKey)

            if (draftStr) {
                try {
                    const draft = JSON.parse(draftStr)
                    const draftDate = new Date(draft.timestamp)

                    if (confirm('Restore unsaved changes from ' + draftDate.toLocaleString() + '?')) {
                        VisualBuilderState.init(draft.shortcodes)
                        VisualBuilderState.markChanged()

                        if (typeof ShortcodeList !== 'undefined') {
                            ShortcodeList.render()
                        }

                        if (typeof PreviewIframe !== 'undefined') {
                            PreviewIframe.reload()
                        }
                    } else {
                        localStorage.removeItem(draftKey)
                    }
                } catch (e) {
                    localStorage.removeItem(draftKey)
                }
            }
        },

        initUnsavedWarning() {
            window.addEventListener('beforeunload', (e) => {
                if (VisualBuilderState.hasChanges) {
                    e.preventDefault()
                    e.returnValue = ''
                    return ''
                }
            })
        },
    }

    window.VisualBuilderApp = VisualBuilderApp
})(jQuery)
