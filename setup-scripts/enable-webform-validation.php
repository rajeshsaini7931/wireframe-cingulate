<?php
/**
 * @file
 * Enable client-side validation specifically on register_form webform.
 *
 * This allows validation on the webform without enabling it globally
 * on all AJAX forms (which was causing paragraph add errors).
 */

$webform = \Drupal::entityTypeManager()->getStorage('webform')->load('register_form');

if (!$webform) {
  echo "Error: register_form webform not found.\n";
  exit(1);
}

// Get current settings
$settings = $webform->getSettings();

// Enable client-side validation for this specific webform
$settings['clientside_validation'] = TRUE;

// Update webform settings
$webform->set('settings', $settings);
$webform->save();

echo "✓ Client-side validation enabled on register_form webform\n";
echo "  - Webform ID: register_form\n";
echo "  - AJAX enabled: " . ($settings['ajax'] ? 'Yes' : 'No') . "\n";
echo "  - Client-side validation: Enabled\n";
echo "\nSettings updated. Clear cache with 'drush cr'\n";
