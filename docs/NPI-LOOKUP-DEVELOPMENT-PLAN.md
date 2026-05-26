# NPI Lookup Feature - Development Plan

## Project Overview

**Feature**: NPI (National Provider Identifier) Lookup Integration  
**Target**: Register Form (`register_form` webform)  
**API**: NPPES NPI Registry Public API (https://npiregistry.cms.hhs.gov/api/)  
**User Story**: Healthcare providers can search for their 10-digit NPI number by name, location, or other criteria instead of manually entering it.

---

## Phase 1: Architecture & Requirements Analysis

### 1.1 User Flow

```
User visits /register
  ↓
Sees NPI Number field with "Don't know your NPI? Look it up here" link
  ↓
Clicks lookup link → Opens modal dialog
  ↓
Modal contains search form:
  - First Name (required)
  - Last Name (required)
  - State (optional dropdown)
  - City (optional)
  - Taxonomy Code (optional - provider type)
  - ZIP Code (optional)
  ↓
User enters search criteria and clicks "Search"
  ↓
AJAX request to custom Drupal route → calls NPI Registry API
  ↓
Results display in modal (max 10 results):
  - Provider Name
  - NPI Number
  - Address
  - Taxonomy Description
  - [Select] button for each result
  ↓
User clicks [Select] → NPI number auto-fills into register form field
  ↓
Modal closes
```

### 1.2 Technical Requirements

| Requirement     | Technology         | Notes                                           |
| --------------- | ------------------ | ----------------------------------------------- |
| API Integration | Guzzle HTTP Client | Already available in Drupal core                |
| Modal Dialog    | Core Dialog/Modal  | Use `core/drupal.dialog.ajax` library           |
| AJAX Handling   | Drupal AJAX API    | `#ajax` render element                          |
| Caching         | Cache API          | Cache API responses for 24 hours per search key |
| Rate Limiting   | Custom Service     | Prevent API abuse (max 10 requests/minute/IP)   |
| Error Handling  | Logger Channel     | Log all API failures                            |
| Security        | CSRF Token         | All AJAX routes require token validation        |

### 1.3 API Endpoint Details

**Base URL**: `https://npiregistry.cms.hhs.gov/api/`  
**Version**: `2.1`  
**Endpoint**: `GET /?version=2.1&first_name={fname}&last_name={lname}&state={state}&limit=10`

**Response Format**: JSON  
**Rate Limit**: 10 requests per second (burst), enforced by CMS  
**Authentication**: None required (public API)

**Sample Response**:

```json
{
  "result_count": 1,
  "results": [
    {
      "number": "1234567890",
      "basic": {
        "first_name": "John",
        "last_name": "Doe",
        "credential": "MD"
      },
      "addresses": [
        {
          "address_1": "123 Main St",
          "city": "Springfield",
          "state": "IL",
          "postal_code": "62701"
        }
      ],
      "taxonomies": [
        {
          "code": "207Q00000X",
          "desc": "Family Medicine",
          "primary": true
        }
      ]
    }
  ]
}
```

---

## Phase 2: Custom Module Setup

### 2.1 Module Structure

Create `web/modules/custom/cingulate_npi/` with:

```
cingulate_npi/
├── cingulate_npi.info.yml
├── cingulate_npi.module
├── cingulate_npi.routing.yml
├── cingulate_npi.services.yml
├── cingulate_npi.libraries.yml
├── cingulate_npi.permissions.yml
├── config/
│   └── schema/
│       └── cingulate_npi.schema.yml
├── src/
│   ├── Controller/
│   │   └── NpiLookupController.php
│   ├── Form/
│   │   └── NpiLookupForm.php
│   └── Service/
│       ├── NpiRegistryClient.php
│       └── NpiRateLimiter.php
├── js/
│   └── npi-lookup.js
└── templates/
    └── npi-lookup-modal.html.twig
```

### 2.2 Module Declaration

**File**: `cingulate_npi.info.yml`

```yaml
name: Cingulate NPI Lookup
type: module
description: "Provides NPI Registry lookup functionality for the registration form."
package: Custom
core_version_requirement: ^10 || ^11
dependencies:
  - drupal:webform
```

### 2.3 Routing

**File**: `cingulate_npi.routing.yml`

```yaml
cingulate_npi.lookup_form:
  path: "/npi-lookup/form"
  defaults:
    _form: '\Drupal\cingulate_npi\Form\NpiLookupForm'
    _title: "NPI Lookup"
  requirements:
    _permission: "access content"
  options:
    _admin_route: false

cingulate_npi.search:
  path: "/npi-lookup/search"
  defaults:
    _controller: '\Drupal\cingulate_npi\Controller\NpiLookupController::search'
  requirements:
    _permission: "access content"
    _csrf_token: "TRUE"
  options:
    _admin_route: false
```

### 2.4 Permissions

**File**: `cingulate_npi.permissions.yml`

```yaml
# No custom permissions needed - uses 'access content' which anonymous users have
```

---

## Phase 3: Backend Services

### 3.1 NPI Registry Client Service

**File**: `src/Service/NpiRegistryClient.php`

**Responsibilities**:

- Build API request URL with query parameters
- Execute HTTP GET request via Guzzle
- Parse JSON response
- Handle API errors (timeout, 500, invalid JSON)
- Cache results for 24 hours per unique search key
- Log all API calls and failures

**Key Methods**:

```php
public function search(array $params): array;
private function buildQueryUrl(array $params): string;
private function getCacheKey(array $params): string;
```

**Cache Strategy**:

- Cache bin: `cache.default`
- Cache key pattern: `npi_search:{md5(json_encode(sorted_params))}`
- TTL: 86400 seconds (24 hours)
- Tags: `['npi_search']`
- Invalidation: Manual via admin form or on module uninstall

**Error Handling**:

```php
try {
  $response = $this->httpClient->get($url, ['timeout' => 10]);
} catch (RequestException $e) {
  $this->logger->error('NPI API request failed: @message', [
    '@message' => $e->getMessage(),
  ]);
  return ['error' => 'Unable to connect to NPI Registry. Please try again.'];
}
```

### 3.2 Rate Limiter Service

**File**: `src/Service/NpiRateLimiter.php`

**Responsibilities**:

- Track requests per IP address using Drupal state API
- Enforce limit: 10 requests per 60 seconds per IP
- Clean up expired rate limit entries

**Storage**: `\Drupal::state()->set('npi_ratelimit_{ip}', $count, time() + 60)`

**Key Methods**:

```php
public function checkLimit(string $ip): bool;
public function incrementCount(string $ip): void;
private function cleanExpired(): void;
```

### 3.3 Service Registration

**File**: `cingulate_npi.services.yml`

```yaml
services:
  cingulate_npi.registry_client:
    class: Drupal\cingulate_npi\Service\NpiRegistryClient
    arguments:
      - "@http_client"
      - "@cache.default"
      - "@logger.channel.cingulate_npi"

  cingulate_npi.rate_limiter:
    class: Drupal\cingulate_npi\Service\NpiRateLimiter
    arguments:
      - "@state"
      - "@request_stack"

  logger.channel.cingulate_npi:
    parent: logger.channel_base
    arguments: ["cingulate_npi"]
```

---

## Phase 4: Frontend Components

### 4.1 Modal Form

**File**: `src/Form/NpiLookupForm.php`

**Form Structure**:

```php
$form['first_name'] = [
  '#type' => 'textfield',
  '#title' => $this->t('First Name'),
  '#required' => TRUE,
  '#maxlength' => 50,
];

$form['last_name'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Last Name'),
  '#required' => TRUE,
  '#maxlength' => 50,
];

$form['state'] = [
  '#type' => 'select',
  '#title' => $this->t('State'),
  '#options' => $this->getStateOptions(),
  '#empty_option' => $this->t('- Any -'),
];

$form['city'] = [
  '#type' => 'textfield',
  '#title' => $this->t('City'),
  '#maxlength' => 50,
];

$form['zip_code'] = [
  '#type' => 'textfield',
  '#title' => $this->t('ZIP Code'),
  '#maxlength' => 10,
  '#pattern' => '^\d{5}(-\d{4})?$',
];

$form['actions']['submit'] = [
  '#type' => 'submit',
  '#value' => $this->t('Search'),
  '#ajax' => [
    'callback' => '::ajaxSearchCallback',
    'wrapper' => 'npi-results-wrapper',
    'progress' => [
      'type' => 'throbber',
      'message' => $this->t('Searching...'),
    ],
  ],
];

$form['results'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'npi-results-wrapper'],
];
```

**AJAX Callback**:

```php
public function ajaxSearchCallback(array &$form, FormStateInterface $form_state): array {
  $params = [
    'first_name' => $form_state->getValue('first_name'),
    'last_name' => $form_state->getValue('last_name'),
    'state' => $form_state->getValue('state'),
    'city' => $form_state->getValue('city'),
    'postal_code' => $form_state->getValue('zip_code'),
    'limit' => 10,
  ];

  // Remove empty values
  $params = array_filter($params);

  // Rate limit check
  $ip = $this->requestStack->getCurrentRequest()->getClientIp();
  if (!$this->rateLimiter->checkLimit($ip)) {
    $form['results']['#markup'] = '<div class="npi-error">Too many requests. Please wait 60 seconds.</div>';
    return $form['results'];
  }

  $this->rateLimiter->incrementCount($ip);

  // API call
  $results = $this->npiClient->search($params);

  // Build results table
  $form['results'] = [
    '#theme' => 'npi_lookup_results',
    '#results' => $results,
  ];

  return $form['results'];
}
```

### 4.2 Results Template

**File**: `templates/npi-lookup-results.html.twig`

```twig
{#
/**
 * @file
 * Template for NPI lookup search results.
 *
 * Available variables:
 * - results: Array of NPI search results, each containing:
 *   - number: 10-digit NPI number
 *   - name: Provider full name
 *   - credential: Professional credential (MD, DO, etc.)
 *   - address: Formatted address string
 *   - taxonomy: Primary taxonomy description
 */
#}
{{ attach_library('cingulate_npi/npi-lookup') }}

{% if results.error %}
  <div class="npi-error">{{ results.error }}</div>
{% elseif results.result_count == 0 %}
  <div class="npi-no-results">
    No providers found. Please refine your search criteria.
  </div>
{% else %}
  <div class="npi-results">
    <p class="npi-results__count">
      Found {{ results.result_count }} provider{{ results.result_count > 1 ? 's' : '' }}
      (showing first {{ results.results|length }})
    </p>

    <table class="npi-results__table">
      <thead>
        <tr>
          <th>Provider</th>
          <th>NPI Number</th>
          <th>Location</th>
          <th>Specialty</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {% for result in results.results %}
          <tr class="npi-result-row">
            <td class="npi-result__name">
              {{ result.basic.first_name }} {{ result.basic.last_name }}
              {% if result.basic.credential %}
                , {{ result.basic.credential }}
              {% endif %}
            </td>
            <td class="npi-result__number">{{ result.number }}</td>
            <td class="npi-result__address">
              {% if result.addresses[0] %}
                {{ result.addresses[0].city }}, {{ result.addresses[0].state }}
              {% endif %}
            </td>
            <td class="npi-result__taxonomy">
              {% if result.taxonomies[0] %}
                {{ result.taxonomies[0].desc }}
              {% endif %}
            </td>
            <td class="npi-result__action">
              <button
                type="button"
                class="npi-select-btn"
                data-npi="{{ result.number }}"
                data-name="{{ result.basic.first_name }} {{ result.basic.last_name }}"
              >
                Select
              </button>
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
{% endif %}
```

### 4.3 JavaScript Integration

**File**: `js/npi-lookup.js`

```javascript
/**
 * @file
 * NPI Lookup modal and form integration.
 */

(function (Drupal, once, drupalSettings) {
  "use strict";

  /**
   * Initialize NPI lookup link handler.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.cingulateNpiLookup = {
    attach(context) {
      // Open modal when lookup link is clicked
      once("npi-lookup-trigger", ".npi-lookup-button", context).forEach(
        (button) => {
          button.addEventListener("click", (e) => {
            e.preventDefault();
            openNpiLookupModal();
          });
        },
      );

      // Handle NPI selection from results
      once("npi-select", ".npi-select-btn", context).forEach((selectBtn) => {
        selectBtn.addEventListener("click", (e) => {
          const npi = e.target.dataset.npi;
          const name = e.target.dataset.name;
          selectNpiNumber(npi, name);
        });
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
      close: () => {
        // Cleanup on close if needed
      },
    };

    // Load form via AJAX into dialog
    Drupal.ajax({
      url: "/npi-lookup/form",
      dialogType: "modal",
      dialog: dialogOptions,
    }).execute();
  }

  /**
   * Fills the NPI number field and closes the modal.
   *
   * @param {string} npi - The selected NPI number
   * @param {string} name - The provider name (for confirmation)
   */
  function selectNpiNumber(npi, name) {
    // Find the NPI input field in the register form
    const npiInput = document.querySelector('input[name="npi_number"]');

    if (npiInput) {
      npiInput.value = npi;

      // Trigger change event for any attached validation
      npiInput.dispatchEvent(new Event("change", { bubbles: true }));

      // Remove any existing validation errors
      const errorLabel = document.querySelector(
        'label[for="' + npiInput.id + '"].error',
      );
      if (errorLabel) {
        errorLabel.remove();
      }

      // Close the modal
      const dialog = jQuery(".npi-lookup-dialog").dialog("instance");
      if (dialog) {
        dialog.close();
      }

      // Optional: Show confirmation message
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
    const npiField = document.querySelector(
      ".form-type-textfield.form-item-npi-number",
    );
    if (!npiField) return;

    const message = document.createElement("div");
    message.className = "npi-confirmation-message";
    message.textContent = `Selected: ${name} (NPI: ${npi})`;

    npiField.appendChild(message);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      message.remove();
    }, 3000);
  }
})(Drupal, once, drupalSettings);
```

### 4.4 Asset Library

**File**: `cingulate_npi.libraries.yml`

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
    - core/jquery.once
    - core/drupal.dialog
    - core/drupal.dialog.ajax
```

### 4.5 CSS Styling

**File**: `css/npi-lookup.css`

```css
/**
 * @file
 * NPI Lookup modal and results styling.
 */

/* Modal dialog customization */
.npi-lookup-dialog .ui-dialog-content {
  padding: 20px;
}

.npi-lookup-dialog .ui-dialog-titlebar {
  background: #525252;
  color: #ffffff;
  border: none;
  padding: 12px 20px;
}

/* Search form */
.npi-lookup-form .form-item {
  margin-bottom: 16px;
}

.npi-lookup-form label {
  font-weight: 700;
  font-size: 14px;
  color: #333333;
  margin-bottom: 6px;
  display: block;
}

.npi-lookup-form input[type="text"],
.npi-lookup-form select {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid #999999;
  border-radius: 4px;
  font-size: 16px;
}

.npi-lookup-form .form-actions {
  margin-top: 24px;
  text-align: right;
}

.npi-lookup-form button[type="submit"] {
  padding: 10px 32px;
  background: #525252;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
}

.npi-lookup-form button[type="submit"]:hover {
  background: #3a3a3a;
}

/* Results table */
.npi-results {
  margin-top: 24px;
}

.npi-results__count {
  font-weight: 700;
  margin-bottom: 12px;
  color: #333333;
}

.npi-results__table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
}

