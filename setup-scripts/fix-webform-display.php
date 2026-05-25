<?php

/**
 * @file
 * Fix webform display to show inline form instead of link.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/fix-webform-display.php
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;

// Fix the webform_embed paragraph view display
$view_display = EntityViewDisplay::load('paragraph.webform_embed.default');

if ($view_display) {
  // Update webform reference field to use entity_view formatter (not label)
  $view_display->setComponent('field_webform_reference', [
    'type'     => 'entity_reference_entity_view',
    'label'    => 'hidden',
    'weight'   => 2,
    'settings' => [
      'view_mode' => 'default',
      'link'      => FALSE,
    ],
  ]);
  
  $view_display->save();
  echo "✓ Updated webform reference field formatter to entity_reference_entity_view\n";
}

echo "\n── Webform Display Fixed ──\n";
echo "Run: ddev exec drush cr\n";
