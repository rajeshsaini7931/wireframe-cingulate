<?php
/**
 * @file
 * Check node/4 structure and field values.
 */

use Drupal\node\Entity\Node;

$node = Node::load(4);
if ($node) {
  echo "Node 4 Title: " . $node->getTitle() . PHP_EOL;
  echo "Bundle: " . $node->bundle() . PHP_EOL;
  echo "Published: " . ($node->isPublished() ? 'YES' : 'NO') . PHP_EOL;
  echo PHP_EOL;
  
  if ($node->hasField('field_webform')) {
    echo "Has field_webform: YES" . PHP_EOL;
    if (!$node->get('field_webform')->isEmpty()) {
      echo "field_webform value: " . $node->get('field_webform')->target_id . PHP_EOL;
    } else {
      echo "field_webform is EMPTY" . PHP_EOL;
    }
  } else {
    echo "Has field_webform: NO" . PHP_EOL;
  }
  echo PHP_EOL;
  
  if ($node->hasField('field_content_sections')) {
    $paragraphs = $node->get('field_content_sections')->referencedEntities();
    echo "Paragraphs count: " . count($paragraphs) . PHP_EOL;
    foreach ($paragraphs as $p) {
      echo "  - " . $p->bundle() . " (id: " . $p->id() . ")" . PHP_EOL;
      if ($p->bundle() === 'webform_embed' || $p->bundle() === 'webform_section') {
        if ($p->hasField('field_webform_reference')) {
          if (!$p->get('field_webform_reference')->isEmpty()) {
            echo "    webform: " . $p->get('field_webform_reference')->target_id . PHP_EOL;
          } else {
            echo "    webform: EMPTY" . PHP_EOL;
          }
        }
      }
    }
  }
  echo PHP_EOL;
  
  // Check view display
  $view_display = \Drupal::entityTypeManager()
    ->getStorage('entity_view_display')
    ->load('node.landing_page.default');
  
  if ($view_display) {
    echo "View display components:" . PHP_EOL;
    $components = $view_display->getComponents();
    foreach ($components as $field_name => $component) {
      echo "  - {$field_name}: {$component['type']}" . PHP_EOL;
    }
  }
} else {
  echo "Node 4 does not exist" . PHP_EOL;
}
