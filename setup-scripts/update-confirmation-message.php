<?php
/**
 * @file
 * Update register_form confirmation message with proper HTML formatting.
 */

use Drupal\webform\Entity\Webform;

$webform = Webform::load('register_form');
if ($webform) {
  $settings = $webform->getSettings();
  
  // Update confirmation message with better formatting
  $settings['confirmation_message'] = '<h3>Thank you for registering!</h3><p>You will receive the latest updates, research, and resources on ADHD.</p>';
  
  $webform->setSettings($settings);
  $webform->save();
  
  echo "✓ Updated confirmation message with HTML formatting\n";
  echo "\nConfirmation settings:\n";
  echo "  Type: " . $settings['confirmation_type'] . "\n";
  echo "  AJAX: " . ($settings['ajax'] ? 'enabled' : 'disabled') . "\n";
  echo "  Message: " . substr($settings['confirmation_message'], 0, 80) . "...\n";
  
  echo "\n── Run: ddev exec drush cr ──\n";
} else {
  echo "ERROR: register_form webform not found\n";
}
