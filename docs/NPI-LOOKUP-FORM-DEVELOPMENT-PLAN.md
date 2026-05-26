# NPI Lookup Form Development Plan

**Date:** May 26, 2026  
**Status:** ✅ COMPLETE — NPI API Integration Live  
**Scope:** NPI Lookup Modal Form with CMS NPI Registry API

---

## ✅ Implementation Complete — All Phases DONE

### Phase 1-5: Bootstrap Modal + Validation + API Integration — COMPLETE

**Files Created/Updated:**

1. **Twig Template** ✅  
   `web/themes/custom/cingulate/templates/webform/npi-lookup-modal.html.twig`
   - Bootstrap 5.3 modal structure
   - 5 fields matching wireframe (firstName optional, lastName required, city optional, state required, zipCode optional)
   - All 50 US states in dropdown
   - Results container for live API data

2. **JavaScript** ✅  
   `web/themes/custom/cingulate/js/npi-lookup-modal.js`
   - jQuery Validate integration (required: lastName, state)
   - **LIVE CMS NPI Registry API Integration**
   - API Endpoint: `https://npiregistry.cms.hhs.gov/api/`
   - Query builder with mandatory fields (lastName + state)
   - Optional fields added when provided (firstName, city, zipCode)
   - ZIP code normalization (removes hyphens)
   - Real-time provider search results display
   - Auto-populates registration form NPI field on selection
   - Loading state with "Searching..." message
   - Empty state handling ("No matching NPI records found")
   - Error handling for API failures
   - Bootstrap modal lifecycle handlers
   - Form reset on modal close

3. **CSS** ✅  
   `web/themes/custom/cingulate/css/components/npi-lookup-modal.css`
   - Matches wireframe design exactly
   - Responsive styles (desktop/tablet/mobile)
   - Error states and validation feedback
   - Results display styling
   - Loading/disabled button states
   - Empty state styling (no results)
   - Error state styling (API failure)
   - Provider location display

4. **Theme Integration** ✅  
   `web/themes/custom/cingulate/cingulate.theme`
   - `hook_theme()` registration for `npi_lookup_modal`
   - `hook_preprocess_page()` injects modal on register page (node 6)

5. **Library Registration** ✅  
   `web/themes/custom/cingulate/cingulate.libraries.yml`
   - `component-npi-modal` library with all dependencies
   - Includes `clientside_validation_jquery/jquery.validate`

**Cache Cleared:** ✅

---

## 🎯 API Integration Details

### API Specification

- **Endpoint:** `https://npiregistry.cms.hhs.gov/api/`
- **Method:** GET
- **Authentication:** None required (public API)
- **Response Format:** JSON

### Query Parameters

| Parameter     | Source Field     | Status       | Handling                      |
| ------------- | ---------------- | ------------ | ----------------------------- |
| `version`     | Static           | Required     | Always `2.1`                  |
| `last_name`   | Last Name field  | **Required** | Always sent                   |
| `state`       | State dropdown   | **Required** | Always sent                   |
| `limit`       | Static           | Required     | Always `10`                   |
| `first_name`  | First Name field | Optional     | Only if provided              |
| `city`        | City field       | Optional     | Only if provided              |
| `postal_code` | Zip Code field   | Optional     | Only if provided (normalized) |

### Response Handling

**Success Response:**

```json
{
  "result_count": 2,
  "results": [
    {
      "number": "1234567890",
      "basic": {
        "first_name": "JOHN",
        "last_name": "SMITH",
        "credential": "MD"
      },
      "taxonomies": [{ "desc": "Internal Medicine" }],
      "addresses": [
        {
          "city": "LOS ANGELES",
          "state": "CA"
        }
      ]
    }
  ]
}
```

**Displayed Fields:**

- Provider name (First + Last + Credential)
- Specialty (from taxonomies[0].desc)
- NPI Number
- Location (City, State)
- "Select This NPI" button

**Empty State:** No results found message  
**Error State:** API failure message

### User Flow

1. User clicks "Look it up here" button → Modal opens
2. User fills **Last Name** (required) and **State** (required)
3. User optionally fills First Name, City, Zip Code
4. User clicks "Submit" → Button shows "Searching..."
5. API call to CMS NPI Registry
6. Results display below submit button
7. User clicks "Select This NPI" on chosen provider
8. NPI auto-fills into main registration form NPI field
9. Modal closes automatically
10. Form resets when modal closes

---

