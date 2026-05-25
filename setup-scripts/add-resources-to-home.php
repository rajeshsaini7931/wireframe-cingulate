<?php

/**
 * @file
 * Add resources paragraph to Home page.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/add-resources-to-home.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$node = Node::load(2);

if (!$node) {
  echo "Home page node not found.\n";
  return;
}

// Create resources paragraph
$resourcesPara = Paragraph::create([
  'type' => 'resources',
  'field_section_heading' => 'Resource Materials',
]);
$resourcesPara->save();

// Add to node
$paragraphs = $node->get('field_content_sections')->getValue();
$paragraphs[] = [
  'target_id' => $resourcesPara->id(),
  'target_revision_id' => $resourcesPara->getRevisionId(),
];
$node->set('field_content_sections', $paragraphs);
$node->save();

echo "✓ Added resources paragraph to Home page.\n";
