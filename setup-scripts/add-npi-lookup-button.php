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

// Remove plain text description from NPI field if it exists
if (isset($elements['npi_number']['#description'])) {
  unset($elements['npi_number']['#description']);
  unset($elements['npi_number']['#description_display']);
}

// Add a webform_markup element right after npi_number field
// This allows HTML rendering for the lookup button
$elements['npi_lookup_markup'] = [
  '#type' => 'webform_markup',
  '#markup' => '<div class="reg-form__sub-label">Need your NPI number? <button type="button" class="webform-cta-button npi-lookup-button">Look it up here</button></div>',
  '#weight' => -4, // Place it right after npi_number field
];

$webform->setElements($elements);
$webform->save();

echo "✓ Added NPI lookup button to register_form as webform_markup element\n";
echo "✓ Button will open modal dialog for NPI search\n";
echo "\nRun: ddev exec drush cr\n";
