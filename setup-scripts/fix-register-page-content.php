<?php

/**
 * @file
 * Fix Register page content to match wireframe.
 *
 * Updates:
 * - Webform paragraph title/subtitle to match wireframe
 * - Webform view display to hide title
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/fix-register-page-content.php
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

// Update the webform_embed paragraph (id: 8)
$webform_paragraph = Paragraph::load(8);

if ($webform_paragraph) {
  $webform_paragraph->set('field_section_title', 'Stay Informed on ADHD');
  $webform_paragraph->set('field_section_subtitle', 'Register to receive the latest updates, research, & resources');
  $webform_paragraph->save();
  echo "✓ Updated webform_embed paragraph with correct title/subtitle\n";
}
else {
  echo "[ERROR] Webform paragraph not found (id: 8)\n";
}

// Configure webform field display to hide title
$view_display = EntityViewDisplay::load('paragraph.webform_embed.default');

if ($view_display) {
  $component = $view_display->getComponent('field_webform_reference');
  $component['settings']['view_mode'] = 'default';
  $component['label'] = 'hidden';
  
  $view_display->setComponent('field_webform_reference', $component);
  $view_display->save();
  echo "✓ Configured field display to hide labels\n";
}

echo "\n── Register Page Content Fixed ──\n";
echo "Run: ddev exec drush cr\n";
