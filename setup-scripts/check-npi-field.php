<?php
/**
 * @file
 * Check NPI field configuration.
 */

use Drupal\webform\Entity\Webform;

$webform = Webform::load('register_form');
if (!$webform) {
  echo "ERROR: register_form not found\n";
  return;
}

$elements = $webform->getElementsDecoded();

if (isset($elements['npi_number'])) {
  echo "NPI Number field configuration:\n";
  echo "=====================================\n";
  echo "Type: " . ($elements['npi_number']['#type'] ?? 'N/A') . "\n";
  echo "Title: " . ($elements['npi_number']['#title'] ?? 'N/A') . "\n";
  echo "Required: " . ($elements['npi_number']['#required'] ? 'Yes' : 'No') . "\n";
  echo "\nDescription:\n";
  echo isset($elements['npi_number']['#description']) ? $elements['npi_number']['#description'] : 'No description set';
  echo "\n\nDescription display: " . ($elements['npi_number']['#description_display'] ?? 'default') . "\n";
} else {
  echo "ERROR: npi_number field not found in webform\n";
}
