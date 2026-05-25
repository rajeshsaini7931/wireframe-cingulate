<?php

/**
 * @file
 * Setup script for Landing Page content type and Hero paragraph.
 *
 * Creates:
 * - landing_page content type with field_content_sections
 * - hero paragraph type with fields
 * - EntityFormDisplay and EntityViewDisplay configurations
 * - Home page node with hero paragraph
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-landing-page-hero.php
 */

use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Landing Page + Hero Paragraph Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 1 — Create landing_page content type
// ────────────────────────────────────────────────────────────────────────────
echo "[1/8] Creating landing_page content type...\n";

if (NodeType::load('landing_page')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  NodeType::create([
    'type'        => 'landing_page',
    'name'        => 'Landing Page',
    'description' => 'Flexible page layout using paragraph components.',
  ])->save();
  echo "  ✓ Created landing_page content type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 2 — Create field_content_sections (entity_reference_revisions → paragraph)
// ────────────────────────────────────────────────────────────────────────────
echo "[2/8] Creating field_content_sections...\n";

if (!FieldStorageConfig::loadByName('node', 'field_content_sections')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_content_sections',
    'entity_type' => 'node',
    'type'        => 'entity_reference_revisions',
    'cardinality' => -1, // Unlimited
    'settings'    => [
      'target_type' => 'paragraph',
    ],
  ])->save();
  echo "  ✓ Created field storage: field_content_sections\n";
}

if (!FieldConfig::loadByName('node', 'landing_page', 'field_content_sections')) {
  FieldConfig::create([
    'field_name'  => 'field_content_sections',
    'entity_type' => 'node',
    'bundle'      => 'landing_page',
    'label'       => 'Content Sections',
    'required'    => FALSE,
    'settings'    => [
      'handler'          => 'default:paragraph',
      'handler_settings' => [
        'negate'         => 0,
        'target_bundles' => NULL, // All paragraph types allowed
        'target_bundles_drag_drop' => [],
      ],
    ],
  ])->save();
  echo "  ✓ Attached field_content_sections to landing_page\n";
}

echo "\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 3 — Configure landing_page EntityFormDisplay
// ────────────────────────────────────────────────────────────────────────────
echo "[3/8] Configuring landing_page EntityFormDisplay...\n";

$node_form_display = EntityFormDisplay::load('node.landing_page.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle'           => 'landing_page',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$node_form_display
  ->setComponent('title', [
    'type'   => 'string_textfield',
    'weight' => 0,
  ])
  ->setComponent('field_content_sections', [
    'type'   => 'paragraphs',
    'weight' => 1,
    'settings' => [
      'title'                  => 'Section',
      'title_plural'           => 'Sections',
      'edit_mode'              => 'open',
      'closed_mode'            => 'summary',
      'autocollapse'           => 'none',
      'add_mode'               => 'dropdown',
      'form_display_mode'      => 'default',
      'default_paragraph_type' => '',
    ],
  ])
  ->save();

echo "  ✓ EntityFormDisplay configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 4 — Configure landing_page EntityViewDisplay
// ────────────────────────────────────────────────────────────────────────────
echo "[4/8] Configuring landing_page EntityViewDisplay...\n";

$node_view_display = EntityViewDisplay::load('node.landing_page.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle'           => 'landing_page',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$node_view_display
  ->removeComponent('title') // Title rendered by page template
  ->setComponent('field_content_sections', [
    'type'     => 'entity_reference_revisions_entity_view',
    'label'    => 'hidden',
    'weight'   => 0,
    'settings' => [
      'view_mode' => 'default',
    ],
  ])
  ->save();

echo "  ✓ EntityViewDisplay configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 5 — Create hero paragraph type
// ────────────────────────────────────────────────────────────────────────────
echo "[5/8] Creating hero paragraph type...\n";

if (ParagraphsType::load('hero')) {
  echo "  → Already exists — skipping.\n\n";
}
else {
  ParagraphsType::create([
    'id'          => 'hero',
    'label'       => 'Hero Banner',
    'description' => 'Main hero section with heading and body text.',
  ])->save();
  echo "  ✓ Created hero paragraph type.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// STEP 6 — Create hero paragraph fields
// ────────────────────────────────────────────────────────────────────────────
echo "[6/8] Creating fields for hero paragraph...\n";

$hero_fields = [
  'field_heading' => [
    'type'        => 'string',
    'label'       => 'Heading',
    'required'    => TRUE,
    'max_length'  => 255,
  ],
  'field_body' => [
    'type'        => 'text_long',
    'label'       => 'Body',
    'required'    => TRUE,
  ],
];

foreach ($hero_fields as $field_name => $config) {
  // Create field storage.
  if (!FieldStorageConfig::loadByName('paragraph', $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'type'        => $config['type'],
      'cardinality' => 1,
      'settings'    => $config['type'] === 'string' ? ['max_length' => $config['max_length']] : [],
    ])->save();
    echo "  ✓ Created field storage: {$field_name}\n";
  }

  // Attach to hero bundle.
  if (!FieldConfig::loadByName('paragraph', 'hero', $field_name)) {
    FieldConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'bundle'      => 'hero',
      'label'       => $config['label'],
      'required'    => $config['required'],
    ])->save();
    echo "  ✓ Attached {$field_name} to hero\n";
  }
}

