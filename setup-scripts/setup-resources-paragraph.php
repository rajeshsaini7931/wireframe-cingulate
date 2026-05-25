<?php

/**
 * @file
 * Setup script for Resource Materials paragraph type.
 *
 * Creates the resources paragraph type with section heading.
 * Cards and items are hardcoded in template for now.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-resources-paragraph.php
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Resource Materials Paragraph Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 1 — Create resources paragraph type
// ────────────────────────────────────────────────────────────────────────────
echo "[1/3] Creating resources paragraph type...\n";

if (ParagraphsType::load('resources')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  ParagraphsType::create([
    'id'          => 'resources',
    'label'       => 'Resource Materials',
    'description' => 'Tabbed resource section with podcasts, videos, and newsletter cards.',
  ])->save();
  echo "  ✓ Created resources paragraph type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 2 — Attach field_section_heading (reuse existing storage)
// ────────────────────────────────────────────────────────────────────────────
echo "[2/3] Attaching field_section_heading to resources...\n";

if (!FieldConfig::loadByName('paragraph', 'resources', 'field_section_heading')) {
  FieldConfig::create([
    'field_name'  => 'field_section_heading',
    'entity_type' => 'paragraph',
    'bundle'      => 'resources',
    'label'       => 'Section Heading',
    'required'    => TRUE,
  ])->save();
  echo "  ✓ Attached field_section_heading to resources\n\n";
}
else {
  echo "  → Already attached — skipping.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 3 — Configure resources paragraph displays
// ────────────────────────────────────────────────────────────────────────────
echo "[3/3] Configuring resources paragraph displays...\n";

// Form display
$para_form_display = EntityFormDisplay::load('paragraph.resources.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'resources',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$para_form_display
  ->setComponent('field_section_heading', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Resource Materials',
    ],
  ])
  ->save();

// View display
$para_view_display = EntityViewDisplay::load('paragraph.resources.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'resources',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$para_view_display
  ->setComponent('field_section_heading', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->save();

echo "  ✓ Resources paragraph displays configured.\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Resources paragraph type created\n";
echo "  ✓ field_section_heading attached\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next: Create template with hardcoded cards/items.\n\n";
