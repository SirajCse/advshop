import { Helpers } from '../Helpers/Helpers'
import { MessageService } from './MessageService'
import { MediaService } from './MediaService'

export class DragDropService {
    constructor() {
        this.draggedItem = null
        this.initEvents()
    }

    initEvents() {
        const _self = this

        // Make items draggable after they are rendered
        $(document).on('click', '.js-media-list-title', function () {
            // Enable dragging on selected items
            setTimeout(() => _self.enableDragging(), 100)
        })

        // Drag start on media items
        $(document).on('dragstart', '.js-media-list-title.js-media-draggable', function (e) {
            _self.handleDragStart(e, $(this))
        })

        $(document).on('dragend', '.js-media-list-title.js-media-draggable', function (e) {
            _self.handleDragEnd(e, $(this))
        })

        // Drop target events - only folders can receive drops
        $(document).on('dragover', '.js-media-list-title[data-context="folder"]', function (e) {
            _self.handleDragOver(e, $(this))
        })

        $(document).on('dragenter', '.js-media-list-title[data-context="folder"]', function (e) {
            _self.handleDragEnter(e, $(this))
        })

        $(document).on('dragleave', '.js-media-list-title[data-context="folder"]', function (e) {
            _self.handleDragLeave(e, $(this))
        })

        $(document).on('drop', '.js-media-list-title[data-context="folder"]', function (e) {
            _self.handleDrop(e, $(this))
        })

        // Enable dragging when media is loaded
        $(document).on('rv_media_loaded', () => {
            _self.enableDragging()
        })
    }

    enableDragging() {
        // Make all media items draggable
        $('.js-media-list-title').each(function () {
            const $item = $(this)
            if (!$item.hasClass('js-media-draggable')) {
                $item.addClass('js-media-draggable')
                $item.attr('draggable', 'true')
            }
        })
    }

    handleDragStart(e, $item) {
        const isFolder = $item.data('context') === 'folder'
        const id = $item.data('id')

        this.draggedItem = {
            id: id,
            is_folder: isFolder,
            name: $item.find('.rv-media-name-item span').text() || $item.find('.rv-media-text-name').text()
        }

        // In grid view, add class to .rv-media-item; in list view, add to li
        const $mediaItem = $item.find('.rv-media-item')
        if ($mediaItem.length) {
            $mediaItem.addClass('rv-media-dragging')
        } else {
            $item.addClass('rv-media-dragging')
        }

        // Set drag data and custom drag image
        if (e.originalEvent && e.originalEvent.dataTransfer) {
            e.originalEvent.dataTransfer.effectAllowed = 'move'
            e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(this.draggedItem))

            // Use .rv-media-item as drag image to avoid white background from li padding
            if ($mediaItem.length) {
                const rect = $mediaItem[0].getBoundingClientRect()
                const offsetX = e.originalEvent.clientX - rect.left
                const offsetY = e.originalEvent.clientY - rect.top
                e.originalEvent.dataTransfer.setDragImage($mediaItem[0], offsetX, offsetY)
            }
        }
    }

    handleDragEnd(e, $item) {
        // Remove classes from both li and .rv-media-item
        $item.removeClass('rv-media-dragging')
        $item.find('.rv-media-item').removeClass('rv-media-dragging')
        $('.js-media-list-title').removeClass('rv-media-drag-over')
        $('.rv-media-item').removeClass('rv-media-drag-over rv-media-dragging')
        this.draggedItem = null
    }

    handleDragOver(e, $item) {
        e.preventDefault()
        if (e.originalEvent && e.originalEvent.dataTransfer) {
            e.originalEvent.dataTransfer.dropEffect = 'move'
        }
    }

    handleDragEnter(e, $item) {
        e.preventDefault()
        const targetId = $item.data('id')

        // Don't allow dropping on itself or if not dragging anything
        if (!this.draggedItem || targetId === this.draggedItem.id) {
            return
        }

        // In grid view, add class to .rv-media-item; in list view, add to li
        const $mediaItem = $item.find('.rv-media-item')
        if ($mediaItem.length) {
            $mediaItem.addClass('rv-media-drag-over')
        } else {
            $item.addClass('rv-media-drag-over')
        }
    }

    handleDragLeave(e, $item) {
        const relatedTarget = e.originalEvent ? e.originalEvent.relatedTarget : null
        if (!$item[0].contains(relatedTarget)) {
            $item.removeClass('rv-media-drag-over')
            $item.find('.rv-media-item').removeClass('rv-media-drag-over')
        }
    }

    handleDrop(e, $item) {
        e.preventDefault()
        e.stopPropagation()

        const targetFolderId = $item.data('id')
        const targetFolderName = $item.find('.rv-media-name-item span').text() || $item.find('.rv-media-text-name').text()

        // Remove visual feedback from both li and .rv-media-item
        $('.js-media-list-title').removeClass('rv-media-drag-over rv-media-dragging')
        $('.rv-media-item').removeClass('rv-media-drag-over rv-media-dragging')

        // Don't allow dropping on itself
        if (!this.draggedItem || targetFolderId === this.draggedItem.id) {
            return
        }

        // Don't allow dropping a folder into itself
        if (this.draggedItem.is_folder && targetFolderId === this.draggedItem.id) {
            MessageService.showMessage(
                'error',
                Helpers.trans('move.cannot_move_to_itself') || 'Cannot move a folder into itself',
                Helpers.trans('message.error_header')
            )
            return
        }

        // Perform the move operation
        this.moveItem(this.draggedItem, targetFolderId, targetFolderName)
    }

    moveItem(item, destinationFolderId, destinationName) {
        Helpers.showAjaxLoading()

        $httpClient
            .make()
            .post(RV_MEDIA_URL.global_actions, {
                action: 'move',
                selected: [{ is_folder: item.is_folder, id: item.id }],
                destination: destinationFolderId,
            })
            .then(({ data }) => {
                MessageService.showMessage(
                    'success',
                    data.message || `Moved "${item.name}" to "${destinationName}"`,
                    Helpers.trans('message.success_header')
                )
                // Refresh the media view
                Helpers.resetPagination()
                new MediaService().getMedia(true)
            })
            .catch(() => {
                MessageService.showMessage(
                    'error',
                    Helpers.trans('move_error') || 'Error moving item',
                    Helpers.trans('message.error_header')
                )
            })
            .finally(() => {
                Helpers.hideAjaxLoading()
            })
    }
}
