<?php
/**
 * @file
 * Check webform_section paragraph fields.
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldConfig;

$para_type = ParagraphsType::load('webform_section');
if ($para_type) {
  echo "webform_section paragraph type exists\n";
  echo "Fields:\n";
  
  $field_definitions = \Drupal::service('entity_field.manager')
    ->getFieldDefinitions('paragraph', 'webform_section');
  
  foreach ($field_definitions as $field_name => $field_def) {
    if (!$field_def->getFieldStorageDefinition()->isBaseField()) {
      echo "  - {$field_name}: " . $field_def->getType() . "\n";
    }
  }
} else {
  echo "webform_section paragraph type NOT found\n";
}

echo "\nChecking register_form webform fields:\n";
$webform = \Drupal::entityTypeManager()->getStorage('webform')->load('register_form');
if ($webform) {
  $elements = $webform->getElementsDecodedAndFlattened();
  foreach ($elements as $key => $element) {
    echo "  - {$key}: " . ($element['#type'] ?? 'unknown') . "\n";
  }
}
