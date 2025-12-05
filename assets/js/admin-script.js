/**
 * Custom Type Forge - Admin JavaScript
 *
 * @package CustomTypeForge
 */

(function($) {
    'use strict';

    const CTF = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            console.log('Custom Type Forge initialized');
        },

        /**
         * Show loading state on button
         */
        setButtonLoading: function($button, loading) {
            if (loading) {
                $button.data('original-text', $button.html());
                $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt" style="animation: rotation 1s infinite linear;"></span> loading...');
            } else {
                $button.prop('disabled', false).html($button.data('original-text'));
            }
        },

        /**
         * Show notification
         */
        showNotice: function(message, type) {
            const noticeClass = 'ctf-notice-' + type;
            const $notice = $('<div class="ctf-notice ' + noticeClass + '"><p>' + message + '</p></div>');

            $('.ctf-container').prepend($notice);

            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CTF.init();
    });

    // Make CTF object globally available
    window.CTF = CTF;

})(jQuery);

// CSS animation for loading spinner
const style = document.createElement('style');
style.innerHTML = `
    @keyframes rotation {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
