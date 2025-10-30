<?php

/**
 * Search Focus Component
 * - Ctrl+Q: Focus search field
 * - Esc: Clear search field (when focused)
 * 
 * Usage: <?php require_once '../includes/components/search-focus/search-focus.php'; ?>
 */
?>

<style>
    /* Highlight animation for search field */
    @keyframes searchPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            transform: scale(1);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
            transform: scale(1.02);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            transform: scale(1);
        }
    }

    .search-focus-active {
        animation: searchPulse 0.6s ease-out;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    /* Clear animation */
    @keyframes searchClear {
        0% {
            opacity: 1;
            transform: translateX(0);
        }

        50% {
            opacity: 0.5;
            transform: translateX(-5px);
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .search-clear-active {
        animation: searchClear 0.3s ease-out;
    }

    /* Keyboard hint badge */
    .search-shortcut-hint {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        color: #6c757d;
        font-family: monospace;
        pointer-events: none;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    /* Hide hint when input is focused or has value */
    .form-control:focus+.search-shortcut-hint,
    .form-control:not(:placeholder-shown)+.search-shortcut-hint {
        opacity: 0;
    }

    /* Ensure parent container has position relative for badge positioning */
    .search-wrapper {
        position: relative;
    }
</style>

<script>
    /**
     * Search Focus Enhancement
     * - Ctrl+Q: Focus and select search field
     * - Esc: Clear search field (when focused)
     */
    (function() {
        'use strict';

        // Configuration
        const SHORTCUT_KEY = 'q';
        const SEARCH_SELECTOR = '#projectSearch';

        let searchInput = null;

        // Initialize on DOM ready
        function init() {
            searchInput = document.querySelector(SEARCH_SELECTOR);

            if (!searchInput) {
                console.warn('Search Focus: Search input not found with selector:', SEARCH_SELECTOR);
                return;
            }

            // Add keyboard shortcut hint badge
            addShortcutHint(searchInput);

            // Register keyboard shortcuts
            registerGlobalShortcut(searchInput);
            registerEscapeKey(searchInput);
        }

        // Add visual hint badge next to search input
        function addShortcutHint(searchInput) {
            // Wrap input in relative container if not already wrapped
            if (!searchInput.parentElement.classList.contains('search-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'search-wrapper';
                searchInput.parentNode.insertBefore(wrapper, searchInput);
                wrapper.appendChild(searchInput);
            }

            // Create hint badge
            const hint = document.createElement('span');
            hint.className = 'search-shortcut-hint';
            hint.innerHTML = 'Ctrl+Q';

            searchInput.parentElement.appendChild(hint);
        }

        // Register Ctrl+Q keyboard shortcut (global)
        function registerGlobalShortcut(searchInput) {
            document.addEventListener('keydown', function(e) {
                // Check for Ctrl+Q (or Cmd+Q on Mac)
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === SHORTCUT_KEY) {
                    e.preventDefault();

                    // Focus the search input
                    searchInput.focus();

                    // Select existing text (if any) for easy overwrite
                    searchInput.select();

                    // Add highlight animation
                    searchInput.classList.add('search-focus-active');

                    // Remove animation class after it completes
                    setTimeout(() => {
                        searchInput.classList.remove('search-focus-active');
                    }, 600);
                }
            });
        }

        // Register Escape key to clear search (only when search is focused)
        function registerEscapeKey(searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();

                    // Only clear if there's text
                    if (searchInput.value.trim() !== '') {
                        // Add clear animation
                        searchInput.classList.add('search-clear-active');

                        // Clear the input
                        searchInput.value = '';

                        // Trigger input event to update DataTable (if applicable)
                        const inputEvent = new Event('input', {
                            bubbles: true
                        });
                        searchInput.dispatchEvent(inputEvent);

                        // Also trigger keyup for DataTables search
                        const keyupEvent = new Event('keyup', {
                            bubbles: true
                        });
                        searchInput.dispatchEvent(keyupEvent);

                        // Remove animation after completion
                        setTimeout(() => {
                            searchInput.classList.remove('search-clear-active');
                        }, 300);

                        // Keep focus on search field after clearing
                        searchInput.focus();
                    } else {
                        // If already empty, blur (exit search field)
                        searchInput.blur();
                    }
                }
            });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>