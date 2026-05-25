<?php

/**
 * @file
 * Add symptoms paragraph to Home page.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/add-symptoms-to-home.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$node = Node::load(2);

if (!$node) {
  echo "Home page node not found.\n";
  return;
}

// Create symptoms paragraph
$symptomsPara = Paragraph::create([
  'type' => 'symptoms',
  'field_section_heading' => 'Symptoms of ADHD',
]);
$symptomsPara->save();

// Add to node
$paragraphs = $node->get('field_content_sections')->getValue();
$paragraphs[] = [
  'target_id' => $symptomsPara->id(),
  'target_revision_id' => $symptomsPara->getRevisionId(),
];
$node->set('field_content_sections', $paragraphs);
$node->save();

echo "✓ Added symptoms paragraph to Home page.\n";