.npi-results__table th {
  background: #f5f5f5;
  padding: 12px;
  text-align: left;
  font-weight: 700;
  font-size: 14px;
  color: #333333;
  border-bottom: 2px solid #ddd;
}

.npi-results__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.npi-result-row:hover {
  background: #f9f9f9;
}

.npi-result__number {
  font-family: "Courier New", monospace;
  font-weight: 700;
  color: #525252;
}

.npi-select-btn {
  padding: 6px 16px;
  background: #0ea5e9;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}

.npi-select-btn:hover {
  background: #0284c7;
}

/* Error and no results messages */
.npi-error,
.npi-no-results {
  padding: 16px;
  border-radius: 4px;
  margin-top: 16px;
}

.npi-error {
  background: #fee;
  border: 1px solid #c0392b;
  color: #c0392b;
}

.npi-no-results {
  background: #fef9e7;
  border: 1px solid #f39c12;
  color: #856404;
}

/* Confirmation message */
.npi-confirmation-message {
  margin-top: 8px;
  padding: 8px 12px;
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 4px;
  color: #155724;
  font-size: 13px;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading state */
.ajax-progress-throbber {
  text-align: center;
  padding: 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .npi-lookup-dialog .ui-dialog {
    width: 95% !important;
    margin: 10px;
  }

  .npi-results__table {
    font-size: 12px;
  }

  .npi-results__table th,
  .npi-results__table td {
    padding: 8px;
  }

  /* Stack table on mobile */
  .npi-results__table thead {
    display: none;
  }

  .npi-results__table tr {
    display: block;
    margin-bottom: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 12px;
  }

  .npi-results__table td {
    display: block;
    text-align: left;
    border: none;
    padding: 4px 0;
  }

  .npi-results__table td::before {
    content: attr(data-label) ": ";
    font-weight: 700;
  }
}
```

---

## Phase 5: Webform Integration

### 5.1 Update Webform Element Configuration

**File**: `setup-scripts/add-npi-lookup-button.php`

```php
<?php
/**
 * @file
 * Add NPI lookup button link to the NPI Number field.
 */

use Drupal\webform\Entity\Webform;

$webform = Webform::load('register_form');
if (!$webform) {
  echo "ERROR: register_form not found\n";
  return;
}

$elements = $webform->getElementsDecoded();

// Update NPI number field to include lookup link
$elements['npi_number']['#description'] = [
  '#theme' => 'item_list',
  '#items' => [
    [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => "Don't know your NPI? ",
    ],
    [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#value' => 'Look it up here',
      '#attributes' => [
        'type' => 'button',
        'class' => ['webform-cta-button', 'npi-lookup-button'],
      ],
    ],
  ],
  '#wrapper_attributes' => ['class' => ['reg-form__sub-label']],
];

$webform->setElements($elements);
$webform->save();

echo "✓ Added NPI lookup button to register_form\n";
echo "Run: ddev exec drush cr\n";
```

### 5.2 Attach Library to Webform

**File**: `cingulate_npi.module`

```php
<?php

/**
 * @file
 * Cingulate NPI Lookup module hooks.
 */

/**
 * Implements hook_webform_element_alter().
 */
function cingulate_npi_webform_element_alter(array &$element, \Drupal\Core\Form\FormStateInterface $form_state, array $context): void {
  // Attach NPI lookup library to forms containing npi_number field
  if (isset($context['webform']) && $context['webform']->id() === 'register_form') {
    $element['#attached']['library'][] = 'cingulate_npi/npi-lookup';
  }
}

/**
 * Implements hook_theme().
 */
function cingulate_npi_theme(): array {
  return [
    'npi_lookup_results' => [
      'variables' => [
        'results' => [],
      ],
    ],
  ];
}
```

---

## Phase 6: Testing Plan

### 6.1 Unit Tests

**File**: `tests/src/Unit/NpiRegistryClientTest.php`

Test cases:

- `testBuildQueryUrl()` - Verify URL construction with various parameters
- `testCacheKeyGeneration()` - Ensure consistent cache keys for identical params
- `testApiErrorHandling()` - Mock API failures (timeout, 500 error)
- `testInvalidJsonResponse()` - Handle malformed API responses
- `testEmptyResults()` - Handle zero-result responses

### 6.2 Kernel Tests

**File**: `tests/src/Kernel/NpiLookupIntegrationTest.php`

Test cases:

- `testServiceRegistration()` - Verify services are properly registered
- `testRateLimiter()` - Enforce rate limits correctly
- `testCacheStorage()` - Verify results are cached and retrieved

### 6.3 Functional Tests

**File**: `tests/src/Functional/NpiLookupFormTest.php`

Test cases:

- `testModalOpens()` - Click lookup link, verify modal appears
- `testSearchFormValidation()` - Submit empty form, verify required field errors
- `testSearchExecution()` - Submit valid search, verify AJAX response
- `testNpiSelection()` - Click Select button, verify NPI fills into main form
- `testRateLimitEnforcement()` - Make 11 requests, verify 11th is blocked

### 6.4 Manual Testing Checklist

- [ ] Modal opens when clicking "Look it up here" link
- [ ] Search form requires First Name and Last Name
- [ ] Search returns results for valid provider names
- [ ] "No results" message displays for non-existent providers
- [ ] Error message displays on API timeout/failure
- [ ] Selecting a provider fills NPI number into register form
- [ ] Modal closes after selection
- [ ] Rate limiting blocks excessive requests
- [ ] Works correctly on mobile devices (responsive table)
- [ ] Browser console shows no JavaScript errors
- [ ] AJAX loading indicator displays during search
- [ ] Works with client-side validation (no conflicts)

---

## Phase 7: Configuration & Deployment

### 7.1 Module Installation Script

**File**: `setup-scripts/install-npi-lookup.php`

```php
<?php
/**
 * @file
 * Install and configure the NPI Lookup module.
 */

// Enable the module
\Drupal::service('module_installer')->install(['cingulate_npi']);

echo "✓ Enabled cingulate_npi module\n";

// Update webform to add lookup button
include __DIR__ . '/add-npi-lookup-button.php';

echo "\n── NPI Lookup Installation Complete ──\n";
echo "Module: cingulate_npi\n";
echo "Route: /npi-lookup/form\n";
echo "API: NPPES NPI Registry v2.1\n";
echo "\nRun: ddev exec drush cr && ddev exec drush cex -y\n";
```

### 7.2 Deployment Steps

```bash
# 1. Create module directory structure
mkdir -p web/modules/custom/cingulate_npi/{src/{Controller,Form,Service},js,css,templates,config/schema,tests/src/{Unit,Kernel,Functional}}

# 2. Copy all module files into place
# (Files created per plan above)

# 3. Install and enable
ddev exec drush en cingulate_npi -y

# 4. Configure webform
ddev exec drush php:script /var/www/html/setup-scripts/add-npi-lookup-button.php

# 5. Clear cache and export config
ddev exec drush cr
ddev exec drush cex -y

# 6. Test on /register page
```

### 7.3 Configuration Export

Expected config files:

- `config/sync/core.extension.yml` (updated with cingulate_npi)
- `config/sync/webform.webform.register_form.yml` (updated with lookup button)

---

## Phase 8: Security Considerations

### 8.1 Security Checklist

- [x] **CSRF Protection**: All AJAX routes require `_csrf_token: 'TRUE'`
- [x] **Rate Limiting**: Max 10 requests/60 seconds per IP
- [x] **Input Validation**: All form inputs validated and sanitized
- [x] **Output Escaping**: All API response data escaped in Twig templates
- [x] **API Credentials**: None required (public API)
- [x] **Error Disclosure**: Generic error messages, detailed errors only in logs
- [x] **Permission Checks**: Uses `'access content'` permission (public access)
- [x] **SQL Injection**: No database queries (state API only)
- [x] **XSS Prevention**: Twig auto-escapes all output, no `|raw` filters used

### 8.2 Privacy Considerations

- No personal data stored (searches not logged)
- API responses cached but contain only public provider information
- Rate limiter uses IP address temporarily (60 seconds, then discarded)
- No cookies or tracking

---

## Phase 9: Performance Optimization

### 9.1 Caching Strategy

| Cache Layer      | TTL        | Invalidation                  |
| ---------------- | ---------- | ----------------------------- |
| API Response     | 24 hours   | Manual or on module uninstall |
| Rate Limit State | 60 seconds | Automatic expiration          |

### 9.2 API Optimization

- Single API call per search (no pagination needed - limit 10)
- Guzzle timeout: 10 seconds (fail fast)
- Cache hit ratio target: >70% (common names searched repeatedly)

### 9.3 Frontend Optimization

- Modal content loaded via AJAX (not included in page load)
- JavaScript behaviors use `once()` to prevent double-initialization
- CSS loaded only when modal is triggered (lazy load via library)

---

## Phase 10: Implementation Timeline

| Phase                            | Estimated Time | Dependencies |
| -------------------------------- | -------------- | ------------ |
| **Phase 2**: Module Setup        | 1 hour         | None         |
| **Phase 3**: Backend Services    | 3 hours        | Phase 2      |
| **Phase 4**: Frontend Components | 4 hours        | Phase 3      |
| **Phase 5**: Webform Integration | 1 hour         | Phase 4      |
| **Phase 6**: Testing             | 3 hours        | Phase 5      |
| **Phase 7**: Deployment          | 1 hour         | Phase 6      |
| **Total**                        | **13 hours**   |              |

---

## Phase 11: Success Criteria

### 11.1 Functional Requirements

- ✅ Modal opens from "Look it up here" link
- ✅ Search form validates required fields (First Name, Last Name)
- ✅ API returns results within 10 seconds or shows error
- ✅ Results display in readable table format
- ✅ Selecting a provider auto-fills NPI number
- ✅ Modal closes after selection
- ✅ Works on desktop and mobile devices

### 11.2 Non-Functional Requirements

- ✅ Zero JavaScript console errors
- ✅ WCAG 2.1 AA accessibility compliance
- ✅ All API calls logged
- ✅ Rate limiting prevents abuse
- ✅ Cache hit ratio >70%
- ✅ Zero PHPCS errors/warnings

### 11.3 User Acceptance Criteria

1. **As a healthcare provider**, I can search for my NPI by entering my name
2. **As a user**, I receive clear error messages if no results are found
3. **As a user**, I can refine my search using optional filters (state, city, ZIP)
4. **As a site administrator**, I can monitor NPI lookup usage via logs
5. **As a mobile user**, the modal is usable on small screens

---

## Appendix A: API Documentation References

- **NPPES NPI Registry API**: https://npiregistry.cms.hhs.gov/api-page
- **NPI Overview**: https://www.cms.gov/Regulations-and-Guidance/Administrative-Simplification/NationalProvIdentStand
- **Taxonomy Code List**: https://www.nucc.org/index.php/code-sets-mainmenu-41/provider-taxonomy-mainmenu-40

---

## Appendix B: State Dropdown Options

**Method**: `getStateOptions()` in `NpiLookupForm.php`

```php
private function getStateOptions(): array {
  return [
    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
    'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
    'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
    // ... all 50 states + DC
  ];
}
```

---

## Appendix C: Error Codes & Messages

| Error Code   | User Message                                              | Log Message                                  |
| ------------ | --------------------------------------------------------- | -------------------------------------------- |
| API_TIMEOUT  | "Unable to connect to NPI Registry. Please try again."    | "NPI API request timed out after 10s: {url}" |
| API_ERROR    | "An error occurred. Please try again later."              | "NPI API returned {status}: {body}"          |
| RATE_LIMIT   | "Too many requests. Please wait 60 seconds."              | "Rate limit exceeded for IP: {ip}"           |
| INVALID_JSON | "Invalid response from NPI Registry."                     | "Failed to parse JSON from NPI API: {json}"  |
| NO_RESULTS   | "No providers found. Please refine your search criteria." | N/A (not an error)                           |

---

**END OF DEVELOPMENT PLAN**

---

## Quick Start Commands

```bash
# Create module structure
mkdir -p web/modules/custom/cingulate_npi/{src/{Controller,Form,Service},js,css,templates,config/schema}

# Copy files (implement all files per plan above)

# Install
ddev exec drush en cingulate_npi -y
ddev exec drush php:script /var/www/html/setup-scripts/add-npi-lookup-button.php
ddev exec drush cr
ddev exec drush cex -y

# Test
# Visit: https://wireframe-cingulate.ddev.site/register
# Click "Look it up here" link under NPI Number field
```