## ✅ What Has Been Implemented

1. ✅ **Bootstrap Modal Template** — `#npiLookupModal` element created
2. ✅ **JavaScript Rewrite** — Bootstrap modal API + jQuery Validate
3. ✅ **CSS Update** — Bootstrap modal classes, matches wireframe
4. ✅ **Client-Side Validation** — jQuery Validate for required fields
5. ✅ **Theme Integration** — `hook_theme()` + `hook_preprocess_page()`
6. ✅ **CMS NPI Registry API** — Live integration with query builder
7. ✅ **Results Display** — Provider list with selection buttons
8. ✅ **Auto-fill** — Selected NPI populates main form field
9. ✅ **Error Handling** — Empty state + API failure handling
10. ✅ **Loading States** — Disabled button with "Searching..." text

---

## Current State Analysis

### ✅ What's Already in Place

1. **Bootstrap 5.3** — Already loaded in global library
   - `js/bootstrap.bundle.min.js`
   - `css/bootstrap.min.css`

2. **NPI Button in Webform Template**

   ```html
   <button
     type="button"
     class="webform-cta-button npi-lookup-button"
     data-bs-toggle="modal"
     data-bs-target="#npiLookupModal"
     aria-haspopup="dialog"
   >
     Look it up here
   </button>
   ```

   - Location: `web/themes/custom/cingulate/templates/webform/webform--register-form.html.twig`
   - Positioned below NPI Number field label
   - Uses Bootstrap modal data attributes

3. **Existing NPI Module** — `cingulate_npi`
   - ⚠️ OLD: Uses Drupal Dialog API (can be deprecated)
   - ⚠️ OLD: Has form class: `NpiLookupForm.php` (incorrect field requirements)
   - ⚠️ OLD: Has JS: `npi-lookup.js` (uses Drupal Dialog)
   - ⚠️ OLD: Has CSS: `npi-lookup.css` (jQuery UI styles)
   - **Status:** No longer needed — new Bootstrap modal replaces this entirely

### ✅ What Has Been Implemented

1. ✅ **Bootstrap Modal Template** — `#npiLookupModal` element created
2. ✅ **JavaScript Rewrite** — Bootstrap modal API + jQuery Validate
3. ✅ **CSS Update** — Bootstrap modal classes, matches wireframe
4. ✅ **Client-Side Validation** — jQuery Validate for form fields
5. ✅ **Theme Integration** — `hook_theme()` + `hook_preprocess_page()`

---

## Development Plan

### **Phase 1: Create Bootstrap Modal Template**

#### File: `web/themes/custom/cingulate/templates/webform/npi-lookup-modal.html.twig`

**Purpose:** Render the NPI lookup modal HTML structure matching the wireframe

**Template Variables Needed:**

- `modal_id`: 'npiLookupModal'
- `modal_title`: 'NPI lookup'
- `modal_description`: 'To find your NPI number, please fill out the information below.'

**Template Structure:**

