/**
 * @file
 * Register Modal behavior with cookie-based suppression.
 *
 * Shows modal on page load UNLESS cookie is set.
 * Sets cookie with configurable expiration when modal is dismissed via:
 * - Close button (X icon)
 * - Register CTA button
 *
 * Modal CANNOT be dismissed via backdrop click or ESC key (backdrop: static, keyboard: false).
 *
 * Cookie name: 'adhdEngage_registerModalDismissed'
 * Cookie value: 'true' when dismissed
 * Cookie duration: Configurable via drupalSettings (default: 30 days)
 */

(function (Drupal, once) {
  "use strict";

  /**
   * Register modal auto-show with cookie suppression.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes register modal with cookie check.
   */
  Drupal.behaviors.cingulateRegisterModal = {
    attach(context) {
      once("registerModal", "#registerModal", context).forEach((modalEl) => {
        // ── Step 1: Read configuration ─────────────────────────────────────
        const settings = drupalSettings.cingulateBlocks?.registerModal || {};
        const cookieDuration = settings.cookieDuration || 2592000; // 30 days default
        const cookieName = "adhdEngage_registerModalDismissed";

        // ── Step 2: Check cookie ───────────────────────────────────────────
        // If user has previously dismissed the modal, do NOT show it again.
        if (getCookie(cookieName) === "true") {
          return; // Exit early — modal stays hidden.
        }

        // ── Step 3: Initialize Bootstrap Modal ─────────────────────────────
        if (typeof bootstrap === "undefined") {
          console.warn(
            "Bootstrap not loaded — register modal cannot initialize",
          );
          return;
        }

        const modal = new bootstrap.Modal(modalEl, {
          backdrop: "static", // Prevent backdrop click from closing modal.
          keyboard: false, // Prevent ESC key from closing modal.
        });

        // ── Step 4: Show Modal on Page Load ────────────────────────────────
        modal.show();

        // ── Step 5: Handle Close Button Click ──────────────────────────────
        // Set cookie when user explicitly clicks the X close button.
        const closeButton = modalEl.querySelector('[data-bs-dismiss="modal"]');
        if (closeButton) {
          closeButton.addEventListener("click", () => {
            setCookie(cookieName, "true", cookieDuration);
            modal.hide(); // Close modal.
          });
        }

        // ── Step 6: Handle Register CTA Click ──────────────────────────────
        // Set cookie BEFORE navigation so modal won't show on return.
        const registerCta = modalEl.querySelector(".register-modal__submit");
        if (registerCta) {
          registerCta.addEventListener("click", () => {
            setCookie(cookieName, "true", cookieDuration);
            modal.hide(); // Close modal before navigation.
          });
        }
      });
    },
  };

  /**
   * Sets a cookie with expiration.
   *
   * @param {string} name
   *   Cookie name.
   * @param {string} value
   *   Cookie value.
   * @param {number} seconds
   *   Expiration time in seconds.
   */
  function setCookie(name, value, seconds) {
    const expires = new Date(Date.now() + seconds * 1000).toUTCString();
    document.cookie = `${name}=${value}; expires=${expires}; path=/; SameSite=Lax`;
  }

  /**
   * Gets a cookie value by name.
   *
   * @param {string} name
   *   Cookie name.
   *
   * @return {string|null}
   *   Cookie value or null if not found.
   */
  function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (let cookie of cookies) {
      const [key, value] = cookie.split("=");
      if (key === name) {
        return value;
      }
    }
    return null;
  }
})(Drupal, once);
