<?php
/**
 * @file
 * Create breadcrumb paragraph type with link fields for navigation hierarchy.
 *
 * Supports up to 3 levels:
 * - Level 1: Home (always shown)
 * - Level 2: Parent category (optional)
 * - Level 3: Sub-category (optional)
 * - Current page: Shown as text without link
 *
 * Run: ddev exec drush php:script setup-scripts/setup-breadcrumb-paragraph.php
 */

use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

// ── Step 1: Create paragraph type ─────────────────────────────────────────

if (ParagraphsType::load('breadcrumb')) {
  echo "  [SKIP] Paragraph type 'breadcrumb' already exists.\n";
} else {
  $paragraph_type = ParagraphsType::create([
    'id' => 'breadcrumb',
    'label' => 'Breadcrumb Navigation',
    'description' => 'Breadcrumb navigation with support for up to 3 levels (Home → Parent → Category → Current).',
  ]);
  $paragraph_type->save();
  echo "  ✓ Created paragraph type: breadcrumb\n";
}

// ── Step 2: Create field storage (shared) ─────────────────────────────────

$fields = [
  'field_breadcrumb_level_2' => [
    'label' => 'Level 2 (Parent)',
    'description' => 'Optional parent category link. Example: "ADHD Overview"',
    'required' => FALSE,
  ],
  'field_breadcrumb_level_3' => [
    'label' => 'Level 3 (Category)',
    'description' => 'Optional category link. Example: "Resources"',
    'required' => FALSE,
  ],
  'field_current_page_title' => [
    'type' => 'string',
    'label' => 'Current Page Title',
    'description' => 'The text shown for the current page (no link). Example: "Register"',
    'required' => TRUE,
  ],
];

// Create link field storages
foreach (['field_breadcrumb_level_2', 'field_breadcrumb_level_3'] as $field_name) {
  if (!FieldStorageConfig::loadByName('paragraph', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'paragraph',
      'type' => 'link',
      'cardinality' => 1,
    ])->save();
    echo "  ✓ Created field storage: {$field_name}\n";
  }
}

// Create string field storage for current page title
if (!FieldStorageConfig::loadByName('paragraph', 'field_current_page_title')) {
  FieldStorageConfig::create([
    'field_name' => 'field_current_page_title',
    'entity_type' => 'paragraph',
    'type' => 'string',
    'cardinality' => 1,
    'settings' => ['max_length' => 255],
  ])->save();
  echo "  ✓ Created field storage: field_current_page_title\n";
}

// ── Step 3: Attach fields to paragraph bundle ─────────────────────────────

// Attach link fields
foreach (['field_breadcrumb_level_2', 'field_breadcrumb_level_3'] as $field_name) {
  if (!FieldConfig::loadByName('paragraph', 'breadcrumb', $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'paragraph',
      'bundle' => 'breadcrumb',
      'label' => $fields[$field_name]['label'],
      'description' => $fields[$field_name]['description'],
      'required' => $fields[$field_name]['required'],
      'settings' => [
        'link_type' => \Drupal\link\LinkItemInterface::LINK_GENERIC,
        'title' => 2,
      ],
    ])->save();
    echo "  ✓ Attached field: {$field_name}\n";
  }
}

// Attach string field
if (!FieldConfig::loadByName('paragraph', 'breadcrumb', 'field_current_page_title')) {
  FieldConfig::create([
    'field_name' => 'field_current_page_title',
    'entity_type' => 'paragraph',
    'bundle' => 'breadcrumb',
    'label' => $fields['field_current_page_title']['label'],
    'description' => $fields['field_current_page_title']['description'],
    'required' => $fields['field_current_page_title']['required'],
  ])->save();
  echo "  ✓ Attached field: field_current_page_title\n";
}

// ── Step 4: Configure EntityFormDisplay ───────────────────────────────────

$form_display = EntityFormDisplay::load('paragraph.breadcrumb.default');
if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'breadcrumb',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

$form_display
  ->setComponent('field_breadcrumb_level_2', [
    'type' => 'link_default',
    'weight' => 0,
    'settings' => [
      'placeholder_url' => '/adhd-overview',
      'placeholder_title' => 'ADHD Overview',
    ],
  ])
  ->setComponent('field_breadcrumb_level_3', [
    'type' => 'link_default',
    'weight' => 1,
    'settings' => [
      'placeholder_url' => '/resources',
      'placeholder_title' => 'Resources',
    ],
  ])
  ->setComponent('field_current_page_title', [
    'type' => 'string_textfield',
    'weight' => 2,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Register',
    ],
  ])
  ->save();

echo "  ✓ Configured form display\n";

// ── Step 5: Configure EntityViewDisplay ───────────────────────────────────

$view_display = EntityViewDisplay::load('paragraph.breadcrumb.default');
if (!$view_display) {
  $view_display = EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => 'breadcrumb',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

$view_display
  ->setComponent('field_breadcrumb_level_2', [
    'type' => 'link',
    'label' => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_breadcrumb_level_3', [
    'type' => 'link',
    'label' => 'hidden',
    'weight' => 1,
  ])
  ->setComponent('field_current_page_title', [
    'type' => 'string',
    'label' => 'hidden',
    'weight' => 2,
  ])
  ->save();

echo "  ✓ Configured view display\n";

echo "\n✅ Breadcrumb paragraph type created successfully!\n\n";
echo "Field Structure:\n";
echo "  - Home: Always shown (hardcoded in template)\n";
echo "  - Level 2: field_breadcrumb_level_2 (optional)\n";
echo "  - Level 3: field_breadcrumb_level_3 (optional)\n";
echo "  - Current: field_current_page_title (required)\n\n";
echo "Example breadcrumb paths:\n";
echo "  - Home → Register\n";
echo "  - Home → ADHD Overview → Register\n";
echo "  - Home → ADHD Overview → Resources → Register\n\n";
echo "Next: Create template at web/themes/custom/cingulate/templates/paragraphs/paragraph--breadcrumb.html.twig\n";
echo "Then: Run 'drush cr' to clear cache\n";
