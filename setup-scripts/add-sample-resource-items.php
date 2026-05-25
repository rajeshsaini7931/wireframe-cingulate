<?php

/**
 * @file
 * Add sample resource items to existing resources paragraph.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/add-sample-resource-items.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

echo "Adding sample resource items to Home page...\n\n";

// Load home page node
$node = Node::load(2);
if (!$node) {
  echo "❌ Home page node not found.\n";
  return;
}

// Find the resources paragraph
$paragraphs = $node->get('field_content_sections')->referencedEntities();
$resources_para = NULL;

foreach ($paragraphs as $para) {
  if ($para->bundle() === 'resources') {
    $resources_para = $para;
    break;
  }
}

if (!$resources_para) {
  echo "❌ Resources paragraph not found on home page.\n";
  return;
}

// Create 3 sample resource items
$sample_items = [
  [
    'title' => 'Understanding ADHD Symptoms',
    'topic' => 'Education',
    'duration' => '5 minutes',
    'video_url' => 'https://vimeo.com/347119375',
  ],
  [
    'title' => 'Diagnostic Criteria Overview',
    'topic' => 'Diagnosis',
    'duration' => '8 minutes',
    'video_url' => 'https://vimeo.com/347119375',
  ],
  [
    'title' => 'Treatment Options',
    'topic' => 'Treatment',
    'duration' => '12 minutes',
    'video_url' => 'https://vimeo.com/347119375',
  ],
];

$created_items = [];

foreach ($sample_items as $item_data) {
  $item = Paragraph::create([
    'type' => 'resource_item',
    'field_item_title' => $item_data['title'],
    'field_topic' => $item_data['topic'],
    'field_duration' => $item_data['duration'],
    'field_video_url' => [
      'uri' => $item_data['video_url'],
      'title' => $item_data['title'],
    ],
    'field_transcript' => 'This is a placeholder transcript for ' . $item_data['title'] . '. The actual transcript content will be provided by the team.',
  ]);
  $item->save();
  $created_items[] = [
    'target_id' => $item->id(),
    'target_revision_id' => $item->getRevisionId(),
  ];
  
  echo "  ✓ Created: {$item_data['title']}\n";
}

// Add items to resources paragraph
$resources_para->set('field_resource_items', $created_items);
$resources_para->save();

// Save the node to trigger revision
$node->save();

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Added 3 sample resource items to Resources paragraph\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next: Clear cache and test.\n\n";
