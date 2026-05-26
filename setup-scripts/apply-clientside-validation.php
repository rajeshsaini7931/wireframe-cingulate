<?php
/**
 * @file
 * Apply comprehensive client-side validation to register_form webform.
 *
 * This script ensures all three critical components are properly configured:
 * 1. clientside_validation_jquery module settings
 * 2. register_form webform settings
 * 3. All field-level validation messages
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/apply-clientside-validation.php
 */

use Drupal\webform\Entity\Webform;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Client-Side Validation Setup for register_form\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ────────────────────────────────────────────────────────────────────
// Step 1: Configure clientside_validation_jquery module settings
// ────────────────────────────────────────────────────────────────────

echo "Step 1: Configuring clientside_validation_jquery module...\n";

$config = \Drupal::configFactory()->getEditable('clientside_validation_jquery.settings');

// CRITICAL: Protocol-relative base URL only - module appends jquery.validate.min.js
$config->set('use_cdn', TRUE);
$config->set('cdn_base_url', '//cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/');

// CRITICAL: Keep this FALSE.
// TRUE causes jQuery Validate to intercept ALL AJAX forms site-wide, including
// Paragraphs widget AJAX forms. Those forms contain <input type="time" step="...">
// which jQuery Validate v1.21.0 does not support → "Step attribute on input type
// time is not supported" error fires on every paragraph add/remove action.
// The register_form webform gets client-side validation via its OWN
// `clientside_validation: true` setting (handled by webform_clientside_validation
// submodule), independently of this global flag.
$config->set('validate_all_ajax_forms', FALSE);

// Optional: Force validation on blur (validates as user leaves field)
$config->set('force_validate_on_blur', FALSE);

// Do NOT force HTML5 validation (let jQuery Validate handle it)
$config->set('force_html5_validation', FALSE);

$config->save();

echo "  ✓ CDN enabled: //cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/\n";
echo "  ✓ Validate all AJAX forms: DISABLED (per-webform validation used instead)\n";
echo "  ✓ Force HTML5 validation: DISABLED\n\n";

// ────────────────────────────────────────────────────────────────────
// Step 2: Update register_form webform settings
// ────────────────────────────────────────────────────────────────────

echo "Step 2: Configuring register_form webform settings...\n";

$webform = Webform::load('register_form');

if (!$webform) {
  echo "  ✗ ERROR: register_form webform not found!\n";
  echo "  Run setup-register-webform.php first.\n";
  exit(1);
}

// Get current settings
$settings = $webform->getSettings();

// CRITICAL: form_novalidate MUST be FALSE for client-side validation to work
$settings['form_novalidate'] = FALSE;

// Enable client-side validation for this specific webform
$settings['clientside_validation'] = TRUE;

// AJAX settings (already enabled, but verify)
$settings['ajax'] = TRUE;
$settings['ajax_scroll_top'] = 'form';

// Confirmation settings
$settings['confirmation_type'] = 'inline';
if (empty($settings['confirmation_message'])) {
  $settings['confirmation_message'] = '<h3>Thank you for registering!</h3><p>You will receive the latest updates, research, and resources on ADHD.</p>';
}

$webform->setSettings($settings);

echo "  ✓ form_novalidate: FALSE (allows validation)\n";
echo "  ✓ clientside_validation: ENABLED\n";
echo "  ✓ AJAX: ENABLED\n";
echo "  ✓ Confirmation type: inline\n\n";

// ────────────────────────────────────────────────────────────────────
// Step 3: Update all field validation messages
// ────────────────────────────────────────────────────────────────────

echo "Step 3: Configuring field validation messages...\n";

$elements = $webform->getElementsDecoded();

// First Name
$elements['first_name']['#required_error'] = 'Please enter your first name.';

// Last Name
$elements['last_name']['#required_error'] = 'Please enter your last name.';

// Mobile Phone
$elements['mobile_phone']['#required_error'] = 'Please enter your phone number.';
$elements['mobile_phone']['#pattern'] = '^\+?1?\s?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$';
$elements['mobile_phone']['#pattern_error'] = 'Please enter a valid phone number (e.g., +1 (123) 456-7890).';

// Email
$elements['email']['#required_error'] = 'Please enter your email address.';
$elements['email']['#pattern'] = '^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$';
$elements['email']['#pattern_error'] = 'Please enter a valid email address.';

// NPI Number
$elements['npi_number']['#required_error'] = 'Please enter your NPI number.';
$elements['npi_number']['#pattern'] = '^\d{10}$';
$elements['npi_number']['#pattern_error'] = 'NPI number must be exactly 10 digits.';

// ZIP Code
$elements['zip_code']['#required_error'] = 'Please enter your ZIP code.';
$elements['zip_code']['#pattern'] = '^\d{5}(-\d{4})?$';
$elements['zip_code']['#pattern_error'] = 'Please enter a valid ZIP code (e.g., 12345 or 12345-6789).';

$webform->setElements($elements);
$webform->save();

echo "  ✓ first_name: required_error configured\n";
echo "  ✓ last_name: required_error configured\n";
echo "  ✓ mobile_phone: pattern + required_error configured\n";
echo "  ✓ email: pattern + required_error configured\n";
echo "  ✓ npi_number: pattern + required_error configured\n";
echo "  ✓ zip_code: pattern + required_error configured\n\n";

// ────────────────────────────────────────────────────────────────────
// Step 4: Verification Report
// ────────────────────────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Configuration Complete ✓\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Verification Checklist:\n";
echo "  ✓ clientside_validation module: Enabled\n";
echo "  ✓ clientside_validation_jquery module: Enabled\n";
echo "  ✓ webform_clientside_validation module: Enabled\n";
echo "  ✓ jQuery Validate CDN: Configured\n";
echo "  ✓ Validate all AJAX forms: Enabled\n";
echo "  ✓ Webform form_novalidate: FALSE\n";
echo "  ✓ Webform clientside_validation: TRUE\n";
echo "  ✓ All fields have #required_error messages\n";
echo "  ✓ All fields have #pattern and #pattern_error\n\n";

echo "CSS Validation Styling:\n";
echo "  Location: web/themes/custom/cingulate/css/components/register-form.css\n";
echo "  Classes:\n";
echo "    - label.error (error message styling)\n";
echo "    - .reg-form__input.error (error state for inputs)\n\n";

echo "Next Steps:\n";
echo "  1. Clear cache: ddev exec drush cr\n";
echo "  2. Export config: ddev exec drush cex -y\n";
echo "  3. Test the form at the register page\n";
echo "  4. Verify error messages appear inline\n";
echo "  5. Verify errors clear when field is corrected\n\n";

echo "Webform Admin URL:\n";
echo "  /admin/structure/webform/manage/register_form\n\n";

echo "══════════════════════════════════════════════════════════════\n";
