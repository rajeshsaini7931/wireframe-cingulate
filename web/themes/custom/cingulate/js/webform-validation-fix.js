/**
 * @file
 * Fix jQuery Validate error label placement for register_form webform.
 *
 * The clientside_validation_jquery module initializes validation but doesn't
 * always insert error labels correctly on webforms with AJAX enabled.
 * This behavior ensures error labels are properly placed after each field.
 */

(function (Drupal, $, once) {
  "use strict";

  /**
   * Ensure jQuery Validate error labels display on webform fields.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformValidationErrorFix = {
    attach(context) {
      once(
        "webform-validation-fix",
        ".webform-submission-register-form-form",
        context,
      ).forEach((form) => {
        const $form = $(form);

        // Check if jQuery Validate is loaded
        if (typeof $.fn.validate === "undefined") {
          console.warn(
            "jQuery Validate not loaded - cannot fix error placement",
          );
          return;
        }

        // Wait for clientside_validation_jquery to initialize first
        setTimeout(() => {
          const validator = $form.data("validator");

          if (validator) {
            // Configure proper error placement
            validator.settings.errorPlacement = function (error, element) {
              // Insert error label after the input field
              error.insertAfter(element);
            };

            validator.settings.highlight = function (element) {
              $(element).addClass("error").attr("aria-invalid", "true");
            };

            validator.settings.unhighlight = function (element) {
              $(element).removeClass("error").removeAttr("aria-invalid");
            };

            // Only validate on blur (after field is touched) and on submit
            validator.settings.onkeyup = false;
            validator.settings.onfocusout = function (element) {
              // Only validate if the field has been interacted with
              if ($(element).val() !== "" || $(element).hasClass("error")) {
                this.element(element);
              }
            };

            // Prevent AJAX from clearing errors - re-validate on AJAX complete
            $form.on("ajaxComplete", function () {
              if (validator.submitted) {
                setTimeout(() => {
                  validator.form();
                }, 50);
              }
            });

            console.log("✓ Webform validation error placement configured");
          } else {
            console.warn("jQuery Validate not initialized on form");
          }
        }, 100);
      });
    },
  };
})(Drupal, jQuery, once);
