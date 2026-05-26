<?php
/**
 * @file
 * Verify client-side validation configuration for register_form.
 *
 * This script checks all critical settings and reports any issues.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/verify-clientside-validation.php
 */

use Drupal\webform\Entity\Webform;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Client-Side Validation Verification Report\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$errors = [];
$warnings = [];
$passes = [];

// ────────────────────────────────────────────────────────────────────
// Check 1: Required modules
// ────────────────────────────────────────────────────────────────────

echo "── Module Status ──────────────────────────────────────────────\n";

$required_modules = [
  'webform' => 'Webform',
  'webform_ui' => 'Webform UI',
  'clientside_validation' => 'Clientside Validation',
  'clientside_validation_jquery' => 'Clientside Validation jQuery',
  'webform_clientside_validation' => 'Webform Clientside Validation',
];

$moduleHandler = \Drupal::moduleHandler();

foreach ($required_modules as $module => $label) {
  if ($moduleHandler->moduleExists($module)) {
    echo "  ✓ {$label}: ENABLED\n";
    $passes[] = "{$label} module enabled";
  } else {
    echo "  ✗ {$label}: MISSING\n";
    $errors[] = "{$label} module is not enabled";
  }
}
echo "\n";

// ────────────────────────────────────────────────────────────────────
// Check 2: jQuery Validate configuration
// ────────────────────────────────────────────────────────────────────

echo "── jQuery Validate Configuration ─────────────────────────────\n";

$config = \Drupal::config('clientside_validation_jquery.settings');

// Check CDN settings
$use_cdn = $config->get('use_cdn');
$cdn_base_url = $config->get('cdn_base_url');
$validate_all_ajax = $config->get('validate_all_ajax_forms');

if ($use_cdn === TRUE) {
  echo "  ✓ CDN enabled: TRUE\n";
  $passes[] = "CDN enabled";
} else {
  echo "  ✗ CDN enabled: FALSE\n";
  $errors[] = "CDN is not enabled";
}

if ($cdn_base_url === '//cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/') {
  echo "  ✓ CDN URL: {$cdn_base_url}\n";
  $passes[] = "CDN URL correct";
} else {
  echo "  ⚠ CDN URL: {$cdn_base_url}\n";
  echo "    Expected: //cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/\n";
  $warnings[] = "CDN URL may be incorrect";
}

if ($validate_all_ajax === TRUE || $validate_all_ajax === 1) {
  echo "  ✓ Validate all AJAX forms: ENABLED\n";
  $passes[] = "AJAX validation enabled";
} else {
  echo "  ⚠ Validate all AJAX forms: DISABLED\n";
  echo "    This may prevent validation on AJAX-enabled webforms\n";
  $warnings[] = "AJAX validation is disabled";
}

echo "\n";

// ────────────────────────────────────────────────────────────────────
// Check 3: Webform settings
// ────────────────────────────────────────────────────────────────────

echo "── Webform Settings ───────────────────────────────────────────\n";

$webform = Webform::load('register_form');

if (!$webform) {
  echo "  ✗ register_form webform: NOT FOUND\n";
  $errors[] = "register_form webform does not exist";
  echo "\n";
  echo "CRITICAL ERROR: Cannot continue verification without webform.\n";
  echo "Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-register-webform.php\n\n";
  exit(1);
}

echo "  ✓ Webform exists: register_form\n";
$passes[] = "Webform exists";

$settings = $webform->getSettings();

// Check form_novalidate
if (isset($settings['form_novalidate']) && $settings['form_novalidate'] === FALSE) {
  echo "  ✓ form_novalidate: FALSE (allows validation)\n";
  $passes[] = "form_novalidate is FALSE";
} else {
  echo "  ✗ form_novalidate: TRUE or not set\n";
  echo "    Validation will not work if this is TRUE\n";
  $errors[] = "form_novalidate is not FALSE";
}

// Check clientside_validation
if (isset($settings['clientside_validation']) && $settings['clientside_validation'] === TRUE) {
  echo "  ✓ clientside_validation: ENABLED\n";
  $passes[] = "clientside_validation enabled";
} else {
  echo "  ✗ clientside_validation: DISABLED or not set\n";
  $errors[] = "clientside_validation is not enabled";
}

// Check AJAX
if (isset($settings['ajax']) && $settings['ajax'] === TRUE) {
  echo "  ✓ AJAX submission: ENABLED\n";
  $passes[] = "AJAX enabled";
} else {
  echo "  ⚠ AJAX submission: DISABLED\n";
  $warnings[] = "AJAX submission is disabled";
}

// Check confirmation type
$confirmation_type = $settings['confirmation_type'] ?? 'none';
echo "  ✓ Confirmation type: {$confirmation_type}\n";
if ($confirmation_type === 'inline') {
  $passes[] = "Inline confirmation configured";
}

