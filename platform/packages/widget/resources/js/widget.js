class WidgetManagement {
    constructor() {
        this.modal = null
        this.selectedWidgetId = null
        this.selectedSidebarId = null
        this.isDragging = false
    }

    saveWidget(parentElement) {
        if (parentElement.length > 0) {
            let items = []
            $.each(parentElement.find('li[data-id]'), (index, widget) => {
                items.push($(widget).find('form').serialize())
            })

            $httpClient
                .make()
                .post(BWidget.routes.save_widgets_sidebar, {
                    items: items,
                    sidebar_id: parentElement.data('id'),
                })
                .then(({ data }) => {
                    parentElement.find('ul').html(data.data)
                    this.updateWidgetCount(parentElement)
                    Botble.callScroll($('.list-page-select-widget'))
                    Botble.initResources()
                    Botble.initMediaIntegrate()
                    Botble.initTreeCheckboxes()

                    if (window.Botble) {
                        window.Botble.initCoreIcon()
                    }

                    $(document).trigger('widgets:reloaded')

                    Botble.showSuccess(data.message)
                })
                .finally(() => {
                    parentElement.find('.widget-save i').remove()
                })
        }
    }

    updateWidgetCount(sidebarElement) {
        const count = sidebarElement.find('.widget-item').length
        const badge = sidebarElement.find('.widget-area-count')
        if (badge.length) {
            badge.text(count)
        }
    }

    updateAllWidgetCounts() {
        $('.sidebar-item').each((index, element) => {
            this.updateWidgetCount($(element))
        })
    }

    showStep1() {
        $('#widget-add-step-1').show()
        $('#widget-add-step-2').hide()
        $('#widget-add-form-container').html(
            '<div class="widget-add-loading"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2 text-muted">Loading...</span></div>'
        )
    }

    showStep2(sidebarName) {
        $('#widget-add-step-1').hide()
        $('#widget-add-step-2').show()
        $('#widget-add-sidebar-name').text(sidebarName)
    }

    loadWidgetForm(widgetId, sidebarId) {
        $httpClient
            .make()
            .get(BWidget.routes.get_widget_form, {
                widget_id: widgetId,
                sidebar_id: sidebarId,
            })
            .then(({ data }) => {
                $('#widget-add-form-container').html(data.data.form)

                Botble.initResources()
                Botble.initMediaIntegrate()
                Botble.initTreeCheckboxes()

                if (window.Botble) {
                    window.Botble.initCoreIcon()
                }
            })
            .catch(() => {
                $('#widget-add-form-container').html(
                    '<div class="alert alert-danger mb-0"><div class="d-flex"><svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg><div>Failed to load widget form. Please try again.</div></div></div>'
                )
            })
    }

    addWidgetToSidebar() {
        const sidebarId = $('#widget-add-modal-sidebar-id').val()
        const widgetId = $('#widget-add-modal-widget-id').val()

        if (!widgetId || !sidebarId) {
            return
        }

        const sidebarItem = $(`.sidebar-item[data-id="${sidebarId}"]`)
        if (sidebarItem.length === 0) {
            return
        }

        const formData = $('#widget-add-form').serialize()
        const sourceWidget = $('#wrap-widget-1 li').filter(function () {
            return $(this).data('id') === widgetId
        })
        if (sourceWidget.length === 0) {
            return
        }

        const clonedWidget = sourceWidget.clone()
        const formParams = new URLSearchParams(formData)
        formParams.forEach((value, key) => {
            const input = clonedWidget.find(`[name="${key}"]`)
            if (input.length) {
                if (input.is(':checkbox') || input.is(':radio')) {
                    input.prop('checked', value === '1' || value === 'on' || value === input.val())
                } else {
                    input.val(value)
                }
            } else {
                clonedWidget.find('form').append(`<input type="hidden" name="${key}" value="${value}">`)
            }
        })

        const targetList = sidebarItem.find('ul')
        targetList.find('.widget-dropzone').remove()
        targetList.append(clonedWidget)

        this.saveWidget(sidebarItem)
        this.modal.hide()
        this.showStep1()
    }

    initWidgetSearch() {
        const searchInput = $('#widget-search')
        const widgetList = $('#wrap-widget-1')
        const emptyState = $('#widget-search-empty')

        if (!searchInput.length) return

        searchInput.on('input', function () {
            const query = $(this).val().toLowerCase().trim()
            let visibleCount = 0

            widgetList.find('.widget-source-item').each(function () {
                const name = $(this).data('name') || ''
                const matches = name.includes(query)
                $(this).toggle(matches)
                if (matches) visibleCount++
            })

            if (visibleCount === 0 && query.length > 0) {
                widgetList.hide()
                emptyState.show()
            } else {
                widgetList.show()
                emptyState.hide()
            }
        })

        searchInput.on('keydown', function (e) {
            if (e.key === 'Escape') {
                $(this).val('').trigger('input')
            }
        })
    }

    initDragIndicator() {
        const self = this

        $(document)
            .on('sortstart', function () {
                self.isDragging = true
                $('#wrap-widgets').addClass('dragging-active')
            })
            .on('sortstop', function () {
                self.isDragging = false
                $('#wrap-widgets').removeClass('dragging-active')
            })
    }

    init() {
        let listWidgets = [
            {
                name: 'wrap-widgets',
                pull: 'clone',
                put: false,
            },
        ]

        $.each($('.sidebar-item'), () => {
            listWidgets.push({ name: 'wrap-widgets', pull: true, put: true })
        })

        listWidgets.forEach((groupOpts, i) => {
            const element = document.getElementById('wrap-widget-' + (i + 1))
            if (!element) return

            Sortable.create(element, {
                sort: i !== 0,
                group: groupOpts,
                delay: 0,
                disabled: false,
                store: null,
                animation: 200,
                easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                handle: '.widget-draggable-handler',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                dataIdAttr: 'data-id',

                forceFallback: false,
                fallbackClass: 'sortable-fallback',
                fallbackOnBody: false,

                scroll: true,
                scrollSensitivity: 50,
                scrollSpeed: 10,

                onStart: () => {
                    this.isDragging = true
                    $('#wrap-widgets').addClass('dragging-active')
                },
                onEnd: () => {
                    this.isDragging = false
                    $('#wrap-widgets').removeClass('dragging-active')
                },
                onUpdate: (evt) => {
                    if (evt.from !== evt.to) {
                        this.saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    this.saveWidget($(evt.item).closest('.sidebar-item'))
                },
                onAdd: (evt) => {
                    if (evt.from !== evt.to) {
                        this.saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    this.saveWidget($(evt.item).closest('.sidebar-item'))
                },
            })
        })

        $('#wrap-widgets')
            .on('click', '.widget-control-delete', (event) => {
                event.preventDefault()
                let _self = $(event.currentTarget)

                let widget = _self.closest('li')

                Botble.showButtonLoading(_self)

                $httpClient
                    .make()
                    .delete(BWidget.routes.delete, {
                        widget_id: widget.data('id'),
                        position: widget.data('position'),
                        sidebar_id: _self.closest('.sidebar-item').data('id'),
                    })
                    .then(({ data }) => {
                        Botble.showSuccess(data.message)
                        const sidebarItem = _self.closest('.sidebar-item')
                        sidebarItem.find('ul').html(data.data)
                        this.updateWidgetCount(sidebarItem)

                        if (window.Botble) {
                            window.Botble.initResources()
                            window.Botble.initMediaIntegrate()
                            window.Botble.initCoreIcon()
                        }

                        $(document).trigger('widgets:reloaded')
                    })
                    .finally(() => {
                        Botble.showButtonLoading(widget.find('.widget-control-delete'))
                    })
            })
            .on('click', '.widget-item .widget-item-header', (event) => {
                if (this.isDragging) return

                const target = $(event.target)
                if (target.closest('.widget-item-toggle').length || target.is('.widget-item-toggle')) {
                    return
                }

                let _self = $(event.currentTarget)
                this.toggleWidgetContent(_self)
            })
            .on('click', '.widget-item-toggle', (event) => {
                event.preventDefault()
                event.stopPropagation()

                if (this.isDragging) return

                let _self = $(event.currentTarget).closest('.widget-item-header')
                this.toggleWidgetContent(_self)
            })
            .on('click', '.widget-save', (event) => {
                event.preventDefault()
                let _self = $(event.currentTarget)
                Botble.showButtonLoading(_self)
                this.saveWidget(_self.closest('.sidebar-item'))
            })
            .on('click', '.widget-add-btn', (event) => {
                event.preventDefault()
                event.stopPropagation()

                const button = $(event.currentTarget)
                this.selectedWidgetId = button.data('widget-id')
                $('#widget-add-modal-widget-id').val(this.selectedWidgetId)

                this.showStep1()

                if (!this.modal) {
                    this.modal = new bootstrap.Modal(document.getElementById('widget-add-modal'))
                }
                this.modal.show()
            })

        $(document).on('click', '.widget-sidebar-option', (event) => {
            event.preventDefault()

            const button = $(event.currentTarget)
            const sidebarId = button.data('sidebar-id')
            const sidebarName = button.data('sidebar-name')
            const widgetId = $('#widget-add-modal-widget-id').val()

            if (!widgetId || !sidebarId) {
                return
            }

            this.selectedSidebarId = sidebarId
            $('#widget-add-modal-sidebar-id').val(sidebarId)

            this.showStep2(sidebarName)
            this.loadWidgetForm(widgetId, sidebarId)
        })

        $(document).on('click', '#widget-add-back', (event) => {
            event.preventDefault()
            this.showStep1()
        })

        $(document).on('click', '#widget-add-submit', (event) => {
            event.preventDefault()
            this.addWidgetToSidebar()
        })

        $('#widget-add-modal').on('hidden.bs.modal', () => {
            this.showStep1()
        })

        this.initWidgetSearch()
        this.updateAllWidgetCounts()
    }

    toggleWidgetContent(headerElement) {
        const widgetItem = headerElement.closest('.widget-item')
        const content = widgetItem.find('.widget-item-content')

        widgetItem.toggleClass('is-expanded')
        content.slideToggle(250, function () {
            $(this).toggleClass('show')
        })
    }
}

$(() => {
    new WidgetManagement().init()
})
