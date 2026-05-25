<?php

/**
 * @file
 * Create new Register page (node 4) with proper paragraph structure.
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/create-new-register-page.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;

// Delete existing alias if it exists
$existing_aliases = \Drupal::entityTypeManager()
  ->getStorage('path_alias')
  ->loadByProperties(['alias' => '/register']);
foreach ($existing_aliases as $alias) {
  $alias->delete();
}

// Create secondary_hero paragraph
$secondary_hero = Paragraph::create([
  'type'          => 'secondary_hero',
  'field_heading' => 'Register',
]);
$secondary_hero->save();

echo "✓ Created secondary_hero paragraph (id: {$secondary_hero->id()})\n";

// Create webform_section paragraph
$webform_section = Paragraph::create([
  'type'                    => 'webform_section',
  'field_section_title'     => 'Stay Informed on ADHD',
  'field_section_subtitle'  => 'Register to receive the latest updates, research, & resources',
  'field_webform_reference' => ['target_id' => 'register_form'],
]);
$webform_section->save();

echo "✓ Created webform_section paragraph (id: {$webform_section->id()})\n";

// Create landing page node
$register_page = Node::create([
  'type'                  => 'landing_page',
  'title'                 => 'Register',
  'status'                => 1,
  'uid'                   => 1,
  'field_content_sections' => [
    [
      'target_id'          => $secondary_hero->id(),
      'target_revision_id' => $secondary_hero->getRevisionId(),
    ],
    [
      'target_id'          => $webform_section->id(),
      'target_revision_id' => $webform_section->getRevisionId(),
    ],
  ],
]);
$register_page->save();

echo "✓ Created landing_page node (nid: {$register_page->id()})\n";

// Create path alias
$path_alias = PathAlias::create([
  'path'     => '/node/' . $register_page->id(),
  'alias'    => '/register',
  'langcode' => 'en',
]);
$path_alias->save();

echo "✓ Created path alias: /register → /node/{$register_page->id()}\n";

echo "\n── New Register Page Created ──\n";
echo "URL: https://wireframe-cingulate.ddev.site/register\n";
echo "Run: ddev exec drush cr\n";
