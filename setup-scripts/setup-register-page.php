<?php

/**
 * @file
 * Setup script for Register page node.
 *
 * Creates:
 * - Landing Page node with path /register
 * - Secondary Hero paragraph with "Register" title
 * - Webform block placement inline
 *
 * Run: ddev exec drush php:script /var/www/html/setup-scripts/setup-register-page.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;

// Create Secondary Hero paragraph
$secondary_hero = Paragraph::create([
  'type'          => 'secondary_hero',
  'field_heading' => 'Register',
]);
$secondary_hero->save();

echo "✓ Created secondary_hero paragraph\n";

// Create the Register page node
$register_page = Node::create([
  'type'                  => 'landing_page',
  'title'                 => 'Register',
  'status'                => 1,
  'uid'                   => 1,
  'field_content_sections' => [
    [
      'target_id'        => $secondary_hero->id(),
      'target_revision_id' => $secondary_hero->getRevisionId(),
    ],
  ],
]);

$register_page->save();

echo "✓ Created landing_page node (nid: {$register_page->id()})\n";

// Create path alias /register
$path_alias = PathAlias::create([
  'path'     => '/node/' . $register_page->id(),
  'alias'    => '/register',
  'langcode' => 'en',
]);
$path_alias->save();

echo "✓ Created path alias: /register\n";

echo "\n── Register Page Setup Complete ──\n";
echo "View at: https://wireframe-cingulate.ddev.site/register\n";
echo "Edit at: /node/{$register_page->id()}/edit\n";
echo "\nNOTE: You need to manually add the Webform block via the UI:\n";
echo "1. Navigate to /node/{$register_page->id()}/edit\n";
echo "2. Edit field_content_sections\n";
echo "3. Add a 'Webform' paragraph type (create this if needed)\n";
echo "4. OR place the webform block in the 'content' region for this page\n";
echo "\nRun: ddev exec drush cr && ddev exec drush cex -y\n";
