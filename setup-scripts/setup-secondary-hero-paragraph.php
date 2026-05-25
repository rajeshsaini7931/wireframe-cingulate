<?php

/**
 * @file
 * Setup script for Secondary Hero paragraph type.
 *
 * Creates:
 * - secondary_hero paragraph type
 * - 1 field (field_heading)
 * - EntityFormDisplay and EntityViewDisplay configurations
 *
 * This paragraph is used across ALL internal pages (Register, ADHD Overview, etc.)
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-secondary-hero-paragraph.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

// ── Step 1: Create secondary_hero paragraph type ────────────────────────────

if (ParagraphsType::load('secondary_hero')) {
  echo "Paragraph type 'secondary_hero' already exists — skipping.\n";
}
else {
  ParagraphsType::create([
    'id'          => 'secondary_hero',
    'label'       => 'Secondary Hero',
    'description' => 'Simple banner with page title for internal pages.',
  ])->save();
  echo "✓ Created paragraph type: secondary_hero\n";
}

// ── Step 2: Create field_heading ────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('paragraph', 'field_heading')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_heading',
    'entity_type' => 'paragraph',
    'type'        => 'string',
    'settings'    => ['max_length' => 255],
    'cardinality' => 1,
  ])->save();
  echo "✓ Created field storage: field_heading\n";
}

if (!FieldConfig::loadByName('paragraph', 'secondary_hero', 'field_heading')) {
  FieldConfig::create([
    'field_name'  => 'field_heading',
    'entity_type' => 'paragraph',
    'bundle'      => 'secondary_hero',
    'label'       => 'Heading',
    'required'    => TRUE,
  ])->save();
  echo "✓ Attached field to secondary_hero: field_heading\n";
}

// ── Step 3: Configure EntityFormDisplay ──────────────────────────────────────

$form_display = EntityFormDisplay::load('paragraph.secondary_hero.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'secondary_hero',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$form_display
  ->setComponent('field_heading', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => ['size' => 60, 'placeholder' => ''],
  ])
  ->save();

echo "✓ Configured EntityFormDisplay for secondary_hero\n";

// ── Step 4: Configure EntityViewDisplay ──────────────────────────────────────

$view_display = EntityViewDisplay::load('paragraph.secondary_hero.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'secondary_hero',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$view_display
  ->setComponent('field_heading', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->save();

echo "✓ Configured EntityViewDisplay for secondary_hero\n";

// ── Step 5: Add to landing_page content type ─────────────────────────────────

// Check if field_content_sections exists on landing_page
$field_config = FieldConfig::loadByName('node', 'landing_page', 'field_content_sections');

if ($field_config) {
  // Get current allowed paragraph types
  $handler_settings = $field_config->getSetting('handler_settings');
  $target_bundles = $handler_settings['target_bundles'] ?? [];
  
  // Add secondary_hero if not already present
  if (!isset($target_bundles['secondary_hero'])) {
    $target_bundles['secondary_hero'] = 'secondary_hero';
    $handler_settings['target_bundles'] = $target_bundles;
    $field_config->setSetting('handler_settings', $handler_settings);
    $field_config->save();
    echo "✓ Added secondary_hero to landing_page allowed paragraph types\n";
  }
  else {
    echo "secondary_hero already in landing_page allowed types — skipping.\n";
  }
}
else {
  echo "WARNING: field_content_sections not found on landing_page content type.\n";
}

echo "\n── Secondary Hero Paragraph Setup Complete ──\n";
echo "Run: ddev exec drush cr && ddev exec drush cex -y\n";
