/**
 * Preview Iframe Module
 * Manages the preview iframe and communication
 */

const PreviewIframe = {
    $iframe: null,
    $container: null,
    $loading: null,
    $error: null,
    $preview: null,
    iframe: null,
    isReady: false,
    deviceMode: 'desktop',

    /**
     * Initialize the module
     */
    init() {
        this.$iframe = $('#vb-preview-iframe');
        this.$container = $('#vb-preview-frame-container');
        this.$loading = $('#vb-preview-loading');
        this.$error = $('#vb-preview-error');
        this.$preview = $('#vb-preview');
        this.iframe = this.$iframe[0];

        // Listen for iframe load
        this.$iframe.on('load', () => {
            this.handleLoad();
        });

        // Listen for iframe errors
        this.$iframe.on('error', () => {
            this.showError('Failed to load preview');
        });

        // Listen for PostMessage from iframe
        if (typeof PostMessageUtil !== 'undefined') {
            PostMessageUtil.listenToIframe((type, payload) => {
                this.handleMessage(type, payload);
            });
        }

        // Set initial device mode
        this.setDeviceMode('desktop');
    },

    /**
     * Handle iframe load event
     */
    handleLoad() {
        this.isReady = true;
        this.hideLoading();
        this.$preview.addClass('loaded');

        // Inject edit icons after a short delay to ensure DOM is ready
        setTimeout(() => {
            this.injectEditIcons();
        }, 500);

        console.log('Preview loaded successfully');
    },

    /**
     * Handle PostMessage from iframe
     */
    handleMessage(type, payload) {
        console.log('Received message from iframe:', type, payload);

        switch (type) {
            case 'ready':
                this.handleLoad();
                break;

            case 'edit-shortcode':
                if (payload && payload.id) {
                    this.handleEditRequest(payload.id);
                }
                break;

            default:
                console.log('Unknown message type:', type);
        }
    },

    /**
     * Handle edit request from iframe
     */
    handleEditRequest(shortcodeId) {
        // Open edit panel
        if (typeof ShortcodeList !== 'undefined') {
            ShortcodeList.openEditPanel(shortcodeId);
        }
    },

    /**
     * Inject edit icons into iframe
     */
    injectEditIcons() {
        if (!this.iframe || !this.iframe.contentDocument) {
            console.error('Iframe document not accessible');
            return;
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;

            // Inject edit icons
            if (typeof EditIconInjector !== 'undefined') {
                EditIconInjector.inject(iframeDoc);
            }

            // Inject CSS for edit icons
            this.injectEditIconStyles(iframeDoc);
        } catch (e) {
            console.error('Failed to inject edit icons:', e);
        }
    },

    /**
     * Inject CSS for edit icons into iframe
     */
    injectEditIconStyles(iframeDoc) {
        // Check if styles already injected
        if (iframeDoc.getElementById('vb-edit-icon-styles')) {
            return;
        }

        const style = iframeDoc.createElement('style');
        style.id = 'vb-edit-icon-styles';
        style.textContent = `
            .vb-edit-icon {
                position: absolute;
                top: 15px;
                right: 15px;
                z-index: 9999;
                width: 30px;
                height: 30px;
                background: #0ea5e9;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                opacity: 0;
                transition: all 0.2s;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }
            .vb-edit-icon:hover {
                background: #0284c7;
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }
            .vb-edit-icon i {
                color: #fff;
                font-size: 14px;
            }
            [data-shortcode-id] {
                position: relative;
            }
            [data-shortcode-id]:hover .vb-edit-icon {
                opacity: 1;
            }
            [data-shortcode-id].vb-active {
                outline: 2px solid #0ea5e9;
                outline-offset: 2px;
            }
            [data-shortcode-id].vb-active::before {
                content: '';
                position: absolute;
                top: -2px;
                left: -2px;
                right: -2px;
                bottom: -2px;
                background: rgba(14, 165, 233, 0.05);
                pointer-events: none;
                z-index: -1;
            }
            [data-shortcode-id].vb-active .vb-edit-icon {
                opacity: 1;
            }
        `;

        iframeDoc.head.appendChild(style);
    },

    /**
     * Reload iframe
     */
    reload() {
        this.isReady = false;
        this.showLoading();
        this.$preview.removeClass('loaded error');

        // Force reload
        const currentSrc = this.$iframe.attr('src');
        this.$iframe.attr('src', currentSrc + (currentSrc.includes('?') ? '&' : '?') + '_=' + Date.now());
    },

    /**
     * Set device mode
     */
    setDeviceMode(mode) {
        this.deviceMode = mode;

        // Remove all device classes
        this.$container.removeClass('device-desktop device-tablet device-mobile');

        // Add current device class
        this.$container.addClass('device-' + mode);

        console.log('Device mode set to:', mode);
    },

    /**
     * Highlight shortcode in preview
     */
    highlightShortcode(shortcodeId) {
        if (!this.iframe || !this.iframe.contentDocument) {
            return;
        }

        try {
            const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;

            if (typeof EditIconInjector !== 'undefined') {
                EditIconInjector.highlight(iframeDoc, shortcodeId);
            }
        } catch (e) {
            console.error('Failed to highlight shortcode:', e);
        }
    },

    /**
     * Update shortcode in preview (live update)
     */
    updateShortcode(shortcodeId, data) {
        // For now, just reload the entire preview
        // Live update implementation would require more complex logic
        this.reload();
    },

    /**
     * Show loading state
     */
    showLoading() {
        this.$loading.show();
        this.$error.hide();
        this.$container.hide();
    },

    /**
     * Hide loading state
     */
    hideLoading() {
        this.$loading.hide();
        this.$container.show();
    },

    /**
     * Show error state
     */
    showError(message) {
        this.isReady = false;
        this.$loading.hide();
        this.$container.hide();
        this.$error.show();
        this.$preview.addClass('error');

        if (message) {
            $('#vb-error-message').text(message);
        }
    }
};

// Export to global scope
window.PreviewIframe = PreviewIframe;
