/**
 * PostMessage Utility
 * Handles secure communication between parent window and iframe
 */

const PostMessageUtil = {
    allowedOrigin: window.location.origin,

    /**
     * Send message to iframe
     */
    sendToIframe(iframe, type, payload) {
        if (!iframe || !iframe.contentWindow) {
            console.error('Invalid iframe reference');
            return;
        }

        const message = {
            source: 'visual-builder',
            type: type,
            payload: payload || {}
        };

        try {
            iframe.contentWindow.postMessage(message, this.allowedOrigin);
        } catch (e) {
            console.error('Failed to send message to iframe:', e);
        }
    },

    /**
     * Listen for messages from iframe
     */
    listenToIframe(callback) {
        window.addEventListener('message', (event) => {
            // Verify origin
            if (event.origin !== this.allowedOrigin) {
                console.warn('Message from unauthorized origin:', event.origin);
                return;
            }

            // Verify message structure
            if (!event.data || event.data.source !== 'visual-builder-preview') {
                return;
            }

            // Call callback with message data
            if (typeof callback === 'function') {
                callback(event.data.type, event.data.payload);
            }
        });
    },

    /**
     * Send message to parent (for use in iframe)
     */
    sendToParent(type, payload) {
        if (window.parent === window) {
            console.warn('Not in iframe context');
            return;
        }

        const message = {
            source: 'visual-builder-preview',
            type: type,
            payload: payload || {}
        };

        try {
            window.parent.postMessage(message, this.allowedOrigin);
        } catch (e) {
            console.error('Failed to send message to parent:', e);
        }
    }
};

// Export to global scope
window.PostMessageUtil = PostMessageUtil;