```twig
{#
/**
 * @file
 * NPI Lookup Modal Template.
 *
 * Bootstrap 5.3 modal for NPI number lookup form.
 *
 * Available variables:
 *   - modal_id: Modal HTML ID (default: npiLookupModal)
 *   - modal_title: Modal heading
 *   - modal_description: Instructional text
 *
 * Wireframe: project/register.html (lines 172-250)
 *
 * @ingroup themeable
 */
#}
{{ attach_library('cingulate/component-npi-modal') }}

<div class="modal fade npi-modal" id="{{ modal_id|default('npiLookupModal') }}"
     tabindex="-1" aria-labelledby="npiLookupTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered npi-modal__dialog">
    <div class="modal-content npi-modal__content">

      {# Close Button #}
      <button type="button" class="btn-close npi-modal__close"
              data-bs-dismiss="modal" aria-label="Close"></button>

      {# Modal Header #}
      <h2 id="npiLookupTitle" class="npi-modal__title">{{ modal_title|default('NPI lookup') }}</h2>
      <p class="npi-modal__description">{{ modal_description|default('To find your NPI number, please fill out the information below.') }}</p>
      <p class="npi-modal__required-note">*Indicates required fields.</p>

      {# NPI Lookup Form #}
      <form class="npi-modal__form" id="npiLookupForm" novalidate>

        {# First Name (optional) #}
        <div class="npi-modal__field">
          <label class="npi-modal__label" for="npiLookupFirstName">First name</label>
          <input id="npiLookupFirstName" name="firstName" type="text"
                 class="npi-modal__input form-control" autocomplete="given-name" />
        </div>

        {# Last Name (required) #}
        <div class="npi-modal__field">
          <label class="npi-modal__label" for="npiLookupLastName">
            Last name<span class="npi-modal__required" aria-hidden="true">*</span>
          </label>
          <input id="npiLookupLastName" name="lastName" type="text"
                 class="npi-modal__input form-control"
                 required autocomplete="family-name"
                 aria-describedby="npiLookupLastNameErr" />
          <span id="npiLookupLastNameErr" class="npi-modal__error" role="alert" aria-live="polite"></span>
        </div>

        {# City (optional) #}
        <div class="npi-modal__field">
          <label class="npi-modal__label" for="npiLookupCity">City</label>
          <input id="npiLookupCity" name="city" type="text"
                 class="npi-modal__input form-control" autocomplete="address-level2" />
        </div>

        {# State (required) #}
        <div class="npi-modal__field">
          <label class="npi-modal__label" for="npiLookupState">
            State<span class="npi-modal__required" aria-hidden="true">*</span>
          </label>
          <select id="npiLookupState" name="state"
                  class="npi-modal__input npi-modal__select form-select"
                  required autocomplete="address-level1"
                  aria-describedby="npiLookupStateErr">
            <option value="">- Select -</option>
            <option value="AL">Alabama</option>
            <option value="AK">Alaska</option>
            <option value="AZ">Arizona</option>
            <option value="AR">Arkansas</option>
            <option value="CA">California</option>
            <option value="CO">Colorado</option>
            <option value="CT">Connecticut</option>
            <option value="DE">Delaware</option>
            <option value="FL">Florida</option>
            <option value="GA">Georgia</option>
            <option value="HI">Hawaii</option>
            <option value="ID">Idaho</option>
            <option value="IL">Illinois</option>
            <option value="IN">Indiana</option>
            <option value="IA">Iowa</option>
            <option value="KS">Kansas</option>
            <option value="KY">Kentucky</option>
            <option value="LA">Louisiana</option>
            <option value="ME">Maine</option>
            <option value="MD">Maryland</option>
            <option value="MA">Massachusetts</option>
            <option value="MI">Michigan</option>
            <option value="MN">Minnesota</option>
            <option value="MS">Mississippi</option>
            <option value="MO">Missouri</option>
            <option value="MT">Montana</option>
            <option value="NE">Nebraska</option>
            <option value="NV">Nevada</option>
            <option value="NH">New Hampshire</option>
            <option value="NJ">New Jersey</option>
            <option value="NM">New Mexico</option>
            <option value="NY">New York</option>
            <option value="NC">North Carolina</option>
            <option value="ND">North Dakota</option>
            <option value="OH">Ohio</option>
            <option value="OK">Oklahoma</option>
            <option value="OR">Oregon</option>
            <option value="PA">Pennsylvania</option>
            <option value="RI">Rhode Island</option>
            <option value="SC">South Carolina</option>
            <option value="SD">South Dakota</option>
            <option value="TN">Tennessee</option>
            <option value="TX">Texas</option>
            <option value="UT">Utah</option>
            <option value="VT">Vermont</option>
            <option value="VA">Virginia</option>
            <option value="WA">Washington</option>
            <option value="WV">West Virginia</option>
            <option value="WI">Wisconsin</option>
            <option value="WY">Wyoming</option>
          </select>
          <span id="npiLookupStateErr" class="npi-modal__error" role="alert" aria-live="polite"></span>
        </div>

        {# Zip Code (optional) #}
        <div class="npi-modal__field">
          <label class="npi-modal__label" for="npiLookupZip">Zip code</label>
          <input id="npiLookupZip" name="zipCode" type="text"
                 class="npi-modal__input form-control"
                 inputmode="numeric" autocomplete="postal-code" />
        </div>

        {# Results Container (placeholder for later API integration) #}
        <div id="npiLookupResults" class="npi-results-container" style="display:none;">
          <h3 class="npi-results__heading">Search Results</h3>
          <div class="npi-results__content"></div>
        </div>

        {# Submit Button #}
        <button type="submit" class="npi-modal__submit">Submit</button>

      </form>
    </div>
  </div>
</div>
```

---

### **Phase 2: Include Modal in Page Template**

#### File: `web/themes/custom/cingulate/templates/page.html.twig`

