<?php
/**
 * @file
 * Check all nodes in the system.
 */

use Drupal\node\Entity\Node;

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->sort('nid', 'ASC')
  ->execute();

echo "All nodes in the system:\n";
echo str_repeat('=', 70) . PHP_EOL;

foreach ($nids as $nid) {
  $node = Node::load($nid);
  if ($node) {
    $published = $node->isPublished() ? '✓' : '✗';
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $nid);
    
    echo "Node {$nid} [{$published}] {$node->bundle()}: {$node->getTitle()}\n";
    echo "  Path: {$alias}\n";
    
    if ($node->hasField('field_content_sections')) {
      $paragraphs = $node->get('field_content_sections')->referencedEntities();
      if (!empty($paragraphs)) {
        echo "  Paragraphs: ";
        $types = array_map(function($p) { return $p->bundle(); }, $paragraphs);
        echo implode(', ', $types) . "\n";
      }
    }
    
    if ($node->hasField('field_webform') && !$node->get('field_webform')->isEmpty()) {
      echo "  Webform field: " . $node->get('field_webform')->target_id . "\n";
    }
    
    echo "\n";
  }
}
