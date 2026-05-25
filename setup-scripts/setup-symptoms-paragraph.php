<?php

/**
 * @file
 * Setup script for Symptoms of ADHD paragraph type.
 *
 * Creates the symptoms paragraph type with heading field.
 * Infographics will be placeholder for now (carousel functionality later).
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-symptoms-paragraph.php
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Symptoms Paragraph Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 1 — Create symptoms paragraph type
// ────────────────────────────────────────────────────────────────────────────
echo "[1/4] Creating symptoms paragraph type...\n";

if (ParagraphsType::load('symptoms')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  ParagraphsType::create([
    'id'          => 'symptoms',
    'label'       => 'Symptoms of ADHD',
    'description' => 'Infographic carousel section for ADHD symptoms.',
  ])->save();
  echo "  ✓ Created symptoms paragraph type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 2 — Create field_section_heading (reuse existing storage)
// ────────────────────────────────────────────────────────────────────────────
echo "[2/4] Creating fields for symptoms paragraph...\n";

// Create field storage if not exists
if (!FieldStorageConfig::loadByName('paragraph', 'field_section_heading')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_section_heading',
    'entity_type' => 'paragraph',
    'type'        => 'string',
    'cardinality' => 1,
    'settings'    => ['max_length' => 255],
  ])->save();
  echo "  ✓ Created field storage: field_section_heading\n";
}

// Attach to symptoms bundle
if (!FieldConfig::loadByName('paragraph', 'symptoms', 'field_section_heading')) {
  FieldConfig::create([
    'field_name'  => 'field_section_heading',
    'entity_type' => 'paragraph',
    'bundle'      => 'symptoms',
    'label'       => 'Section Heading',
    'required'    => TRUE,
  ])->save();
  echo "  ✓ Attached field_section_heading to symptoms\n";
}

echo "\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 3 — Configure symptoms paragraph displays
// ────────────────────────────────────────────────────────────────────────────
echo "[3/4] Configuring symptoms paragraph displays...\n";

// Form display
$para_form_display = EntityFormDisplay::load('paragraph.symptoms.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'symptoms',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$para_form_display
  ->setComponent('field_section_heading', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Symptoms of ADHD',
    ],
  ])
  ->save();

// View display
$para_view_display = EntityViewDisplay::load('paragraph.symptoms.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'symptoms',
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

echo "  ✓ Symptoms paragraph displays configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 4 — Summary
// ────────────────────────────────────────────────────────────────────────────
echo "[4/4] Setup complete!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Symptoms paragraph type created\n";
echo "  ✓ field_section_heading attached\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next: Add symptoms paragraph to Home page node and create template.\n\n";