**Action:** Add modal include at the end of the page (before closing `</body>`)

```twig
{# Include NPI Lookup Modal globally #}
{% include '@cingulate/webform/npi-lookup-modal.html.twig' with {
  'modal_id': 'npiLookupModal',
  'modal_title': 'NPI lookup',
  'modal_description': 'To find your NPI number, please fill out the information below.'
} %}
```

**OR** attach it only on the register page via preprocess:

```php
// cingulate.theme
function cingulate_preprocess_page(&$variables) {
  $route_name = \Drupal::routeMatch()->getRouteName();

  // Add NPI modal on register page
  if ($route_name === 'entity.node.canonical') {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node && $node->bundle() === 'landing_page' && $node->id() == 6) {
      $variables['npi_modal'] = [
        '#theme' => 'npi_lookup_modal',
        '#modal_id' => 'npiLookupModal',
        '#modal_title' => t('NPI lookup'),
        '#modal_description' => t('To find your NPI number, please fill out the information below.'),
      ];
    }
  }
}
```

---

### **Phase 3: Rewrite JavaScript for Bootstrap Modal**

#### File: `web/modules/custom/cingulate_npi/js/npi-lookup.js`

**Current:** Uses Drupal Dialog API  
**New:** Uses Bootstrap 5 modal API

