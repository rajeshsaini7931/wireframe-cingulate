<?php

/**
 * @file
 * Setup script for Resource Item sub-paragraph type.
 *
 * Creates the resource_item paragraph type with all fields and adds
 * field_resource_items ERR field to resources paragraph.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-resource-item-paragraph.php
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Resource Item Sub-Paragraph Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 1 — Create resource_item paragraph type
// ────────────────────────────────────────────────────────────────────────────
echo "[1/5] Creating resource_item paragraph type...\n";

if (ParagraphsType::load('resource_item')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  ParagraphsType::create([
    'id'          => 'resource_item',
    'label'       => 'Resource Item',
    'description' => 'Individual resource item (video, podcast, newsletter) with thumbnail, title, video URL, and transcript.',
  ])->save();
  echo "  ✓ Created resource_item paragraph type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 2 — Create fields for resource_item
// ────────────────────────────────────────────────────────────────────────────
echo "[2/5] Creating fields for resource_item...\n";

$fields = [
  'field_item_title' => [
    'type' => 'string',
    'label' => 'Title',
    'required' => TRUE,
    'settings' => ['max_length' => 255],
  ],
  'field_topic' => [
    'type' => 'string',
    'label' => 'Topic',
    'required' => FALSE,
    'settings' => ['max_length' => 255],
  ],
  'field_duration' => [
    'type' => 'string',
    'label' => 'Duration',
    'required' => FALSE,
    'settings' => ['max_length' => 50],
  ],
  'field_video_url' => [
    'type' => 'link',
    'label' => 'Video URL',
    'required' => TRUE,
    'settings' => ['link_type' => 17], // LINK_EXTERNAL + LINK_INTERNAL
  ],
  'field_thumbnail' => [
    'type' => 'entity_reference',
    'label' => 'Thumbnail',
    'required' => FALSE,
    'settings' => ['target_type' => 'media'],
    'handler_settings' => ['target_bundles' => ['image' => 'image']],
  ],
  'field_transcript' => [
    'type' => 'text_long',
    'label' => 'Transcript',
    'required' => FALSE,
  ],
];

foreach ($fields as $field_name => $config) {
  // Create storage if not exists
  if (!FieldStorageConfig::loadByName('paragraph', $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'type'        => $config['type'],
      'cardinality' => 1,
      'settings'    => $config['settings'] ?? [],
    ])->save();
    echo "  ✓ Created storage: {$field_name}\n";
  }

  // Attach to resource_item bundle
  if (!FieldConfig::loadByName('paragraph', 'resource_item', $field_name)) {
    $field_config = [
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'bundle'      => 'resource_item',
      'label'       => $config['label'],
      'required'    => $config['required'],
    ];
    
    if (isset($config['handler_settings'])) {
      $field_config['settings'] = [
        'handler' => 'default:media',
        'handler_settings' => $config['handler_settings'],
      ];
    }
    
    FieldConfig::create($field_config)->save();
    echo "  ✓ Attached {$field_name} to resource_item\n";
  }
}

echo "\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 3 — Configure resource_item displays
// ────────────────────────────────────────────────────────────────────────────
echo "[3/5] Configuring resource_item displays...\n";

$item_form = EntityFormDisplay::load('paragraph.resource_item.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'resource_item',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$item_form
  ->setComponent('field_item_title', ['type' => 'string_textfield', 'weight' => 0])
  ->setComponent('field_topic', ['type' => 'string_textfield', 'weight' => 1])
  ->setComponent('field_duration', ['type' => 'string_textfield', 'weight' => 2])
  ->setComponent('field_video_url', ['type' => 'link_default', 'weight' => 3])
  ->setComponent('field_thumbnail', ['type' => 'media_library_widget', 'weight' => 4])
  ->setComponent('field_transcript', ['type' => 'text_textarea', 'weight' => 5])
  ->save();

$item_view = EntityViewDisplay::load('paragraph.resource_item.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'resource_item',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$item_view
  ->setComponent('field_item_title', ['type' => 'string', 'label' => 'hidden', 'weight' => 0])
  ->setComponent('field_topic', ['type' => 'string', 'label' => 'hidden', 'weight' => 1])
  ->setComponent('field_duration', ['type' => 'string', 'label' => 'hidden', 'weight' => 2])
  ->setComponent('field_video_url', ['type' => 'link', 'label' => 'hidden', 'weight' => 3])
  ->setComponent('field_thumbnail', ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'weight' => 4])
  ->setComponent('field_transcript', ['type' => 'text_default', 'label' => 'hidden', 'weight' => 5])
  ->save();

echo "  ✓ Resource item displays configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 4 — Add field_resource_items to resources paragraph
// ────────────────────────────────────────────────────────────────────────────
echo "[4/5] Adding field_resource_items to resources paragraph...\n";

if (!FieldStorageConfig::loadByName('paragraph', 'field_resource_items')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_resource_items',
    'entity_type' => 'paragraph',
    'type'        => 'entity_reference_revisions',
    'cardinality' => -1, // Unlimited
    'settings'    => ['target_type' => 'paragraph'],
  ])->save();
  echo "  ✓ Created field storage: field_resource_items\n";
}

if (!FieldConfig::loadByName('paragraph', 'resources', 'field_resource_items')) {
  FieldConfig::create([
    'field_name'  => 'field_resource_items',
    'entity_type' => 'paragraph',
    'bundle'      => 'resources',
    'label'       => 'Resource Items',
    'required'    => FALSE,
    'settings'    => [
      'handler' => 'default:paragraph',
      'handler_settings' => [
        'target_bundles' => ['resource_item' => 'resource_item'],
        'negate' => 0,
      ],
    ],
  ])->save();
  echo "  ✓ Attached field_resource_items to resources\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 5 — Configure resources paragraph displays
// ────────────────────────────────────────────────────────────────────────────
echo "[5/5] Updating resources paragraph displays...\n";

$res_form = EntityFormDisplay::load('paragraph.resources.default');
if ($res_form) {
  $res_form->setComponent('field_resource_items', [
    'type' => 'paragraphs',
    'weight' => 10,
    'settings' => [
      'title' => 'Resource Item',
      'title_plural' => 'Resource Items',
      'edit_mode' => 'open',
      'add_mode' => 'dropdown',
      'form_display_mode' => 'default',
    ],
  ])->save();
  echo "  ✓ Updated resources form display.\n";
}

$res_view = EntityViewDisplay::load('paragraph.resources.default');
if ($res_view) {
  $res_view->setComponent('field_resource_items', [
    'type' => 'entity_reference_revisions_entity_view',
    'label' => 'hidden',
    'weight' => 10,
    'settings' => ['view_mode' => 'default'],
  ])->save();
  echo "  ✓ Updated resources view display.\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Resource item sub-paragraph created\n";
echo "  ✓ All 6 fields attached and configured\n";
echo "  ✓ field_resource_items added to resources paragraph\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next: Update template and add sample resource items.\n\n";
