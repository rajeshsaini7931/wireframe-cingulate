<?php

/**
 * @file
 * Setup script: Create block_content types for site header and footer.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-header-footer-types.php
 */

use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "=== Creating Site Header and Footer Block Content Types ===\n\n";

// ============================================================================
// STEP 1: Create site_header Block Content Type
// ============================================================================

echo "[1/2] Creating site_header block content type...\n";

if (!BlockContentType::load('site_header')) {
  $site_header_type = BlockContentType::create([
    'id' => 'site_header',
    'label' => 'Site Header',
    'description' => 'Header block with HCP notice, site name, navigation menu, and register CTA.',
    'revision' => FALSE,
  ]);
  $site_header_type->save();
  echo "  ✓ Block content type 'site_header' created.\n";
}
else {
  echo "  ⊙ Block content type 'site_header' already exists — skipping creation.\n";
}

// ── Field: field_hcp_notice (string) ─────────────────────────────────────
echo "  Creating field_hcp_notice...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_hcp_notice')) {
  FieldStorageConfig::create([
    'field_name' => 'field_hcp_notice',
    'entity_type' => 'block_content',
    'type' => 'string',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_header', 'field_hcp_notice')) {
  FieldConfig::create([
    'field_name' => 'field_hcp_notice',
    'entity_type' => 'block_content',
    'bundle' => 'site_header',
    'label' => 'HCP Notice',
    'description' => 'Text displayed in the utility bar (e.g., "This site is intended for US HCPs").',
    'required' => FALSE,
    'settings' => ['max_length' => 255],
  ])->save();
  echo "    ✓ field_hcp_notice created.\n";
}

// ── Field: field_site_name (string) ──────────────────────────────────────
echo "  Creating field_site_name...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_site_name')) {
  FieldStorageConfig::create([
    'field_name' => 'field_site_name',
    'entity_type' => 'block_content',
    'type' => 'string',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_header', 'field_site_name')) {
  FieldConfig::create([
    'field_name' => 'field_site_name',
    'entity_type' => 'block_content',
    'bundle' => 'site_header',
    'label' => 'Site Name',
    'description' => 'Site name displayed in the header (e.g., "ADHD ENGAGE"). Falls back to system site name if empty.',
    'required' => FALSE,
    'settings' => ['max_length' => 255],
  ])->save();
  echo "    ✓ field_site_name created.\n";
}

// ── Field: field_header_logo (entity_reference → media) ──────────────────
echo "  Creating field_header_logo...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_header_logo')) {
  FieldStorageConfig::create([
    'field_name' => 'field_header_logo',
    'entity_type' => 'block_content',
    'type' => 'entity_reference',
    'settings' => ['target_type' => 'media'],
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_header', 'field_header_logo')) {
  FieldConfig::create([
    'field_name' => 'field_header_logo',
    'entity_type' => 'block_content',
    'bundle' => 'site_header',
    'label' => 'Header Logo',
    'description' => 'Logo image displayed in the header. If empty, site name text is used.',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:media',
      'handler_settings' => [
        'target_bundles' => ['image' => 'image'],
        'sort' => ['field' => '_none'],
        'auto_create' => FALSE,
      ],
    ],
  ])->save();
  echo "    ✓ field_header_logo created.\n";
}

// ── Field: field_header_menu (entity_reference → menu) ───────────────────
echo "  Creating field_header_menu...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_header_menu')) {
  FieldStorageConfig::create([
    'field_name' => 'field_header_menu',
    'entity_type' => 'block_content',
    'type' => 'entity_reference',
    'settings' => ['target_type' => 'menu'],
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_header', 'field_header_menu')) {
  FieldConfig::create([
    'field_name' => 'field_header_menu',
    'entity_type' => 'block_content',
    'bundle' => 'site_header',
    'label' => 'Header Menu',
    'description' => 'Main navigation menu (select "Main navigation" or create custom menu).',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:menu',
    ],
  ])->save();
  echo "    ✓ field_header_menu created.\n";
}

// ── Field: field_register_cta_link (link) ────────────────────────────────
echo "  Creating field_register_cta_link...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_register_cta_link')) {
  FieldStorageConfig::create([
    'field_name' => 'field_register_cta_link',
    'entity_type' => 'block_content',
    'type' => 'link',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_header', 'field_register_cta_link')) {
  FieldConfig::create([
    'field_name' => 'field_register_cta_link',
    'entity_type' => 'block_content',
    'bundle' => 'site_header',
    'label' => 'Register CTA Link',
    'description' => 'Link to the registration page (URL + button text).',
    'required' => FALSE,
    'settings' => [
      'link_type' => 1,  // LINK_GENERIC
      'title' => 1,      // TITLE_REQUIRED
    ],
  ])->save();
  echo "    ✓ field_register_cta_link created.\n";
}

// ── Configure Form Display for site_header ──────────────────────────────
echo "  Configuring form display...\n";
$header_form_display = EntityFormDisplay::load('block_content.site_header.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle' => 'site_header',
    'mode' => 'default',
    'status' => TRUE,
  ]);

$header_form_display
  ->setComponent('field_hcp_notice', [
    'type' => 'string_textfield',
    'weight' => 0,
  ])
  ->setComponent('field_site_name', [
    'type' => 'string_textfield',
    'weight' => 1,
  ])
  ->setComponent('field_header_logo', [
    'type' => 'media_library_widget',
    'weight' => 2,
    'settings' => ['media_types' => ['image']],
  ])
  ->setComponent('field_header_menu', [
    'type' => 'options_select',
    'weight' => 3,
  ])
  ->setComponent('field_register_cta_link', [
    'type' => 'link_default',
    'weight' => 4,
  ])
  ->save();
echo "    ✓ Form display configured.\n";

// ── Configure View Display for site_header ──────────────────────────────
echo "  Configuring view display...\n";
$header_view_display = EntityViewDisplay::load('block_content.site_header.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle' => 'site_header',
    'mode' => 'default',
    'status' => TRUE,
  ]);

$header_view_display
  ->setComponent('field_hcp_notice', [
    'type' => 'string',
    'label' => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_site_name', [
    'type' => 'string',
    'label' => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_header_logo', [
    'type' => 'entity_reference_entity_view',
    'label' => 'hidden',
    'weight' => 2,
    'settings' => ['view_mode' => 'default'],
  ])
  ->setComponent('field_header_menu', [
    'type' => 'entity_reference_label',
    'label' => 'hidden',
    'weight' => 3,
  ])
  ->setComponent('field_register_cta_link', [
    'type' => 'link',
    'label' => 'hidden',
    'weight' => 4,
  ])
  ->save();
echo "    ✓ View display configured.\n\n";

// ============================================================================
// STEP 2: Create site_footer Block Content Type
// ============================================================================

echo "[2/2] Creating site_footer block content type...\n";

if (!BlockContentType::load('site_footer')) {
  $site_footer_type = BlockContentType::create([
    'id' => 'site_footer',
    'label' => 'Site Footer',
    'description' => 'Footer block with logo, legal navigation menu, and copyright text.',
    'revision' => FALSE,
  ]);
  $site_footer_type->save();
  echo "  ✓ Block content type 'site_footer' created.\n";
}
else {
  echo "  ⊙ Block content type 'site_footer' already exists — skipping creation.\n";
}

// ── Field: field_footer_logo (entity_reference → media) ──────────────────
echo "  Creating field_footer_logo...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_footer_logo')) {
  FieldStorageConfig::create([
    'field_name' => 'field_footer_logo',
    'entity_type' => 'block_content',
    'type' => 'entity_reference',
    'settings' => ['target_type' => 'media'],
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_footer', 'field_footer_logo')) {
  FieldConfig::create([
    'field_name' => 'field_footer_logo',
    'entity_type' => 'block_content',
    'bundle' => 'site_footer',
    'label' => 'Footer Logo',
    'description' => 'Company logo displayed in the footer.',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:media',
      'handler_settings' => [
        'target_bundles' => ['image' => 'image'],
        'sort' => ['field' => '_none'],
        'auto_create' => FALSE,
      ],
    ],
  ])->save();
  echo "    ✓ field_footer_logo created.\n";
}

// ── Field: field_footer_menu (entity_reference → menu) ───────────────────
echo "  Creating field_footer_menu...\n";
// Check if storage exists (may be shared with header menu field)
if (!FieldStorageConfig::loadByName('block_content', 'field_footer_menu')) {
  FieldStorageConfig::create([
    'field_name' => 'field_footer_menu',
    'entity_type' => 'block_content',
    'type' => 'entity_reference',
    'settings' => ['target_type' => 'menu'],
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_footer', 'field_footer_menu')) {
  FieldConfig::create([
    'field_name' => 'field_footer_menu',
    'entity_type' => 'block_content',
    'bundle' => 'site_footer',
    'label' => 'Footer Menu',
    'description' => 'Legal navigation menu (select "Footer" or create custom menu).',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:menu',
    ],
  ])->save();
  echo "    ✓ field_footer_menu created.\n";
}

// ── Field: field_copyright_text (text_long) ──────────────────────────────
echo "  Creating field_copyright_text...\n";
if (!FieldStorageConfig::loadByName('block_content', 'field_copyright_text')) {
  FieldStorageConfig::create([
    'field_name' => 'field_copyright_text',
    'entity_type' => 'block_content',
    'type' => 'text_long',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'site_footer', 'field_copyright_text')) {
  FieldConfig::create([
    'field_name' => 'field_copyright_text',
    'entity_type' => 'block_content',
    'bundle' => 'site_footer',
    'label' => 'Copyright Text',
    'description' => 'Footer copyright and legal text (supports basic HTML).',
    'required' => FALSE,
  ])->save();
  echo "    ✓ field_copyright_text created.\n";
}

// ── Configure Form Display for site_footer ──────────────────────────────
echo "  Configuring form display...\n";
$footer_form_display = EntityFormDisplay::load('block_content.site_footer.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle' => 'site_footer',
    'mode' => 'default',
    'status' => TRUE,
  ]);

$footer_form_display
  ->setComponent('field_footer_logo', [
    'type' => 'media_library_widget',
    'weight' => 0,
    'settings' => ['media_types' => ['image']],
  ])
  ->setComponent('field_footer_menu', [
    'type' => 'options_select',
    'weight' => 1,
  ])
  ->setComponent('field_copyright_text', [
    'type' => 'text_textarea',
    'weight' => 2,
  ])
  ->save();
echo "    ✓ Form display configured.\n";

// ── Configure View Display for site_footer ──────────────────────────────
echo "  Configuring view display...\n";
$footer_view_display = EntityViewDisplay::load('block_content.site_footer.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'block_content',
    'bundle' => 'site_footer',
    'mode' => 'default',
    'status' => TRUE,
  ]);

$footer_view_display
  ->setComponent('field_footer_logo', [
    'type' => 'entity_reference_entity_view',
    'label' => 'hidden',
    'weight' => 0,
    'settings' => ['view_mode' => 'default'],
  ])
  ->setComponent('field_footer_menu', [
    'type' => 'entity_reference_label',
    'label' => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_copyright_text', [
    'type' => 'text_default',
    'label' => 'hidden',
    'weight' => 2,
  ])
  ->save();
echo "    ✓ View display configured.\n\n";

echo "=== Setup Complete ===\n";
echo "✓ site_header block content type created with 5 fields.\n";
echo "✓ site_footer block content type created with 3 fields.\n";
echo "\nNext steps:\n";
echo "1. Run: drush cr\n";
echo "2. Run: drush cex -y (export configuration)\n";
echo "3. Run setup-navigation.php to create menus\n";
echo "4. Run setup-header-footer-blocks.php to create block instances\n";