```javascript
/**
 * @file
 * NPI Lookup Bootstrap modal integration.
 */

(function (Drupal, $, once) {
  "use strict";

  /**
   * NPI Lookup modal behavior.
   *
   * Handles form submission, validation, and NPI selection.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.cingulateNpiLookup = {
    attach(context) {
      // Initialize modal instance
      const modalElement = document.getElementById("npiLookupModal");
      if (!modalElement) return;

      const npiModal = new bootstrap.Modal(modalElement, {
        backdrop: true,
        keyboard: true,
        focus: true,
      });

      // Initialize jQuery Validate on the lookup form
      const $form = $("#npiLookupForm", context);
      if ($form.length && !$form.data("validator")) {
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
              required: "Please enter a last name.",
              minlength: "Last name must be at least 2 characters.",
            },
            state: {
              required: "Please select a state.",
            },
          },
          errorClass: "npi-modal__error",
          errorElement: "span",
          errorPlacement: function (error, element) {
            error.insertAfter(element);
          },
          submitHandler: function (form) {
            handleNpiLookupSubmit(form);
          },
        });
      }

      // Modal event listeners
      modalElement.addEventListener("shown.bs.modal", function () {
        // Focus on first field when modal opens
        document.getElementById("npiLookupFirstName").focus();
      });

      modalElement.addEventListener("hidden.bs.modal", function () {
        // Clear form when modal closes
        resetNpiLookupForm();
      });
    },
  };

  /**
   * Handles NPI lookup form submission.
   *
   * @param {HTMLFormElement} form - The lookup form element.
   */
  function handleNpiLookupSubmit(form) {
    const formData = {
      firstName: form.firstName.value || "",
      lastName: form.lastName.value,
      city: form.city.value || "",
      state: form.state.value,
      zipCode: form.zipCode.value || "",
    };

    console.log("NPI Lookup Form Data:", formData);

    // Show placeholder results for now
    // TODO: Replace with actual API call in Phase 2
    displayPlaceholderResults(formData);
  }

  /**
   * Displays placeholder search results.
   *
   * @param {Object} formData - Search form data.
   */
  function displayPlaceholderResults(formData) {
    const resultsContainer = document.getElementById("npiLookupResults");
    const resultsContent = resultsContainer.querySelector(
      ".npi-results__content",
    );

    const placeholderHTML = `
      <div class="npi-results">
        <p class="npi-results__count">Found 3 providers matching "${formData.lastName}"</p>
        <table class="npi-results__table">
          <thead>
            <tr>
              <th>Name</th>
              <th>NPI Number</th>
              <th>City, State</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Dr. ${formData.lastName}, John</td>
              <td>1234567890</td>
              <td>${formData.city || "New York"}, ${formData.state}</td>
              <td>
                <button type="button" class="npi-select-btn" 
                        data-npi="1234567890" 
                        data-name="Dr. ${formData.lastName}, John">
                  Select
                </button>
              </td>
            </tr>
            <tr>
              <td>Dr. ${formData.lastName}, Jane</td>
              <td>0987654321</td>
              <td>${formData.city || "Boston"}, ${formData.state}</td>
              <td>
                <button type="button" class="npi-select-btn" 
                        data-npi="0987654321" 
                        data-name="Dr. ${formData.lastName}, Jane">
                  Select
                </button>
              </td>
            </tr>
            <tr>
              <td>Dr. ${formData.lastName}, Robert</td>
              <td>5555555555</td>
              <td>${formData.city || "Chicago"}, ${formData.state}</td>
              <td>
                <button type="button" class="npi-select-btn" 
                        data-npi="5555555555" 
                        data-name="Dr. ${formData.lastName}, Robert">
                  Select
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    `;

    resultsContent.innerHTML = placeholderHTML;
    resultsContainer.style.display = "block";

    // Attach select button handlers
    attachSelectButtonHandlers();
  }

  /**
   * Attaches click handlers to NPI selection buttons.
   */
  function attachSelectButtonHandlers() {
    const selectButtons = document.querySelectorAll(".npi-select-btn");
    selectButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const npi = this.getAttribute("data-npi");
        const name = this.getAttribute("data-name");
        selectNpiNumber(npi, name);
      });
    });
  }

  /**
   * Fills the NPI field in the main form and closes the modal.
   *
   * @param {string} npi - The selected NPI number.
   * @param {string} name - The provider name.
   */
  function selectNpiNumber(npi, name) {
    // Find the NPI input field in the register form
    const npiInput = document.querySelector('input[name="npi_number"]');

    if (npiInput) {
      npiInput.value = npi;

      // Trigger change event for validation
      npiInput.dispatchEvent(new Event("change", { bubbles: true }));
      npiInput.dispatchEvent(new Event("blur", { bubbles: true }));

      // Remove any validation errors
      const errorLabel = document.querySelector(
        'label[for="edit-npi-number"].error',
      );
      if (errorLabel) {
        errorLabel.remove();
      }
      npiInput.classList.remove("error");

      // Close the modal
      const modalElement = document.getElementById("npiLookupModal");
      const npiModal = bootstrap.Modal.getInstance(modalElement);
      npiModal.hide();

      // Show confirmation message
      showNpiConfirmation(name, npi);
    }
  }

  /**
   * Shows a brief confirmation message after NPI selection.
   *
   * @param {string} name - Provider name.
   * @param {string} npi - NPI number.
   */
  function showNpiConfirmation(name, npi) {
    const npiField = document.querySelector(
      '.reg-form__field:has(input[name="npi_number"])',
    );
    if (!npiField) return;

    // Remove any existing confirmation
    const existing = npiField.querySelector(".npi-confirmation-message");
    if (existing) existing.remove();

    const message = document.createElement("div");
    message.className = "npi-confirmation-message";
    message.textContent = `Selected: ${name} (NPI: ${npi})`;

    npiField.appendChild(message);

    // Auto-remove after 4 seconds
    setTimeout(() => {
      message.style.opacity = "0";
      setTimeout(() => message.remove(), 300);
    }, 4000);
  }

  /**
   * Resets the NPI lookup form to initial state.
   */
  function resetNpiLookupForm() {
    const form = document.getElementById("npiLookupForm");
    if (form) {
      form.reset();

      // Clear validation errors
      $(form).validate().resetForm();

      // Hide results
      const resultsContainer = document.getElementById("npiLookupResults");
      if (resultsContainer) {
        resultsContainer.style.display = "none";
        resultsContainer.querySelector(".npi-results__content").innerHTML = "";
      }
    }
  }
})(Drupal, jQuery, once);
```

---

### **Phase 4: Update CSS for Bootstrap Modal**

#### File: `web/modules/custom/cingulate_npi/css/npi-lookup.css`

**Replace Drupal Dialog styles with Bootstrap modal styles**

