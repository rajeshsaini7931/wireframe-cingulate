<?php

/**
 * @file
 * Setup script for Webform paragraph type.
 *
 * Creates:
 * - webform_embed paragraph type
 * - field_webform_reference (entity_reference → webform)
 * - field_section_title (optional heading above form)
 * - field_section_subtitle (optional subtitle)
 * - EntityFormDisplay and EntityViewDisplay configuration
 * - Adds to landing_page allowed types
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-webform-paragraph.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

// Create paragraph type
if (!ParagraphsType::load('webform_embed')) {
  $paragraph_type = ParagraphsType::create([
    'id'          => 'webform_embed',
    'label'       => 'Webform Embed',
    'description' => 'Embeds a webform with optional title and subtitle.',
  ]);
  $paragraph_type->save();
  echo "✓ Created paragraph type: webform_embed\n";
}
else {
  echo "  [SKIP] Paragraph type webform_embed already exists\n";
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

if (!FieldConfig::loadByName('paragraph', 'webform_embed', 'field_webform_reference')) {
  FieldConfig::create([
    'field_name'  => 'field_webform_reference',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_embed',
    'label'       => 'Webform',
    'required'    => TRUE,
    'settings'    => [
      'handler'          => 'default:webform',
      'handler_settings' => [],
    ],
  ])->save();
  echo "✓ Attached field to webform_embed: field_webform_reference\n";
}

// Field: Section Title (optional)
if (!FieldStorageConfig::loadByName('paragraph', 'field_section_title')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_section_title',
    'entity_type' => 'paragraph',
    'type'        => 'string',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('paragraph', 'webform_embed', 'field_section_title')) {
  FieldConfig::create([
    'field_name'  => 'field_section_title',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_embed',
    'label'       => 'Section Title',
    'required'    => FALSE,
    'settings'    => ['max_length' => 255],
  ])->save();
  echo "✓ Attached field to webform_embed: field_section_title\n";
}

// Field: Section Subtitle (optional)
if (!FieldStorageConfig::loadByName('paragraph', 'field_section_subtitle')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_section_subtitle',
    'entity_type' => 'paragraph',
    'type'        => 'string_long',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('paragraph', 'webform_embed', 'field_section_subtitle')) {
  FieldConfig::create([
    'field_name'  => 'field_section_subtitle',
    'entity_type' => 'paragraph',
    'bundle'      => 'webform_embed',
    'label'       => 'Section Subtitle',
    'required'    => FALSE,
  ])->save();
  echo "✓ Attached field to webform_embed: field_section_subtitle\n";
}

// Configure EntityFormDisplay
$form_display = EntityFormDisplay::load('paragraph.webform_embed.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'webform_embed',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$form_display
  ->setComponent('field_webform_reference', [
    'type'   => 'options_select',
    'weight' => 0,
  ])
  ->setComponent('field_section_title', [
    'type'   => 'string_textfield',
    'weight' => 1,
  ])
  ->setComponent('field_section_subtitle', [
    'type'   => 'string_textarea',
    'weight' => 2,
  ])
  ->save();

echo "✓ Configured EntityFormDisplay for webform_embed\n";

// Configure EntityViewDisplay
$view_display = EntityViewDisplay::load('paragraph.webform_embed.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'webform_embed',
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
    'type'     => 'webform_entity_reference_entity_view',
    'label'    => 'hidden',
    'weight'   => 2,
    'settings' => [],
  ])
  ->save();

echo "✓ Configured EntityViewDisplay for webform_embed\n";

// Add to landing_page allowed types
$landing_page_field = FieldConfig::loadByName('node', 'landing_page', 'field_content_sections');
$allowed_types = $landing_page_field->getSetting('handler_settings')['target_bundles'] ?? [];
$allowed_types['webform_embed'] = 'webform_embed';

$landing_page_field->setSetting('handler_settings', [
  'target_bundles'        => $allowed_types,
  'negate'                => 0,
  'target_bundles_drag_drop' => array_fill_keys(array_keys($allowed_types), ['enabled' => TRUE]),
]);
$landing_page_field->save();

echo "✓ Added webform_embed to landing_page allowed paragraph types\n";

echo "\n── Webform Paragraph Setup Complete ──\n";
echo "Run: ddev exec drush cr && ddev exec drush cex -y\n";