echo "\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 7 — Configure hero paragraph displays
// ────────────────────────────────────────────────────────────────────────────
echo "[7/8] Configuring hero paragraph displays...\n";

// Form display
$para_form_display = EntityFormDisplay::load('paragraph.hero.default')
  ?? EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'hero',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$para_form_display
  ->setComponent('field_heading', [
    'type'   => 'string_textfield',
    'weight' => 0,
    'settings' => [
      'size' => 60,
      'placeholder' => 'Hero Banner',
    ],
  ])
  ->setComponent('field_body', [
    'type'   => 'text_textarea',
    'weight' => 1,
    'settings' => [
      'rows' => 5,
      'placeholder' => 'Enter hero description...',
    ],
  ])
  ->save();

// View display
$para_view_display = EntityViewDisplay::load('paragraph.hero.default')
  ?? EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle'           => 'hero',
    'mode'             => 'default',
    'status'           => TRUE,
  ]);

$para_view_display
  ->setComponent('field_heading', [
    'type'   => 'string',
    'label'  => 'hidden',
    'weight' => 0,
  ])
  ->setComponent('field_body', [
    'type'   => 'text_default',
    'label'  => 'hidden',
    'weight' => 1,
  ])
  ->save();

echo "  ✓ Hero paragraph displays configured.\n\n";

// ────────────────────────────────────────────────────────────────────────────
// STEP 8 — Create Home page node with hero paragraph
// ────────────────────────────────────────────────────────────────────────────
echo "[8/8] Creating Home page node...\n";

$existing = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['type' => 'landing_page', 'title' => 'Home']);

if (empty($existing)) {
  // Create hero paragraph
  $hero_paragraph = Paragraph::create([
    'type' => 'hero',
    'field_heading' => 'Hero Banner',
    'field_body' => [
      'value' => 'Cingulate is developing ADHD medications capable of achieving precise once-daily dosing using a novel erosion based controlled release technology that provides unrivaled control of drug release.',
      'format' => 'basic_html',
    ],
  ]);
  $hero_paragraph->save();

  // Create Home node
  $home_node = Node::create([
    'type'  => 'landing_page',
    'title' => 'Home',
    'field_content_sections' => [
      [
        'target_id' => $hero_paragraph->id(),
        'target_revision_id' => $hero_paragraph->getRevisionId(),
      ],
    ],
    'status' => 1,
    'promote' => 1,
  ]);
  $home_node->save();

  echo "  ✓ Created Home page (node/{$home_node->id()}) with hero paragraph.\n\n";
}
else {
  $home_node = reset($existing);
  echo "  → Home page already exists (node/{$home_node->id()}) — skipping.\n\n";
}

// ────────────────────────────────────────────────────────────────────────────
// SUMMARY
// ────────────────────────────────────────────────────────────────────────────
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Landing Page content type created\n";
echo "  ✓ Hero paragraph type created with 2 fields\n";
echo "  ✓ Home page node created with hero paragraph\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Next steps:\n";
echo "  1. Create template: web/themes/custom/cingulate/templates/paragraphs/paragraph--hero.html.twig\n";
echo "  2. Create CSS: web/themes/custom/cingulate/css/components/hero.css\n";
echo "  3. Add library to cingulate.libraries.yml\n";
echo "  4. Run: ddev exec drush cr\n\n";
