import { Helpers } from '../Helpers/Helpers'
import { MessageService } from './MessageService'
import { MediaService } from './MediaService'

export class MoveService {
    constructor() {
        this.selectedFolderId = null
        this.itemsToMove = []
        this.excludeIds = []
        this.expandedFolders = new Set()
        this.draggedFolderId = null

        this.initEvents()
    }

    initEvents() {
        const _self = this

        $(document).on('shown.bs.modal', '#modal_move_items', () => {
            _self.init()
        })

        $(document).on('hidden.bs.modal', '#modal_move_items', () => {
            _self.reset()
        })

        // Toggle folder expand/collapse
        $(document).on('click', '.js-move-folder-toggle', function (e) {
            e.stopPropagation()
            const $item = $(this).closest('.js-move-folder-item')
            const folderId = $item.data('folder-id')
            _self.toggleFolder(folderId)
        })

        // Select folder
        $(document).on('click', '.js-move-folder-item', function (e) {
            if ($(e.target).closest('.js-move-folder-toggle').length) {
                return
            }
            const $item = $(this)
            const folderId = $item.data('folder-id')
            _self.selectFolder($item, folderId)
        })

        $(document).on('change', '#move_to_root', function () {
            if ($(this).is(':checked')) {
                $('#move-folder-list .js-move-folder-item').removeClass('active')
                _self.selectedFolderId = 0
            } else {
                _self.selectedFolderId = null
            }
        })

        $(document).on('click', '#btn-confirm-move', () => {
            _self.confirmMove()
        })

        // Drag and drop events - using drag handle
        $(document).on('dragstart', '.js-move-folder-drag-handle', function (e) {
            const $item = $(this).closest('.js-move-folder-item')
            _self.handleDragStart(e, $item)
        })

        $(document).on('dragend', '.js-move-folder-drag-handle', function (e) {
            const $item = $(this).closest('.js-move-folder-item')
            _self.handleDragEnd(e, $item)
        })

        $(document).on('dragover', '.js-move-folder-item', function (e) {
            _self.handleDragOver(e, $(this))
        })

        $(document).on('dragenter', '.js-move-folder-item', function (e) {
            _self.handleDragEnter(e, $(this))
        })

        $(document).on('dragleave', '.js-move-folder-item', function (e) {
            _self.handleDragLeave(e, $(this))
        })

        $(document).on('drop', '.js-move-folder-item', function (e) {
            _self.handleDrop(e, $(this))
        })
    }

    init() {
        this.itemsToMove = []
        this.excludeIds = []
        this.expandedFolders = new Set()
        this.selectedFolderId = null
        this.draggedFolderId = null

        Helpers.each(Helpers.getSelectedItems(), (value) => {
            this.itemsToMove.push({
                is_folder: value.is_folder,
                id: value.id,
            })

            if (value.is_folder) {
                this.excludeIds.push(value.id)
            }
        })

        $('#move_to_root').prop('checked', false)

        this.loadFolderTree()
    }

    reset() {
        this.selectedFolderId = null
        this.itemsToMove = []
        this.excludeIds = []
        this.expandedFolders = new Set()
        this.draggedFolderId = null
    }

    loadFolderTree() {
        const $list = $('#move-folder-list')

        $list.html(`
            <li class="list-group-item text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </li>
        `)

        $httpClient
            .make()
            .get(RV_MEDIA_URL.folder_tree, {
                exclude_ids: this.excludeIds,
            })
            .then(({ data }) => {
                this.renderTree(data.data.tree)
            })
            .catch(() => {
                $list.html(`
                    <li class="list-group-item text-center text-danger py-4">
                        ${Helpers.trans('message.error_header')}
                    </li>
                `)
            })
    }

    renderTree(tree) {
        const $list = $('#move-folder-list')
        $list.empty()

        if (!tree || tree.length === 0) {
            $list.html(`
                <li class="list-group-item text-center text-muted py-4">
                    ${Helpers.trans('no_item.default.message') || 'No folders available'}
                </li>
            `)
            return
        }

        const html = this.buildTreeHtml(tree, 0)
        $list.html(html)
    }

    buildTreeHtml(folders, level) {
        let html = ''

        folders.forEach((folder) => {
            const isExpanded = this.expandedFolders.has(folder.id)
            const hasChildren = folder.has_children && folder.children && folder.children.length > 0
            const paddingLeft = level * 20

            html += `
                <li class="list-group-item list-group-item-action js-move-folder-item"
                    data-folder-id="${folder.id}"
                    data-folder-name="${folder.name}"
                    data-has-children="${hasChildren}"
                    data-level="${level}">
                    <div class="d-flex align-items-center" style="padding-left: ${paddingLeft}px;">
                        <span class="js-move-folder-drag-handle" draggable="true" title="${Helpers.trans('move.drag_to_move') || 'Drag to move'}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-grip-vertical" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M9 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M9 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M15 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M15 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M15 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </span>
                        <span class="js-move-folder-toggle me-1 ${hasChildren ? '' : 'invisible'}" style="width: 20px; cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler ${isExpanded ? 'icon-tabler-chevron-down' : 'icon-tabler-chevron-right'}" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                ${isExpanded
                                    ? '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6" />'
                                    : '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" />'}
                            </svg>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder me-2" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" />
                        </svg>
                        <span class="folder-name">${folder.name}</span>
                    </div>
                </li>
            `

            if (hasChildren && isExpanded) {
                html += this.buildTreeHtml(folder.children, level + 1)
            }
        })

        return html
    }

