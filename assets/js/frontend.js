/**
 * Frontend JavaScript for MeinTurnierplan Plugin
 * Handles auto-resizing of tournament table iframes
 */

(function() {
  'use strict';

  // Global object to store current iframe dimensions for admin use
  window.MTP_IframeDimensions = window.MTP_IframeDimensions || {};

  // Configuration
  const config = {
    minWidth: 300,
    minHeight: 150,
    maxWidth: null, // No max width limit
    maxHeight: 9999, // Reasonable max height
    fallbackHeight: 200,
    resizeTimeout: 5000,
    debugMode: false
  };

  /**
   * Validate and constrain dimensions
   */
  function validateDimensions(width, height) {
    const result = {};

    if (width && typeof width === 'number') {
      result.width = Math.max(config.minWidth, width);
      if (config.maxWidth) {
        result.width = Math.min(result.width, config.maxWidth);
      }
    }

    if (height && typeof height === 'number') {
      result.height = Math.max(config.minHeight, height);
      if (config.maxHeight) {
        result.height = Math.min(result.height, config.maxHeight);
      }
    }

    return result;
  }

  /**
   * Resize iframe with validated dimensions
   */
  function resizeIframe(iframe, dimensions) {
    const validated = validateDimensions(dimensions.width, dimensions.height);

    if (validated.width) {
      iframe.style.width = validated.width + 'px';
      iframe.setAttribute('width', validated.width);
    }

    if (validated.height) {
      iframe.style.height = validated.height + 'px';
      iframe.setAttribute('height', validated.height);
    }

    // Store dimensions globally for admin shortcode generation
    window.MTP_IframeDimensions[iframe.id] = {
      width: validated.width || dimensions.width,
      height: validated.height || dimensions.height,
      timestamp: Date.now()
    };

    // Trigger shortcode update if we're in admin and the function exists
    if (typeof window.updateShortcode === 'function') {
      window.updateShortcode();
    }
  }

  /**
   * Handle postMessage events for iframe resizing
   */
  function handlePostMessage(event) {
    // Verify the message is for tournament table or matches sizing
    if (!event.data || (event.data.type !== "iframeSizeMtpTable" && event.data.type !== "iframeSizeMtpMatches")) {
      return;
    }

    // Find all tournament table and matches iframes
    const iframes = document.querySelectorAll('iframe[id^="mtp-table-"], iframe[id^="mtp-matches-"]');

    let resized = false;
    iframes.forEach(function(iframe) {
      // Check if this iframe matches the source of the message
      if (iframe.contentWindow === event.source) {
        // Clear any pending fallback timeout for this iframe
        iframe.removeAttribute('data-mtp-fallback-pending');

        resizeIframe(iframe, {
          width: event.data.width,
          height: event.data.height
        });
        resized = true;
      }
    });
  }

  /**
   * Set up fallback resize behavior
   */
  function setupFallbacks() {
    const iframes = document.querySelectorAll('iframe[id^="mtp-table-"], iframe[id^="mtp-matches-"]');

    iframes.forEach(function(iframe) {
      // Mark iframe as needing fallback
      iframe.setAttribute('data-mtp-fallback-pending', 'true');

      // Set up load event listener
      iframe.addEventListener('load', function() {
        // Don't remove fallback pending flag immediately - wait for postMessage
      });

      // Set up error event listener
      iframe.addEventListener('error', function() {
        iframe.style.height = config.fallbackHeight + 'px';
        iframe.setAttribute('height', config.fallbackHeight);
      });

      // Set a fallback timeout - but allow postMessage to cancel it
      setTimeout(function() {
        if (iframe.getAttribute('data-mtp-fallback-pending') === 'true') {
          iframe.style.height = config.fallbackHeight + 'px';
          iframe.setAttribute('height', config.fallbackHeight);
          iframe.removeAttribute('data-mtp-fallback-pending');
        }
      }, config.resizeTimeout);
    });
  }

  /**
   * Initialize the auto-resize functionality
   */
  function initialize() {
    // Set up postMessage listener
    window.addEventListener("message", handlePostMessage, false);

    // Set up fallbacks when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setupFallbacks);
    } else {
      setupFallbacks();
    }

    // Set up mutation observer to watch for new iframes or changes
    if (window.MutationObserver) {
      const observer = new MutationObserver(function(mutations) {
        let shouldReset = false;
        mutations.forEach(function(mutation) {
          // Check for new iframes or src changes
          if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(function(node) {
              if (node.nodeType === 1 && (node.tagName === 'IFRAME' || node.querySelector('iframe[id^="mtp-table-"], iframe[id^="mtp-matches-"]'))) {
                shouldReset = true;
              }
            });
          } else if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
            if (mutation.target.id && (mutation.target.id.startsWith('mtp-table-') || mutation.target.id.startsWith('mtp-matches-'))) {
              shouldReset = true;
            }
          }
        });

        if (shouldReset) {
          setTimeout(setupFallbacks, 100); // Small delay to ensure DOM is settled
        }
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['src']
      });
    }
  }

  // Initialize when script loads
  initialize();

})();