```css
/**
 * @file
 * NPI Lookup Bootstrap Modal Styling.
 *
 * Matches wireframe design from project/register.html
 */

/* ── Bootstrap Modal Override ──────────────────────────────────── */
.npi-modal .modal-dialog {
  max-width: 800px;
}

.npi-modal__content {
  padding: 32px;
  border-radius: 8px;
  position: relative;
}

/* ── Close Button ─────────────────────────────────────────────── */
.npi-modal__close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 32px;
  height: 32px;
  opacity: 0.6;
  transition: opacity 0.2s ease;
  background: transparent;
  border: none;
  font-size: 24px;
  line-height: 1;
  color: #333333;
  cursor: pointer;
}

.npi-modal__close:hover {
  opacity: 1;
}

.npi-modal__close:focus {
  outline: 2px solid #525252;
  outline-offset: 2px;
  border-radius: 2px;
}

/* ── Modal Header ─────────────────────────────────────────────── */
.npi-modal__title {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 28px;
  font-weight: 700;
  line-height: 1.25;
  color: #333333;
  margin: 0 0 12px;
}

.npi-modal__description {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 16px;
  font-weight: 400;
  line-height: 1.5;
  color: #666666;
  margin: 0 0 8px;
}

.npi-modal__required-note {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 14px;
  font-weight: 400;
  font-style: italic;
  color: #999999;
  margin: 0 0 24px;
}

/* ── Form Fields ──────────────────────────────────────────────── */
.npi-modal__form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.npi-modal__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.npi-modal__label {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #333333;
  margin: 0;
}

.npi-modal__required {
  color: #c0392b;
  margin-left: 2px;
}

.npi-modal__input,
.npi-modal__select {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid #999999;
  border-radius: 4px;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 16px;
  font-weight: 400;
  line-height: 1.556;
  color: #000000;
  background: #ffffff;
  outline: none;
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}

.npi-modal__input::placeholder {
  color: #999999;
}

.npi-modal__input:focus,
.npi-modal__select:focus {
  border-color: #525252;
  box-shadow: 0 0 0 2px rgba(82, 82, 82, 0.15);
}

.npi-modal__input.error,
.npi-modal__select.error {
  border-color: #c0392b;
}

.npi-modal__error {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  color: #c0392b;
  display: block;
  margin-top: 4px;
}

/* ── Submit Button ────────────────────────────────────────────── */
.npi-modal__submit {
  align-self: flex-start;
  padding: 12px 40px;
  background: #525252;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
  margin-top: 4px;
}

.npi-modal__submit:hover {
  background: #3a3a3a;
}

.npi-modal__submit:active {
  background: #2a2a2a;
}

.npi-modal__submit:focus {
  outline: 2px solid #525252;
  outline-offset: 2px;
}

/* ── Results Container (Placeholder) ─────────────────────────── */
.npi-results-container {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e0e0e0;
}

.npi-results__heading {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #333333;
  margin: 0 0 16px;
}

.npi-results__count {
  font-weight: 700;
  font-size: 15px;
  margin-bottom: 16px;
  color: #333333;
  padding: 12px 16px;
  background: #f5f5f5;
  border-left: 4px solid #525252;
  border-radius: 2px;
}

.npi-results__table {
  width: 100%;
  border-collapse: collapse;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 14px;
}

.npi-results__table thead {
  background: #f5f5f5;
}

.npi-results__table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 700;
  color: #333333;
  border-bottom: 2px solid #e0e0e0;
}

.npi-results__table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  color: #666666;
}

.npi-select-btn {
  padding: 6px 16px;
  background: #525252;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
}

.npi-select-btn:hover {
  background: #3a3a3a;
}

.npi-select-btn:active {
  background: #2a2a2a;
}

/* ── Confirmation Message ────────────────────────────────────── */
.npi-confirmation-message {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #27ae60;
  margin-top: 8px;
  padding: 8px 12px;
  background: #eafaf1;
  border-left: 3px solid #27ae60;
  border-radius: 2px;
  opacity: 1;
  transition: opacity 0.3s ease;
}

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width: 768px) {
  .npi-modal .modal-dialog {
    max-width: 95%;
    margin: 20px auto;
  }

  .npi-modal__content {
    padding: 24px;
  }

  .npi-modal__title {
    font-size: 24px;
  }

  .npi-results__table {
    font-size: 12px;
  }

  .npi-results__table th,
  .npi-results__table td {
    padding: 8px 12px;
  }
}
```

---

### **Phase 5: Update Library Definition**

#### File: `web/modules/custom/cingulate_npi/cingulate_npi.libraries.yml`

**Update to remove Drupal Dialog dependencies**

```yaml
npi-lookup:
  version: VERSION
  js:
    js/npi-lookup.js: {}
  css:
    component:
      css/npi-lookup.css: {}
  dependencies:
    - core/drupal
    - core/jquery
    - core/once
    - core/jquery.validate # For form validation
```

**OR** create a new library for the modal in the theme:

#### File: `web/themes/custom/cingulate/cingulate.libraries.yml`

