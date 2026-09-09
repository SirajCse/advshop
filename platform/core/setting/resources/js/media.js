$(() => {
    $('.generate-thumbnails-trigger-button').on('click', (event) => {
        event.preventDefault()

        const currentTarget = $(event.currentTarget)

        const $form = currentTarget.closest('form')

        $httpClient
            .make()
            .withButtonLoading(currentTarget)
            .postForm($form.prop('action'), new FormData($form[0]))
            .then(({ data }) => {
                const $modal = $('#generate-thumbnails-modal')

                $modal.modal('show')
                $modal.data('total-files', data.data.files_count)

                // Reset progress state when opening modal
                resetProgress($modal)

                // Update max value for start offset
                $modal.find('#generate_thumbnails_start_offset').attr('max', data.data.files_count)
            })
    })

    function resetProgress($modal) {
        const $progress = $modal.find('#generate-thumbnails-progress')
        $progress.addClass('d-none')
        $progress.find('#generate-thumbnails-progress-bar').css('width', '0%').removeClass('bg-danger')
        $progress.find('#generate-thumbnails-error-log').addClass('d-none')
        $progress.find('#generate-thumbnails-error-text').text('')
    }

    function updateProgress($modal, offset, total, errorCount) {
        const $progress = $modal.find('#generate-thumbnails-progress')
        $progress.removeClass('d-none')

        const current = Math.min(offset, total)
        const percent = total > 0 ? Math.round((current / total) * 100) : 0

        $progress.find('#generate-thumbnails-progress-text').text(`${current} / ${total}`)
        $progress.find('#generate-thumbnails-progress-percent').text(`${percent}%`)
        $progress.find('#generate-thumbnails-progress-bar').css('width', `${percent}%`)

        // Update offset input so user can see current position and resume from it
        $modal.find('#generate_thumbnails_start_offset').val(current)

        if (errorCount > 0) {
            const $errorLog = $progress.find('#generate-thumbnails-error-log')
            $errorLog.removeClass('d-none')
            $errorLog.find('#generate-thumbnails-error-text').text(
                `${errorCount} file(s) failed to generate thumbnails.`
            )
        }
    }

    $('#generate-thumbnails-button').on('click', (event) => {
        event.preventDefault()

        const currentTarget = $(event.currentTarget)

        const $modal = currentTarget.closest('.modal')
        const $form = currentTarget.closest('form')

        const totalFiles = $modal.data('total-files')
        const overrideExisting = $modal.find('input[name="override_existing"]').is(':checked') ? 1 : 0
        const startOffset = parseInt($modal.find('input[name="start_offset"]').val()) || 0
        let message = null
        let totalErrors = 0

        Botble.showButtonLoading(currentTarget)

        // Disable inputs during processing
        $modal.find('input').prop('disabled', true)

        const batchSize = parseInt($modal.find('input[name="batch_size"]').val()) || $modal.data('chunk-limit')

        function sendRequest(offset = startOffset, limit = batchSize) {
            if (offset >= totalFiles) {
                onComplete()

                return
            }

            updateProgress($modal, offset, totalFiles, totalErrors)

            $httpClient
                .make()
                .post($form.prop('action'), { total: totalFiles, offset, limit, override_existing: overrideExisting })
                .then(({ data }) => {
                    message = data.message

                    if (data.error) {
                        totalErrors++
                    }

                    if (data.data.next && data.data.next < totalFiles) {
                        sendRequest(data.data.next, limit)
                    } else {
                        updateProgress($modal, totalFiles, totalFiles, totalErrors)
                        onComplete()
                    }
                })
                .catch(() => {
                    // On network error/timeout, update offset so user can resume
                    totalErrors++
                    updateProgress($modal, offset, totalFiles, totalErrors)
                    onError(offset)
                })
        }

        function onComplete() {
            Botble.hideButtonLoading(currentTarget)
            $modal.find('input').prop('disabled', false)

            if (totalErrors > 0) {
                Botble.showError(message)
            } else {
                Botble.showSuccess(message)
            }
        }

        function onError(failedOffset) {
            Botble.hideButtonLoading(currentTarget)
            $modal.find('input').prop('disabled', false)
            $modal.find('#generate_thumbnails_start_offset').val(failedOffset)

            const $errorLog = $modal.find('#generate-thumbnails-error-log')
            $errorLog.removeClass('d-none')
            $errorLog.find('#generate-thumbnails-error-text').text(
                `Process stopped at offset ${failedOffset}. You can click "Generate" to resume from this position.`
            )
        }

        sendRequest()
    })

    $(document).on('change', '.check-all', (event) => {
        const currentTarget = $(event.currentTarget)
        const set = currentTarget.attr('data-set')
        const checked = currentTarget.prop('checked')

        $(set).each((index, el) => {
            $(el).prop('checked', checked)
        })
    })
})
