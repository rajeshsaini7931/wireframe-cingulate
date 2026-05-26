<?php
/**
 * @file
 * Check for time/date fields that might be causing the error.
 */

$fields = \Drupal::entityTypeManager()->getStorage('field_storage_config')->loadMultiple();

echo "Looking for time/date fields...\n\n";

foreach ($fields as $field) {
  $type = $field->getType();
  if (stripos($type, 'time') !== FALSE || stripos($type, 'date') !== FALSE) {
    echo $field->id() . ' => ' . $type . "\n";
  }
}

echo "\n--- Checking webform fields ---\n";

// Check webform fields
$webform = \Drupal::entityTypeManager()->getStorage('webform')->load('register_form');
if ($webform) {
  $elements = $webform->getElementsDecoded();
  foreach ($elements as $key => $element) {
    if (isset($element['#type']) && (stripos($element['#type'], 'time') !== FALSE || stripos($element['#type'], 'date') !== FALSE)) {
      echo "Webform field: {$key} => {$element['#type']}\n";
      if (isset($element['#step'])) {
        echo "  HAS STEP ATTRIBUTE: {$element['#step']}\n";
      }
    }
  }
}
