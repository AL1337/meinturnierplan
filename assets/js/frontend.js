/**
 * Frontend JavaScript for MeinTurnierplan Plugin
 * Handles auto-resizing of tournament table and match list iframes
 */

(function() {
  'use strict';

  // Global object to store current iframe dimensions for admin use
  window.MTRN_IframeDimensions = window.MTRN_IframeDimensions || {};

  // Configuration
  const config = {
    minWidth: 300,
    minHeight: 150,
    maxWidth: null, // No max width limit
    maxHeight: 9999, // Reasonable max height
    fallbackHeight: 200,
    resizeTimeout: 5000
  };

  // Selector matching every embed rendered by the plugin
  const EMBED_SELECTOR = 'iframe[id^="mtrn-table-"], iframe[id^="mtrn-matches-"]';

  function isEmbed(node) {
    return !!node && node.nodeType === 1 && typeof node.matches === 'function' && node.matches(EMBED_SELECTOR);
  }

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
    window.MTRN_IframeDimensions[iframe.id] = {
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
   * Apply the fallback height to an embed that never reported its size
   */
  function applyFallbackHeight(iframe) {
    iframe.style.height = config.fallbackHeight + 'px';
    iframe.setAttribute('height', config.fallbackHeight);
  }

  /**
   * Handle postMessage events for iframe resizing
   */
  function handlePostMessage(event) {
    // Only handle the size messages sent by the tournament table / matches embeds
    if (!event.data || (event.data.type !== "iframeSizeMtpTable" && event.data.type !== "iframeSizeMtpMatches")) {
      return;
    }

    document.querySelectorAll(EMBED_SELECTOR).forEach(function(iframe) {
      if (iframe.contentWindow === event.source) {
        // The embed reported its size, so the fallback is no longer needed
        iframe.removeAttribute('data-mtrn-fallback-pending');

        resizeIframe(iframe, {
          width: event.data.width,
          height: event.data.height
        });
      }
    });
  }

  /**
   * Arm the fallback for a single embed.
   *
   * Runs once per iframe (and again only when its src changes, see
   * rearmFallback). Embeds that already received their size are never
   * re-armed, so iframes added elsewhere on the page later (cookie banners,
   * chat widgets, ads) cannot collapse them to the fallback height.
   */
  function armFallback(iframe) {
    if (iframe.getAttribute('data-mtrn-armed') === 'true') {
      return;
    }

    iframe.setAttribute('data-mtrn-armed', 'true');
    iframe.setAttribute('data-mtrn-fallback-pending', 'true');

    if (!iframe.mtrnErrorHandlerBound) {
      iframe.mtrnErrorHandlerBound = true;
      iframe.addEventListener('error', function() {
        applyFallbackHeight(iframe);
      });
    }

    // Fall back to a fixed height if the embed never reports its size
    setTimeout(function() {
      if (iframe.getAttribute('data-mtrn-fallback-pending') === 'true') {
        applyFallbackHeight(iframe);
        iframe.removeAttribute('data-mtrn-fallback-pending');
      }
    }, config.resizeTimeout);
  }

  /**
   * Re-arm the fallback for an embed whose src changed: it loads a new
   * document and will report its size again.
   */
  function rearmFallback(iframe) {
    iframe.removeAttribute('data-mtrn-armed');
    armFallback(iframe);
  }

  /**
   * Arm the fallback for every embed that is not armed yet
   */
  function setupFallbacks() {
    document.querySelectorAll(EMBED_SELECTOR).forEach(armFallback);
  }

  /**
   * Watch for embeds added later (e.g. the admin preview or AJAX-loaded
   * content) and for embeds whose src changes. Other iframes are ignored.
   */
  function observeEmbeds() {
    if (!window.MutationObserver || !document.body) {
      return;
    }

    const observer = new MutationObserver(function(mutations) {
      let hasNewEmbeds = false;

      mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach(function(node) {
            if (node.nodeType !== 1) {
              return;
            }
            if (isEmbed(node) || (typeof node.querySelector === 'function' && node.querySelector(EMBED_SELECTOR))) {
              hasNewEmbeds = true;
            }
          });
        } else if (mutation.type === 'attributes' && mutation.attributeName === 'src' && isEmbed(mutation.target)) {
          rearmFallback(mutation.target);
        }
      });

      if (hasNewEmbeds) {
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

  /**
   * Initialize the auto-resize functionality
   */
  function initialize() {
    // Set up postMessage listener
    window.addEventListener("message", handlePostMessage, false);

    // Set up fallbacks and the observer when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        setupFallbacks();
        observeEmbeds();
      });
    } else {
      setupFallbacks();
      observeEmbeds();
    }
  }

  // Initialize when script loads
  initialize();

})();
