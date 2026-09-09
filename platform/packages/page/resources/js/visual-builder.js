import './visual-builder/utils/postmessage.js'
import './visual-builder/utils/inject-edit-icons.js'
import './visual-builder/utils/shortcode-serializer.js'

import './visual-builder/history-manager.js'
import './visual-builder/state.js'

import './visual-builder/context-menu.js'
import './visual-builder/shortcode-list.js'
import './visual-builder/edit-panel.js'
import './visual-builder/add-modal.js'
import './visual-builder/preview-iframe.js'

import './visual-builder/app.js'

$(document).ready(function () {
    if (window.visualBuilderData) {
        VisualBuilderApp.init()
    }
})