```yaml
component-npi-modal:
  version: VERSION
  css:
    component:
      css/components/npi-modal.css: {} # Move CSS to theme
  js:
    js/npi-modal.js: {} # Move JS to theme
  dependencies:
    - cingulate/global # Includes Bootstrap
    - core/jquery.validate
    - core/once
```

---

### **Phase 6: Register Theme Hook**

#### File: `web/themes/custom/cingulate/cingulate.theme`

**Add hook_theme() to register the NPI modal template**

```php
<?php

/**
 * @file
 * Theme functions for Cingulate theme.
 */

/**
 * Implements hook_theme().
 */
function cingulate_theme($existing, $type, $theme, $path) {
  return [
    'npi_lookup_modal' => [
      'variables' => [
        'modal_id' => 'npiLookupModal',
        'modal_title' => 'NPI lookup',
        'modal_description' => 'To find your NPI number, please fill out the information below.',
      ],
      'template' => 'npi-lookup-modal',
    ],
  ];
}

/**
 * Implements hook_preprocess_page().
 */
function cingulate_preprocess_page(&$variables) {
  $route_name = \Drupal::routeMatch()->getRouteName();

  // Add NPI modal only on register page
  if ($route_name === 'entity.node.canonical') {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node && $node->bundle() === 'landing_page' && $node->getTitle() === 'Register Web page') {
      $variables['npi_modal'] = [
        '#theme' => 'npi_lookup_modal',
        '#modal_id' => 'npiLookupModal',
        '#modal_title' => t('NPI lookup'),
        '#modal_description' => t('To find your NPI number, please fill out the information below.'),
      ];
    }
  }
}
```

---

### **Phase 7: Update Page Template to Render Modal**

#### File: `web/themes/custom/cingulate/templates/page.html.twig`

**Add at the end of the template (before closing `</div>` or `</body>`)**

```twig
{# NPI Lookup Modal (conditionally rendered) #}
{% if npi_modal %}
  {{ npi_modal }}
{% endif %}
```

---

### **Phase 8: Remove Old Drupal Dialog Implementation**

#### Actions:

1. **Delete or comment out** `NpiLookupForm.php` (not needed for Bootstrap modal)
2. **Delete or comment out** `cingulate_npi.routing.yml` entry for `/npi-lookup/form`
3. **Update** `cingulate_npi.module` to remove `hook_webform_element_alter()` if only used for library attachment

**OR** keep the module but remove the form class and routing — just use it as a library container.

---

## Implementation Checklist

### ✅ Phase 1: Template Creation

- [ ] Create `npi-lookup-modal.html.twig` in theme templates
- [ ] Add all 50 US states in dropdown
- [ ] Include results container placeholder
- [ ] Test template rendering manually

### ✅ Phase 2: Template Integration

- [ ] Register `npi_lookup_modal` in `hook_theme()`
- [ ] Add preprocess logic to attach modal on register page
- [ ] Update `page.html.twig` to render `{{ npi_modal }}`
- [ ] Clear cache and verify modal renders in HTML

### ✅ Phase 3: JavaScript Rewrite

- [ ] Rewrite `npi-lookup.js` with Bootstrap Modal API
- [ ] Add jQuery Validate for form validation
- [ ] Implement `handleNpiLookupSubmit()`
- [ ] Implement `displayPlaceholderResults()`
- [ ] Implement `selectNpiNumber()` auto-fill function
- [ ] Test modal open/close behavior
- [ ] Test form validation (last name, state required)
- [ ] Test NPI selection and auto-fill

### ✅ Phase 4: CSS Update

- [ ] Replace Drupal Dialog CSS with Bootstrap modal CSS
- [ ] Style modal header, close button
- [ ] Style form fields matching wireframe
- [ ] Style results table
- [ ] Add confirmation message styling
- [ ] Test responsive behavior

### ✅ Phase 5: Library Updates

- [ ] Update or replace `cingulate_npi.libraries.yml`
- [ ] OR create `component-npi-modal` in theme libraries
- [ ] Add `core/jquery.validate` dependency
- [ ] Clear cache

### ✅ Phase 6: Cleanup

- [ ] Remove or disable `NpiLookupForm.php`
- [ ] Remove `/npi-lookup/form` route
- [ ] Remove Drupal Dialog references
- [ ] Test modal still works without form class

### ✅ Phase 7: Testing

