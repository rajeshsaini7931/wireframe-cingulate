<?php
/**
 * @file
 * Check if npi_lookup_markup element exists.
 */

use Drupal\webform\Entity\Webform;

$webform = Webform::load('register_form');
if (!$webform) {
  echo "ERROR: register_form not found\n";
  return;
}

$elements = $webform->getElementsDecoded();

if (isset($elements['npi_lookup_markup'])) {
  echo "✓ NPI lookup markup element EXISTS\n\n";
  echo "Type: " . ($elements['npi_lookup_markup']['#type'] ?? 'N/A') . "\n";
  echo "Weight: " . ($elements['npi_lookup_markup']['#weight'] ?? 'N/A') . "\n";
  echo "Markup:\n";
  echo $elements['npi_lookup_markup']['#markup'] . "\n";
} else {
  echo "✗ NPI lookup markup element NOT FOUND\n";
}
