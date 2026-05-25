<?php
/**
 * @file
 * Configure client-side validation for register_form webform.
 */

use Drupal\webform\Entity\Webform;

// Step 1: Configure clientside_validation_jquery settings.
$config = \Drupal::configFactory()->getEditable('clientside_validation_jquery.settings');
$config->set('use_cdn', TRUE);
// CRITICAL: Use protocol-relative base URL only - module appends jquery.validate.min.js
$config->set('cdn_base_url', '//cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/');
$config->set('validate_all_ajax_forms', TRUE);
$config->save();

echo "✓ Configured clientside_validation_jquery CDN settings\n";

// Step 2: Update register_form webform with validation messages.
$webform = Webform::load('register_form');
if ($webform) {
  $elements = $webform->getElementsDecoded();
  
  // First Name field
  $elements['first_name']['#required_error'] = 'Please enter your first name.';
  
  // Last Name field
  $elements['last_name']['#required_error'] = 'Please enter your last name.';
  
  // Mobile Phone field
  $elements['mobile_phone']['#required_error'] = 'Please enter your phone number.';
  $elements['mobile_phone']['#pattern'] = '^\+?1?\s?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$';
  $elements['mobile_phone']['#pattern_error'] = 'Please enter a valid phone number (e.g., +1 (123) 456-7890).';
  
  // Email field
  $elements['email']['#required_error'] = 'Please enter your email address.';
  $elements['email']['#pattern'] = '^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$';
  $elements['email']['#pattern_error'] = 'Please enter a valid email address.';
  
  // NPI Number field
  $elements['npi_number']['#required_error'] = 'Please enter your NPI number.';
  $elements['npi_number']['#pattern'] = '^\d{10}$';
  $elements['npi_number']['#pattern_error'] = 'NPI number must be exactly 10 digits.';
  
  // ZIP Code field
  $elements['zip_code']['#required_error'] = 'Please enter your ZIP code.';
  $elements['zip_code']['#pattern'] = '^\d{5}(-\d{4})?$';
  $elements['zip_code']['#pattern_error'] = 'Please enter a valid ZIP code (e.g., 12345 or 12345-6789).';
  
  $webform->setElements($elements);
  
  // Update webform settings for client-side validation
  $settings = $webform->getSettings();
  $settings['form_novalidate'] = FALSE;  // CRITICAL: Must be FALSE for validation to work
  $webform->setSettings($settings);
  
  $webform->save();
  
  echo "✓ Updated register_form with validation error messages\n";
  echo "✓ Set form_novalidate to FALSE\n";
} else {
  echo "ERROR: register_form webform not found\n";
}

echo "\n── Configuration Complete ──\n";
echo "Run: ddev exec drush cr\n";
