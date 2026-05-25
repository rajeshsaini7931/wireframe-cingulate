<?php

/**
 * @file
 * Switch to Webform Node approach - attach webform directly to landing_page.
 *
 * Creates:
 * - field_webform on landing_page content type
 * - Configures display settings
 * - Updates Register page to use direct webform field
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/switch-to-webform-node.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\node\Entity\Node;

// Create webform field storage
if (!FieldStorageConfig::loadByName('node', 'field_webform')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_webform',
    'entity_type' => 'node',
    'type'        => 'webform',
    'cardinality' => 1,
  ])->save();
  echo "✓ Created field storage: field_webform\n";
}

// Attach webform field to landing_page
if (!FieldConfig::loadByName('node', 'landing_page', 'field_webform')) {
  FieldConfig::create([
    'field_name'  => 'field_webform',
    'entity_type' => 'node',
    'bundle'      => 'landing_page',
    'label'       => 'Webform',
    'required'    => FALSE,
    'settings'    => [],
  ])->save();
  echo "✓ Attached field_webform to landing_page\n";
}

// Configure form display
$form_display = EntityFormDisplay::load('node.landing_page.default');
if ($form_display) {
  $form_display->setComponent('field_webform', [
    'type'   => 'webform_entity_reference_select',
    'weight' => 10,
    'settings' => [],
  ])->save();
  echo "✓ Configured form display for field_webform\n";
}

// Configure view display - this is critical for inline rendering
$view_display = EntityViewDisplay::load('node.landing_page.default');
if ($view_display) {
  $view_display->setComponent('field_webform', [
    'type'   => 'webform_entity_reference_entity_view',
    'label'  => 'hidden',
    'weight' => 20,
    'settings' => [
      'view_mode' => 'default',
    ],
  ])->save();
  echo "✓ Configured view display for field_webform\n";
}

// Update Register page (node 3) to use webform field
$register_page = Node::load(3);
if ($register_page) {
  $register_page->set('field_webform', 'register_form');
  $register_page->save();
  echo "✓ Updated Register page to use field_webform\n";
}

echo "\n── Webform Node Approach Configured ──\n";
echo "Run: ddev exec drush cr\n";
