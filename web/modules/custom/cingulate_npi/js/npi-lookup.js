/**
 * @file
 * NPI Lookup modal and form integration.
 */

(function (Drupal, drupalSettings, $) {
  "use strict";

  /**
   * Initialize NPI lookup link handler.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.cingulateNpiLookup = {
    attach(context) {
      // Open modal when lookup link is clicked
      $(".npi-lookup-button", context)
        .once("npi-lookup-trigger")
        .on("click", function (e) {
          e.preventDefault();
          openNpiLookupModal();
        });

      // Handle NPI selection from results
      $(context).on("click", ".npi-select-btn", function (e) {
        e.preventDefault();
        const npi = $(this).data("npi");
        const name = $(this).data("name");
        selectNpiNumber(npi, name);
      });
    },
  };

  /**
   * Opens the NPI lookup modal dialog.
   */
  function openNpiLookupModal() {
    const dialogOptions = {
      title: "NPI Lookup",
      width: 800,
      modal: true,
      resizable: false,
      dialogClass: "npi-lookup-dialog",
      close: function () {
        // Cleanup on close if needed
      },
    };

    // Create AJAX command to load form into dialog
    const ajaxSettings = {
      url: Drupal.url("npi-lookup/form"),
      dialogType: "modal",
      dialog: dialogOptions,
    };

    const myAjaxObject = Drupal.ajax(ajaxSettings);
    myAjaxObject.execute();
  }

  /**
   * Fills the NPI number field and closes the modal.
   *
   * @param {string} npi - The selected NPI number
   * @param {string} name - The provider name (for confirmation)
   */
  function selectNpiNumber(npi, name) {
    // Find the NPI input field in the register form
    const npiInput = $('input[name="npi_number"]');

    if (npiInput.length) {
      npiInput.val(npi);

      // Trigger change event for any attached validation
      npiInput.trigger("change").trigger("blur");

      // Remove any existing validation errors
      const errorLabel = $('label[for="' + npiInput.attr("id") + '"].error');
      if (errorLabel.length) {
        errorLabel.remove();
      }

      // Remove error class from input
      npiInput.removeClass("error");

      // Close the modal
      $(".npi-lookup-dialog").dialog("close");

      // Show confirmation message
      showNpiConfirmation(name, npi);
    }
  }

  /**
   * Shows a brief confirmation message.
   *
   * @param {string} name - Provider name
   * @param {string} npi - NPI number
   */
  function showNpiConfirmation(name, npi) {
    const npiField = $(
      ".form-type-textfield.form-item-npi-number, .form-item--npi-number",
    );
    if (!npiField.length) return;

    // Remove any existing confirmation message
    npiField.find(".npi-confirmation-message").remove();

    const message = $('<div class="npi-confirmation-message"></div>').text(
      "Selected: " + name + " (NPI: " + npi + ")",
    );

    npiField.append(message);

    // Auto-remove after 4 seconds
    setTimeout(function () {
      message.fadeOut(300, function () {
        $(this).remove();
      });
    }, 4000);
  }
})(Drupal, drupalSettings, jQuery);
