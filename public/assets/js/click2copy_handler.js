/**
 * GENERIC COPY-TO-CLIPBOARD HANDLER
 * Reusable across entire application
 * Place in: public/assets/js/copy-handler.js
 * Called via: <script src="public/assets/js/copy-handler.js"></script> from projects.php
 */

$(document).ready(function () {
    /**
     * Event delegation: Any element with data-copy attribute can be clicked to copy
     * Usage: 
     *   <span data-copy="value" title="Click to copy">value</span>
     *   <code data-copy="12345678">12345678</code>
     *   <div data-copy="some-text" class="copyable">some-text</div>
     */
    $(document).on('click', '[data-copy]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const text = $(this).data('copy');

        // Fallback for empty values
        if (!text) {
            showToast('Error', 'Nothing to copy', 'error');
            return;
        }

        copyToClipboard(text, $(this));
    });

    /**
     * Core copy logic (extracted for reuse)
     * @param {string} text - Text to copy
     * @param {jQuery} element - Element that was clicked (for feedback)
     */
    function copyToClipboard(text, element) {
        // Method 1: Modern Clipboard API (recommended)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    showCopyFeedback(element, text);
                })
                .catch(() => {
                    // Fallback to Method 2 if clipboard fails
                    fallbackCopy(text, element);
                });
        } else {
            // Method 2: Fallback for older browsers
            fallbackCopy(text, element);
        }
    }

    /**
     * Fallback copy method (older browser support)
     */
    function fallbackCopy(text, element) {
        const $temp = $('<textarea>').val(text).css({
            position: 'fixed',
            top: '-9999px',
            left: '-9999px'
        }).appendTo('body');

        $temp.select();

        try {
            document.execCommand('copy');
            showCopyFeedback(element, text);
        } catch (err) {
            showToast('Error', 'Failed to copy', 'error');
        } finally {
            $temp.remove();
        }
    }

    /**
     * Visual feedback after successful copy
     * @param {jQuery} element - Element to highlight
     * @param {string} text - Text that was copied (for toast message)
     */
    function showCopyFeedback(element, text) {
        // 1. Show toast notification
        const displayText = text.length > 30 ? text.substring(0, 30) + '...' : text;
        showToast('Copied', `"${displayText}" copied to clipboard`, 'info', 1000);

        // 2. Visual highlight (optional but nice UX)
        element.addClass('copy-flash');
        setTimeout(() => {
            element.removeClass('copy-flash');
        }, 500);

        // 3. Temporary cursor change (optional)
        const originalTitle = element.attr('title');
        element.attr('title', '✓ Copied!');
        setTimeout(() => {
            element.attr('title', originalTitle || 'Click to copy');
        }, 1000);
    }
});
