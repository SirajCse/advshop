import Toastify from '../../../../core/base/resources/js/base/toast'

// Reading `Theme` in its own `const` initializer is a temporal-dead-zone
// error under strict ES modules. Go through window so the expression is
// spec-compliant regardless of whether the bundler transpiles const → var.
window.Theme = window.Theme || {}
const Theme = window.Theme

// Get toast config with defaults
Theme.getToastConfig = function () {
    return window.ThemeToastConfig || {
        position: 'bottom',
        alignment: 'right',
        offsetX: 15,
        offsetY: 15,
        timeout: 5000,
        successIcon: '',
        errorIcon: '',
    }
}

// Get icon - use pre-rendered SVG from config or fallback
Theme.getToastIcon = function (configIcon, fallbackSvg) {
    return configIcon || fallbackSvg
}

Theme.showNotice = function (messageType, message) {
    const config = this.getToastConfig()
    let color = '#fff'
    let icon = ''

    const defaultSuccessIcon =
        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>'
    const defaultErrorIcon =
        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>'

    switch (messageType) {
        case 'success':
            color = '#437a43'
            icon = this.getToastIcon(config.successIcon, defaultSuccessIcon)
            break
        case 'danger':
            color = '#bd362f'
            icon = this.getToastIcon(config.errorIcon, defaultErrorIcon)
            break
        case 'warning':
            color = '#f89406'
            icon =
                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8v4" /><path d="M12 16h.01" /></svg>'
            break
        case 'info':
            color = '#2f96b4'
            icon =
                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>'
            break
    }

    Toastify({
        text: message,
        icon: icon,
        duration: parseInt(config.timeout) || 5000,
        close: true,
        gravity: config.position,
        position: config.alignment,
        offset: {
            x: parseInt(config.offsetX) || 15,
            y: parseInt(config.offsetY) || 15,
        },
        stopOnFocus: true,
        style: {
            background: color,
        },
        escapeMarkup: false,
        className: 'toastify-' + messageType,
    }).showToast()
}

Theme.showError = function (message) {
    this.showNotice('danger', message)
}

Theme.showSuccess = function (message) {
    this.showNotice('success', message)
}

Theme.handleError = (data) => {
    if (typeof data.errors !== 'undefined' && data.errors.length) {
        Theme.handleValidationError(data.errors)
    } else if (typeof data.responseJSON !== 'undefined') {
        if (typeof data.responseJSON.errors !== 'undefined') {
            if (data.status === 422) {
                Theme.handleValidationError(data.responseJSON.errors)
            }
        } else if (typeof data.responseJSON.message !== 'undefined') {
            Theme.showError(data.responseJSON.message)
        } else {
            Theme.showError(data.responseJSON.join(', ').join(', '))
        }
    } else {
        Theme.showError(data.statusText)
    }
}

Theme.handleValidationError = (errors) => {
    let message = ''

    Object.values(errors).forEach((item) => {
        if (message !== '') {
            message += '\n'
        }
        message += item
    })

    Theme.showError(message)
}