echo "\n";

// ────────────────────────────────────────────────────────────────────
// Check 4: Field validation messages
// ────────────────────────────────────────────────────────────────────

echo "── Field Validation Messages ──────────────────────────────────\n";

$elements = $webform->getElementsDecoded();

$required_fields = [
  'first_name' => 'First Name',
  'last_name' => 'Last Name',
  'mobile_phone' => 'Mobile Phone',
  'email' => 'Email',
  'npi_number' => 'NPI Number',
  'zip_code' => 'ZIP Code',
];

foreach ($required_fields as $field_key => $field_label) {
  if (!isset($elements[$field_key])) {
    echo "  ✗ {$field_label}: FIELD MISSING\n";
    $errors[] = "{$field_label} field does not exist";
    continue;
  }

  $field = $elements[$field_key];
  $has_required_error = !empty($field['#required_error']);
  $has_pattern = !empty($field['#pattern']);
  $has_pattern_error = !empty($field['#pattern_error']);

  if ($has_required_error) {
    echo "  ✓ {$field_label}: #required_error configured\n";
    $passes[] = "{$field_label} has required_error";
  } else {
    echo "  ✗ {$field_label}: #required_error MISSING\n";
    $errors[] = "{$field_label} missing required_error";
  }

  // Check pattern fields (not all fields need patterns)
  if (in_array($field_key, ['mobile_phone', 'email', 'npi_number', 'zip_code'])) {
    if ($has_pattern && $has_pattern_error) {
      echo "    ✓ Pattern validation configured\n";
      $passes[] = "{$field_label} has pattern validation";
    } else {
      echo "    ✗ Pattern validation MISSING\n";
      $errors[] = "{$field_label} missing pattern validation";
    }
  }
}

echo "\n";

// ────────────────────────────────────────────────────────────────────
// Check 5: Theme files
// ────────────────────────────────────────────────────────────────────

echo "── Theme Files ────────────────────────────────────────────────\n";

$theme_path = DRUPAL_ROOT . '/../web/themes/custom/cingulate';

// Check template
$template_file = $theme_path . '/templates/webform/webform--register1-form.html.twig';
if (file_exists($template_file)) {
  echo "  ✓ Webform template: EXISTS\n";
  echo "    {$template_file}\n";
  $passes[] = "Webform template exists";
} else {
  echo "  ⚠ Webform template: NOT FOUND\n";
  echo "    {$template_file}\n";
  $warnings[] = "Webform template not found";
}

// Check CSS
$css_file = $theme_path . '/css/components/register-form.css';
if (file_exists($css_file)) {
  echo "  ✓ CSS file: EXISTS\n";
  echo "    {$css_file}\n";
  
  // Check for validation styles
  $css_content = file_get_contents($css_file);
  if (strpos($css_content, 'label.error') !== FALSE) {
    echo "    ✓ Contains label.error styles\n";
    $passes[] = "CSS contains validation styles";
  } else {
    echo "    ✗ Missing label.error styles\n";
    $errors[] = "CSS missing validation styles";
  }
} else {
  echo "  ⚠ CSS file: NOT FOUND\n";
  echo "    {$css_file}\n";
  $warnings[] = "CSS file not found";
}

echo "\n";

// ────────────────────────────────────────────────────────────────────
// Summary
// ────────────────────────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Verification Summary\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Passed checks: " . count($passes) . "\n";
echo "Warnings: " . count($warnings) . "\n";
echo "Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
  echo "── ERRORS (must be fixed) ─────────────────────────────────────\n";
  foreach ($errors as $error) {
    echo "  ✗ {$error}\n";
  }
  echo "\n";
}

if (count($warnings) > 0) {
  echo "── WARNINGS (should be reviewed) ──────────────────────────────\n";
  foreach ($warnings as $warning) {
    echo "  ⚠ {$warning}\n";
  }
  echo "\n";
}

if (count($errors) === 0 && count($warnings) === 0) {
  echo "✓ ALL CHECKS PASSED\n\n";
  echo "Client-side validation is properly configured!\n\n";
  echo "Next steps:\n";
  echo "  1. Visit the register page in a browser\n";
  echo "  2. Try submitting without filling fields\n";
  echo "  3. Verify error messages appear\n";
  echo "  4. Try entering invalid data\n";
  echo "  5. Verify pattern errors appear\n";
  echo "  6. Submit valid data\n";
  echo "  7. Verify confirmation message appears\n\n";
} else {
  echo "⚠ CONFIGURATION INCOMPLETE\n\n";
  echo "Fix the errors listed above, then run:\n";
  echo "  ddev exec drush php:script /var/www/html/setup-scripts/apply-clientside-validation.php\n";
  echo "  ddev exec drush cr\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
