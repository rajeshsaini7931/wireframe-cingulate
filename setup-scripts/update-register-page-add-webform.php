<?php

/**
 * @file
 * Update Register page to add webform_embed paragraph.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/update-register-page-add-webform.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

// Load the Register page (node 3)
$register_page = Node::load(3);

if (!$register_page) {
  echo "[ERROR] Register page node not found (nid 3)\n";
  return;
}

// Create webform_embed paragraph
$webform_paragraph = Paragraph::create([
  'type'                    => 'webform_embed',
  'field_webform_reference' => ['target_id' => 'register_form'],
  'field_section_title'     => 'Stay in the Loop',
  'field_section_subtitle'  => 'Get the latest on ADHD, from medical news to personal insights.',
]);
$webform_paragraph->save();

echo "✓ Created webform_embed paragraph (id: {$webform_paragraph->id()})\n";

// Get existing paragraphs
$existing_paragraphs = $register_page->get('field_content_sections')->getValue();

// Add webform paragraph to the end
$existing_paragraphs[] = [
  'target_id'          => $webform_paragraph->id(),
  'target_revision_id' => $webform_paragraph->getRevisionId(),
];

// Update the node
$register_page->set('field_content_sections', $existing_paragraphs);
$register_page->save();

echo "✓ Updated Register page (nid: {$register_page->id()})\n";
echo "✓ Added webform_embed paragraph after secondary_hero\n";

echo "\n── Register Page Update Complete ──\n";
echo "View at: https://wireframe-cingulate.ddev.site/register\n";
echo "Run: ddev exec drush cr && ddev exec drush cex -y\n";
