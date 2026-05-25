<?php

/**
 * @file
 * Create webform_section paragraph type with proper structure.
 *
 * Creates:
 * - webform_section paragraph type
 * - field_section_title
 * - field_section_subtitle  
 * - field_webform_reference (entity_reference → webform)
 * - Proper display configuration
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/create-webform-section-paragraph.php
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

// Create paragraph type
if (!ParagraphsType::load('webform_section')) {
  $paragraph_type = ParagraphsType::create([
    'id'    => 'webform_section',
    'label' => 'Webform Section',
  ]);
  $paragraph_type->save();
  echo "✓ Created paragraph type: webform_section\n";
}

// Field: Section Title
if (!FieldConfig::loadByName('paragraph', 'webform_section', 'field_section_title')) {
  FieldConfig::create([
    'field_name'  => 'field_section_title',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_section',
    'label'       => 'Section Title',
    'required'    => TRUE,
  ])->save();
  echo "✓ Attached field_section_title\n";
}

// Field: Section Subtitle
if (!FieldConfig::loadByName('paragraph', 'webform_section', 'field_section_subtitle')) {
  FieldConfig::create([
    'field_name'  => 'field_section_subtitle',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_section',
    'label'       => 'Section Subtitle',
    'required'    => FALSE,
  ])->save();
  echo "✓ Attached field_section_subtitle\n";
}

// Field: Webform Reference
if (!FieldStorageConfig::loadByName('paragraph', 'field_webform_reference')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_webform_reference',
    'entity_type' => 'paragraph',
    'type'        => 'entity_reference',
    'settings'    => ['target_type' => 'webform'],
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('paragraph', 'webform_section', 'field_webform_reference')) {
  FieldConfig::create([
    'field_name'  => 'field_webform_reference',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_section',
    'label'       => 'Webform',
    'required'    => TRUE,
    'settings'    => [
      'handler' => 'default:webform',
    ],
  ])->save();
  echo "✓ Attached field_webform_reference\n";
}

// Configure form display
$form_display = EntityFormDisplay::load('paragraph.webform_section.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'webform_section',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$form_display
  ->setComponent('field_section_title', [
    'type'   => 'string_textfield',
    'weight' => 0,
  ])
  ->setComponent('field_section_subtitle', [
    'type'   => 'string_textarea',
    'weight' => 1,
  ])
  ->setComponent('field_webform_reference', [
    'type'   => 'options_select',
    'weight' => 2,
  ])
  ->save();

echo "✓ Configured form display\n";

// Configure view display - CRITICAL for inline webform rendering
$view_display = EntityViewDisplay::load('paragraph.webform_section.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'webform_section',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$view_display
  ->setComponent('field_section_title', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_section_subtitle', [
    'type'   => 'basic_string',
    'label'  => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_webform_reference', [
    'type'     => 'entity_reference_entity_view',
    'label'    => 'hidden',
    'weight'   => 2,
    'settings' => [
      'view_mode' => 'default',
    ],
  ])
  ->save();

echo "✓ Configured view display\n";

// Add to landing_page allowed types
$landing_page_field = FieldConfig::loadByName('node', 'landing_page', 'field_content_sections');
if ($landing_page_field) {
  $allowed_types = $landing_page_field->getSetting('handler_settings')['target_bundles'] ?? [];
  $allowed_types['webform_section'] = 'webform_section';

  $landing_page_field->setSetting('handler_settings', [
    'target_bundles' => $allowed_types,
    'negate' => 0,
    'target_bundles_drag_drop' => array_fill_keys(array_keys($allowed_types), ['enabled' => TRUE]),
  ]);
  $landing_page_field->save();
  echo "✓ Added webform_section to landing_page allowed types\n";
}

echo "\n── Webform Section Paragraph Complete ──\n";
echo "Run: ddev exec drush cr\n";
