/**
 * @file
 * NPI Lookup Bootstrap modal integration with CMS NPI Registry API.
 *
 * Handles NPI lookup form validation, API calls, and result selection.
 * API: https://npiregistry.cms.hhs.gov/api/
 */

(function (Drupal, $, once) {
  "use strict";

  /**
   * NPI Lookup modal behavior.
   *
   * Handles form submission, CMS NPI Registry API integration,
   * result display, and NPI selection.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.cingulateNpiLookup = {
    attach(context) {
      // Initialize jQuery Validate on the lookup form.
      const $form = $("#npiLookupForm", context);

      once("npi-lookup-validate", "#npiLookupForm", context).forEach(
        (element) => {
          const $form = $(element);

          // Initialize validation.
          $form.validate({
            rules: {
              lastName: {
                required: true,
                minlength: 2,
              },
              state: {
                required: true,
              },
            },
            messages: {
              lastName: {
                required: "Please enter the last name.",
                minlength: "Last name must be at least 2 characters.",
              },
              state: {
                required: "Please select a state.",
              },
            },
            errorClass: "npi-modal__error is-invalid",
            validClass: "is-valid",
            errorElement: "span",
            errorPlacement: function (error, element) {
              // Place error message after the input/select element.
              error.insertAfter(element);
            },
            highlight: function (element) {
              $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
              $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form) {
              // Get form values.
              const formValues = {
                firstName: $(form).find('[name="firstName"]').val().trim(),
                lastName: $(form).find('[name="lastName"]').val().trim(),
                city: $(form).find('[name="city"]').val().trim(),
                state: $(form).find('[name="state"]').val(),
                zipCode: $(form).find('[name="zipCode"]').val().trim(),
              };

              // Show loading state.
              const $submitBtn = $(form).find('button[type="submit"]');
              const originalBtnText = $submitBtn.text();
              $submitBtn.prop("disabled", true).text("Searching...");

              const $results = $("#npiLookupResults");
              const $resultsContent = $results.find(".npi-results__content");

              // Hide previous results.
              $results.hide();
              $resultsContent.html("");

              // Build API query with mandatory fields only.
              const params = new URLSearchParams({
                version: "2.1",
                last_name: formValues.lastName,
                state: formValues.state,
                limit: "10",
              });

              // Add optional fields if provided.
              if (formValues.firstName) {
                params.append("first_name", formValues.firstName);
              }
              if (formValues.city) {
                params.append("city", formValues.city);
              }
              if (formValues.zipCode) {
                // Normalize ZIP code (remove hyphen).
                const normalizedZip = formValues.zipCode.replace("-", "");
                params.append("postal_code", normalizedZip);
              }

              // Call Drupal NPI API proxy endpoint (avoids CORS issues).
              const apiUrl = `/api/npi-lookup/search?${params.toString()}`;

              fetch(apiUrl)
                .then((response) => {
                  if (!response.ok) {
                    throw new Error("API request failed");
                  }
                  return response.json();
                })
                .then((data) => {
                  // Reset button.
                  $submitBtn.prop("disabled", false).text(originalBtnText);

                  // Check if API returned an error.
                  if (data.error) {
                    $resultsContent.html(`
                      <div class="npi-results__error">
                        <p>${data.message || "Unable to retrieve NPI information."}</p>
                      </div>
                    `);
                    $results.slideDown();
                    return;
                  }

                  // Check if results exist.
                  if (!data.results || data.results.length === 0) {
                    $resultsContent.html(`
                      <div class="npi-results__empty">
                        <p>No matching NPI records found.</p>
                        <p>Please verify the provider information and try again.</p>
                      </div>
                    `);
                    $results.slideDown();
                    return;
                  }

                  // Build results HTML.
                  let resultsHtml = "";

                  data.results.forEach((provider) => {
                    const npiNumber = provider.number || "N/A";
                    const firstName = provider.basic?.first_name || "";
                    const lastName = provider.basic?.last_name || "Unknown";
                    const credential = provider.basic?.credential || "";
                    const specialty =
                      provider.taxonomies?.[0]?.desc || "Not specified";
                    const city = provider.addresses?.[0]?.city || "";
                    const state = provider.addresses?.[0]?.state || "";

                    // Format provider name.
                    let providerName = lastName;
                    if (firstName) {
                      providerName = `${firstName} ${lastName}`;
                    }
                    if (credential) {
                      providerName += `, ${credential}`;
                    }

                    // Format location.
                    let location = "";
                    if (city && state) {
                      location = `${city}, ${state}`;
                    } else if (city) {
                      location = city;
                    } else if (state) {
                      location = state;
                    }

                    resultsHtml += `
                      <div class="npi-result-item">
                        <p class="npi-result__name"><strong>${providerName}</strong></p>
                        <p class="npi-result__specialty">${specialty}</p>
                        <p class="npi-result__npi">NPI: <strong>${npiNumber}</strong></p>
                        ${location ? `<p class="npi-result__location">${location}</p>` : ""}
                        <button type="button" class="npi-result__select-btn" data-npi="${npiNumber}">
                          Select This NPI
                        </button>
                      </div>
                    `;
                  });

                  $resultsContent.html(resultsHtml);
                  $results.slideDown();
                })
                .catch((error) => {
                  // Reset button.
                  $submitBtn.prop("disabled", false).text(originalBtnText);

                  // Show error message.
                  $resultsContent.html(`
                    <div class="npi-results__error">
                      <p>Unable to retrieve NPI information at this time.</p>
                      <p>Please try again later.</p>
                    </div>
                  `);
                  $results.slideDown();

                  console.error("NPI API Error:", error);
                });

              return false; // Prevent default form submission.
            },
          });
        },
      );

      // Handle NPI selection from results.
      once("npi-select", "#npiLookupResults", context).forEach((element) => {
        $(element).on("click", ".npi-result__select-btn", function (e) {
          e.preventDefault();

          const selectedNpi = $(this).data("npi");

          // Populate the main registration form's NPI field.
          const $npiField = $("#edit-npi-number");
          if ($npiField.length) {
            $npiField.val(selectedNpi);

            // Trigger validation on the NPI field if it has clientside validation.
            $npiField.valid();
          }

          // Close the modal.
          const modalElement = document.getElementById("npiLookupModal");
          if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
              modal.hide();
            }
          }

          // Reset the lookup form.
          $("#npiLookupForm")[0].reset();
          $("#npiLookupResults").slideUp();
        });
      });

      // Reset form and hide results when modal is closed.
      once("npi-modal-reset", "#npiLookupModal", context).forEach((element) => {
        $(element).on("hidden.bs.modal", function () {
          $("#npiLookupForm")[0].reset();
          $("#npiLookupResults").hide();
          $("#npiLookupForm").validate().resetForm();
          $(".is-invalid, .is-valid").removeClass("is-invalid is-valid");
        });
      });
    },
  };
})(Drupal, jQuery, once);
