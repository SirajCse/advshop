/**
 * Compare page enhancements (plugin-default — works for any theme):
 *  - Highlight differences toggle.
 *  - Copy share link button.
 *  - Standalone product-picker modal: opens from any empty slot, lets the user
 *    search/paginate products, click to add, or paste a product URL.
 *    No Bootstrap dependency — vanilla CSS + jQuery for AJAX.
 *  - Listens to ecommerce.compare.added/removed events to keep the canonical
 *    share URL in the address bar without a navigation.
 */

const FrontCompare = {
    showError(message) {
        if (typeof Theme !== 'undefined' && Theme.showError) {
            Theme.showError(message)
        } else if (typeof EcommerceApp !== 'undefined' && EcommerceApp.showError) {
            EcommerceApp.showError(message)
        } else {
            alert(message)
        }
    },
    showSuccess(message) {
        if (typeof Theme !== 'undefined' && Theme.showSuccess) {
            Theme.showSuccess(message)
        } else if (typeof EcommerceApp !== 'undefined' && EcommerceApp.showSuccess) {
            EcommerceApp.showSuccess(message)
        }
    },
    handleError(error) {
        if (typeof Theme !== 'undefined' && Theme.handleError) {
            Theme.handleError(error)
        } else if (typeof EcommerceApp !== 'undefined' && EcommerceApp.handleError) {
            EcommerceApp.handleError(error)
        }
    },
    collectRowValues($row) {
        const values = []

        $row.find('td.compare-spec-value').each(function () {
            const raw = $(this).attr('data-spec-value')
            const text = (raw !== undefined ? raw : $(this).text()).trim().toLowerCase()
            values.push(text)
        })

        return values
    },
    isRowAllEqual(values) {
        if (values.length === 0) {
            return true
        }

        // Normalize "no value" markers (empty string, em-dash) to empty so they
        // compare equal to each other when every column lacks data.
        const normalized = values.map((v) => (v === '' || v === '—') ? '' : v)

        // All columns empty → no useful comparison, hide on diff-only filter.
        if (normalized.every((v) => v === '')) {
            return true
        }

        // Treat as "same" only when every column reports the exact same value.
        // A column that has data while another is empty IS a difference worth
        // showing — return false in that case.
        return normalized.every((v) => v === normalized[0])
    },
    refreshSameRowFlags($table) {
        const SAME_VALUE_CLASS = 'compare-row-same'

        // Step 1: flag spec rows that have nothing different worth showing.
        $table.find('tr[data-spec-row]').each((_, row) => {
            const $row = $(row)
            const values = FrontCompare.collectRowValues($row)
            $row.toggleClass(SAME_VALUE_CLASS, FrontCompare.isRowAllEqual(values))
        })

        // Step 2: walk the body once, hide group titles whose every following
        // spec row (up to the next group title) is flagged as "same" — keeps
        // empty section headers from sticking around when filtered.
        const $rows = $table.find('tbody tr')
        let currentGroup = null
        let groupHasVisible = false

        const finalize = () => {
            if (currentGroup) {
                currentGroup.toggleClass(SAME_VALUE_CLASS, ! groupHasVisible)
            }
        }

        $rows.each((_, row) => {
            const $row = $(row)

            if ($row.hasClass('compare-group-row')) {
                finalize()
                currentGroup = $row
                groupHasVisible = false
                return
            }

            if ($row.attr('data-spec-row') !== undefined && ! $row.hasClass(SAME_VALUE_CLASS)) {
                groupHasVisible = true
            }
        })

        finalize()
    },
    writeClipboard(value) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(value)
        }

        const $tmp = $('<textarea>').val(value).appendTo('body').select()

        try {
            document.execCommand('copy')
        } finally {
            $tmp.remove()
        }

        return Promise.resolve()
    },
    updateCanonicalUrl(data) {
        if (! data || ! data.redirect_url || typeof window.history === 'undefined') {
            return
        }

        try {
            window.history.replaceState({}, '', data.redirect_url)
        } catch (err) {
            // Same-origin guarantee should hold; non-fatal if it doesn't.
        }
    },
    debounce(fn, wait) {
        let timer = null

        return function (...args) {
            clearTimeout(timer)
            timer = setTimeout(() => fn.apply(this, args), wait)
        }
    },
}

