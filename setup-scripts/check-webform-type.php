<?php
/**
 * @file
 * Check webform content type structure.
 */

use Drupal\node\Entity\Node;

$node = Node::load(4);
if ($node) {
  echo "Node 4: " . $node->getTitle() . PHP_EOL;
  echo "Bundle: " . $node->bundle() . PHP_EOL;
  echo PHP_EOL;
  
  // Get all fields
  $fields = $node->getFieldDefinitions();
  echo "All fields on webform content type:\n";
  foreach ($fields as $field_name => $field_def) {
    if (!$field_def->getFieldStorageDefinition()->isBaseField()) {
      echo "  - {$field_name}: " . $field_def->getType() . PHP_EOL;
      
      if (!$node->get($field_name)->isEmpty()) {
        $value = $node->get($field_name)->getValue();
        echo "    Value: " . print_r($value, TRUE);
      } else {
        echo "    (empty)\n";
      }
    }
  }
}
