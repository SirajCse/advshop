const PostMessageUtil = {
    allowedOrigin: window.location.origin,

    sendToIframe(iframe, type, payload) {
        if (!iframe || !iframe.contentWindow) {
            return
        }

        const message = {
            source: 'visual-builder',
            type: type,
            payload: payload || {},
        }

        try {
            iframe.contentWindow.postMessage(message, this.allowedOrigin)
        } catch (e) {}
    },

    listenToIframe(callback) {
        window.addEventListener('message', (event) => {
            if (event.origin !== this.allowedOrigin) {
                return
            }

            if (!event.data || event.data.source !== 'visual-builder-preview') {
                return
            }

            if (typeof callback === 'function') {
                callback(event.data.type, event.data.payload)
            }
        })
    },

    sendToParent(type, payload) {
        if (window.parent === window) {
            return
        }

        const message = {
            source: 'visual-builder-preview',
            type: type,
            payload: payload || {},
        }

        try {
            window.parent.postMessage(message, this.allowedOrigin)
        } catch (e) {}
    },
}

window.PostMessageUtil = PostMessageUtil