/**
 * Standalone product-picker modal — opens via [data-bb-toggle="compare-open-picker"],
 * closes via backdrop / ESC / [data-bb-toggle="compare-picker-close"].
 *  - AJAX search (debounced 250ms) hits public.compare.search-products.
 *  - "Load more" appends the next page.
 *  - Click on a card POSTs to public.compare.add then reloads to the canonical URL.
 *  - Paste-URL <details> at the bottom POSTs to public.compare.add-by-url.
 */
const FrontComparePicker = {
    $modal: null,
    $grid: null,
    $status: null,
    $loadMore: null,
    $search: null,
    cardTemplate: null,
    searchUrl: '',
    addByUrlUrl: '',
    currentQuery: '',
    currentPage: 1,
    isLoading: false,
    isSelecting: false, // Race guard — only one in-flight select POST at a time.
    lastFocus: null,

    init() {
        const $modal = $('[data-bb-toggle="compare-picker"]')

        if (! $modal.length) {
            return
        }

        this.$modal = $modal
        this.$grid = $modal.find('[data-bb-toggle="compare-picker-grid"]')
        this.$status = $modal.find('[data-bb-toggle="compare-picker-status"]')
        this.$loadMore = $modal.find('[data-bb-toggle="compare-picker-load-more"]')
        this.$search = $modal.find('[data-bb-toggle="compare-picker-search"]')
        this.cardTemplate = $modal.find('[data-bb-toggle="compare-picker-card-template"]')[0]
        this.searchUrl = $modal.data('search-url') || ''
        this.addByUrlUrl = $modal.data('add-by-url') || ''

        this.bind()
    },

    bind() {
        $(document).on('click', '[data-bb-toggle="compare-open-picker"]', (e) => {
            e.preventDefault()
            this.open(e.currentTarget)
        })

        $(document).on('click', '[data-bb-toggle="compare-picker-close"]', (e) => {
            e.preventDefault()
            this.close()
        })

        $(document).on('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close()
            }
        })

        const debouncedSearch = FrontCompare.debounce(() => {
            this.currentQuery = (this.$search.val() || '').toString().trim()
            this.currentPage = 1
            this.fetchPage(true)
        }, 250)

        this.$search.on('input', debouncedSearch)

        this.$loadMore.on('click', (e) => {
            e.preventDefault()
            this.currentPage += 1
            this.fetchPage(false)
        })

        this.$grid.on('click', '[data-bb-toggle="compare-picker-select"]', (e) => {
            e.preventDefault()
            const $btn = $(e.currentTarget)
            this.select($btn)
        })

        $(document).on('submit', '[data-bb-toggle="compare-picker-url-form"]', (e) => {
            e.preventDefault()
            this.submitUrlForm($(e.currentTarget))
        })
    },

    isOpen() {
        return this.$modal && this.$modal.hasClass('is-open')
    },

    open(trigger) {
        this.lastFocus = trigger || document.activeElement

        this.$modal.addClass('is-open').attr('aria-hidden', 'false')
        $('body').addClass('compare-picker-open')

        // Reset & focus search.
        this.$search.val('')
        this.currentQuery = ''
        this.currentPage = 1
        setTimeout(() => this.$search.trigger('focus'), 50)

        this.fetchPage(true)
    },

    close() {
        if (! this.$modal) return

        this.$modal.removeClass('is-open').attr('aria-hidden', 'true')
        $('body').removeClass('compare-picker-open')

        if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
            this.lastFocus.focus()
        }
    },

    setStatus(text) {
        this.$status.text(text || '')
    },

    fetchPage(reset) {
        if (this.isLoading) return
        this.isLoading = true
        this.$loadMore.prop('disabled', true).attr('hidden', true)

        const loadingText = this.$modal.data('loading-text') || 'Loading…'
        const emptyText = this.$modal.data('empty-text') || 'No products found.'

        if (reset) {
            this.$grid.empty()
            this.setStatus(loadingText)
        }

        $.ajax({
            url: this.searchUrl,
            method: 'GET',
            data: { q: this.currentQuery, page: this.currentPage },
            success: ({ error, message, data }) => {
                if (error) {
                    this.setStatus(message || '')
                    return
                }

                const items = (data && data.items) || []

                if (reset && items.length === 0) {
                    this.setStatus(emptyText)
                    return
                }

                this.setStatus('')
                items.forEach((item) => this.appendCard(item))

                if (data && data.has_more) {
                    this.$loadMore.prop('disabled', false).removeAttr('hidden')
                }
            },
            error: (xhr) => {
                FrontCompare.handleError(xhr)
                this.setStatus('')
            },
            complete: () => {
                this.isLoading = false
            },
        })
    },

    appendCard(item) {
        if (! this.cardTemplate || ! ('content' in this.cardTemplate)) return

        const fragment = this.cardTemplate.content.cloneNode(true)
        const $card = $(fragment).find('.compare-picker-card')

        const $thumb = $card.find('.compare-picker-card-thumb')
        $thumb.attr('href', item.url || '#')
        $thumb.find('img').attr('src', item.image || '').attr('alt', item.name || '')

        $card.find('.compare-picker-card-title a')
            .attr('href', item.url || '#')
            .text(item.name || '')

        $card.find('.compare-picker-card-price').html(item.price || '')

        const $oldPrice = $card.find('.compare-picker-card-price-old')
        if (item.original_price) {
            $oldPrice.html(item.original_price)
        } else {
            $oldPrice.remove()
        }

        $card.find('[data-bb-toggle="compare-picker-select"]')
            .attr('data-product-id', item.id)
            .attr('data-add-url', item.add_url)

        this.$grid.append(fragment)
    },

    select($btn) {
        const addUrl = $btn.data('add-url')

        if (! addUrl || this.isSelecting) {
            return
        }

        // Race guard: lock the namespace + disable every select button in the
        // grid so rapid clicks can't fire concurrent POSTs that each pass the
        // server-side cap check (the session cart isn't transactional).
        this.isSelecting = true
        const $allButtons = this.$grid.find('[data-bb-toggle="compare-picker-select"]')
        $allButtons.prop('disabled', true)

        $.ajax({
            url: addUrl,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: ({ error, message, data }) => {
                if (error) {
                    FrontCompare.showError(message)
                    this.isSelecting = false
                    $allButtons.prop('disabled', false)
                    return
                }

                FrontCompare.showSuccess(message)

                if (data && data.redirect_url) {
                    window.location.href = data.redirect_url
                } else {
                    window.location.reload()
                }
            },
            error: (xhr) => {
                FrontCompare.handleError(xhr)
                this.isSelecting = false
                $allButtons.prop('disabled', false)
            },
        })
    },

    submitUrlForm($form) {
        const $submit = $form.find('button[type="submit"]')
        const originalLabel = $submit.html()

        $submit.prop('disabled', true).text('…')

        $.ajax({
            url: $form.prop('action'),
            method: 'POST',
            data: $form.serialize(),
            success: ({ error, message, data }) => {
                if (error) {
                    FrontCompare.showError(message)
                    return
                }

                FrontCompare.showSuccess(message)

                if (data && data.redirect_url) {
                    window.location.href = data.redirect_url
                } else {
                    window.location.reload()
                }
            },
            error: (xhr) => FrontCompare.handleError(xhr),
            complete: () => {
                $submit.prop('disabled', false).html(originalLabel)
            },
        })
    },
}

