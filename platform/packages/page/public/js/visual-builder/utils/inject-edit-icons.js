/**
 * Edit Icon Injector
 * Injects edit icons into shortcode blocks in the iframe
 */

const EditIconInjector = {
    /**
     * Inject edit icons into iframe document
     */
    inject(iframeDocument) {
        if (!iframeDocument) {
            console.error('Invalid iframe document');
            return;
        }

        // Find all elements with data-shortcode-id
        const shortcodeElements = iframeDocument.querySelectorAll('[data-shortcode-id]');

        if (shortcodeElements.length === 0) {
            console.warn('No shortcode elements found in preview');
            return;
        }

        // Inject icon into each element
        shortcodeElements.forEach((element) => {
            this.injectIcon(element, iframeDocument);
        });

        console.log(`Injected ${shortcodeElements.length} edit icons`);
    },

    /**
     * Inject single edit icon
     */
    injectIcon(element, iframeDocument) {
        const shortcodeId = element.getAttribute('data-shortcode-id');
        const shortcodeName = element.getAttribute('data-shortcode-name');

        // Check if icon already exists
        if (element.querySelector('.vb-edit-icon')) {
            return;
        }

        // Ensure element has position context
        const position = window.getComputedStyle(element).position;
        if (position === 'static') {
            element.style.position = 'relative';
        }

        // Create edit icon element
        const icon = iframeDocument.createElement('div');
        icon.className = 'vb-edit-icon';
        icon.setAttribute('data-shortcode-id', shortcodeId);
        icon.setAttribute('title', `Edit ${shortcodeName}`);
        icon.innerHTML = '<i class="ti ti-pencil"></i>';

        // Attach click handler
        icon.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.handleIconClick(shortcodeId);
        });

        // Insert icon as first child
        element.insertBefore(icon, element.firstChild);
    },

    /**
     * Handle icon click
     */
    handleIconClick(shortcodeId) {
        // Send message to parent window
        if (typeof PostMessageUtil !== 'undefined') {
            PostMessageUtil.sendToParent('edit-shortcode', { id: shortcodeId });
        }
    },

    /**
     * Remove all edit icons
     */
    removeAll(iframeDocument) {
        if (!iframeDocument) return;

        const icons = iframeDocument.querySelectorAll('.vb-edit-icon');
        icons.forEach(icon => icon.remove());
    },

    /**
     * Highlight shortcode element
     */
    highlight(iframeDocument, shortcodeId) {
        if (!iframeDocument) return;

        // Remove previous highlights
        const previousActive = iframeDocument.querySelectorAll('.vb-active');
        previousActive.forEach(el => el.classList.remove('vb-active'));

        // Add highlight to target element
        if (shortcodeId) {
            const element = iframeDocument.querySelector(`[data-shortcode-id="${shortcodeId}"]`);
            if (element) {
                element.classList.add('vb-active');
                // Scroll into view
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
};

// Export to global scope
window.EditIconInjector = EditIconInjector;
