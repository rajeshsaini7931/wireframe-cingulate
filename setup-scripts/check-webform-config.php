<?php
/**
 * @file
 * Check register_form webform configuration.
 */

$webform = \Drupal::entityTypeManager()->getStorage('webform')->load('register_form');
if ($webform) {
  echo "Register Form Configuration:\n";
  echo str_repeat('=', 70) . PHP_EOL;
  
  $elements = $webform->getElementsDecoded();
  
  foreach ($elements as $key => $element) {
    echo "\n{$key}:\n";
    echo "  Type: " . ($element['#type'] ?? 'unknown') . "\n";
    echo "  Title: " . ($element['#title'] ?? 'N/A') . "\n";
    
    if (isset($element['#markup'])) {
      echo "  Markup: " . substr($element['#markup'], 0, 100) . "...\n";
    }
    
    if (isset($element['#placeholder'])) {
      echo "  Placeholder: " . $element['#placeholder'] . "\n";
    }
    
    if (isset($element['#required'])) {
      echo "  Required: " . ($element['#required'] ? 'YES' : 'NO') . "\n";
    }
    
    if ($key === 'actions' && isset($element['submit'])) {
      echo "  Submit Label: " . ($element['submit']['#value'] ?? 'Submit') . "\n";
    }
  }
}