$(() => {
    'use strict'

    FrontComparePicker.init()

    const $table = $('.compare-grid')

    if (! $table.length) {
        return
    }

    FrontCompare.refreshSameRowFlags($table)

    $(document).on('change', '[data-bb-toggle="compare-diff-only"]', function () {
        FrontCompare.refreshSameRowFlags($table)
        $table.toggleClass('compare-diff-only', this.checked)
    })

    $(document).on('click', '[data-bb-toggle="compare-copy-link"]', function () {
        const url = $(this).data('url')

        if (! url) {
            return
        }

        Promise.resolve(FrontCompare.writeClipboard(url)).then(() => {
            FrontCompare.showSuccess($table.data('copy-success-text') || 'Link copied')
        })
    })

    document.addEventListener('ecommerce.compare.added', (e) => {
        FrontCompare.updateCanonicalUrl(e.detail && e.detail.data)
    })

    document.addEventListener('ecommerce.compare.removed', (e) => {
        const data = e.detail && e.detail.data

        // Reload onto the canonical URL so the server re-renders empty slot cells
        // in place of the removed product column. (Otherwise front-ecommerce.js
        // simply rips the <td> out and leaves a gap where the slot should be.)
        if (data && data.redirect_url) {
            window.location.href = data.redirect_url
            return
        }

        window.location.reload()
    })
})
