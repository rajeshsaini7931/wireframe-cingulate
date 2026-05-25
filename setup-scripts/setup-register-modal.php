<?php

/**
 * @file
 * Setup script for Register Modal block_content type.
 *
 * Creates:
 * - register_modal block_content type
 * - 3 fields (field_modal_title, field_modal_subtitle, field_register_link)
 * - EntityFormDisplay and EntityViewDisplay configurations
 * - Single block_content instance with default content
 * - Block placement in 'modals' region
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-register-modal.php
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

// ── Step 1: Create register_modal block_content type ───────────────────────

if (BlockContentType::load('register_modal')) {
  echo "Block content type 'register_modal' already exists — skipping type creation.\n";
}
else {
  BlockContentType::create([
    'id'          => 'register_modal',
    'label'       => 'Register Modal',
    'revision'    => FALSE,
    'description' => 'Auto-show registration modal with cookie suppression.',
  ])->save();
  echo "✓ Created block_content type: register_modal\n";
}

// ── Step 2: Create fields ───────────────────────────────────────────────────

$fields_config = [
  // field_modal_title: string — "Welcome to ADHD"
  'field_modal_title' => [
    'type'     => 'string',
    'label'    => 'Modal Title',
    'required' => TRUE,
    'settings' => ['max_length' => 255],
    'widget'   => 'string_textfield',
    'formatter' => 'string',
  ],
  // field_modal_subtitle: text_long — "Kindly register to know more..."
  'field_modal_subtitle' => [
    'type'     => 'text_long',
    'label'    => 'Modal Subtitle',
    'required' => FALSE,
    'settings' => [],
    'widget'   => 'text_textarea',
    'formatter' => 'text_default',
  ],
  // field_register_link: link — URL + title for Register CTA
  'field_register_link' => [
    'type'     => 'link',
    'label'    => 'Register CTA Link',
    'required' => TRUE,
    'settings' => [
      'link_type'  => 3, // Internal and external links
      'title'      => 1, // Title required
    ],
    'widget'   => 'link_default',
    'formatter' => 'link',
  ],
];

foreach ($fields_config as $field_name => $config) {
  // Create field storage.
  if (!FieldStorageConfig::loadByName('block_content', $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'block_content',
      'type'        => $config['type'],
      'settings'    => $config['settings'],
      'cardinality' => 1,
    ])->save();
    echo "✓ Created field storage: {$field_name}\n";
  }

  // Attach field to register_modal bundle.
  if (!FieldConfig::loadByName('block_content', 'register_modal', $field_name)) {
    FieldConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'block_content',
      'bundle'      => 'register_modal',
      'label'       => $config['label'],
      'required'    => $config['required'],
    ])->save();
    echo "✓ Attached field to register_modal: {$field_name}\n";
  }
}

// ── Step 3: Configure EntityFormDisplay ─────────────────────────────────────

$form_display = EntityFormDisplay::load('block_content.register_modal.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle'           => 'register_modal',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$form_display
  ->setComponent('field_modal_title', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => ['size' => 60, 'placeholder' => ''],
  ])
  ->setComponent('field_modal_subtitle', [
    'type'   => 'text_textarea',
    'weight' => 1,
    'settings' => ['rows' => 3, 'placeholder' => ''],
  ])
  ->setComponent('field_register_link', [
    'type'   => 'link_default',
    'weight' => 2,
  ])
  ->save();

echo "✓ Configured EntityFormDisplay for register_modal\n";

// ── Step 4: Configure EntityViewDisplay ─────────────────────────────────────

$view_display = EntityViewDisplay::load('block_content.register_modal.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle'           => 'register_modal',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$view_display
  ->setComponent('field_modal_title', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_modal_subtitle', [
    'type'   => 'text_default',
    'label'  => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_register_link', [
    'type'   => 'link',
    'label'  => 'hidden',
    'weight' => 2,
  ])
  ->save();

echo "✓ Configured EntityViewDisplay for register_modal\n";

// ── Step 5: Create block_content instance ───────────────────────────────────

$existing = \Drupal::entityTypeManager()
  ->getStorage('block_content')
  ->loadByProperties([
    'type' => 'register_modal',
    'info' => 'Register Modal',
  ]);

if (!empty($existing)) {
  echo "Block content instance 'Register Modal' already exists — skipping.\n";
  $block_content = reset($existing);
}
else {
  $block_content = BlockContent::create([
    'type' => 'register_modal',
    'info' => 'Register Modal',
    'field_modal_title' => 'Welcome to ADHD',
    'field_modal_subtitle' => 'Kindly register to know more about the details of the site.',
    'field_register_link' => [
      'uri'   => 'internal:/register',
      'title' => 'Register',
    ],
  ]);
  $block_content->save();
  echo "✓ Created block_content instance: Register Modal (ID: {$block_content->id()})\n";
}

// ── Step 6: Place block in 'modals' region ─────────────────────────────────

if (Block::load('cingulate_registermodal')) {
  echo "Block placement 'cingulate_registermodal' already exists — skipping.\n";
}
else {
  Block::create([
    'id'       => 'cingulate_registermodal',
    'theme'    => 'cingulate',
    'region'   => 'modals',
    'weight'   => 0,
    'plugin'   => 'block_content:' . $block_content->uuid(),
    'settings' => [
      'label'         => 'Register Modal',
      'label_display' => '0',
    ],
  ])->save();
  echo "✓ Placed block in 'modals' region: cingulate_registermodal\n";
}

echo "\n── Register Modal Setup Complete ──\n";
echo "Run: ddev exec drush cr && ddev exec drush cex -y\n";
