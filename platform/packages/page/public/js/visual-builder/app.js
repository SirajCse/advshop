/**
 * Visual Builder Main Application
 * jQuery-based initialization and coordination
 */

(function($) {
    'use strict';

    const VisualBuilderApp = {
        config: {},
        modules: {},

        /**
         * Initialize the application
         */
        init() {
            // Load configuration from window
            this.config = window.visualBuilderData || {};

            // Initialize state
            VisualBuilderState.init(this.config.shortcodes);

            // Initialize UI components
            this.initHeader();
            this.initSidebar();
            this.initPreview();
            this.initAutoSave();
            this.initUnsavedWarning();

            console.log('Visual Builder initialized', this.config);
        },

        /**
         * Initialize header controls
         */
        initHeader() {
            const self = this;

            // Save button
            $('#vb-save-btn').on('click', function() {
                self.save();
            });

            // Close button with unsaved changes warning
            $('#vb-close-btn').on('click', function(e) {
                if (VisualBuilderState.hasChanges) {
                    if (!confirm(self.config.translations.unsavedChanges)) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Device mode switcher
            $('#vb-device-modes button').on('click', function() {
                const device = $(this).data('device');
                $('#vb-device-modes button').removeClass('active');
                $(this).addClass('active');
                VisualBuilderState.setDeviceMode(device);
            });

            // Listen to state changes
            VisualBuilderState.on('has-changes', (hasChanges) => {
                if (hasChanges) {
                    $('#vb-unsaved-indicator').show();
                } else {
                    $('#vb-unsaved-indicator').hide();
                }
            });

            VisualBuilderState.on('saving-changed', (isSaving) => {
                const $btn = $('#vb-save-btn');
                if (isSaving) {
                    $btn.prop('disabled', true);
                    $btn.html('<i class="ti ti-loader"></i> ' + self.config.translations.saving);
                } else {
                    $btn.prop('disabled', false);
                    $btn.html('<i class="ti ti-device-floppy"></i> ' + self.config.translations.saved);
                }
            });
        },

        /**
         * Initialize sidebar
         */
        initSidebar() {
            // Load sidebar module
            if (typeof ShortcodeList !== 'undefined') {
                ShortcodeList.init();
            }

            if (typeof ShortcodeEditPanel !== 'undefined') {
                ShortcodeEditPanel.init();
            }

            if (typeof AddShortcodeModal !== 'undefined') {
                AddShortcodeModal.init();
            }

            // Back button
            $('#vb-back-btn, #vb-close-panel-btn').on('click', function() {
                $('#vb-edit-panel').hide();
                $('#vb-list-view').show();
                VisualBuilderState.clearActive();
            });

            // Add shortcode button
            $('#vb-add-shortcode-btn').on('click', function() {
                if (typeof AddShortcodeModal !== 'undefined') {
                    AddShortcodeModal.show();
                }
            });
        },

        /**
         * Initialize preview iframe
         */
        initPreview() {
            if (typeof PreviewIframe !== 'undefined') {
                PreviewIframe.init();
            }

            // Reload button
            $('#vb-reload-preview-btn').on('click', function() {
                if (typeof PreviewIframe !== 'undefined') {
                    PreviewIframe.reload();
                }
            });

            // Listen to device mode changes
            VisualBuilderState.on('device-mode-changed', (mode) => {
                if (typeof PreviewIframe !== 'undefined') {
                    PreviewIframe.setDeviceMode(mode);
                }
            });
        },

        /**
         * Save changes
         */
        save() {
            const self = this;

            VisualBuilderState.setSaving(true);

            // Serialize shortcodes
            let content = '';
            if (typeof ShortcodeSerializer !== 'undefined') {
                content = ShortcodeSerializer.serialize(VisualBuilderState.getAllShortcodes());
            }

            // Send AJAX request
            $.ajax({
                url: this.config.saveUrl,
                method: 'POST',
                data: {
                    _token: this.config.csrfToken,
                    content: content
                },
                success: function(response) {
                    VisualBuilderState.setSaving(false);
                    VisualBuilderState.clearChanges();

                    // Clear draft
                    localStorage.removeItem('vb_draft_' + self.config.pageId);

                    // Show success message
                    if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                        Botble.showSuccess(self.config.translations.saved);
                    } else {
                        alert(self.config.translations.saved);
                    }
                },
                error: function(xhr) {
                    VisualBuilderState.setSaving(false);

                    let errorMsg = self.config.translations.error;
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    if (typeof Botble !== 'undefined' && Botble.showError) {
                        Botble.showError(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        },

        /**
         * Initialize auto-save to localStorage
         */
        initAutoSave() {
            const self = this;
            let autoSaveInterval = null;

            VisualBuilderState.on('has-changes', (hasChanges) => {
                if (hasChanges && !autoSaveInterval) {
                    // Start auto-save every 30 seconds
                    autoSaveInterval = setInterval(() => {
                        self.autoSave();
                    }, 30000);
                } else if (!hasChanges && autoSaveInterval) {
                    clearInterval(autoSaveInterval);
                    autoSaveInterval = null;
                }
            });

            // Check for existing draft on load
            this.checkDraft();
        },

        /**
         * Auto-save to localStorage
         */
        autoSave() {
            const draft = {
                timestamp: Date.now(),
                shortcodes: VisualBuilderState.getAllShortcodes()
            };

            try {
                localStorage.setItem('vb_draft_' + this.config.pageId, JSON.stringify(draft));
                console.log('Draft auto-saved');
            } catch (e) {
                console.error('Failed to auto-save draft', e);
            }
        },

        /**
         * Check for existing draft
         */
        checkDraft() {
            const self = this;
            const draftKey = 'vb_draft_' + this.config.pageId;
            const draftStr = localStorage.getItem(draftKey);

            if (draftStr) {
                try {
                    const draft = JSON.parse(draftStr);
                    const draftDate = new Date(draft.timestamp);

                    if (confirm('Restore unsaved changes from ' + draftDate.toLocaleString() + '?')) {
                        VisualBuilderState.init(draft.shortcodes);
                        VisualBuilderState.markChanged();

                        // Re-render list
                        if (typeof ShortcodeList !== 'undefined') {
                            ShortcodeList.render();
                        }

                        // Reload preview
                        if (typeof PreviewIframe !== 'undefined') {
                            PreviewIframe.reload();
                        }
                    } else {
                        localStorage.removeItem(draftKey);
                    }
                } catch (e) {
                    console.error('Failed to restore draft', e);
                    localStorage.removeItem(draftKey);
                }
            }
        },

        /**
         * Initialize unsaved changes warning
         */
        initUnsavedWarning() {
            window.addEventListener('beforeunload', (e) => {
                if (VisualBuilderState.hasChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        VisualBuilderApp.init();
    });

    // Export for debugging
    window.VisualBuilderApp = VisualBuilderApp;

})(jQuery);
