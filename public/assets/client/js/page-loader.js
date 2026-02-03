/**
 * Page Loader Script
 * Handles loading screen display and hide
 */

(function() {
    'use strict';
    
    const pageLoader = document.getElementById('page-loader');
    
    if (pageLoader) {
        // Hide loader when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                pageLoader.classList.add('fade-out');
                
                // Remove from DOM after animation completes
                setTimeout(function() {
                    pageLoader.style.display = 'none';
                }, 500);
            }, 300); // Small delay for better UX
        });
        
        // Fallback: Force hide after 5 seconds if something goes wrong
        setTimeout(function() {
            if (!pageLoader.classList.contains('fade-out')) {
                pageLoader.classList.add('fade-out');
                setTimeout(function() {
                    pageLoader.style.display = 'none';
                }, 500);
            }
        }, 5000);
    }
})();
