<script>
    /**
     * Visual Builder Iframe-Side Script
     * Handles PostMessage communication from iframe to parent
     */
    (function() {
        'use strict';

        const VisualBuilderIframe = {
            allowedOrigin: window.location.origin,

            init() {
                // Send ready message to parent
                this.sendToParent('ready', {});

                // Listen for messages from parent
                window.addEventListener('message', (event) => {
                    this.handleMessage(event);
                });

                console.log('Visual Builder iframe script initialized');
            },

            handleMessage(event) {
                // Verify origin
                if (event.origin !== this.allowedOrigin) {
                    return;
                }

                // Verify message structure
                if (!event.data || event.data.source !== 'visual-builder') {
                    return;
                }

                const {
                    type,
                    payload
                } = event.data;

                switch (type) {
                    case 'update-shortcode':
                        this.updateShortcode(payload);
                        break;

                    case 'reorder-shortcodes':
                        this.reloadPage();
                        break;

                    case 'highlight-shortcode':
                        this.highlightShortcode(payload);
                        break;

                    case 'reload-preview':
                        this.reloadPage();
                        break;

                    default:
                        console.log('Unknown message type:', type);
                }
            },

            updateShortcode(payload) {
                if (!payload || !payload.id) {
                    return;
                }

                // Find element
                const element = document.querySelector(`[data-shortcode-id="${payload.id}"]`);
                if (!element) {
                    console.warn('Shortcode element not found:', payload.id);
                    return;
                }

                // For simple updates, try to update in place
                // For complex changes, reload the page
                this.reloadPage();
            },

            highlightShortcode(payload) {
                // Remove previous highlights
                document.querySelectorAll('.vb-active').forEach(el => {
                    el.classList.remove('vb-active');
                });

                // Add highlight to target
                if (payload && payload.id) {
                    const element = document.querySelector(`[data-shortcode-id="${payload.id}"]`);
                    if (element) {
                        element.classList.add('vb-active');
                        element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            },

            reloadPage() {
                window.location.reload();
            },

            sendToParent(type, payload) {
                if (window.parent === window) {
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

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                VisualBuilderIframe.init();
            });
        } else {
            VisualBuilderIframe.init();
        }
    })();
</script>