    // Drag and drop handlers
    handleDragStart(e, $item) {
        this.draggedFolderId = $item.data('folder-id')
        $item.addClass('dragging')

        // Set drag data
        e.originalEvent.dataTransfer.effectAllowed = 'move'
        e.originalEvent.dataTransfer.setData('text/plain', this.draggedFolderId)
    }

    handleDragEnd(e, $item) {
        $item.removeClass('dragging')
        $('.js-move-folder-item').removeClass('drag-over')
        this.draggedFolderId = null
    }

    handleDragOver(e, $item) {
        e.preventDefault()
        e.originalEvent.dataTransfer.dropEffect = 'move'
    }

    handleDragEnter(e, $item) {
        e.preventDefault()
        const targetFolderId = $item.data('folder-id')

        // Don't allow dropping on itself
        if (targetFolderId !== this.draggedFolderId) {
            $item.addClass('drag-over')
        }
    }

    handleDragLeave(e, $item) {
        // Only remove class if we're actually leaving the element
        const relatedTarget = e.originalEvent.relatedTarget
        if (!$item[0].contains(relatedTarget)) {
            $item.removeClass('drag-over')
        }
    }

    handleDrop(e, $item) {
        e.preventDefault()
        e.stopPropagation()

        const targetFolderId = $item.data('folder-id')
        const targetFolderName = $item.data('folder-name')

        // Remove visual feedback
        $('.js-move-folder-item').removeClass('drag-over dragging')

        // Don't allow dropping on itself
        if (targetFolderId === this.draggedFolderId || !this.draggedFolderId) {
            return
        }

        // Perform the move operation
        this.moveFolder(this.draggedFolderId, targetFolderId, targetFolderName)
    }

    moveFolder(sourceFolderId, destinationFolderId, destinationName) {
        $httpClient
            .make()
            .post(RV_MEDIA_URL.global_actions, {
                action: 'move',
                selected: [{ is_folder: true, id: sourceFolderId }],
                destination: destinationFolderId,
            })
            .then(({ data }) => {
                MessageService.showMessage(
                    'success',
                    data.message || `Moved folder to "${destinationName}"`,
                    Helpers.trans('message.success_header')
                )
                // Refresh the tree
                this.loadFolderTree()
                // Also refresh the main media view
                Helpers.resetPagination()
                new MediaService().getMedia(true)
            })
            .catch(() => {
                MessageService.showMessage(
                    'error',
                    Helpers.trans('move_error') || 'Error moving folder',
                    Helpers.trans('message.error_header')
                )
            })
    }

    toggleFolder(folderId) {
        if (this.expandedFolders.has(folderId)) {
            this.expandedFolders.delete(folderId)
        } else {
            this.expandedFolders.add(folderId)
        }

        // Re-fetch and re-render to update the tree
        this.loadFolderTree()
    }

    selectFolder($item, folderId) {
        $('#move_to_root').prop('checked', false)
        $('#move-folder-list .js-move-folder-item').removeClass('active')
        $item.addClass('active')
        this.selectedFolderId = folderId
    }

    confirmMove() {
        const moveToRoot = $('#move_to_root').is(':checked')
        const destination = moveToRoot ? 0 : this.selectedFolderId

        if (destination === null) {
            MessageService.showMessage(
                'warning',
                Helpers.trans('move.select_destination') || 'Please select a destination folder',
                Helpers.trans('message.error_header')
            )
            return
        }

        const currentFolderId = Helpers.getRequestParams().folder_id || 0
        if (destination === currentFolderId) {
            MessageService.showMessage(
                'warning',
                Helpers.trans('move.same_location') || 'Items are already in this location',
                Helpers.trans('message.error_header')
            )
            return
        }

        $httpClient
            .make()
            .withButtonLoading($('#btn-confirm-move'))
            .post(RV_MEDIA_URL.global_actions, {
                action: 'move',
                selected: this.itemsToMove,
                destination: destination,
            })
            .then(({ data }) => {
                MessageService.showMessage('success', data.message, Helpers.trans('message.success_header'))
                $('#modal_move_items').modal('hide')
                Helpers.resetPagination()
                new MediaService().getMedia(true)
            })
    }
}
