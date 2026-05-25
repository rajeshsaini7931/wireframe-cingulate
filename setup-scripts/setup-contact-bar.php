<?php

/**
 * @file
 * Setup script for Contact Bar block_content type.
 *
 * Creates the contact_bar block_content type with fields,
 * configures displays, creates a block instance, and places it
 * in the content_bottom region.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-contact-bar.php
 */

use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block\Entity\Block;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Contact Bar Block Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 1 — Create block_content type: contact_bar
// ────────────────────────────────────────────────────────────────────────────
echo "[1/7] Creating contact_bar block_content type...\n";

if (BlockContentType::load('contact_bar')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  BlockContentType::create([
    'id'          => 'contact_bar',
    'label'       => 'Contact Bar',
    'description' => 'Global contact bar with phone and email.',
  ])->save();
  echo "  ✓ Created contact_bar block type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 2 — Create fields for contact_bar
// ────────────────────────────────────────────────────────────────────────────
echo "[2/7] Creating fields for contact_bar...\n";

$fields = [
  'field_contact_label' => [
    'type'        => 'string',
    'label'       => 'Contact Label',
    'description' => 'Label text for the contact bar (e.g., "Reach for more info:")',
    'required'    => FALSE,
    'max_length'  => 255,
  ],
  'field_phone' => [
    'type'        => 'string',
    'label'       => 'Phone Number',
    'description' => 'Contact phone number',
    'required'    => TRUE,
    'max_length'  => 50,
  ],
  'field_email' => [
    'type'        => 'email',
    'label'       => 'Email Address',
    'description' => 'Contact email address',
    'required'    => TRUE,
  ],
];

foreach ($fields as $field_name => $config) {
  // Create field storage (shared across bundles).
  if (!FieldStorageConfig::loadByName('block_content', $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'block_content',
      'type'        => $config['type'],
      'cardinality' => 1,
      'settings'    => $config['type'] === 'string' ? ['max_length' => $config['max_length']] : [],
    ])->save();
    echo "  ✓ Created field storage: {$field_name}\n";
  }

  // Attach field to contact_bar bundle.
  if (!FieldConfig::loadByName('block_content', 'contact_bar', $field_name)) {
    FieldConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'block_content',
      'bundle'      => 'contact_bar',
      'label'       => $config['label'],
      'description' => $config['description'],
      'required'    => $config['required'],
    ])->save();
    echo "  ✓ Attached {$field_name} to contact_bar\n";
  }
}

echo "\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 3 — Configure EntityFormDisplay (Manage Form Display)
// ────────────────────────────────────────────────────────────────────────────
echo "[3/7] Configuring EntityFormDisplay for contact_bar...\n";

$form_display = EntityFormDisplay::load('block_content.contact_bar.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle'           => 'contact_bar',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$form_display
  ->setComponent('field_contact_label', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Reach for more info:',
    ],
  ])
  ->setComponent('field_phone', [
    'type'   => 'string_textfield',
    'weight' => 1,
    'settings' => [
      'size' => 30,
      'placeholder' => '000-000-0000',
    ],
  ])
  ->setComponent('field_email', [
    'type'   => 'email_default',
    'weight' => 2,
    'settings' => [
      'placeholder' => 'info@mail.com',
    ],
  ])
  ->save();

echo "  ✓ EntityFormDisplay configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 4 — Configure EntityViewDisplay (Manage Display)
// ────────────────────────────────────────────────────────────────────────────
echo "[4/7] Configuring EntityViewDisplay for contact_bar...\n";

$view_display = EntityViewDisplay::load('block_content.contact_bar.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle'           => 'contact_bar',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

// All fields should have label hidden — template controls all markup.
$view_display
  ->setComponent('field_contact_label', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_phone', [
    'type'   => 'basic_string',
    'label'  => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_email', [
    'type'   => 'basic_string',
    'label'  => 'hidden',
    'weight' => 2,
  ])
  ->save();

echo "  ✓ EntityViewDisplay configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 5 — Create contact_bar block_content instance
// ────────────────────────────────────────────────────────────────────────────
echo "[5/7] Creating contact_bar block_content instance...\n";

$existing = \Drupal::entityTypeManager()
  ->getStorage('block_content')
  ->loadByProperties(['type' => 'contact_bar']);

if (empty($existing)) {
  $block_content = BlockContent::create([
    'type'  => 'contact_bar',
    'info'  => 'Site Contact Bar',
    'field_contact_label' => 'Reach for more info:',
    'field_phone' => '000-000-0000',
    'field_email' => 'info@mail.com',
  ]);
  $block_content->save();
  echo "  ✓ Created block_content instance (ID: {$block_content->id()})\n\n";
}
else {
  $block_content = reset($existing);
  echo "  → Block content already exists (ID: {$block_content->id()}) — skipping.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 6 — Place block in content_bottom region
// ────────────────────────────────────────────────────────────────────────────
echo "[6/7] Placing contact_bar block in content_bottom region...\n";

$block_id = 'cingulate_contactbar';
if (Block::load($block_id)) {
  echo "  → Block already placed — skipping.\n\n";
}
else {
  Block::create([
    'id'       => $block_id,
    'theme'    => 'cingulate',
    'region'   => 'content_bottom',
    'weight'   => -10,
    'plugin'   => 'block_content:' . $block_content->uuid(),
    'settings' => [
      'id'    => 'block_content:' . $block_content->uuid(),
      'label' => 'Contact Bar',
      'label_display' => FALSE,
    ],
    'visibility' => [],
  ])->save();
  echo "  ✓ Placed block in content_bottom region (weight: -10)\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 7 — Summary
// ────────────────────────────────────────────────────────────────────────────
echo "[7/7] Setup complete!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Block content type: contact_bar\n";
echo "  ✓ Fields: field_contact_label, field_phone, field_email\n";
echo "  ✓ Block instance created and placed in content_bottom region\n";
echo "  ✓ Region: content_bottom (appears after main content, before footer)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next steps:\n";
echo "  1. Create template: web/themes/custom/cingulate/templates/block/block--block-content--contact-bar.html.twig\n";
echo "  2. Create CSS: web/themes/custom/cingulate/css/components/contact.css\n";
echo "  3. Add library to cingulate.libraries.yml\n";
echo "  4. Run: ddev exec drush cr\n\n";
