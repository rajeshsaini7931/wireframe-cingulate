/**
 * @file
 * Register Modal behavior with localStorage suppression.
 *
 * Shows modal on page load UNLESS localStorage flag is set.
 * Sets flag when modal is dismissed via ANY method:
 * - Close button (X icon)
 * - Register CTA button
 * - Backdrop click
 * - ESC key press
 *
 * localStorage key: 'adhdEngage_registerModalDismissed'
 * localStorage value: 'true' when dismissed, null when never shown.
 */

(function (Drupal, once) {
  "use strict";

  /**
   * Register modal auto-show with suppression.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes register modal with localStorage check.
   */
  Drupal.behaviors.cingulateRegisterModal = {
    attach(context) {
      once("registerModal", "#registerModal", context).forEach((modalEl) => {
        // ── Step 1: Check localStorage ─────────────────────────────────────
        // If user has previously dismissed the modal, do NOT show it again.
        if (
          localStorage.getItem("adhdEngage_registerModalDismissed") === "true"
        ) {
          return; // Exit early — modal stays hidden.
        }

        // ── Step 2: Initialize Bootstrap Modal ─────────────────────────────
        if (typeof bootstrap === "undefined") {
          console.warn(
            "Bootstrap not loaded — register modal cannot initialize",
          );
          return;
        }

        const modal = new bootstrap.Modal(modalEl, {
          backdrop: true, // Allow backdrop click to close.
          keyboard: true, // Allow ESC key to close.
        });

        // ── Step 3: Show Modal on Page Load ────────────────────────────────
        modal.show();

        // ── Step 4: Listen for Dismissal (ANY method) ──────────────────────
        // Bootstrap's 'hidden.bs.modal' event fires AFTER modal is fully hidden.
        // This captures ALL dismissal methods:
        //   - Close button (X icon) with data-bs-dismiss="modal"
        //   - Backdrop click
        //   - ESC key press
        //   - Programmatic hide()
        modalEl.addEventListener("hidden.bs.modal", () => {
          localStorage.setItem("adhdEngage_registerModalDismissed", "true");
        });

        // ── Step 5: Handle Register CTA Click ──────────────────────────────
        // Set localStorage BEFORE navigation so modal won't show on return.
        const registerCta = modalEl.querySelector(".register-modal__submit");
        if (registerCta) {
          registerCta.addEventListener("click", () => {
            localStorage.setItem("adhdEngage_registerModalDismissed", "true");
            modal.hide(); // Close modal before navigation.
          });
        }
      });
    },
  };
})(Drupal, once);
