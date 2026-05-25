<?php

/**
 * @file
 * Clean up Register page - remove webform paragraph, keep only field_webform.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/cleanup-register-page.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

// Load Register page
$register_page = Node::load(3);

if ($register_page) {
  // Get current paragraphs
  $current_paragraphs = $register_page->get('field_content_sections')->getValue();
  
  // Keep only secondary_hero, remove webform_embed  
  $keep_paragraphs = [];
  foreach ($current_paragraphs as $para_ref) {
    $para = Paragraph::load($para_ref['target_id']);
    if ($para && $para->bundle() === 'secondary_hero') {
      $keep_paragraphs[] = $para_ref;
    }
  }
  
  // Update node with only secondary_hero
  $register_page->set('field_content_sections', $keep_paragraphs);
  
  // Make sure field_webform is set
  $register_page->set('field_webform', 'register_form');
  
  $register_page->save();
  
  echo "✓ Cleaned up Register page - removed webform_embed paragraph\n";
  echo "✓ field_content_sections now has only secondary_hero\n";
  echo "✓ field_webform is set to register_form\n";
}

echo "\n── Register Page Cleanup Complete ──\n";
echo "Run: ddev exec drush cr\n";