- [ ] Modal opens when clicking "Look it up here"
- [ ] Form validates correctly (last name, state required)
- [ ] Submit button triggers placeholder results
- [ ] Results display in table
- [ ] "Select" button fills NPI field in main form
- [ ] Modal closes after selection
- [ ] Confirmation message appears
- [ ] Form resets when modal closes
- [ ] Test on mobile/tablet

---

## File Changes Summary

| File                               | Action         | Location                                         |
| ---------------------------------- | -------------- | ------------------------------------------------ |
| `npi-lookup-modal.html.twig`       | CREATE         | `web/themes/custom/cingulate/templates/webform/` |
| `npi-modal.css`                    | CREATE         | `web/themes/custom/cingulate/css/components/`    |
| `npi-modal.js`                     | CREATE         | `web/themes/custom/cingulate/js/`                |
| `cingulate.libraries.yml`          | UPDATE         | Add `component-npi-modal` library                |
| `cingulate.theme`                  | UPDATE         | Add `hook_theme()` and `hook_preprocess_page()`  |
| `page.html.twig`                   | UPDATE         | Add `{{ npi_modal }}` before closing tag         |
| `cingulate_npi/js/npi-lookup.js`   | DELETE/REPLACE | Old Drupal Dialog version                        |
| `cingulate_npi/css/npi-lookup.css` | DELETE/REPLACE | Old Drupal Dialog version                        |
| `NpiLookupForm.php`                | DELETE/DISABLE | Not needed for Bootstrap modal                   |
| `cingulate_npi.routing.yml`        | DELETE/UPDATE  | Remove `/npi-lookup/form` route                  |

---

## Testing Plan

### Manual Testing Steps

1. **Navigate to register page** (`/register`)
2. **Verify button appears** below NPI Number field
3. **Click "Look it up here"** — modal should open
4. **Try submitting empty form** — should show validation errors
5. **Fill in Last Name and State** — submit should work
6. **Verify placeholder results** appear in table
7. **Click "Select" button** — NPI field should auto-fill
8. **Verify modal closes** automatically
9. **Check confirmation message** appears below NPI field
10. **Reopen modal** — form should be reset
11. **Test on mobile** — modal should be responsive

### Browser Testing

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (macOS + iOS)
- [ ] Android Chrome

---

## Future Enhancements (Not in Scope)

1. **API Integration** — Connect to NPI Registry API
2. **Real-time search** — Search as user types
3. **Advanced filters** — Specialty, taxonomy, etc.
4. **Pagination** — For large result sets
5. **Sorting** — Sort by name, city, etc.
6. **Save recent searches** — LocalStorage
7. **Export results** — CSV download

---

## Estimated Development Time

| Phase     | Task                       | Time           |
| --------- | -------------------------- | -------------- |
| 1         | Create modal template      | 30 min         |
| 2         | Integrate template in page | 20 min         |
| 3         | Rewrite JavaScript         | 60 min         |
| 4         | Update CSS                 | 40 min         |
| 5         | Update libraries           | 10 min         |
| 6         | Cleanup old code           | 15 min         |
| 7         | Testing & debugging        | 45 min         |
| **Total** |                            | **~3.5 hours** |

---

## Dependencies

### Required Modules

- ✅ `webform` (already installed)
- ✅ `webform_ui` (already installed)
- ✅ `clientside_validation` (already installed)
- ✅ `clientside_validation_jquery` (already installed)

### Required Libraries

- ✅ Bootstrap 5.3 JS (`bootstrap.bundle.min.js`) — already in theme
- ✅ Bootstrap 5.3 CSS (`bootstrap.min.css`) — already in theme
- ✅ jQuery (via Drupal core)
- ✅ jQuery Validate (via Drupal core)

---

## Notes

1. **Bootstrap vs Drupal Dialog** — We're switching from Drupal Dialog to Bootstrap modal because:
   - Bootstrap is already loaded globally
   - Wireframe uses Bootstrap modal syntax
   - Simpler integration with existing theme
   - Better mobile responsiveness

2. **Placeholder Results** — The `displayPlaceholderResults()` function creates fake data for testing. This will be replaced with real API calls in Phase 2 (API Integration).

3. **Validation** — Using jQuery Validate for consistency with the main register form validation.

4. **Accessibility** — All ARIA attributes from wireframe are preserved (`aria-labelledby`, `aria-describedby`, `role="alert"`, etc.).

5. **State Dropdown** — All 50 US states hardcoded in template (alphabetical order).

---

**Ready to Proceed?** Start with Phase 1 (Template Creation).
